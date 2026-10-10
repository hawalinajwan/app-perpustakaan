<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profiles.show', ['user' => auth()->user()]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'password_lama' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password baru.',
        ]);

        if (! Hash::check($validated['password_lama'], auth()->user()->password)) {
            return back()->withErrors(['password_lama' => 'Password lama salah.'])->withInput();
        }

        auth()->user()->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('profil')->with('success', 'Password berhasil diubah.');
    }
}
