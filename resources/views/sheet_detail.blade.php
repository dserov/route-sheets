@forelse($sheet_details as $sheet_detail)
    <div class="row border-bottom my-2 py-2">
        <div class="col-1">{{ $sheet_detail->npp }}</div>
        <div class="col-5 col-sm-4">{{ $sheet_detail->contragent }}</div>
        <div class="col-5 col-sm-4">{{ $sheet_detail->playground }}</div>
        <div class="col col-sm-3"><a
                    href="{{ route('sheet_detail::detail_photo::list_by_sheet_detail', [ 'sheetDetail' => $sheet_detail ]) }}"
                    class="btn btn-info position-relative">Фотографии
                <span class="position-absolute top-0 start-100 translate-middle badge badge-dark rounded-pill bg-secondary px-2 py-1">
                                                {{ $sheet_detail->detail_fotos()->count() }}
                    <span class="visually-hidden"></span>
                                            </span>
            </a>
        </div>
    </div>
@empty
    <div class="row border-bottom my-2 py-2 g-2 flex-wrap flex-column flex-sm-row">
        <div class="col font-weight-bold">Data not found</div>
    </div>
@endforelse
