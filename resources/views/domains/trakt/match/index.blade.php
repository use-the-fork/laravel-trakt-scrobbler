@extends('layouts.base')
@section('title', 'Home')
@section('body')
<livewire:trakt.http.livewire.match.page :type="$type" />
@stop