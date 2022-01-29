@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header d-flex">
                        <div class="flex-grow-1">
                            {{ __('Sheet details') }} <strong>&laquo;{{ $sheet->name }}&raquo;</strong><br>
                            № <strong>{{ $sheet->nomer }}</strong> от <strong>{{ $sheet->data }}</strong>
                            <br>
                            <strong>Адресов: {{ $sheet_details->count() }}</strong>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="history.go(-1);">{{ __('Back') }}</button>
                            </div>
                            <form class="form-inline my-2">
                                <input class="form-control" type="search" placeholder="Search" aria-label="Search"
                                       id="search_input">
                                <button class="btn btn-outline-success my-2 my-sm-0" type="submit" style="display: none">
                                    Search
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <div class="container-fluid">
                            <div class="row border-bottom border-top my-2 py-2">
                                <div class="col-1 font-weight-bold"><label><input type="radio" name="sorting" value="0" class="d-none"> №</label></div>
                                <div class="col-5 col-sm-4 font-weight-bold"><label><input type="radio" name="sorting" value="1" class="d-none"> Контрагент</label></div>
                                <div class="col-5 col-sm-4 font-weight-bold"><label><input type="radio" name="sorting" value="2" class="d-none"> Площадка</label></div>
                                <div class="col col-sm-3 font-weight-bold">&nbsp;</div>
                            </div>
                            <div id="sheet_detail">
                                @include('sheet_detail')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
      window.addEventListener('load', function () {

        let handleSearch = function () {
          let value = $(this).val();
          value = value.trim().toLowerCase();
          let $rows = $('.row', '#sheet_detail');
          $rows.each(function (index) {
            let content = $(this).text().toLowerCase();
            if (content.search(value) >= 0) {
              $(this).removeClass('d-none');
            } else {
              $(this).addClass('d-none');
            }
          });
        };


        $('#search_input').on('keyup', _.debounce(handleSearch, 500));
        $('#search_input').on('search', _.debounce(handleSearch, 500));


        let sortTableHandler = function (fieldNum){
          let rows = $('#sheet_detail > div').get();

          rows.sort(function(a, b) {
            let A = $(a).children('div').eq(fieldNum).text().toLowerCase();
            let B = $(b).children('div').eq(fieldNum).text().toLowerCase();

            if (parseInt(A) == A ) {
              A = parseInt(A);
            }

            if (parseInt(B) == B ) {
              B = parseInt(B);
            }

            if(A < B) {
              return -1;
            }

            if(A > B) {
              return 1;
            }

            return 0;
          });

          $.each(rows, function(index, row) {
            $('#sheet_detail').append(row);
          });
        };

        // сортировка
        $('input[name="sorting"]').on('change', function (e) {
          let fieldNum = this.value;
          sortTableHandler(fieldNum);
        });
      });
    </script>
@endsection
