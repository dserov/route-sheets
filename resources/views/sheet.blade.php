@forelse($sheets as $sheet)
    <tr class="d-sm-table-row d-flex flex-column">
        <th scope="row">{{ $sheet->nomer }}</th>
        <td>{{ $sheet->data }}</td>
        <td>
            <a href="{{ route('sheet_detail::show', [ $sheet ]) }}" class="">{{ $sheet->name }}</a>
        </td>
        <td>@if(!empty($sheet->user)) {{ $sheet->user->name }}@endif</td>
        <td>
            <a href="{{ route('sheet::update', [ $sheet ]) }}"
               class="btn btn-outline-info">Редактор
            </a>
            <a class="ml-1 btn btn-danger" rel="nofollow" href="#"
               onclick="event.preventDefault();
                       if (confirm('Удалить маршрутный лист #{{ $sheet->id }}?')) { document.getElementById('delete-sheet-{{ $sheet->id }}').submit(); }">
                Удалить
            </a>
            <form id="delete-sheet-{{ $sheet->id }}"
                  action="{{ route('sheet::delete', [$sheet]) }}" method="POST"
                  class="d-none">
                @method('DELETE')
                @csrf
            </form>
        </td>
    </tr>
@empty
    <tr class="d-sm-table-row d-flex flex-column">
        <th colspan="5">Data not found</th>
    </tr>
@endforelse
