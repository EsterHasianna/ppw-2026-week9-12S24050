<x-layout title="Daftar Kategori">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-del fw-bold mb-0">Daftar Kategori</h3>
        <a href="{{ route('kategori.create') }}" class="btn btn-del">+ Tambah Kategori</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-del align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Buku</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoris as $kategori)
                        <tr>
                            <td>{{ $kategoris->firstItem() + $loop->index }}</td>
                            <td>{{ $kategori->kode_kategori }}</td>
                            <td>{{ $kategori->nama_kategori }}</td>
                            <td>{{ $kategori->bukus_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('kategori.show', $kategori) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $kategoris->links() }}
    </div>
</x-layout>