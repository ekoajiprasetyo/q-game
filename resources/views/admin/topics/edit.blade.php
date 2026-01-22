@extends('layouts.admin')

@section('title', 'Edit Topik')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div>
                <h1 class="page-title">
                    <div class="page-title-icon">{{ $topic->icon ?? '📚' }}</div>
                    Edit Topik
                </h1>
                <p class="page-subtitle">{{ $topic->name }}</p>
            </div>
            <a href="{{ route('admin.topics.index') }}" class="btn btn-secondary">
                <i data-feather="arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card" style="max-width: 600px;">
        <div class="card-body">
            <form action="{{ route('admin.topics.update', $topic) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="name">Nama Topik *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        placeholder="Contoh: Perkalian Dasar"
                        value="{{ old('name', $topic->name) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="subject">Mata Pelajaran *</label>
                    <input 
                        type="text" 
                        id="subject" 
                        name="subject" 
                        class="form-control" 
                        placeholder="Contoh: Matematika"
                        value="{{ old('subject', $topic->subject) }}"
                        list="subjects-list"
                        required
                    >
                    <datalist id="subjects-list">
                        @foreach($subjects as $subject)
                            <option value="{{ $subject }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control" 
                        placeholder="Deskripsi singkat topik ini..."
                        rows="3"
                    >{{ old('description', $topic->description) }}</textarea>
                </div>

                <div class="d-flex gap-3">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="icon">Ikon (Emoji)</label>
                        <input 
                            type="text" 
                            id="icon" 
                            name="icon" 
                            class="form-control" 
                            placeholder="📚"
                            value="{{ old('icon', $topic->icon ?? '📚') }}"
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
                            value="{{ old('color', $topic->color ?? '#FF9B50') }}"
                            style="height: 52px; padding: 0.25rem; cursor: pointer;"
                        >
                    </div>
                </div>

                <div class="d-flex gap-2" style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.topics.index') }}" class="btn btn-secondary">
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
