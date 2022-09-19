@extends('layouts.base')
@section('title', 'Home')
@section('body')

<div class="row row-cols-1 row-cols-md-2 g-4 pt-5">
    <div class="col">
        <livewire:trakt.http.livewire.movie-stats />
    </div>
    <div class="col">
        <livewire:trakt.http.livewire.episode-stats />
    </div>
</div>
@stop