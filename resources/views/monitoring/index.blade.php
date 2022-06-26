@extends('layouts.app')

@section('content')
    <div class="container-md">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {!! session('status') !!}
            </div>
        @endif
        <div class="row" style="min-height: 400px">
            <div class="col-12 col-lg-4">
                <h4>{{ __('Trucks') }}</h4>
                <div id="vehicle_tree"></div>
            </div>
            <div class="col">
                <div id="cmsv6flash"></div>
            </div>
        </div>
        <div id="map" style="width: 100%; height: 600px;"></div>
    </div>
@endsection

@section('scripts')
    <script src="//api-maps.yandex.ru/2.1/?apikey=81482589-ffbc-4b95-9bf4-aad7100e024d&lang=ru_RU"></script>
    <script> let geo_list = {!! $geo_list !!};</script>
    <script src="{{ asset('js/monitoring.js') }}"></script>
@endsection

@section('head_scripts')
    <link href="//cdn.jsdelivr.net/npm/jquery.fancytree@2.27/dist/skin-win8/ui.fancytree.min.css" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/jquery.fancytree@2.27/dist/jquery.fancytree-all-deps.min.js" defer></script>
    <script src="{{ asset('player/js/cmsv6player.min.js') }}"></script>
@endsection