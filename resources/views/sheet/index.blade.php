@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header d-md-flex">
                        <div class="flex-grow-1">
                            {{ __('Dashboard') }}
                        </div>
                        <form class="form-inline my-2 my-lg-0">
                            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search"
                                   id="search_input">
                            <button class="btn btn-outline-success my-2 my-sm-0" type="submit" style="display: none">
                                Search
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                {{ $sheets->links() }}
                            </div>
                            <div>
                                @can('create', \App\Models\Sheet::class)
                                    <a href="{{route('sheet::import_form')}}" class="btn btn-primary">Импорт</a>
                                @endcan
                            </div>
                        </div>
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr class="d-sm-table-row d-flex flex-column">
                                <th scope="col">Номер</th>
                                <th scope="col">Дата</th>
                                <th scope="col">Наименование</th>
                                <th scope="col">Водитель</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                                @include('sheet')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('load', function () {

            let handleSearch = function() {
                let $value = $(this).val();
                $.ajax({
                    type : 'get',
                    url : '{{ route('sheet::search') }}',
                    data:{'search': $value},
                    success: function(data){
                        $('tbody').html(data.html);
                    }
                });
            };


            $('#search_input').on('keyup', handleSearch);
            $('#search_input').on('search', handleSearch);
        });
    </script>
@endsection
