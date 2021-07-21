@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header">{{ __('Sheet details') }} <strong>&laquo;{{ $sheet->name }}&raquo;</strong><br>
                        № <strong>{{ $sheet->nomer }}</strong> от <strong>{{ $sheet->data }}</strong></div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        {{ $sheet_details->links() }}
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">№ п/п</th>
                                <th scope="col">Контрагент</th>
                                <th scope="col">Площадка</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($sheet_details as $sheet_detail)
                                <tr>
                                    <th scope="row">{{ $sheet_detail->id }}</th>
                                    <td>{{ $sheet_detail->npp }}</td>
                                    <td>{{ $sheet_detail->contragent }}</td>
                                    <td>{{ $sheet_detail->playground }}</td>
                                    <td><a href="{{ route('sheet_detail::detail_photo::list_by_sheet_detail', [ 'sheetDetail' => $sheet_detail ]) }}" class="btn btn-info position-relative">Фотографии
                                            <span class="position-absolute top-0 start-100 translate-middle badge badge-dark rounded-pill bg-secondary px-2 py-1">
                                                {{ $sheet_detail->detail_fotos()->count() }}
                                                <span class="visually-hidden"></span>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <th scope="row">-</th>
                                    <td colspan="4">Data not found</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
