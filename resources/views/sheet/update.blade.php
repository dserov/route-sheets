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
                    <div class="card-header">{!! __('Edit sheet') !!}</div>
                    <div class="card-body">
                        {!! Form::model($sheet, [
                                'route' => ['sheet::store', [ $sheet->id ]],
                                'method' => 'PUT',
                            ]
                        ) !!}
                        <div class="form-group row">
                            {!! Form::label('name', __('Sheet name'), ['class' => 'col-md-4 col-form-label text-md-right']) !!}
                            <div class="col-md-8">
                                {!! Form::input('text', 'name', null, ['id' => 'name', 'placeholder' => __('Sheet name') . ' ...', 'class' => 'form-control']) !!}
                            </div>
                            @error('name')
                            <div class="alert alert-danger">{{$message}}</div>
                            @enderror
                        </div>

                        <div class="form-group row">
                            {!! Form::label('user_id', __('Sheet driver'), ['class' => 'col-md-4 col-form-label text-md-right']) !!}
                            <div class="col-md-8">
                                {!! Form::select('user_id', $drivers, null, ['id' => 'user_id', 'class' => 'form-control select2']) !!}
                            </div>
                            @error('user_id')
                            <div class="alert alert-danger">{{$message}}</div>
                            @enderror
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                {!! Form::submit(__('Save'), ['class' => 'btn btn-primary']) !!}
                                <button type="button" class="btn btn-outline-secondary ml-4" onclick="history.go(-1);">{{ __('Cancel') }}</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('load', function() {
            $('.select2').select2();
        });
    </script>
@endsection
