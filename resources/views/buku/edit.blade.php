<x-layout title="Edit Buku">
    <h3 class="text-del fw-bold mb-3">Edit Buku</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('buku.update', $buku) }}" method="POST">
                @csrf
                @method('PUT')
                @include('buku._form')
                <div class="mt-4">
                    <button type="submit" class="btn btn-del">Perbarui</button>
                    <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layout>