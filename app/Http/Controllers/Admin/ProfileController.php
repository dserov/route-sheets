<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveUserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isNull;

class ProfileController extends Controller
{
    function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        if (\request()->user()->can('create', \Auth::user())) {
            $users = User::all();
        } else {
            $users = [ \Auth::user() ];
        }
        return view('admin.profile.index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        $this->authorize('create', \Auth::user());
        return view('admin.profile.update');
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', \Auth::user());
        if (isNull($user->avatar) ) {
            $user->avatar = 'https://via.placeholder.com/150';
        }
        $request->replace($user->toArray())->flash();
        return view('admin.profile.update');
    }

    public function save(SaveUserRequest $request)
    {
        $this->authorize('update', \Auth::user());
        $user = User::findOrNew($request->input('id'));
        $data = $request->except(['password', 'is_admin', 'is_logistic', 'is_driver']);
        if ($request->input('password')) {
            $data['password'] = \Hash::make($request->input('password'));
        }
        if (\Auth::user()->is_admin) {
            $data['is_admin'] = $request->has('is_admin');
            $data['is_driver'] = $request->has('is_driver');
            $data['is_logistic'] = $request->has('is_logistic');
        }

        $user->fill($data);
        if (!$user->save()) {
            return redirect()->back()
                ->withErrors(['Не удалось сохранить!'])
                ->withInput();
        }

        return redirect()
            ->route('admin::profile::index')
            ->with('success', 'Пользователь сохранен!');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        return redirect()
            ->route('admin::profile::index')
            ->with('success', 'Пользователь удален!');
    }

    public function import() {
        $content = file_get_contents(storage_path('app\public') . DIRECTORY_SEPARATOR . 'drivers.txt');
        $lines = explode("\r\n", $content);
        $data = [];
        $password = \Hash::make('top_secret');
        foreach ($lines as $line) {
            $line = str_replace('.', ' ', $line);
            $parts = explode(' ', $line);
            $parts = array_filter($parts, function ($item) {
                return !empty($item);
            });

            // make name
            $name = implode(' ', $parts);

            // make login
            $parts = array_map(function ($item){
                return \Str::slug($item);
            }, $parts);

            $parts = array_values($parts);
            $login = (isset($parts[1]) ? $parts[1] : '')
                . (isset($parts[2]) ? $parts[2] : '')
                . '_'
                . (isset($parts[0]) ? $parts[0] : '');

            if (!$name) {
                continue;
            }
            $user = [
                'name' => $name,
                'email' => $login . '@ecos.ru',
                'password' => $password,
                'is_driver' => true,
                'created_at' => Carbon::now(),
            ];
            $data[] = $user;
        }

        User::insert($data);
        return 'Users imported';
    }
}
