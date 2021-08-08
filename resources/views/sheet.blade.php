@forelse($sheets as $sheet)
    <div class="row border-bottom my-2 py-2 g-2 flex-sm-column flex-md-row">
        <div class="col">
            <div class="row flex-column flex-sm-row flex-grow-1">
                <div class="col">{{ $sheet->nomer }}</div>
                <div class="col">{{ $sheet->data }}</div>
                <div class="col">
                    <a href="{{ route('sheet::sheet_detail', [ $sheet ]) }}" class="">{{ $sheet->name }}</a>
                </div>
                <div class="col">@if(!empty($sheet->user)) {{ $sheet->user->name }}@endif</div>
            </div>
        </div>
        <div class="col-auto">
            <div class="row flex-column flex-sm-row">
                @can('update', $sheet)
                    <a href="{{ route('sheet::update', [ $sheet ]) }}"
                       class="btn btn-outline-info">Редактор
                    </a>
                @endcan
                @can('delete', $sheet)
                    <a class="ml-1 btn btn-danger" rel="nofollow" href="#"
                       onclick="event.preventDefault();
                               if (confirm('Удалить маршрутный лист #{{ $sheet->id }}?')) { document.getElementById('delete-sheet-{{ $sheet->id }}').submit(); }">
                        Удалить
                    </a>
                    <form style="display: none" id="delete-sheet-{{ $sheet->id }}"
                          action="{{ route('sheet::delete', [$sheet]) }}" method="POST"
                          class="d-none">
                        @method('DELETE')
                        @csrf
                    </form>
                @endcan
            </div>
        </div>
    </div>
@empty
    <div class="row border-bottom my-2 py-2 g-2 flex-wrap flex-column flex-sm-row">
        <div class="col font-weight-bold">Data not found</div>
    </div>
@endforelse
