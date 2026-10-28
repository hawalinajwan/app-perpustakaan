<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category');

        if ($request->filled('judul')) {
            $query->where('judul', 'like', '%'.$request->query('judul').'%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        return BookResource::collection($query->paginate(10));
    }

    public function show(string $id)
    {
        return new BookResource(Book::with('category')->findOrFail($id));
    }

    public function store(StoreBookRequest $request)
    {
        $book = Book::create($request->validated());

        return (new BookResource($book->load('category')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, string $id)
    {
        $book = Book::findOrFail($id);

        $book->update($request->validate([
            'judul' => 'required|string|max:200',
            'penulis' => 'required|string|max:100',
            'penerbit' => 'required|string|max:100',
            'tahun_terbit' => 'required|integer|min:1900|max:'.date('Y'),
            'isbn' => 'nullable|string|max:20',
            'stok' => 'required|integer|min:0',
            'category_id' => 'required|integer|exists:categories,id',
        ]));

        return new BookResource($book->load('category'));
    }

    public function destroy(string $id)
    {
        Book::findOrFail($id)->delete();

        return response()->json(['message' => 'Buku berhasil dihapus.']);
    }
}
