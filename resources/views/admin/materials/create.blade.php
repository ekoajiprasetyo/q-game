@extends('layouts.admin')

@section('title', 'Tambah Materi - ' . $topic->name)

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div>
                <div class="d-flex align-center gap-2 mb-2">
                    <a href="{{ route('admin.topics.materials.index', $topic) }}" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.5rem;">
                        <i data-feather="arrow-left"></i>
                    </a>
                    <span class="text-muted">{{ $topic->name }}</span>
                </div>
                <h1 class="page-title">
                    <div class="page-title-icon">➕</div>
                    Tambah Materi Baru
                </h1>
                <p class="page-subtitle">Buat materi baru untuk topik "{{ $topic->name }}"</p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card" style="max-width: 600px;">
        <div class="card-body">
            <form action="{{ route('admin.topics.materials.store', $topic) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="name">Nama Materi *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        placeholder="Contoh: Perkalian Dasar"
                        value="{{ old('name') }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control" 
                        placeholder="Deskripsi singkat materi ini..."
                        rows="3"
                    >{{ old('description') }}</textarea>
                </div>

                <div class="d-flex gap-3">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="icon">Ikon (Emoji)</label>
                        <input 
                            type="text" 
                            id="icon" 
                            name="icon" 
                            class="form-control" 
                            placeholder="📄"
                            value="{{ old('icon', '📄') }}"
                            maxlength="10"
                            style="font-size: 1.5rem; text-align: center;"
                        >
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="color">Warna Tema</label>
                        <input 
                            type="color" 
                            id="color" 
                            name="color" 
                            class="form-control" 
                            value="{{ old('color', '#B47EFF') }}"
                            style="height: 52px; padding: 0.25rem; cursor: pointer;"
                        >
                    </div>
                </div>

                <div class="d-flex gap-2" style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Simpan Materi
                    </button>
                    <a href="{{ route('admin.topics.materials.index', $topic) }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    feather.replace();
</script>
@endpush
