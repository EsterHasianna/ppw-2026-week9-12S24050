<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans (Bearer Token)
     * Filter: ?status=dipinjam|dikembalikan &nim= &per_page=
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 5), 1), 50);

        $paginator = Peminjaman::query()
            ->with('buku')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('nim'), fn ($q, $nim) => $q->where('nim_peminjam', $nim))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar peminjaman berhasil diambil.',
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
     * POST /api/v1/peminjamans (Bearer Token)
     * Transaksi ACID: kunci baris buku, cek stok & duplikasi, lalu kurangi stok.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id' => 'required|integer|exists:bukus,id',
            'nim_peminjam' => 'required|string|max:15',
            'nama_peminjam' => 'required|string|max:150',
            'durasi_hari' => 'required|integer|min:1|max:30',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // 1. Kunci baris buku (SELECT ... FOR UPDATE) agar tidak ada race condition
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();

            // 2. Stok habis -> 422
            if ($buku->stok < 1) {
                return $this->problemResponse(
                    title: 'Unprocessable Content',
                    detail: "Stok buku '{$buku->judul}' sedang habis.",
                    status: 422,
                    errorSlug: 'stok-habis'
                );
            }

            // 3. Mahasiswa yang sama masih meminjam buku yang sama -> 409
            $masihDipinjam = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $validated['nim_peminjam'])
                ->where('status', 'dipinjam')
                ->exists();

            if ($masihDipinjam) {
                return $this->problemResponse(
                    title: 'Conflict - Peminjaman Ganda',
                    detail: "Mahasiswa dengan NIM '{$validated['nim_peminjam']}' masih meminjam buku ini dan belum mengembalikannya.",
                    status: 409,
                    errorSlug: 'duplicate-loan'
                );
            }

            // 4. Kurangi stok dan catat peminjaman secara atomik
            $buku->decrement('stok');

            $tanggalPinjam = now();

            $peminjaman = Peminjaman::create([
                'kode_pinjam' => 'PJM-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 4)),
                'buku_id' => $buku->id,
                'nim_peminjam' => strtoupper(trim($validated['nim_peminjam'])),
                'nama_peminjam' => $validated['nama_peminjam'],
                'tanggal_pinjam' => $tanggalPinjam->toDateString(),
                'tanggal_jatuh_tempo' => $tanggalPinjam->copy()->addDays($validated['durasi_hari'])->toDateString(),
                'status' => 'dipinjam',
            ]);

            $peminjaman->load('buku');

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: 'Peminjaman berhasil dicatat. Stok buku sudah dikurangi.'
            );
        });
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan (Bearer Token)
     * Transaksi ACID: kunci peminjaman & buku, ubah status, tambah stok (+1).
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->first();

            if (!$peminjaman) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: "Data peminjaman dengan ID '{$id}' tidak ditemukan.",
                    status: 404,
                    errorSlug: 'peminjaman-not-found'
                );
            }

            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    title: 'Conflict - Sudah Dikembalikan',
                    detail: 'Buku pada peminjaman ini sudah dikembalikan sebelumnya.',
                    status: 409,
                    errorSlug: 'already-returned'
                );
            }

            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            $buku->increment('stok');

            $peminjaman->load('buku');

            return $this->successResponse(
                data: new PeminjamanResource($peminjaman),
                message: 'Buku berhasil dikembalikan. Stok buku sudah ditambah.'
            );
        });
    }
}