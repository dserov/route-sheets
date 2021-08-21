@extends('layouts.app')

@section('content')
    <div class="container-md">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header d-md-flex">
                        <div class="flex-grow-1">
                            {{ __('Map') }}
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
                        <div>
                            {{--@can('create', \App\Models\Sheet::class)--}}
                            <a href="{{route('map::import_form')}}" class="btn btn-primary">Импорт</a>
                            {{--@endcan--}}
                        </div>
                    </div>
                </div>
                <button id="draw">Выделить область</button>
                <div id="container" style="position: relative;">
                    <div id="map" style="width: 800px; height: 600px;"></div>
                    <canvas id="draw-canvas" style="position: absolute; left: 0; top: 0; display: none;"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=b6468a16-8b3a-4c8a-876c-238104223db5"></script>
    <script> let geo_list = {!! $geo_list !!};</script>
    <script src="{{ asset('js/mymap.js') }}"></script>
@endsection
