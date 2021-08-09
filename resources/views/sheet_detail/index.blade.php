@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header d-flex">
                        <div class="flex-grow-1">
                            {{ __('Sheet details') }} <strong>&laquo;{{ $sheet->name }}&raquo;</strong><br>
                            № <strong>{{ $sheet->nomer }}</strong> от <strong>{{ $sheet->data }}</strong>
                            <br>
                            <strong>Адресов: {{ $sheet_details->count() }}</strong>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary" onclick="history.go(-1);">{{ __('Back') }}</button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <div class="container-fluid">
                            <div class="row border-bottom border-top my-2 py-2">
                                <div class="col-1 font-weight-bold">№</div>
                                <div class="col-5 col-sm-4 font-weight-bold">Контрагент</div>
                                <div class="col-5 col-sm-4 font-weight-bold">Площадка</div>
                                <div class="col col-sm-3 font-weight-bold">&nbsp;</div>
                            </div>
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
                                <tr>
                                    <th scope="row">-</th>
                                    <td colspan="4">Data not found</td>
                                </tr>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
