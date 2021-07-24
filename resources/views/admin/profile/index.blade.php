@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header">{{ __('Users') }}</div>
                    <div class="card-body">
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
                        @can ('create', \App\Models\User::class)
                            <a href="{{route('admin::profile::create')}}" class="btn btn-dark mb-4">{!! __('Add new user') !!}</a>
                            <a href="{{route('admin::profile::import')}}" class="btn btn-dark mb-4">{!! __('Import users') !!}</a>
                        @endcan
                        @foreach($users as $user)
                            <div class="d-flex p-2 shadow">
                                <div class=" p-2">
                                    <img src="https://via.placeholder.com/150" alt="{{$user->name}}" class="card">
                                </div>
                                <div class="flex-grow-1 p-2">
                                    <div>
                                        <span class="font-weight-bold">{{ __('Name') }}: </span>
                                        <span class="">{{$user->name}}</span>
                                    </div>
                                    <div>
                                        <span class="font-weight-bold">{{ __('Email') }}: </span>
                                        <span class="">{{$user->email}}</span>
                                    </div>
                                    <div>
                                        <span class="font-weight-bold">{{ __('Administrator') }}: </span>
                                        @if($user->is_admin)
                                            <span class="">{{__('Yes')}}</span>
                                        @else
                                            <span class="">{{ __('No') }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-weight-bold">{{ __('Logistic') }}: </span>
                                        @if($user->is_logistic)
                                            <span class="">{{__('Yes')}}</span>
                                        @else
                                            <span class="">{{ __('No') }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-weight-bold">{{ __('Driver') }}: </span>
                                        @if($user->is_driver)
                                            <span class="">{{__('Yes')}}</span>
                                        @else
                                            <span class="">{{ __('No') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="p-2 d-flex flex-column">
                                    <a href="{{route('admin::profile::update', ['user' => $user])}}" class="btn btn-info">{!! __('Edit') !!}</a>
                                    @can('delete', $user)
                                        <a href="{{route('admin::profile::delete', ['user' => $user])}}" class="btn btn-danger">{!! __('Delete') !!}</a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
