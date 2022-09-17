<?php

namespace App\Domains\Trakt\Jobs;

use Illuminate\Bus\Queueable;
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
        $this->onQueue('trakt-get');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $traktSearchService = (new TraktSearchService());

        if (
            !isset($this->show->trakt['ids']['trakt'])
        ) {

            $matches = $traktSearchService->search('show', $this->episode->show);

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
            !empty($this->show->trakt) &&
            isset($this->show->trakt['ids']['trakt'])
        ) {

            $match = $traktSearchService->searchEpisode($this->show->trakt['ids']['trakt'], $this->episode->season, $this->episode->number);
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
            echo "\nNo match for: {$this->show->title}";
        }

        $append = [];
        $append['match-type'] = TraktMatchType::NONE;

        $this->show->trakt = $append;
        return $this->show->save();
    }

    private function appendEpisode($match, $matchType)
    {

        if (app()->runningInConsole()) {
            echo "\nFound match: {$this->show->title} / {$this->episode->title} ({$matchType})";
        }

        $append = [];
        $append['ids'] = $match['ids'];
        $append['match-type'] = $matchType;
        $append['info']['title'] = $match['title'];

        $this->episode['trakt'] = $append;
        $this->episode->save();
    }

    private function appendShow($match, $matchType)
    {

        if (app()->runningInConsole()) {
            echo "\nFound match: {$this->show->title} ({$matchType})";
        }

        $append = [];
        $append['ids'] = $match['show']['ids'];
        $append['match-type'] = $matchType;
        $append['score'] = $match['score'];
        $append['info']['title'] = $match['show']['title'];
        $append['info']['year'] = $match['show']['year'];
        $append['info']['overview'] = $match['show']['overview'];
        $append['info']['trailer'] = $match['show']['trailer'];
        $append['info']['homepage'] = $match['show']['homepage'];

        if (isset($match['show']['year'])) {
            $this->show->year = $match['show']['year'];
        }

        $this->show['trakt'] = $append;

        $this->show->save();
    }
}
