<x-layout title="Tambah Kategori">
    <h3 class="text-del fw-bold mb-3">Tambah Kategori</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('kategori.store') }}" method="POST">
                @csrf
                @include('kategori._form')
                <div class="mt-4">
                    <button type="submit" class="btn btn-del">Simpan</button>
                    <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layout>