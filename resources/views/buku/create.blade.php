<x-layout title="Tambah Buku">
    <h3 class="text-del fw-bold mb-3">Tambah Buku</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('buku.store') }}" method="POST">
                @csrf
                @include('buku._form')
                <div class="mt-4">
                    <button type="submit" class="btn btn-del">Simpan</button>
                    <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layout>