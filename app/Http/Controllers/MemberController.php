<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    private array $members = [
        ['id' => 1, 'nama' => 'Siti Aminah', 'nim' => '2101001', 'email' => 'siti.aminah@example.com', 'nomor_telepon' => '081234567890', 'alamat' => 'Jl. Merdeka No. 1', 'status' => 'aktif'],
        ['id' => 2, 'nama' => 'Budi Santoso', 'nim' => '2101002', 'email' => 'budi.santoso@example.com', 'nomor_telepon' => '081298765432', 'alamat' => 'Jl. Sudirman No. 5', 'status' => 'aktif'],
        ['id' => 3, 'nama' => 'Rina Wijaya', 'nim' => '2101003', 'email' => 'rina.wijaya@example.com', 'nomor_telepon' => '081211112222', 'alamat' => 'Jl. Diponegoro No. 9', 'status' => 'nonaktif'],
    ];

    public function index()
    {
        $members = $this->members;

        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(StoreMemberRequest $request)
    {
        $validated = $request->validated();

        return redirect()->route('members.index')
            ->with('success', "Anggota \"{$validated['nama']}\" berhasil ditambahkan (data dummy, belum tersimpan ke database).");
    }

    public function show(string $id)
    {
        $member = collect($this->members)->firstWhere('id', (int) $id);

        abort_if(! $member, 404);

        return view('members.show', compact('member'));
    }

    public function edit(string $id)
    {
        return "MemberController@edit, id: {$id}";
    }

    public function update(Request $request, string $id)
    {
        return "MemberController@update, id: {$id}";
    }

    public function destroy(string $id)
    {
        return "MemberController@destroy, id: {$id}";
    }
}