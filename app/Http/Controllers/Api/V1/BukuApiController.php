<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BukuApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus (Publik)
     * Filter: ?search= &kategori_id= &tersedia=1 &sort= &dir= &per_page=
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['judul', 'penulis', 'tahun_terbit', 'stok', 'created_at'];
        $sortBy = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'judul';
        $sortDir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) $request->query('per_page', 5), 1), 50);

        $paginator = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->query('kategori_id'), fn ($q, $id) => $q->where('kategori_id', $id))
            ->when($request->boolean('tersedia'), fn ($q) => $q->where('stok', '>', 0))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar buku berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'last' => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/bukus/{id} (Publik)
     */
    public function show(string $id): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans'])
            ->withCount('peminjamans')
            ->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        return $this->successResponse(
            data: new BukuResource($buku),
            message: 'Detail buku berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/bukus (Bearer Token)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'isbn' => 'required|string|max:20|unique:bukus,isbn',
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
            'kategori_id' => 'required|integer|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string',
        ]);

        $buku = Buku::create($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->createdResponse(
            data: new BukuResource($buku),
            locationUrl: url("/api/v1/bukus/{$buku->id}"),
            message: 'Buku baru berhasil ditambahkan.'
        );
    }

    /**
     * PUT / PATCH /api/v1/bukus/{id} (Bearer Token)
     * PUT: seluruh field wajib dikirim. PATCH: hanya field yang dikirim yang diubah.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        $isPut = $request->isMethod('put');

        if ($isPut) {
            // Semantik PUT: representasi lengkap wajib dikirim
            $validated = $request->validate([
                'isbn' => "required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:150',
                'penerbit' => 'required|string|max:150',
                'tahun_terbit' => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
                'kategori_id' => 'required|integer|exists:kategoris,id',
                'stok' => 'required|integer|min:0',
                'sinopsis' => 'nullable|string',
            ]);

            // Di PUT murni, sinopsis yang tidak dikirim dianggap dikosongkan
            $validated['sinopsis'] = $request->input('sinopsis');
        } else {
            // Semantik PATCH: hanya field yang dikirim yang divalidasi dan diubah
            $validated = $request->validate([
                'isbn' => "sometimes|required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'judul' => 'sometimes|required|string|max:255',
                'penulis' => 'sometimes|required|string|max:150',
                'penerbit' => 'sometimes|required|string|max:150',
                'tahun_terbit' => 'sometimes|required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
                'kategori_id' => 'sometimes|required|integer|exists:kategoris,id',
                'stok' => 'sometimes|required|integer|min:0',
                'sinopsis' => 'sometimes|nullable|string',
            ]);

            if (empty($validated)) {
                return $this->problemResponse(
                    title: 'Bad Request',
                    detail: 'Permintaan PATCH wajib menyertakan minimal satu atribut yang ingin diperbarui.',
                    status: 400,
                    errorSlug: 'empty-patch-payload'
                );
            }
        }

        $buku->update($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->successResponse(
            data: new BukuResource($buku),
            message: $isPut
                ? 'Seluruh representasi buku berhasil diganti (PUT).'
                : 'Atribut buku berhasil diperbarui sebagian (PATCH).'
        );
    }

    /**
     * DELETE /api/v1/bukus/{id} (Bearer Token)
     * 409 jika buku masih punya peminjaman aktif, 204 jika berhasil dihapus.
     */
    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        $pinjamAktif = $buku->peminjamans()->where('status', 'dipinjam')->count();

        if ($pinjamAktif > 0) {
            return $this->problemResponse(
                title: 'Conflict - Buku Sedang Dipinjam',
                detail: "Buku '{$buku->judul}' tidak dapat dihapus karena masih memiliki {$pinjamAktif} peminjaman aktif.",
                status: 409,
                errorSlug: 'buku-has-active-loans'
            );
        }

        $buku->delete();

        return $this->noContentResponse();
    }
}