<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">ISBN</label>
        <input type="text" name="isbn" value="{{ old('isbn', $buku->isbn ?? '') }}"
               class="form-control @error('isbn') is-invalid @enderror">
        @error('isbn') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Kategori</label>
        <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
            <option value="">-- Pilih kategori --</option>
            @foreach($kategoris as $kategori)
                <option value="{{ $kategori->id }}"
                    @selected(old('kategori_id', $buku->kategori_id ?? '') == $kategori->id)>
                    {{ $kategori->nama_kategori }}
                </option>
            @endforeach
        </select>
        @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Judul</label>
        <input type="text" name="judul" value="{{ old('judul', $buku->judul ?? '') }}"
               class="form-control @error('judul') is-invalid @enderror">
        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Penulis</label>
        <input type="text" name="penulis" value="{{ old('penulis', $buku->penulis ?? '') }}"
               class="form-control @error('penulis') is-invalid @enderror">
        @error('penulis') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Penerbit</label>
        <input type="text" name="penerbit" value="{{ old('penerbit', $buku->penerbit ?? '') }}"
               class="form-control @error('penerbit') is-invalid @enderror">
        @error('penerbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Tahun Terbit</label>
        <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit', $buku->tahun_terbit ?? '') }}"
               class="form-control @error('tahun_terbit') is-invalid @enderror">
        @error('tahun_terbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Stok</label>
        <input type="number" name="stok" value="{{ old('stok', $buku->stok ?? 0) }}"
               class="form-control @error('stok') is-invalid @enderror">
        @error('stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Sinopsis</label>
        <textarea name="sinopsis" rows="4"
                  class="form-control @error('sinopsis') is-invalid @enderror">{{ old('sinopsis', $buku->sinopsis ?? '') }}</textarea>
        @error('sinopsis') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>