@extends('layouts.app')

@section('content')
    <style>
        .gallery {
            overflow: hidden;
            /*width: 480px;*/
        }

        .gallery .gallery__ramka {
            float: left;
            margin-right: 10px;
            margin-bottom: 10px;
            width: 300px;
            height: 300px;
        }

        .gallery .gallery__img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header">{{ __('Playground photos') }}
                        <strong>{{ $sheet_detail->playground }}</strong></div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if ($errors->has('files'))
                            @foreach ($errors->get('files') as $error)
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $error }}</strong>
                                </span>
                            @endforeach
                        @endif
                        {{ $photos->links() }}
                        <div class="gallery">
                            @foreach($photos as $photo)
                                <div class="gallery__ramka">
                                    <img src="{{ $photo->path }}" class="gallery__img img-thumbnail"
                                         alt="{{ $photo->description }}">
                                </div>
                            @endforeach
                        </div>
                        <br>
                        <form action="{{ route('sheet_detail::detail_photo::upload_photos', [ 'sheetDetail' => $sheet_detail ]) }}"
                              method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <input type="file" name="images[]" multiple class="form-control" accept="image/*">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-success">{{ __('Upload') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
