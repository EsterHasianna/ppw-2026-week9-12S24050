<x-layout title="Riwayat Peminjaman">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-del fw-bold mb-0">Riwayat Peminjaman</h3>
        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">&larr; Katalog Buku</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-del align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Buku</th>
                        <th>NIM</th>
                        <th>Peminjam</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamans as $p)
                        <tr>
                            <td>{{ $p->kode_pinjam }}</td>
                            <td>{{ $p->buku->judul }}</td>
                            <td>{{ $p->nim_peminjam }}</td>
                            <td>{{ $p->nama_peminjam }}</td>
                            <td>{{ $p->tanggal_pinjam->format('d M Y') }}</td>
                            <td>{{ $p->tanggal_jatuh_tempo->format('d M Y') }}</td>
                            <td>{{ $p->tanggal_kembali?->format('d M Y') ?? '-' }}</td>
                            <td>
                                @if($p->status === 'dipinjam')
                                    <span class="badge text-bg-warning">Dipinjam</span>
                                @else
                                    <span class="badge text-bg-success">Dikembalikan</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($p->status === 'dipinjam')
                                    <form action="{{ route('peminjaman.kembali', $p->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Kembalikan buku ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-del">Kembalikan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Belum ada transaksi peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $peminjamans->links() }}
    </div>
</x-layout>