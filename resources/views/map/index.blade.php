@extends('layouts.app')

@section('content')
    <div class="container-md">
        <div class="row justify-content-center">
            <div class="col">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {!! session('status') !!}
                    </div>
                @endif
                <div id="container" style="position: relative;">
                    <div id="map" style="width: 100%; height: 600px;"></div>
                    <canvas id="draw-canvas" style="position: absolute; left: 0; top: 0; display: none;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <form action="{{route('map::export_geopoints')}}" id="export_geopoints" method="post">
        @csrf
        <input type="hidden" name="id_list" id="id_list" value="">
    </form>
@endsection

@section('scripts')
    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
    <script> let geo_list = {!! $geo_list !!};</script>
    <script src="{{ asset('js/mymap.js') }}"></script>
@endsection
