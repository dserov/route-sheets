@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        {{ $sheets->links() }}
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Номер</th>
                                <th scope="col">Дата</th>
                                <th scope="col">Наименование</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($sheets as $sheet)
                                <tr>
                                    <th scope="row">{{ $sheet->id }}</th>
                                    <td>{{ $sheet->nomer }}</td>
                                    <td>{{ $sheet->data }}</td>
                                    <td>{{ $sheet->name }}</td>
                                    <td><a href="#" class="btn btn-outline-info">Детали</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <th scope="row">2</th>
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
