<div>
    <div class="card mb-3">
        <div class="row g-0">
            <div class="col-md-4">
                <img src="{{$single['media']['poster']}}" class="img-fluid rounded-start" alt="{{$single['title']}}">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title">{{$single['title']}}</h5>
                    <p class="card-text">{{$single['watched_at']}}</p>
                    <div class="d-flex justify-content-between mt-4">
                        <p>{{$single['trakt']['info']['title']}}</p>
                        <p class="text-muted">{{$single['trakt']['info']['year']}}</p>
                    </div>
                    <p>Match Type: {{$single['trakt']['match_type']}}</p>
                    <p>Score: {{$single['trakt']['score']}}</p>
                </div>
                <div class="card-body">
                    <a href="#" class="card-link">Approve</a>
                    <a href="#" class="card-link">Fix</a>
                </div>
            </div>
        </div>
    </div>
</div>