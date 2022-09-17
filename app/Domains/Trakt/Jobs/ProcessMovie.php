<?php

namespace App\Domains\Trakt\Jobs;

use Illuminate\Bus\Queueable;
use App\Domains\Common\Models\Movie;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Domains\Trakt\Enums\TraktMatchType;
use App\Domains\Trakt\Services\TraktSearchService;
use Spatie\RateLimitedMiddleware\RateLimited;

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
        $matches = $traktSearchService->search('movie', $this->movie);

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

        $append = [];
        $append['match-type'] = TraktMatchType::NONE;

        $this->movie->trakt = $append;
        return $this->movie->save();
    }

    private function append($match, $matchType)
    {

        if (app()->runningInConsole()) {
            echo "\nFound match: {$this->movie->title} ({$matchType})";
        }

        $append = [];
        $append['ids'] = $match['movie']['ids'];
        $append['match-type'] = $matchType;
        $append['score'] = $match['score'];
        $append['info']['title'] = $match['movie']['title'];
        $append['info']['year'] = $match['movie']['year'];
        $append['info']['tagline'] = $match['movie']['tagline'];
        $append['info']['overview'] = $match['movie']['overview'];
        $append['info']['released'] = $match['movie']['released'];
        $append['info']['trailer'] = $match['movie']['trailer'];
        $append['info']['homepage'] = $match['movie']['homepage'];

        $this->movie->trakt = $append;
        return $this->movie->save();
    }
}
