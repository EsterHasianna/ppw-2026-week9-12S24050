<x-layout title="Detail Kategori">
    <h3 class="text-del fw-bold mb-1">{{ $kategori->nama_kategori }}</h3>
    <p class="text-muted">Kode: {{ $kategori->kode_kategori }}</p>

    <div class="card shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table table-hover table-del align-middle mb-0">
                <thead>
                    <tr>
                        <th>ISBN</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bukus as $buku)
                        <tr>
                            <td>{{ $buku->isbn }}</td>
                            <td><a href="{{ route('buku.show', $buku) }}">{{ $buku->judul }}</a></td>
                            <td>{{ $buku->penulis }}</td>
                            <td>{{ $buku->stok }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada buku di kategori ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $bukus->links() }}

    <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Kembali</a>
</x-layout>