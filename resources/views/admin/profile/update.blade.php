@extends('layouts.app')

@section('content')
    <style>
        .gallery__ramka {
            margin: 5px;
            width: 150px;
            height: 150px;
            cursor: pointer;
        }

        .gallery__img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .file_upload {
            position: relative;
            overflow: hidden;
            font-size: 1em; /* example */
            height: 2em; /* example */
            line-height: 2em /* the same as height */
        }

        .file_upload > button {
            float: right;
            width: 8em; /* example */
            height: 100%
        }

        .file_upload > div {
            padding-left: 1em /* example */
        }

        @media only screen and ( max-width: 500px ) {
            /* example */
            .file_upload > div {
                display: none
            }

            .file_upload > button {
                width: 100%
            }
        }

        .file_upload input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            transform: scale(20);
            letter-spacing: 10em; /* IE 9 fix */
            -ms-transform: scale(20); /* IE 9 fix */
            opacity: 0;
            cursor: pointer
        }

        /* Making it beautiful */

        .file_upload {
            border: 1px solid #ccc;
            border-radius: 3px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.1s linear
        }

        .file_upload.focus {
            box-shadow: 0 0 5px rgba(0, 30, 255, 0.4)
        }

        .file_upload > button {
            background: #7300df;
            transition: background 0.2s;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
            border-radius: 2px;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
            color: #fff;
            text-shadow: #6200bd 0 -1px 0;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis
        }

        .file_upload:hover > button {
            background: #6200bd;
            text-shadow: #5d00b3 0 -1px 0
        }

        .file_upload:active > button {
            background: #5d00b3;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.3) inset
        }
    </style>
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
                    <div class="card-header">@if(request()->is('*/create')) {!! __('Add user') !!} @else {!! __('Edit user') !!} @endif</div>

                    <div class="card-body">
                        {!! Form::open(['route' => 'admin::profile::save', 'files' => true]) !!}
                        {!! Form::input('hidden', 'id', old('id')) !!}
                        <div class="form-group row border pb-1">
                            <div class="col-md-6 text-md-right">
                                {!! Form::label('user_avatar', __('Avatar'), ['class' => 'col-md-6 col-form-label text-md-right']) !!}
                            </div>
                            <div class="col-md-6">
                                <div class="gallery__ramka">
                                    <img src="{{ old('avatar') }}" class="gallery__img img-thumbnail" id="preview"
                                         alt="{{ __('Avatar') }}">
                                </div>
                                <div class="file_upload">
                                    {{--<button type="button">Выбрать</button>--}}
                                    <div>Файл не выбран</div>
                                    {!! Form::input('file', 'avatar', '', ['id' => 'user_avatar', 'placeholder' => __('Avatar') . ' ...']) !!}
                                </div>
                                @error('user_name')
                                <div class="alert alert-danger">{{$message}}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            {!! Form::label('user_name', __('labels.user_name'), ['class' => 'col-md-6 col-form-label text-md-right']) !!}
                            <div class="col-md-6">
                                {!! Form::input('text', 'name', old('name'), ['id' => 'user_name', 'placeholder' => __('labels.user_name') . ' ...', 'class' => 'form-control']) !!}
                            </div>
                            @error('user_name')
                            <div class="alert alert-danger">{{$message}}</div>
                            @enderror
                        </div>

                        <div class="form-group row">
                            {!! Form::label('user_email', __('labels.user_email'), ['class' => 'col-md-6 col-form-label text-md-right']) !!}
                            <div class="col-md-6">
                                {!! Form::input('text', 'email', old('email'), ['id' => 'user_email', 'placeholder' => __('labels.user_email') . ' ...', 'class' => 'form-control']) !!}
                            </div>
                            @error('email')
                            <div class="alert alert-danger">{{$message}}</div>
                            @enderror
                        </div>

                        @can('create', Auth::user())
                            <div class="form-group row">
                                <div class="col-md-6 offset-md-6">
                                    <div class="form-check">
                                        {!! Form::checkbox('is_driver', 1, old('is_driver'), ['id' => 'is_driver', 'class' => 'form-check-input']) !!}
                                        {!! Form::label('is_driver', __('Driver'), ['class' => 'form-check-label']) !!}
                                    </div>
                                </div>
                                @error('is_driver')
                                <div class="alert alert-danger">{{$message}}</div>
                                @enderror
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6 offset-md-6">
                                    <div class="form-check">
                                        {!! Form::checkbox('is_logistic', 1, old('is_logistic'), ['id' => 'is_logistic', 'class' => 'form-check-input']) !!}
                                        {!! Form::label('is_logistic', __('Logistic'), ['class' => 'form-check-label']) !!}
                                    </div>
                                </div>
                                @error('is_logistic')
                                <div class="alert alert-danger">{{$message}}</div>
                                @enderror
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6 offset-md-6">
                                    <div class="form-check">
                                        {!! Form::checkbox('is_admin', 1, old('is_admin'), ['id' => 'is_admin', 'class' => 'form-check-input']) !!}
                                        {!! Form::label('is_admin', __('Administrator'), ['class' => 'form-check-label']) !!}
                                    </div>
                                </div>
                                @error('is_admin')
                                <div class="alert alert-danger">{{$message}}</div>
                                @enderror
                            </div>
                        @endcan

                        <div class="form-group row">
                            {!! Form::label('user_password', __('labels.user_password'), ['class' => 'col-md-6 col-form-label text-md-right']) !!}
                            <div class="col-md-6">
                                {!! Form::input('password', 'password', '', ['id' => 'user_password', 'placeholder' => __('labels.user_password') . ' ...', 'class' => 'form-control']) !!}
                            </div>
                            @error('password')
                            <div class="alert alert-danger">{{$message}}</div>
                            @enderror
                        </div>

                        <div class="form-group row">
                            {!! Form::label('user_password_confirmationd', __('labels.user_password_confirmation'), ['class' => 'col-md-6 col-form-label text-md-right']) !!}
                            <div class="col-md-6">
                                {!! Form::input('password', 'password_confirmation', '', ['id' => 'user_password_confirmation', 'placeholder' => __('labels.user_password_confirmation') . ' ...', 'class' => 'form-control']) !!}
                            </div>
                            @error('password_confirmation')
                            <div class="alert alert-danger">{{$message}}</div>
                            @enderror
                        </div>

                        <div class="form-group row">
                            {!! Form::label('user_current_password', __('labels.user_current_password'), ['class' => 'col-md-6 col-form-label text-md-right']) !!}
                            <div class="col-md-6">
                                {!! Form::input('password', 'current_password', '', ['id' => 'user_current_password', 'placeholder' => __('labels.user_current_password') . ' ...', 'class' => 'form-control is-invalid']) !!}
                            </div>
                            @error('current_password')
                            <div class="badge badge-danger">{{$message}}</div>
                            @enderror
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                {!! Form::submit(__('Save'), ['class' => 'btn btn-primary']) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.URL = window.URL || window.webkitURL || window.mozURL;
        let wrapper = document.querySelector(".file_upload"),
            inp = wrapper.querySelector("input"),
            // btn = wrapper.querySelector("button"),
            lbl = wrapper.querySelector("div");
        // btn.addEventListener('focus', function () {
        //     inp.dispatchEvent(new Event('focus'));
        // });

        // Crutches for the :focus style:
        inp.addEventListener('focus', function () {
            wrapper.classList.add("focus");
        });
        inp.addEventListener('blur', function () {
            wrapper.classList.remove("focus");
        });
        inp.addEventListener('change', function () {
            let url = URL.createObjectURL(this.files[0]);
            document.getElementById('preview').src = url;
        });


        let file_api = (window.File && window.FileReader && window.FileList && window.Blob) ? true : false;
        inp.addEventListener('change', function () {
            let file_name;
            if (file_api && inp.files.length) {
                file_name = inp.files[0].name;
            }
            else {
                file_name = inp.innerText.replace("C:\\fakepath\\", '');
            }

            if (!file_name.length)
                return;

            if (lbl.style.display !== 'none') {
                lbl.innerText = file_name;
                // btn.innerText = "Выбрать";
            }
            // else
            // btn.innerText = file_name;
        });
        inp.dispatchEvent(new Event('change'));

        window.addEventListener('resize', function () {
            inp.dispatchEvent(new Event('change'));
        });
    </script>
@endsection
