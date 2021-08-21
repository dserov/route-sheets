@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header d-md-flex">
                        <div class="flex-grow-1">
                            {{ __('Users') }}
                        </div>
                        <form class="form-inline my-2 my-lg-0">
                            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" id="search_input">
                            <button class="btn btn-outline-success my-2 my-sm-0" type="submit" style="display: none">Search</button>
                        </form>
                    </div>
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
                            <a href="{{route('admin::profile::create')}}" class="btn btn-dark mb-4" style="max-width: 150px">{!! __('Add new user') !!}</a>
                            <a href="{{route('admin::profile::import_form')}}" class="btn btn-dark mb-4" style="max-width: 150px">{!! __('Import users') !!}</a>
                        @endcan
                        <div id="cards_list">
                            @foreach($users as $user)
                                <div class="user_card">
                                    <div class="d-sm-flex p-2 shadow">
                                    <a href="{{route('admin::profile::update', ['user' => $user])}}" class="">
                                        <div class=" p-2 gallery__ramka">
                                            <img src="{{ $user->avatar ?? 'https://via.placeholder.com/150' }}"
                                                 alt="{{$user->name}}" class="card gallery__img">
                                        </div>
                                    </a>
                                    <div class="flex-grow-1 p-2">
                                        <div>
                                            <span class="font-weight-bold">{{ __('Name') }}: </span>
                                            <span class="user_name">{{$user->name}}</span>
                                        </div>
                                        <div>
                                            <span class="font-weight-bold">{{ __('Email') }}: </span>
                                            <span class="user_email">{{$user->email}}</span>
                                        </div>
                                        <div>
                                            <span class="font-weight-bold">{{ __('Phone') }}: </span>
                                            <span class="user_phone">{{$user->phone}}</span>
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
                                        <div>
                                            <span class="font-weight-bold">{{ __('Mapper') }}: </span>
                                            @if($user->is_map)
                                                <span class="">{{__('Yes')}}</span>
                                            @else
                                                <span class="">{{ __('No') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="p-2 d-flex flex-column">
                                        <a href="{{route('admin::profile::update', ['user' => $user])}}"
                                           class="btn btn-info" style="max-width: 150px">{!! __('Edit') !!}</a>
                                        @can('delete', $user)
                                            <a href="{{route('admin::profile::delete', ['user' => $user])}}"
                                               class="btn btn-danger" style="max-width: 150px">{!! __('Delete') !!}</a>
                                        @endcan
                                    </div>
                                </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        'use strict';
        var userCardsList = [];
        let userCards = document.getElementById('cards_list').childNodes;

        userCards.forEach((item) => {
            if (item.nodeType !== 1) {
                return;
            }

            userCardsList.push(
                {
                    card: item,
                    text: item.innerText
                }
            );
        });

        let handleSearchInput = function (event) {
            let value = this.value;

            if (value.length === 0) {
                userCardsList.map(item => {
                    item.card.style.display = '';
                });
                return;
            }

            let regex = new RegExp('.*' + value + '.*', 'i');

            userCardsList.map(item => {
                item.card.style.display = (regex.test(item.text) ? '' : 'none');
            });
        };

        let searchInput = document.querySelector('#search_input');
        searchInput.addEventListener('keyup', handleSearchInput);
        searchInput.addEventListener('search', handleSearchInput);
    </script>
@endsection
