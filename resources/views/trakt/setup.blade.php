@extends('layouts.base')
@section('title', 'setup Trakt')
@section('body')
	<form name="add-blog-post-form" id="add-blog-post-form" method="post" action="{{route('trakt-setup.store')}}">
		@csrf
		<div class="mb-3">
			<label for="username" class="form-label">Username</label>
			<input type="text" id="username" name="username" class="form-control">
		</div>
		<div class="mb-3">
			<label for="password" class="form-label">Password</label>
			<input type="password" name="password" class="form-control" id="password">
		</div>
		<button type="submit" class="btn btn-primary">Submit</button>
	</form>
@stop
