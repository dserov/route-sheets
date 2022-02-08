@forelse($sheet_details as $sheet_detail)
    <div class="row border-bottom my-2 py-2">
        <a name="npp{{ $sheet_detail->npp }}"></a>
        <div class="col-2 col-sm-1 text-nowrap"><strong>{{ $sheet_detail->npp }}</strong></div>
        <div class="col-10 col-sm-4">{{ $sheet_detail->contragent }}</div>
        <div class="col-12 col-sm-4"><strong>{{ $sheet_detail->playground }}</strong></div>
        <div class="col">
            <a href="{{ route('sheet_detail::detail_photo::list_by_sheet_detail', [ 'sheetDetail' => $sheet_detail ]) }}"
                    class="btn btn-info position-relative foto-npp" data-npp="{{ $sheet_detail->npp }}">Фотографии
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
