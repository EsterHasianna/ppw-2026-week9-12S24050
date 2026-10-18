<x-layout title="Daftar Buku">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-del fw-bold mb-0">Daftar Buku</h3>
        <div>
            <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary">Riwayat Peminjaman</a>
            <a href="{{ route('buku.create') }}" class="btn btn-del">+ Tambah Buku</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-del align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ISBN</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bukus as $buku)
                        <tr>
                            <td>{{ $bukus->firstItem() + $loop->index }}</td>
                            <td>{{ $buku->isbn }}</td>
                            <td>{{ $buku->judul }}</td>
                            <td>{{ $buku->penulis }}</td>
                            <td><span class="badge text-bg-secondary">{{ $buku->kategori->nama_kategori }}</span></td>
                            <td>{{ $buku->stok }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-success btn-pinjam"
                                        data-bs-toggle="modal" data-bs-target="#modalPinjam"
                                        data-action="{{ route('buku.pinjam', $buku->id) }}"
                                        data-judul="{{ $buku->judul }}">Pinjam</button>
                                <a href="{{ route('buku.show', $buku) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                <a href="{{ route('buku.edit', $buku) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus buku ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data buku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $bukus->links() }}
    </div>

    <div class="modal fade" id="modalPinjam" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formPinjam" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Pinjam: <span id="judulPinjam"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">NIM Peminjam</label>
                        <input type="text" name="nim_peminjam" class="form-control" required minlength="6" maxlength="15">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Peminjam</label>
                        <input type="text" name="nama_peminjam" class="form-control" required minlength="3" maxlength="150">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-del">Pinjam Buku</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-pinjam').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('formPinjam').action = btn.dataset.action;
                document.getElementById('judulPinjam').textContent = btn.dataset.judul;
            });
        });
    </script>
</x-layout>