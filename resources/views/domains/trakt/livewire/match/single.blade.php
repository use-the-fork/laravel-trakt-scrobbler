<div>
    <div class="row">
        <div class="col-1">
            <div class="d-flex flex-column justify-content-center align-items-end h-100">
                <div class="form-check">
                    <input class="form-check-input m-historyCard__toggle" wire:model="sync" type="checkbox">
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card m-traktCard">
                <div class="card-body">
                    <div class="d-flex flex-column card-text">
                        <span class="fw-light text-center text-uppercase">On {{$single['service']['name']}}</span>
                        <span class="border-bottom border-dark m-2"></span>
                        <span class="fw-light text-center text-uppercase">{{ $single['service']['intro'] }}</span>
                        <span class="fw-bold p-0 text-center fs-6">{{ $single['service']['sub-title'] }}</span>
                        <span class="fw-bold p-0 text-center  fs-4">{{ $single['service']['title'] }}</span>
                        <span class="border-bottom border-dark m-2"></span>
                        <div class="fw-light text-center text-uppercase">WATCHED ON {{ $single['service']['watched_at'] }}</div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $single['service']['progress'] }}%;" aria-valuenow="{{ $single['service']['progress'] }}" aria-valuemin="0" aria-valuemax="100">{{ $single['service']['progress'] }}%</div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-1">
            <div class="d-flex flex-column justify-content-center align-items-center h-100">
                @if ($single['trakt']['status'] == 2)
                <span class="badge rounded-pill bg-success">Synced</span>
                @elseif ($single['trakt']['status'] == 1)
                <span class="badge rounded-pill bg-success">Ready To Sync</span>
                @else
                <span class="badge rounded-pill bg-danger">Not Synced</span>
                @endif
                <span class="badge rounded-pill bg-info mt-3">Score: {{ $single['trakt']['score'] }}</span>
                <span class="badge rounded-pill bg-primary mt-3">Type: {{ $single['trakt']['match_type'] }}</span>

                <button class="btn btn-outline-success mt-3" type="button" wire:click="forceSync">Sync</button>
            </div>
        </div>
        <div class="col">
            <div class="card m-traktCard">
                <div class="m-traktCard__wrapper">
                    <div class="m-traktCard__background">
                        <img src="{{ $single['media']['backdrop'] }}" />
                    </div>
                </div>
                <div class="card-img-overlay">
                    <div class="d-flex flex-column justify-content-center card-text">
                        <span class="fw-light text-center text-uppercase text-white">On Trakt</span>
                        <span class="border-bottom border-white m-2"></span>


                        <div class="d-flex justify-content-start">
                            <img src="{{ $single['media']['poster'] }}" class="img-fluid m-traktCard__poster">
                            <div class="d-flex p-2 flex-column">
                                <span class="fw-light text-left text-uppercase text-white">{{ $single['service']['intro'] }}</span>
                                <span class="fw-bold p-0 text-left fs-6 text-white">{{ $single['trakt']['sub-title'] }}</span>
                                <span class="fw-bold p-0 text-left fs-4 text-white">{{ $single['trakt']['title'] }}</span>
                            </div>
                        </div>

                        <span class="border-bottom border-white m-2"></span>
                        <div class="fw-light text-center text-uppercase text-white">{{ $single['trakt']['watched_at'] }}</div>
                        <button type="button" class="btn btn-outline-light btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modal_{{ $item['id'] }}">Is this wrong?</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-1"></div>
    </div>
    <div class="modal fade" id="modal_{{ $item['id'] }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Correct item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>
                        <label class="form-label">Enter the correct Trakt URL for {{ $single['service']['title'] }} {{ $single['service']['intro'] }} in the field below.</label>
                        <label class="form-label"><a href="{{ $single['url'] }}" target="_blank">{{ $single['url'] }}</a></label>
                        <input type="text" class="form-control" wire:model="newMatch">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="fixMatch">
                        <div wire:loading>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </div>
                        Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>