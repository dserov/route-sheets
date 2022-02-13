@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header d-flex">{{ __('Playground photos') }}
                        <div class="flex-grow-1">
                            <strong>{{ $sheet_detail->playground }}</strong>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="window.location.href='{{ route('sheet::sheet_detail', [ 'sheet' => $sheet_detail->sheet_id ]) }}'">{{ __('Back') }}</button>
                        </div>
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
                                    <a class="gallery__link" href="{{ $photo->path }}"
                                       data-caption="{{ \Carbon\Carbon::parse($photo->created_at)->locale('ru')->format('d.m.Y H:i:s') }}"
                                       data-fancybox="gallery" style="transform: rotate({{ $photo->rotate * 90 }}deg)"
                                       data-rotate="{{ $photo->rotate }}"
                                    >
                                        <img src="{{ $photo->thumb }}" class="gallery__img img-thumbnail"
                                             alt="{{ $photo->description }}">
                                    </a>
                                    <div class="gallery__sign">{{ \Carbon\Carbon::parse($photo->created_at)->locale('ru')->format('d.m.Y H:i:s') }}</div>
                                    <a class="gallery__delete" data-image-id="{{ $photo->id }}" href="#"></a>
                                    <a class="gallery__rotate" data-image-id="{{ $photo->id }}" href="#"></a>
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
        $(document).on('click', '.gallery__delete', function (e) {
          e.preventDefault();
          e.stopPropagation();
          if (!confirm('Удалить фото?')) {
            return;
          }

          let detailFoto = $(this).data('imageId');
          let self = this;

          const url = '{{ route('sheet_detail::detail_photo::upload_photos', [ 'sheetDetail' => $sheet_detail ]) }}/' + detailFoto;
          axios.post(url, {_method: 'delete'})
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

        $(document).on('click', '.gallery__rotate', function (e) {
          e.preventDefault();
          e.stopPropagation();

          let detailFoto = $(this).data('imageId');
          let rotate = 1 * $(this).data('imageRotate');
          let self = this;

          const url = '{{ route('sheet_detail::detail_photo::upload_photos', [ 'sheetDetail' => $sheet_detail ]) }}/' + detailFoto;
          axios.post(url, {rotate: rotate})
            .then((response) => {
              if (response.data.message) {
                alert(response.data.message);
                return;
              }
              // foto rotate
              self.parentElement.querySelector('.gallery__link').style.transform = 'rotate(' + (response.data.rotate * 90) + 'deg)';
              self.parentElement.querySelector('.gallery__link').dataset['rotate'] = parseInt(response.data.rotate);
              console.log('rotate = ' + response.data.rotate);
            }, (error) => {
              //     error callback
              console.log(error);
            });
        });

        Fancybox.bind('[data-fancybox]', {
          caption: function (fancybox, carousel, slide) {
            return (
              `${slide.index + 1} / ${carousel.slides.length} <br />` + slide.caption
            );
          },
          groupAll: true, // Group all items
          on: {
            // "*": (event, fancybox, slide) => {
            // },
            done: (fancybox, slide) => {
              slide.$content.style.transform = `rotate(${slide.$trigger.dataset['rotate'] * 90}deg)`;
              if(button = slide.$content.querySelector('button.carousel__button.is-close')) {
                button.remove();
              }
            },
          },
          // Carousel: {
          //
          // },
        });
      });

    </script>
@endsection
