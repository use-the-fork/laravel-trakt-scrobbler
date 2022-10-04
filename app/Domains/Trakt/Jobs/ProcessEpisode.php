<?php

namespace App\Domains\Trakt\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use App\Domains\Trakt\Models\Trakt;
use App\Domains\Common\Models\Movie;
use App\Domains\Common\Models\Episode;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Domains\Trakt\Enums\TraktMatchType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Domains\Trakt\Services\TraktSearchService;

class ProcessEpisode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $episode;
    public $show;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Episode $episode)
    {
        $this->episode = $episode;
        $this->show = $episode->show;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        if ($this->episode->traktable->exists) {
            return;
        }

        $traktSearchService = (new TraktSearchService());

        if (
            !isset($this->show->traktable->trakt_id)
        ) {

            $matches = $traktSearchService->search('show', $this->episode->show->getEncoded());

            if (
                count($matches) === 1
            ) {

                if ($traktSearchService->compareServiceMatch('show', $this->episode, $matches[0])) {
                    $this->appendShow($matches[0], TraktMatchType::SERVICE);
                } else {
                    $this->appendShow($matches[0], TraktMatchType::SINGLE);
                }
            } else {
                foreach ($matches as $match) {
                    if ($traktSearchService->compareServiceMatch('show', $this->episode, $match)) {
                        $this->appendShow($match, TraktMatchType::SERVICE);
                    };

                    if ($traktSearchService->compareMatch('show', $this->episode, $match)) {
                        $this->appendShow($match, TraktMatchType::COMPARED);
                    };
                }
            }

            $this->show->refresh();
        }

        if (
            !empty($this->show->traktable->trakt_id) &&
            isset($this->show->traktable->trakt_id)
        ) {

            $match = $traktSearchService->searchEpisode($this->show->traktable->trakt_id, $this->episode->season, $this->episode->number);
            if (
                !empty($match)
            ) {
                return $this->appendEpisode($match, TraktMatchType::SINGLE);
            }

            //even if there is no match, return true
            return true;
        }

        return $this->noMatch();
    }


    private function noMatch()
    {

        if (app()->runningInConsole()) {
            echo "\nNo match for: {$this->show->title} ({$this->show->id})";
        }

        $trakt = new Trakt();
        $trakt->match_type = TraktMatchType::NONE;
        $trakt->score = 0;
        $trakt->status = 0;

        return $this->show->saveTrakt($trakt);
    }

    private function appendEpisode($match, $matchType)
    {

        if (app()->runningInConsole()) {
            echo "\nFound match: {$this->show->title} / {$this->episode->title} ({$matchType})";
        }

        $trakt = new Trakt();

        $trakt->trakt_id = isset($match['ids']['trakt']) ? $match['ids']['trakt'] : null;
        $trakt->match_type = $matchType;
        $trakt->ids = isset($match['ids']) ? $match['ids'] : null;
        $trakt->information = [
            'title' => $match['title']
        ];
        $trakt->score = 0;
        $trakt->status = 0;

        $this->episode->saveTrakt($trakt);
    }

    private function appendShow($match, $matchType)
    {

        if (app()->runningInConsole()) {
            echo "\nFound match: {$this->show->title} ({$matchType})";
        }

        $trakt = new Trakt();

        $trakt->trakt_id = isset($match['show']['ids']['trakt']) ? $match['show']['ids']['trakt'] : null;
        $trakt->match_type = $matchType;
        $trakt->ids = isset($match['show']['ids']) ? $match['show']['ids'] : null;
        $trakt->information = [
            'title' => $match['show']['title'],
            'year' => $match['show']['year'],
            'overview' => $match['show']['overview'],
            'trailer' => $match['show']['trailer'],
            'homepage' => $match['show']['homepage'],
        ];
        $trakt->score = isset($match['score']) ? intval($match['score']) : 0;
        $trakt->status = 0;

        $this->show->saveTrakt($trakt);
    }
}
