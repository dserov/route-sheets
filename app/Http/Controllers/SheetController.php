<?php

namespace App\Http\Controllers;

use App\Http\Requests\SheetRequest;
use App\Models\Sheet;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $sheets = Sheet::orderByDesc('data');
        if (!$request->user()->can('viewAny', Sheet::class)) {
            $sheets = $sheets->whereUserId(\Auth::id());
            if ($request->user()->is_driver) {
              $sheets = $sheets->whereData(Carbon::now()->format('Y-m-d'));
            }
        }
        $sheets = $sheets->paginate(30);

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

    /**
   * @param Sheet $sheet
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   * @throws AuthorizationException
   */
    public function delete(Sheet $sheet, Request $request)
    {
        $this->authorize('delete', $sheet);
        $this->deleteCascade($sheet);
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

    public function deleteByPeriod(Request $request)
  {
    $from = Carbon::createFromFormat('d/m/Y', $request->input('from'));
    $to = Carbon::createFromFormat('d/m/Y', $request->input('to'));
    header('Content-type: application/json');
    $response =array(
      'message' => 'Что-то пошло не по плану.',
      'error' => 'true',
    );
    try {
      if (!$from || !$to) {
        throw new \Exception('Формат входных дат не верный');
      }

      $sheets = Sheet::query()->whereBetween('data', [ $from->format('Y-m-d'), $to->format('Y-m-d')])
        ->with(['sheet_details', 'sheet_details.detail_fotos'])
        ->get();
      $sheets = $sheets->filter(function ($sheet) use ($request){
        return $request->user()->can('delete', $sheet);
      });

      $sheets->each(function ($sheet) {
        $this->deleteCascade($sheet);
      });

      $response['error'] = 'false';
      $response['message'] = 'Листы удалены!';
    } catch (NotFoundHttpException $notFoundException) {
      $response['message'] = 'Лист не найден';
    } catch (AuthorizationException $authorizationException) {
      $response['message'] = 'У вас нет прав делать это';
    } catch (\Exception $exception) {
      $response['message'] = $exception->getMessage();
    }
    return json_encode($response);
  }

    public function deleteCascade($sheet) {
      $sheet->sheet_details->each(function ($sheet_detail){
        $sheet_detail->detail_fotos->each(function ($detail_foto){
          $detail_foto->delete();
        });
        $sheet_detail->delete();
      });
      $sheet->delete();
    }
}
