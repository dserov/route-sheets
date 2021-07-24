@extends('layouts.app')

@section('content')
    <style>
        .gallery {
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
        }

        .gallery .gallery__ramka {
            margin: 5px;
            width: 300px;
            height: 300px;
            cursor: pointer;
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
                        <strong>{{ $sheet_detail->playground }}</strong>
                    </div>
                    <div>
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
                                         alt="{{ $photo->description }}" data-fancybox="gallery">
                                </div>
                            @endforeach
                            <div class="gallery__ramka">
                                <div style="
                                        font-size: 170px;
                                        background-color: #adadad;
                                        width: 100%;
                                        height: 100%;
                                        display: none;
                                        justify-content: center;
                                        align-items: center;
                                    " class="img-thumbnail">+
                                </div>
                            </div>
                        </div>
                        <br>
                        <form action="{{ route('sheet_detail::detail_photo::upload_photos', [ 'sheetDetail' => $sheet_detail ]) }}"
                              method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <input type="file" name="images[]" multiple class="form-control" accept="image/*" >
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-success">{{ __('Upload') }}</button>
                            </div>
                        </form>

                        <div id="showImageHere">
                            <div class="card-group">
                                <div class="row">
                                    <!-- Image preview -->
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // onchange="showImageHereFunc();" id="uploadImageFile"
        function showImageHereFunc() {
            let total_file = document.getElementById("uploadImageFile").files.length;
            for (let i = 0; i < total_file; i++) {
                $('#showImageHere').append("<div class='card col-md-4'><img class='card-img-top' src='" + URL.createObjectURL(event.target.files[i]) + "'></div>");
            }
        }
    </script>
@endsection
