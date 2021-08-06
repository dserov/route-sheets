<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveUserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        if (\request()->user()->can('create', User::class)) {
            $users = User::all();
        } else {
            $users = [\Auth::user()];
        }
        return view('admin.profile.index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);
        return view('admin.profile.update');
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        if (empty($user->avatar)) {
            $user->avatar = 'https://via.placeholder.com/150';
        }
        $request->replace($user->toArray())->flash();
        return view('admin.profile.update');
    }

    public function save(SaveUserRequest $request)
    {
        $user = User::findOrNew($request->input('id'));

        $this->authorize('update', $user);

        $data = $request->except(['password', 'is_admin', 'is_logistic', 'is_driver', 'avatar']);
        if ($request->input('password')) {
            $data['password'] = \Hash::make($request->input('password'));
        }
        if (\Auth::user()->is_admin) {
            $data['is_admin'] = $request->has('is_admin');
            $data['is_driver'] = $request->has('is_driver');
            $data['is_logistic'] = $request->has('is_logistic');
        }

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $name = $image->hashName();
            $name = $this->_getUniqueFileName($name);
            $data['avatar'] = '/storage/' . $image->storeAs('avatars', $name, 'public');
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

    public function import(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'driver' => 'required',
        ]);

        $content = '';

        if ($request->hasfile('driver')) {
            $file = $request->file('driver');
            $content = $file->get();
        }
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
            $parts = array_map(function ($item) {
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
        return 'Drivers imported';
    }

    private function _getUniqueFileName($name)
    {
        $folder = storage_path('uploads');
        $name = $this->_getNewName($name);
        while (is_file($folder . DIRECTORY_SEPARATOR . $name)) {
            $name = $this->_getNewName($name);
        }
        return $name;
    }

    private function _getNewName($name)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $name = \Str::random(16) . ($ext ? "." . $ext : "");
        return \Str::lower($name);
    }

    public function showImportForm()
    {
        $this->authorize('create', User::class);
        return \View::make('admin.profile.import');
    }
}
