@extends('layouts.app')

@section('content')
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
                        @if (count($errors))
                            @foreach ($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">
                                    {{ $error }}
                                </div>
                            @endforeach
                        @endif
                        {{ $photos->links() }}
                        <div class="gallery">
                            @foreach($photos as $photo)
                                <div class="gallery__ramka">
                                    <div class="gallery__delete" data-image-id="{{ $photo->id }}"></div>
                                    <a href="{{ $photo->path }}" data-fancybox="gallery">
                                        <img src="{{ $photo->thumb }}" class="gallery__img img-thumbnail"
                                             alt="{{ $photo->description }}">
                                    </a>
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
                                <button type="button" class="btn btn-outline-secondary ml-4"
                                        onclick="window.location.href='{{ route('sheet::sheet_detail', [ 'sheet' => $sheet_detail->sheet_id ]) }}'">{{ __('Cancel') }}</button>
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
      window.addEventListener('load', function () {
        const url = '{{ route('sheet_detail::detail_photo::upload_photos', [ 'sheetDetail' => $sheet_detail ]) }}/';
        console.log(url);

        $(document).on('click', '.gallery__delete', function () {
          let imageId = $(this).data('imageId');
          let self = this;

          axios.post(url + imageId, {_method: 'delete'})
            .then((response) => {
              if (response.data.error == 'false') {
                // foto deleted
                $(self).closest('.gallery__ramka').remove();
                return;
              }
              alert(response.data.message);
            }, (error) => {
              //     error callback
              console.log(error);
            });
        });

      });

      // onchange="showImageHereFunc();" id="uploadImageFile"
      function showImageHereFunc() {
        let total_file = document.getElementById("uploadImageFile").files.length;
        for (let i = 0; i < total_file; i++) {
          $('#showImageHere').append("<div class='card col-md-4'><img class='card-img-top' src='" + URL.createObjectURL(event.target.files[i]) + "'></div>");
        }
      }
    </script>
@endsection
