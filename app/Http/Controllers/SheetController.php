<?php

namespace App\Http\Controllers;

use App\Http\Requests\SheetRequest;
use App\Models\Sheet;
use App\Models\User;
use Illuminate\Http\Request;

class SheetController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        if ($request->user()->can('viewAny', Sheet::class)) {
            $sheets = Sheet::orderByDesc('data')->paginate(20);
        } else {
            $sheets = Sheet::orderByDesc('data')->where('user_id', \Auth::id())->paginate(20);
        }

        return view('sheet.index', [
            'sheets' => $sheets,
        ]);
    }

    public function search(Request $request)
    {
        if ($request->has('search')) {
            $search_string = $request->input('search');
            $sheets = Sheet::where('name', 'like', '%' . $search_string . '%')
                ->orWhere('nomer', 'like', '%' . $search_string . '%')
                ->orderByDesc('data')
                ->get();
        } else {
            $sheets = Sheet::orderByDesc('data')
                ->get();
        }

        $html = \View::make('sheet', [ 'sheets' => $sheets ])->render();
        return response()->json(['html' => $html]);
    }

    public function delete(Sheet $sheet, Request $request)
    {
        $this->authorize('delete', $sheet);
        $sheet->delete();
        return back()->with('status', __('List deleted'));
    }

    public function update(Sheet $sheet)
    {
        $this->authorize('update', $sheet);
        $drivers = User::where('is_driver', true)
            ->get(['id', 'name'])
            ->pluck('name', 'id');
        $drivers->prepend('Не установлен', '');

        return \View::make('sheet.update', [
                'drivers' => $drivers,
                'sheet' => $sheet,
            ]
        );
    }

    public function store(SheetRequest $request, Sheet $sheet)
    {
        $this->authorize('update', $sheet);
        $sheet->fill($request->validated());
        $sheet->saveOrFail();
        return response()->redirectToRoute('sheet::index')->with('status', __('Sheet saved!'));
    }
}
