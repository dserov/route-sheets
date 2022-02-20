@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header d-md-flex">
                        <div class="flex-grow-1">
                            {{ __('Export') }}
                        </div>
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
                            <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                            <form action="{{route('admin::export::export')}}" method="post" class="d-flex justify-content-between">
                                @csrf
                                <input type="hidden" name="from_date" value="" required>
                                <input type="hidden" name="to_date" value="" required>
                                <button class="btn btn-primary mt-4" type="submit">Скачать архив</button>
                                <button class="btn btn-danger mt-4" type="button" id="delete_sheet">Удалить м.листы</button>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
      window.addEventListener('load', function() {
        $(function() {

          var start = moment().subtract(29, 'days');
          var end = moment();

          $('#delete_sheet').on('click', function (e) {
            let from = $('input[name="from_date"]').val();
            let to = $('input[name="to_date"]').val();

            if (!confirm('Удалить маршрутные листы за период ' + from  + ' - ' + to + ' ?')) {
              return;
            }

            let url =  "{{route('sheet::delete_by_period')}}";
            axios.post(url, {
              _method: 'delete',
              from: from,
              to: to,
            })
              .then((response) => {
                alert(response.data.message);
              }, (error) => {
                console.log(error);
              });
          });

          function cb(start, end) {
            $('#reportrange span').html(start.format('DD MMMM YYYY') + ' - ' + end.format('DD MMMM YYYY'));

            $('input[name="from_date"]').val(start.format('DD/MM/YYYY'));
            $('input[name="to_date"]').val(end.format('DD/MM/YYYY'));
          }

          $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
              'Сегодня': [moment(), moment()],
              'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
              'Последние 7 дней': [moment().subtract(6, 'days'), moment()],
              'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
              'Этот месяц': [moment().startOf('month'), moment().endOf('month')],
              'Прошлый месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            "locale": {
              "format": "DD/MM/YYYY",
              "separator": " - ",
              "applyLabel": "Выбрать",
              "cancelLabel": "Отмена",
              "fromLabel": "От",
              "toLabel": "До",
              "customRangeLabel": "Произвольный",
              "weekLabel": "W",
              "daysOfWeek": [
                "Вс",
                "Пн",
                "Вт",
                "Ср",
                "Чт",
                "Пт",
                "Сб"
              ],
              "monthNames": [
                "Январь",
                "Февраль",
                "Март",
                "Апрель",
                "Май",
                "Июнь",
                "Июль",
                "Август",
                "Сентябрь",
                "Октябрь",
                "Ноябрь",
                "Декабрь"
              ],
              "firstDay": 1
            },
          }, cb);
          cb(start, end);
        });
      });
    </script>
@endsection
