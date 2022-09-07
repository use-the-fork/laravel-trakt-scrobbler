<?php

	namespace App\Domains\Netflix\Services;

	use App\Domains\Common\Models\Episode;
	use App\Domains\Common\Models\Movie;
	use App\Domains\Common\Models\Service;
	use App\Domains\Common\Models\Show;
	use App\Domains\Common\Services\StreamingService;
	use App\Domains\Netflix\Enums\NetflixConstant;
	use Carbon\Carbon;
	use Illuminate\Support\Str;

	class NetflixService extends StreamingService
	{

		private $AUTH_URL;
		private $BUILD_IDENTIFIER;
		private $PROFILE_NAME;
		private $SERVICE;

		public function __construct()
		{
			$this->SERVICE = Service::where('name', 'netflix')->firstOrFail();

			parent::__construct();
		}

		public function isActive(){
			return $this->activate();
		}

		public function activate()
		{

			$page = $this->browser->createPage();
			$navigation = $page->navigate(NetflixConstant::HOST_URL . '/browse');
			$navigation->waitForNavigation();

			//Check if we need to login
			if (
				Str::contains($page->getCurrentUrl(), 'login')
			) {
				throw new \Exception('Netflix has logged out. Run Setup script. service:netflix:activate');
			}

			// get page title
			$this->AUTH_URL = $page->evaluate('window.netflix.reactContext.models.userInfo.data.authURL')->getReturnValue();
			$this->BUILD_IDENTIFIER = $page->evaluate('window.netflix.reactContext.models.serverDefs.data.BUILD_IDENTIFIER')->getReturnValue();
			$this->PROFILE_NAME = $page->evaluate('window.netflix.reactContext.models.userInfo.data.name')->getReturnValue();

			if (
				!empty($this->AUTH_URL) &&
				!empty($this->BUILD_IDENTIFIER) &&
				!empty($this->PROFILE_NAME)
			) {
				$this->isActivated = TRUE;
			}

			return TRUE;

		}

		public function getItem($id)
		{

			$this->activate();

			$page = $this->browser->createPage();
			$navigation = $page->navigate(NetflixConstant::API_URL . "/{$this->BUILD_IDENTIFIER}/metadata?languages=en-US&movieid={$id}");
			$navigation->waitForNavigation();

			return json_decode($page->evaluate('document.documentElement.innerText')->getReturnValue());

		}

		public function loadHistoryItems($pagesToSync = 5)
		{

			if(!$this->isActivated) {
				$this->activate();
			}

			$config = $this->SERVICE->config;
			$config['lastHistorySync'] = Carbon::now();
			$this->SERVICE->config = $config;
			$this->SERVICE->save();

			$page = $this->browser->createPage();

			$currentPage = 0;
			$continue = TRUE;
			$theHistory = collect([]);

			while ($continue) {
				$page->navigate(NetflixConstant::API_URL . "/{$this->BUILD_IDENTIFIER}/viewingactivity?languages=en-US&authURL={$this->AUTH_URL}&pg={$currentPage}")->waitForNavigation();
				$history = json_decode($page->evaluate('document.documentElement.innerText')->getReturnValue());

				dump(NetflixConstant::API_URL . "/{$this->BUILD_IDENTIFIER}/viewingactivity?languages=en-US&authURL={$this->AUTH_URL}&pg={$currentPage}");

				if (count($history->viewedItems) <= 0) {
					$continue = FALSE;
				} else {
					$theHistory->push(...$history->viewedItems);
					$currentPage++;
				}

				if ($currentPage == $pagesToSync) {
					break;
				}

			}

			foreach ($theHistory as $historyItem){
				$this->parseHistorydata($historyItem);
			}
			return $theHistory;
		}

		public function parseHistorydata($item)
		{

			if (
				isset($item->series)
			) {

				$theShow = Show::firstOrNew([
												  'service_id' => $this->SERVICE->id,
												  'item_id' => $item->series
											  ]);

				$theShow->title = $item->seriesTitle;
				$theShow->service()->associate($this->SERVICE);
				$theShow->save();

				$theEpisode = Episode::firstOrNew([
												'service_id' => $this->SERVICE->id,
												'item_id' => $item->movieID
											]);

				$theEpisode->title = $item->title;
				$theEpisode->watched_at = Carbon::parse($this->convertUnixDate($item->date));
				$theEpisode->progress = round((($item->bookmark / $item->duration) * 100));
				$theShow->service()->associate($this->SERVICE);

				$theShow->episodes()->save($theEpisode);

			} else {
				$theMovie = Movie::firstOrNew([
												  'service_id' => $this->SERVICE->id,
												  'item_id' => $item->movieID
											  ]);

				$theMovie->title = $item->title;
				$theMovie->watched_at = Carbon::parse($this->convertUnixDate($item->date));
				$theMovie->progress = round((($item->bookmark / $item->duration) * 100));
				$theMovie->service()->associate($this->SERVICE);

				$theMovie->save();

			}

		}

		public function parseMetadata($meta)
		{
			$video = $meta->video;
			$type = $video->type;
			$title = $video->title;
			$year = $video->year;

			if ($type === 'show') {

			} else {
				$theMovie = Movie::firstOrNew([
												  'service_id' => $this->SERVICE->id,
												  'item_id' => $video->id
											  ]);

				$theMovie->title = $title;
				$theMovie->year = $year;
				$theMovie->watched_at = $year;

				$theMovie->save();
			}
		}

		public function convertUnixDate($date){
			return $date / 1000;
		}



	}
