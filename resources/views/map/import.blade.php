@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {!! session('status') !!}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert-warning alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{!! __('Import new geo lists') !!}</div>
                    <div class="card-body">
                        {!! Form::open(['route' => 'map::import_save', 'files' => true]) !!}
                        <div class="form-group row border pb-1">
                            <div>{{__('Geo list')}}</div>
                            {!! Form::input('file', 'geo_list', '', ['id' => 'geo_list', 'placeholder' => __('Geo list') . ' ...']) !!}
                            @error('geo_list')
                                <div class="alert alert-danger">{{$message}}</div>
                            @enderror
                        </div>

                        <div class="form-group row border pb-1">
                            <div>{{__('Ut list')}}</div>
                            {!! Form::input('file', 'ut_list', '', ['id' => 'ut_list', 'placeholder' => __('Ut list') . ' ...']) !!}
                            @error('ut_list')
                                <div class="alert alert-danger">{{$message}}</div>
                            @enderror
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                {!! Form::submit(__('Import'), ['class' => 'btn btn-primary']) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <div class="card-body">
                        <strong>Справка.</strong> Загружать нужно два файла одновременно.<br>
                        При загрузке текущее сосотояние таблиц очищается.<br>
                        Сначала создается таблица геоточек.<br>
                        Затем создается таблица УТ-номеров, привязанная к таблице геоточек.<br>
                        Если УТ-номер в геоточках не найдется, то будет строка, что УТ-номер не определен.<br>
                        Если будет две строки с одним УТ-номером - сохранятся обе.<br>
                        В результирующей Xls адрес будет взят из таблицы геоточек.<br>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
