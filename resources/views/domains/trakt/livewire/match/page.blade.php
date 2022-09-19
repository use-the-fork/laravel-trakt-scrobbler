        <div>
            <div class="row row-cols-1 row-cols-md-2 g-4 pt-5">

                @foreach($items as $item)
                <div class="col">
                    <livewire:trakt.http.livewire.match.single :item="$item" />
                </div>
                @endforeach

            </div>
        </div>