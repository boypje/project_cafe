<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    public function data()
    {
        return response()->json(User::orderBy('level')->orderBy('name')->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'level' => (int) $u->level,
                'saya' => $u->id === auth()->id(),
            ];
        }));
    }

    private function validasi(Request $request, $id = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'level' => ['required', Rule::in([1, 2])],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:6'],
        ], ['email.unique' => 'Email sudah dipakai pengguna lain.']);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->level = $data['level'];
        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json(['message' => 'Pengguna ditambahkan.']);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $this->validasi($request, $id);

        if ($user->id === auth()->id() && (int) $data['level'] !== 1) {
            return response()->json(['message' => 'Anda tidak bisa menurunkan level akun sendiri.'], 422);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->level = $data['level'];
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return response()->json(['message' => 'Pengguna diperbarui.']);
    }

    public function destroy($id)
    {
        if ((int) $id === auth()->id()) {
            return response()->json(['message' => 'Tidak bisa menghapus akun yang sedang dipakai.'], 422);
        }
        User::findOrFail($id)->delete();

        return response()->json(['message' => 'Pengguna dihapus.']);
    }

    public function profil()
    {
        return view('user.profil', ['profil' => auth()->user()]);
    }

    public function updateProfil(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'old_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'old_password.required_with' => 'Isi password lama untuk mengganti password.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        if (! empty($data['password'])) {
            if (! Hash::check($data['old_password'], $user->password)) {
                return response()->json([
                    'message' => 'Password lama tidak sesuai.',
                    'errors' => ['old_password' => ['Password lama tidak sesuai.']],
                ], 422);
            }
            $user->password = Hash::make($data['password']);
        }

        $user->name = $data['name'];
        $user->save();

        return response()->json(['message' => 'Profil diperbarui.']);
    }
}
