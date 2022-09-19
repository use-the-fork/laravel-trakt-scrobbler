<div>
    <div class="card">
        <div class="card-header d-flex flex-row align-items-center">
            <img src="{{ asset('img/logos/trakt/trakt-icon-red-white.svg') }}" style="width: 2rem;" class="img-fluid">
            <span class="p-2">Movie Stastics</span>
        </div>
        <div class="card-body">
            <div class="card-text">
                <div class="d-flex justify-content-between">
                    <p class="display-1">{{ $stats['total'] }}</p>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $stats['synced-percent'] }}%;" aria-valuenow="{{ $stats['synced-percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <table class="table table-borderless table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="font-weight-normal align-middle">Synced</td>
                            <td class="float-end font-weight-normal">
                                <p class="mb-1">{{ $stats['synced'] }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-normal align-middle">Not Synced</td>
                            <td class="float-end font-weight-normal">
                                <p class="mb-1">{{ $stats['not-synced'] }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="font-weight-normal align-middle"><strong>Match Type (Not Synced)</strong></td>
                        </tr>
                        <tr>
                            <td class="font-weight-normal align-middle">Service Match</td>
                            <td class="float-end font-weight-normal">
                                <p class="mb-1">{{ $stats['match-type-service'] }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-normal align-middle">Single Result Match</td>
                            <td class="float-end font-weight-normal">
                                <p class="mb-1">{{ $stats['match-type-single'] }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-normal align-middle">Compare Match</td>
                            <td class="float-end font-weight-normal">
                                <p class="mb-1">{{ $stats['match-type-compare'] }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-normal align-middle">No Match</td>
                            <td class="float-end font-weight-normal">
                                <p class="mb-1">{{ $stats['match-type-none'] }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-normal align-middle">No Meta</td>
                            <td class="float-end font-weight-normal">
                                <p class="mb-1">{{ $stats['match-type-no-meta'] }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body">
            <a href="{{ route('trakt.match', 'movie') }}" class="btn btn-primary">Fix Matches</a>
        </div>
    </div>
</div>