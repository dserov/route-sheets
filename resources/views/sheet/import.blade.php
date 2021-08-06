@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
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
                    <div class="card-header">{!! __('Import new route sheet') !!}</div>

                    <div class="card-body">
                        {!! Form::open(['route' => 'sheet::import_save', 'files' => true]) !!}
                        <div class="form-group row border pb-1">
                            {!! Form::input('file', 'route_sheet', '', ['id' => 'route_sheet', 'placeholder' => __('Route sheet') . ' ...']) !!}
                            @error('route_sheet')
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
                </div>
            </div>
        </div>
    </div>
@endsection
