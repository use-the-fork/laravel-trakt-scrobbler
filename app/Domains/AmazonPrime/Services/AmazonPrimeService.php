<?php

namespace App\Domains\AmazonPrime\Services;

use Carbon\Carbon;
use HeadlessChromium\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Domains\Common\Models\Show;
use App\Domains\Common\Models\Movie;
use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Service;
use App\Domains\Trakt\Jobs\ProcessMovie;
use App\Domains\Trakt\Jobs\ProcessEpisode;
use HeadlessChromium\Communication\Message;
use App\Domains\Common\Services\StreamingService;
use App\Domains\AmazonPrime\Enums\AmazonPrimeConstant;

class AmazonPrimeService extends StreamingService
{
    private $HOST_URL = 'https://www.primevideo.com';
    private $API_URL = 'https://atv-ps.primevideo.com';
    private $SETTINGS_URL;
    private $PROFILE_URL;
    private $HISTORY_URL;
    private $ENRICHMENTS_URL;

    /**
     * These values were retrieved by watching network requests.
     */
    private $DEVICE_TYPE_ID = 'AOAGZA014O5RE';
    private $DEVICE_ID;

    public function __construct()
    {
        $this->SERVICE = Service::where('name', 'amazon prime')->firstOrFail();
        $this->SETTINGS_URL = "{$this->HOST_URL}/settings";
        $this->PROFILE_URL = "{$this->HOST_URL}/gp/video/api/getProfiles";
        $this->HISTORY_URL = "{$this->HOST_URL}/gp/video/api/getWatchHistorySettingsPage?widgets=activity-history&widgetArgs=%7B%22startIndex%22%3A{index}%7D";
        $this->ENRICHMENTS_URL = "{$this->HOST_URL}/gp/video/api/enrichItemMetadata?metadataToEnrich=%7B%22playback%22%3Atrue%7D&titleIDsToEnrich=%5B{ids}%5D";

        parent::__construct();
    }

    public function isActive()
    {

        return $this->activate();
    }

    public function activate()
    {

        if (
            isset($this->SERVICE['config']['auth']['expires_at']) &&
            !(Carbon::parse($this->SERVICE['config']['auth']['expires_at']))->isPast() &&
            !empty($this->SERVICE['config']['auth']['device_id'])
        ) {

            $this->DEVICE_ID = $this->SERVICE['config']['auth']['device_id'];
            $this->isActivated = TRUE;
            return TRUE;
        }

        $page = $this->browser->createPage();
        //Send user to profile page to see if a login is needed
        $navigation = $page->navigate('https://www.amazon.com/gp/video/profiles/ref=atv_pr_nv_mng?step=manage');
        $navigation->waitForNavigation();

        //Check if we need to login
        if (
            Str::contains($page->getCurrentUrl(), 'ap/signin')
        ) {
            throw new \Exception('Amazon Prime has logged out. Run Setup script. service:amazon-prime:activate');
        }


        // $page->screenshot()->saveToFile(storage_path() . '/bar.png');

        // get page title
        $this->DEVICE_ID = $page->evaluate('window.localStorage.atvwebplayersdk_atvwebplayer_deviceid')->getReturnValue();

        //Save this item in the database for later

        $config = $this->SERVICE['config'];
        $config['auth']['device_id'] = $this->DEVICE_ID;
        $config['auth']['expires_at'] = Carbon::now()->addDay(7)->format('c');

        $this->SERVICE['config'] = $config;
        $this->SERVICE->save();
        $this->SERVICE->refresh();

        if (
            !empty($this->DEVICE_ID)
        ) {
            $this->isActivated = TRUE;
        }

        return TRUE;
    }

    public function loadHistoryItems($pagesToSync = 10)
    {


        if (!$this->isActivated) {
            $this->activate();
        }

        $lastSync = Carbon::parse($this->SERVICE->config['lastHistorySync']);
        $updatedLastSync = Carbon::parse($this->SERVICE->config['lastHistorySync']);

        $page = $this->browser->createPage();

        $page->navigate(AmazonPrimeConstant::WATCHED_HISTORY_URL)->waitForNavigation();
        sleep(2);
        $page->getSession()->sendMessageSync(new Message('Network.setExtraHTTPHeaders', [
            'headers' => [
                'x-requested-with' => 'XMLHttpRequest',
            ]
        ]));

        $historyItems = collect([]);
        $nextToken = null;

        for ($x = 0; $x <= $pagesToSync; $x++) {

            $page->navigate("https://www.amazon.com/gp/video/api/getWatchHistorySettingsPage?widgetArgs=%7B{$nextToken}%7D")->waitForNavigation();
            $history = json_decode($page->evaluate('document.body.innerText')->getReturnValue(), TRUE);
            $nextToken = $this->getNextToken($history);

            if (isset($history['widgets'])) {
                foreach ($history['widgets'] as $d) {
                    if ($d['widgetType'] == 'watch-history') {
                        foreach ($d['content']['content']['titles'] as $s) {
                            if (isset($s['titles']))
                                foreach ($s['titles'] as $title) {
                                    $historyItem = [
                                        'id' => $title['gti'],
                                        'title' => $title['title']['text'],
                                        'href' => $title['title']['href'],
                                        'percentWatched' => 0,
                                        'watchedAt' => Carbon::parse(
                                            $this->convertUnixDate($title['time'])
                                        ),
                                    ];

                                    $historyItems->push($historyItem);

                                    // new items since date is past last sync
                                    if ($historyItem['watchedAt']->greaterThan($updatedLastSync)) {
                                        $updatedLastSync = $historyItem['watchedAt'];
                                    }

                                    //If the watched at date is less then the last sync then break;
                                    if ($historyItem['watchedAt']->lessThan($lastSync)) {
                                        //break;
                                    }
                                }
                        }
                    }
                }
            }


            if (!$nextToken) {
                break;
            }
        }

        $historyItems = $historyItems->map(function ($historyItem) use ($page) {
            $page->navigate("https://www.amazon.com/gp/video/api/enrichItemMetadata?metadataToEnrich=%7B%22playback%22%3Atrue%7D&titleIDsToEnrich=%5B%22{$historyItem['id']}%22%5D")->waitForNavigation();
            $history = json_decode($page->evaluate('document.body.innerText')->getReturnValue(), TRUE);
            $historyItem['percentWatched'] = @isset($history['enrichments'][$historyItem['id']]['progress']['percentage']) ? $history['enrichments'][$historyItem['id']]['progress']['percentage'] : 0;

            return $historyItem;
        });

        $historyItems = $historyItems->map(function ($historyItem) use ($page) {
            $page->navigate("https://atv-ps.amazon.com/cdp/catalog/GetPlaybackResources?asin={$historyItem['id']}&consumptionType=Streaming&desiredResources=CatalogMetadata&deviceID={$this->DEVICE_ID}&deviceTypeID=AOAGZA014O5RE&firmware=1&gascEnabled=false&resourceUsage=CacheResources&videoMaterialType=Feature&titleDecorationScheme=primary-content&uxLocale=en_US")->waitForNavigation(Page::DOM_CONTENT_LOADED);
            $history = json_decode($page->evaluate('document.body.innerText')->getReturnValue(), TRUE);

            $historyItem['type'] = @$history['catalogMetadata']['catalog']['entityType'] == 'TV Show' ? 'show' : 'movie';
            if ($historyItem['type'] == 'show') {
                if (
                    isset($history['catalogMetadata']['family'])
                ) {

                    $show = [
                        'id' => null,
                        'seasonNumber' => 0,
                        'title' => '',
                    ];

                    foreach ($history['catalogMetadata']['family']['tvAncestors'] as $family) {

                        if (
                            $family['catalog']['type'] == 'SEASON'
                        ) {
                            $show['seasonNumber'] = $family['catalog']['seasonNumber'];
                        }

                        if (
                            $family['catalog']['type'] == 'SHOW'
                        ) {
                            $show['title'] = $family['catalog']['title'];
                            $show['id'] = $family['catalog']['id'];
                        }
                    }
                }

                $historyItem['number'] = $history['catalogMetadata']['catalog']['episodeNumber'];
                $historyItem['episodeTitle'] = $history['catalogMetadata']['catalog']['title'];
                $historyItem['showTitle'] = $show['title'];
                $historyItem['showId'] = $show['id'];
                $historyItem['showSeason'] = $show['seasonNumber'];
            }

            return $historyItem;
        });

        //done ready to create show items
        $historyItems->each(function ($historyItem) {
            $this->parseMetadata($historyItem);
        });

        //Update last time we synced
        $config = $this->SERVICE->config;
        $config['lastHistorySync'] = $updatedLastSync;
        $this->SERVICE->config = $config;
        $this->SERVICE->save();
    }

    public function parseMetadata($meta)
    {
        if (
            $meta['type'] == 'movie'
        ) {
            $theMovie = Movie::firstOrNew([
                'service_id' => $this->SERVICE->id,
                'item_id' => $meta['id']
            ]);

            if ($theMovie->exists) {
                return;
            }

            $theMovie->title = $meta['title'];
            $theMovie->watched_at = $meta['watchedAt'];
            $theMovie->progress = round($meta['percentWatched']);
            $theMovie->service()->associate($this->SERVICE);

            $theMovie->save();
            dispatch(new ProcessMovie($theMovie));
        } else {
            $theShow = Show::firstOrNew([
                'service_id' => $this->SERVICE->id,
                'item_id' => $meta['showId']
            ]);

            $theShow->title = $meta['showTitle'];
            $theShow->service()->associate($this->SERVICE);
            $theShow->save();

            $theEpisode = Episode::firstOrNew([
                'service_id' => $this->SERVICE->id,
                'item_id' => $meta['id']
            ]);


            $theEpisode->title = $meta['episodeTitle'];
            $theEpisode->watched_at = $meta['watchedAt'];
            $theEpisode->progress = round($meta['percentWatched']);

            $theEpisode->season = ($meta['showSeason']);
            $theEpisode->number = ($meta['number']);

            $theShow->service()->associate($this->SERVICE);
            $theShow->episodes()->save($theEpisode);

            dispatch(new ProcessEpisode($theEpisode));
        }
    }
    private function getNextToken($history)
    {
        foreach (Arr::dot($history) as $index => $token) {
            if (Str::of($index)->endsWith(".nextToken")) {
                return "%22nextToken%22%3A%22{$token}%22";
            }
        }

        return false;
    }

    public function convertUnixDate($date)
    {
        return $date / 1000;
    }
}
