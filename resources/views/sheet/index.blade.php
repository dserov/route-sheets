@extends('layouts.app')

@section('content')
    <div class="container-md">
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
                                {!! session('status') !!}
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
                        <div class="row border-bottom border-top my-2 py-2 g-2 flex-sm-column flex-md-row">
                            <div class="col">
                                <div class="row flex-column flex-sm-row flex-grow-1">
                                    <div class="col font-weight-bold">Номер</div>
                                    <div class="col font-weight-bold">Дата</div>
                                    <div class="col font-weight-bold">Наименование</div>
                                    <div class="col font-weight-bold">Водитель</div>
                                </div>
                            </div>
                            <div class="col-auto">&nbsp;
                            </div>
                        </div>
                        <div id="sheet_list">
                            @include('sheet')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('load', function () {

            let handleSearch = function () {
                let $value = $(this).val();
                $.ajax({
                    type: 'get',
                    url: '{{ route('sheet::search') }}',
                    data: {'search': $value},
                    success: function (data) {
                        $('#sheet_list').html(data.html);
                    }
                });
            };


            $('#search_input').on('keyup', _.debounce(handleSearch, 300));
            $('#search_input').on('search', _.debounce(handleSearch, 300));
        });
    </script>
@endsection
