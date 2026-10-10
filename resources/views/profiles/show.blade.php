@extends('layouts.app')

@section('title', 'Profil Petugas')

@section('content')
    <style>
        .profil-form { max-width: 500px; }
        .profil-form label { display: block; margin-top: 12px; font-weight: bold; }
        .profil-form input { width: 100%; padding: 6px; margin-top: 4px; box-sizing: border-box; }
        .profil-form .error { color: #b91c1c; font-size: 14px; margin-top: 4px; }
    </style>

    <h1>Profil Petugas</h1>
    <p><em>Data diambil dari akun yang sedang login lewat <code>auth()-&gt;user()</code> - tidak bisa diisi dari form.</em></p>

    <table>
        <tr>
            <th>Nama</th>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th>Role</th>
            <td>{{ ucfirst($user->role) }}</td>
        </tr>
    </table>

    <h2>Ganti Password</h2>

    <form method="POST" action="{{ route('profil.password.update') }}" class="profil-form">
        @csrf
        @method('PUT')

        <label for="password_lama">Password lama</label>
        <input type="password" name="password_lama" id="password_lama">
        @error('password_lama')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password">Password baru</label>
        <input type="password" name="password" id="password">
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password_confirmation">Konfirmasi password baru</label>
        <input type="password" name="password_confirmation" id="password_confirmation">

        <p><button type="submit" class="btn">Simpan Password Baru</button></p>
    </form>
@endsection