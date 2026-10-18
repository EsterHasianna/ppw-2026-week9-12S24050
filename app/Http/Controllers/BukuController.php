<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('search')) {
            session(['last_search' => $request->search]);
        }

        $bukus = Buku::with('kategori')
            ->withCount('peminjamans')
            ->when($request->search, function ($q, $s) {
                $q->where(function ($w) use ($s) {
                    $w->where('judul', 'like', "%{$s}%")
                      ->orWhere('penulis', 'like', "%{$s}%")
                      ->orWhere('isbn', 'like', "%{$s}%");
                });
            })
            ->when($request->kategori_id, fn ($q, $k) => $q->where('kategori_id', $k))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total_judul'   => DB::table('bukus')->count(),
            'total_stok'    => DB::table('bukus')->sum('stok'),
            'sedang_pinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'dikembalikan'  => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.index', compact('bukus', 'stats', 'kategoris'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('buku.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn' => 'required|string|max:20|unique:bukus,isbn',
            'judul' => 'required|string|min:5|max:255',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|between:1900,' . date('Y'),
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil ditambahkan!');
    }

    public function show(Buku $buku)
    {
        $buku->load('kategori');
        return view('buku.show', compact('buku'));
    }

    public function edit(Buku $buku)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('buku.edit', compact('buku', 'kategoris'));
    }

    public function update(Request $request, Buku $buku)
    {
        $validated = $request->validate([
            'isbn' => [
                'required', 'string', 'max:20',
                Rule::unique('bukus', 'isbn')->ignore($buku->id),
            ],
            'judul' => 'required|string|min:5|max:255',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|between:1900,' . date('Y'),
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string',
        ]);

        $buku->update($validated);

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil diperbarui!');
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil dihapus!');
    }
}