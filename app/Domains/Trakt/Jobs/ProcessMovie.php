<?php

namespace App\Domains\Trakt\Jobs;

use Illuminate\Bus\Queueable;
use App\Domains\Trakt\Models\Trakt;
use App\Domains\Common\Models\Movie;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Domains\Trakt\Enums\TraktMatchType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\RateLimitedMiddleware\RateLimited;
use App\Domains\Trakt\Services\TraktSearchService;

class ProcessMovie implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $movie;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Movie $movie)
    {
        $this->movie = $movie;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        if ($this->movie->traktable) {
            return;
        }

        $traktSearchService = (new TraktSearchService());
        $matches = $traktSearchService->search('movie', $this->movie->getEncoded());

        if (
            count($matches) === 1
        ) {

            if ($traktSearchService->compareServiceMatch('movie', $this->movie, $matches[0])) {
                return $this->append($matches[0], TraktMatchType::SERVICE);
            } else {
                return $this->append($matches[0], TraktMatchType::SINGLE);
            }
        } else {
            foreach ($matches as $match) {
                if ($traktSearchService->compareServiceMatch('movie', $this->movie, $match)) {
                    return $this->append($match, TraktMatchType::SERVICE);
                };

                if ($traktSearchService->compareMatch('movie', $this->movie, $match)) {
                    return $this->append($match, TraktMatchType::COMPARED);
                };
            }
        }

        return $this->noMatch();
    }

    private function noMatch()
    {

        if (app()->runningInConsole()) {
            echo "\nNo match for: {$this->movie->title}";
        }
        $trakt = new Trakt();
        $trakt->match_type = TraktMatchType::NONE;
        $trakt->score = 0;
        $trakt->status = 0;

        return $this->movie->saveTrakt($trakt);
    }

    private function append($match, $matchType)
    {

        if (app()->runningInConsole()) {
            echo "\nFound match: {$this->movie->title} ({$matchType})";
        }

        $trakt = new Trakt();

        $trakt->trakt_id = isset($match['movie']['ids']['trakt']) ? $match['movie']['ids']['trakt'] : null;
        $trakt->match_type = $matchType;
        $trakt->ids = isset($match['movie']['ids']) ? $match['movie']['ids'] : null;
        $trakt->information = [
            'title' => $match['movie']['title'],
            'year' => $match['movie']['year'],
            'tagline' => $match['movie']['tagline'],
            'overview' => $match['movie']['overview'],
            'released' => $match['movie']['released'],
            'trailer' => $match['movie']['trailer'],
            'homepage' => $match['movie']['homepage'],
        ];
        $trakt->score = isset($match['score']) ? intval($match['score']) : 0;
        $trakt->status = 0;

        return $this->movie->saveTrakt($trakt);
    }
}
