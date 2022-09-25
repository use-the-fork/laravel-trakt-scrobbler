        <div>
            <div class="row pt-5">
                <div class="col">
                    <nav class="navbar navbar-light bg-light">
                        <div class="container-fluid">
                            <div class="d-flex align-items-center  justify-content-evenly w-100">
                                <div class="form-check form-switch ml-2">
                                    <input class="form-check-input" type="checkbox" id="hideSynced" wire:model="hideSynced">
                                    <label class="form-check-label" for="hideSynced">Hide synced</label>
                                </div>
                                <div class="ml-2">
                                    <label for="minimumPercentageWatched">Match Type</label>
                                    <select class="form-select form-select-sm" wire:model="match_type">
                                        <option value="all" selected>All</option>
                                        <option value="service">Exact Service</option>
                                        <option value="single">Single Result</option>
                                        <option value="compare">Compare</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                                <div class="ml-2">
                                    <label for="minimumPercentageWatched">Type</label>
                                    <select class="form-select form-select-sm" wire:model="type">
                                        <option value="all" selected>All</option>
                                        <option value="episodes">Episodes</option>
                                        <option value="movies">Movies</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
            <div class="row pt-5 pb-5">
                @foreach($items as $item)
                <div class="col-sm-12 col-md-12 mb-3">
                    <div>
                        @livewire('trakt.http.livewire.match.single', ['item' => $item], key($item->id))
                    </div>
                </div>
                @endforeach
                <div class="d-flex justify-content-center pb-5" wire:loading>
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>

            <nav class="navbar fixed-bottom navbar-light bg-light">
                <div class="container-fluid">
                    <div class="d-flex align-items-center  justify-content-evenly w-100">

                        <div class="form-check form-switch ml-2">
                            <input class="form-check-input" type="checkbox" id="addWithReleaseDate">
                            <label class="form-check-label" for="addWithReleaseDate">Add with release date</label>
                        </div>

                        <div class="ml-2">
                            <label for="minimumPercentageWatched">Minimum Percentage Watched</label>
                            <input type="text" class="form-control form-control-sm" id="minimumPercentageWatched" wire:model="minWatched">
                        </div>

                        <div class="ml-2"><label>Select</label>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-primary" id="select__all">All</button>
                                <button type="button" class="btn btn-primary">None</button>
                                <button type="button" class="btn btn-primary">Toggle</button>
                            </div>
                        </div>

                        <form class="d-flex">
                            <button class="btn btn-outline-success" id='sync' type="submit">Sync All Selected</button>
                        </form>
                    </div>
                </div>
            </nav>

            <script type="text/javascript">
                $(document).ready(function() {
                    $('#select__all').click(function(e) {
                        e.preventDefault();
                        $('.m-historyCard__toggle').each(function() {
                            $(this).click();
                            //$(this).prop('checked', true);
                            //$(this).prop('checked', $(this).is(':checked'));
                        });
                    });

                    $('#sync').click(function(e) {
                        e.preventDefault();
                        Livewire.emit('syncTrakt', $('#minimumPercentageWatched').val());
                    });

                });

                window.onscroll = function(ev) {
                    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 50) {
                        window.livewire.emit('load-more');
                    }
                };
            </script>

        </div>