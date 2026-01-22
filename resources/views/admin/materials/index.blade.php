@extends('layouts.admin')

@section('title', 'Materi - ' . $topic->name)

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div>
                <div class="mb-3">
                    <a href="{{ route('admin.topics.index') }}" class="btn btn-ghost btn-sm" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--gray); font-weight: 500;">
                        <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
                        Kembali ke Topik
                    </a>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div class="page-title-icon">
                        <i data-feather="layers"></i>
                    </div>
                    <div>
                        <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; line-height: 1.2; color: var(--dark);">Materi</h1>
                        <p class="page-subtitle" style="margin: 4px 0 0 0; line-height: 1.2;">Kelola materi pembelajaran untuk topik <strong>{{ $topic->name }}</strong></p>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary" onclick="openAddMaterialModal()">
                <i data-feather="plus"></i>
                Tambah Materi
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body" style="padding: 1rem 1.5rem;">
            <form action="{{ route('admin.topics.materials.index', $topic) }}" method="GET" class="d-flex gap-3 flex-wrap align-center">
                <div style="position:relative;">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Cari materi..."
                        value="{{ request('search') }}"
                        style="max-width: 300px; padding-left: 35px;"
                    >
                    <i data-feather="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:16px; color:#94A3B8;"></i>
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i data-feather="filter"></i>
                    Filter
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.topics.materials.index', $topic) }}" class="btn btn-ghost">
                        <i data-feather="x"></i>
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Materials Grid -->
    @if($materials->isEmpty())
        <div class="card">
            <div class="empty-state" style="padding: 4rem 2rem;">
                <div class="empty-state-icon">📄</div>
                <div class="empty-state-title">Belum ada materi</div>
                <div class="empty-state-text">Tambahkan materi pertama untuk topik "{{ $topic->name }}"</div>
                <button type="button" class="btn btn-primary" onclick="openAddMaterialModal()">
                    <i data-feather="plus"></i>
                    Tambah Materi Pertama
                </button>
            </div>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.25rem;">
            @foreach($materials as $material)
                <div class="card" style="position: relative;">
                    <div style="height: 5px; background: linear-gradient(90deg, {{ $material->color ?? '#B47EFF' }}, {{ $material->color ?? '#9B5DE5' }}80);"></div>
                    <div class="card-body">
                        <div class="d-flex align-center gap-3 mb-3">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, {{ $material->color ?? '#B47EFF' }}20, {{ $material->color ?? '#B47EFF' }}40); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                {{ $material->icon ?? '📄' }}
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.125rem;">{{ $material->name }}</h3>
                                <span class="badge badge-blue">{{ $material->questions_count }} soal</span>
                            </div>
                        </div>
                        
                        @if($material->description)
                            <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem; line-height: 1.5;">
                                {{ Str::limit($material->description, 80) }}
                            </p>
                        @endif

                        <div class="d-flex align-center justify-between" style="border-top: 2px solid var(--cream); padding-top: 1rem; margin-top: auto;">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.questions.index', ['material_id' => $material->id]) }}" class="btn btn-secondary btn-sm">
                                    <i data-feather="list"></i>
                                    Lihat Soal
                                </a>
                                @php
                                    $hasActiveSession = $material->activeSession && $material->activeSession->session_pin;
                                @endphp
                                <button type="button" class="btn btn-sm {{ $hasActiveSession ? 'btn-success' : 'btn-primary' }} pin-btn-{{ $material->id }}" 
                                        @if($hasActiveSession) data-pin="{{ $material->activeSession->session_pin }}" title="Klik untuk menyalin" @endif
                                        onclick="event.preventDefault(); event.stopPropagation(); toggleGamePin(this, {{ $topic->id }}, {{ $material->id }})"
                                        style="font-weight: 600;">
                                    @if($hasActiveSession)
                                        <i data-feather="copy" style="width: 14px; height: 14px; margin-right: 4px;"></i> 
                                        {{ $material->activeSession->session_pin }}
                                    @else
                                        <i data-feather="play" style="width: 14px; height: 14px; margin-right: 4px;"></i> 
                                        Main
                                    @endif
                                </button>
                            </div>
                            
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-ghost btn-icon" title="Edit" onclick="openEditMaterialModal({{ $material->id }}, '{{ addslashes($material->name) }}', '{{ addslashes($material->description ?? '') }}', '{{ $material->icon ?? '📄' }}', '{{ $material->color ?? '#B47EFF' }}')">
                                    <i data-feather="edit-2"></i>
                                </button>
                                <button type="button" class="btn btn-ghost btn-icon" title="Hapus" style="color: var(--danger);" onclick="confirmDelete('{{ route('admin.topics.materials.destroy', [$topic, $material]) }}')">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($materials->hasPages())
            <div class="pagination-container">
                {{ $materials->appends(request()->query())->links() }}
            </div>
        @endif
    @endif

    <!-- Quick Info -->
    <div class="card mt-4" style="background: linear-gradient(135deg, rgba(125, 206, 160, 0.1), rgba(90, 184, 144, 0.1)); border: 2px solid rgba(125, 206, 160, 0.3);">
        <div class="card-body">
            <div class="d-flex align-center gap-3">
                <div style="font-size: 2rem;">💡</div>
                <div>
                    <h4 style="font-weight: 700; margin-bottom: 0.25rem;">Tips Pertanyaan</h4>
                    <p class="text-muted" style="margin: 0;">Buat pertanyaan yang variatif dengan tingkat kesulitan yang berbeda untuk membuat game lebih seru.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Material Modal -->
    <div class="modal-overlay" id="addMaterialModal">
        <div class="modal" style="display: flex; flex-direction: column; max-height: 85vh;">
            <div class="modal-header" style="flex-shrink: 0;">
                <h3 class="modal-title">
                    <i data-feather="file-plus"></i>
                    Tambah Materi Baru
                </h3>
                <button type="button" class="modal-close" onclick="closeModal('addMaterialModal')">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form action="{{ route('admin.topics.materials.store', $topic) }}" method="POST" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                @csrf
                <div class="modal-body" style="overflow-y: auto; flex: 1;">
                    <div class="form-group">
                        <label class="form-label" for="add_m_name">Nama Materi *</label>
                        <input type="text" id="add_m_name" name="name" class="form-control" placeholder="Contoh: Perkalian Dasar" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add_m_description">Deskripsi</label>
                        <textarea id="add_m_description" name="description" class="form-control" placeholder="Deskripsi singkat materi ini..." rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pilih Ikon</label>
                        <div class="icon-preview">
                            <div class="icon-preview-display" id="add_m_icon_preview">📄</div>
                            <span class="text-muted" style="font-size: 0.85rem;">Klik salah satu ikon di bawah</span>
                        </div>
                        <input type="hidden" name="icon" id="add_m_icon" value="📄">
                        <div class="icon-picker" id="add_m_icon_picker"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add_m_color">Warna Tema</label>
                        <input type="color" id="add_m_color" name="color" class="form-control" value="#B47EFF" style="height: 48px; padding: 0.25rem; cursor: pointer;">
                    </div>
                </div>
                <div class="modal-footer" style="flex-shrink: 0;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addMaterialModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Material Modal -->
    <div class="modal-overlay" id="editMaterialModal">
        <div class="modal" style="display: flex; flex-direction: column; max-height: 85vh;">
            <div class="modal-header" style="flex-shrink: 0;">
                <h3 class="modal-title">
                    <i data-feather="edit-2"></i>
                    Edit Materi
                </h3>
                <button type="button" class="modal-close" onclick="closeModal('editMaterialModal')">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="editMaterialForm" method="POST" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                @csrf
                @method('PUT')
                <div class="modal-body" style="overflow-y: auto; flex: 1;">
                    <div class="form-group">
                        <label class="form-label" for="edit_m_name">Nama Materi *</label>
                        <input type="text" id="edit_m_name" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_m_description">Deskripsi</label>
                        <textarea id="edit_m_description" name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pilih Ikon</label>
                        <div class="icon-preview">
                            <div class="icon-preview-display" id="edit_m_icon_preview">📄</div>
                            <span class="text-muted" style="font-size: 0.85rem;">Klik salah satu ikon di bawah</span>
                        </div>
                        <input type="hidden" name="icon" id="edit_m_icon" value="📄">
                        <div class="icon-picker" id="edit_m_icon_picker"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_m_color">Warna Tema</label>
                        <input type="color" id="edit_m_color" name="color" class="form-control" style="height: 48px; padding: 0.25rem; cursor: pointer;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editMaterialModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    feather.replace();

    const topicId = {{ $topic->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Icon pack for materials
    const materialIcons = [
        '📄', '📃', '📋', '📝', '✏️', '📌', '📍', '🔖',
        '➕', '➖', '✖️', '➗', '🔢', '🔣', '📐', '📏',
        '🌍', '🌎', '🌏', '🗺️', '🏛️', '⏰', '📅', '🎭',
        '🔬', '🧪', '⚗️', '🧫', '🔭', '🌡️', '💊', '🧬',
        '📖', '📚', '🔤', '🔠', '✍️', '🗣️', '💬', '📰',
        '🎨', '🎵', '🎹', '🎸', '🏃', '⚽', '🏀', '🎯',
        '💻', '🖥️', '📱', '⌨️', '🖱️', '💡', '⭐', '🏆'
    ];

    function renderIconPicker(containerId, inputId, previewId, selectedIcon) {
        const container = document.getElementById(containerId);
        container.innerHTML = materialIcons.map(icon => `
            <div class="icon-picker-item ${icon === selectedIcon ? 'selected' : ''}" data-icon="${icon}" onclick="selectIcon('${inputId}', '${previewId}', '${containerId}', '${icon}')">
                ${icon}
            </div>
        `).join('');
    }

    function selectIcon(inputId, previewId, containerId, icon) {
        document.getElementById(inputId).value = icon;
        document.getElementById(previewId).textContent = icon;
        document.querySelectorAll(`#${containerId} .icon-picker-item`).forEach(el => {
            el.classList.toggle('selected', el.dataset.icon === icon);
        });
    }

    function openAddMaterialModal() {
        renderIconPicker('add_m_icon_picker', 'add_m_icon', 'add_m_icon_preview', '📄');
        document.getElementById('add_m_icon').value = '📄';
        document.getElementById('add_m_icon_preview').textContent = '📄';
        openModal('addMaterialModal');
    }

    function openEditMaterialModal(id, name, description, icon, color) {
        const form = document.getElementById('editMaterialForm');
        form.action = `/admin/topics/${topicId}/materials/${id}`;
        document.getElementById('edit_m_name').value = name;
        document.getElementById('edit_m_description').value = description;
        document.getElementById('edit_m_icon').value = icon;
        document.getElementById('edit_m_icon_preview').textContent = icon;
        document.getElementById('edit_m_color').value = color;
        renderIconPicker('edit_m_icon_picker', 'edit_m_icon', 'edit_m_icon_preview', icon);
        openModal('editMaterialModal');
    }

</script>

<style>
    /* Pagination Container Reset */
    .pagination-container {
        margin-top: 2rem;
        padding-bottom: 2rem;
        display: flex;
        justify-content: center;
    }

    /* Modern Orange Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .page-item {
        margin: 0 2px;
    }

    .page-item .page-link {
        border: none;
        border-radius: 12px;
        min-width: 40px; height: 40px;
        padding: 0 12px;
        display: flex; align-items: center; justify-content: center;
        color: #64748B;
        font-weight: 700;
        font-size: 0.9rem;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
    }
    
    .page-item:not(.active):not(.disabled) .page-link:hover {
        background: #FFF7ED;
        color: #EA580C;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(234, 88, 12, 0.15);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, #FF9F43, #EE5A24);
        color: white;
        box-shadow: 0 4px 10px rgba(255, 155, 80, 0.4);
        transform: scale(1.05);
        z-index: 2;
    }

    .page-item.disabled .page-link {
        background: transparent;
        color: #CBD5E1;
        box-shadow: none;
        cursor: default;
    }
    
    .page-link svg { width: 16px; height: 16px; }
</style>
@endpush
