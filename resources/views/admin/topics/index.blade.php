@extends('layouts.admin')

@section('title', 'Topik & Materi')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="page-title-icon">
                    <i data-feather="folder"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; line-height: 1.2; color: var(--dark);">Topik & Materi</h1>
                    <p class="page-subtitle" style="margin: 4px 0 0 0; line-height: 1.2;">Kelola topik dan materi pembelajaran untuk game</p>
                </div>
            </div>
            <button type="button" class="btn btn-primary" onclick="openAddTopicModal()">
                <i data-feather="plus"></i>
                Tambah Topik
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body" style="padding: 1rem 1.5rem;">
            <form action="{{ route('admin.topics.index') }}" method="GET" class="d-flex gap-3 flex-wrap align-center">
                <div style="position:relative;">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Cari topik..."
                        value="{{ request('search') }}"
                        style="max-width: 280px; padding-left: 35px;"
                    >
                    <i data-feather="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:16px; color:#94A3B8;"></i>
                </div>
                <select name="subject" class="form-control" style="max-width: 200px;">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject }}" {{ request('subject') === $subject ? 'selected' : '' }}>
                            {{ $subject }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-secondary">
                    <i data-feather="filter"></i>
                    Filter
                </button>
                @if(request()->hasAny(['search', 'subject']))
                    <a href="{{ route('admin.topics.index') }}" class="btn btn-ghost">
                        <i data-feather="x"></i>
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Topics Grid -->
    @if($topics->isEmpty())
        <div class="card">
            <div class="empty-state" style="padding: 4rem 2rem;">
                <div class="empty-state-icon">📂</div>
                <div class="empty-state-title">Belum ada topik</div>
                <div class="empty-state-text">Mulai dengan membuat topik baru untuk mengelompokkan materi</div>
                <button type="button" class="btn btn-primary" onclick="openAddTopicModal()">
                    <i data-feather="plus"></i>
                    Buat Topik Pertama
                </button>
            </div>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
            @foreach($topics as $topic)
                <div class="card" style="position: relative;">
                    <div style="height: 6px; background: linear-gradient(90deg, {{ $topic->color ?? '#FF9B50' }}, {{ $topic->color ?? '#FFD699' }}80);"></div>
                    <div class="card-body">
                        <div class="d-flex align-center gap-3 mb-3">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, {{ $topic->color ?? '#FF9B50' }}20, {{ $topic->color ?? '#FFD699' }}40); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                {{ $topic->icon ?? '📚' }}
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.25rem;">{{ $topic->name }}</h3>
                                <span class="badge badge-orange">{{ $topic->subject }}</span>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-ghost btn-icon" title="Edit Topik" onclick="openEditTopicModal({{ $topic->id }}, '{{ addslashes($topic->name) }}', '{{ addslashes($topic->subject) }}', '{{ addslashes($topic->description ?? '') }}', '{{ $topic->icon ?? '📚' }}', '{{ $topic->color ?? '#FF9B50' }}')">
                                    <i data-feather="edit-2"></i>
                                </button>
                                <button type="button" class="btn btn-ghost btn-icon" title="Hapus" style="color: var(--danger);" onclick="confirmDelete('{{ route('admin.topics.destroy', $topic) }}')">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        @if($topic->description)
                            <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem; line-height: 1.5;">
                                {{ Str::limit($topic->description, 100) }}
                            </p>
                        @endif

                        <!-- Materials Preview -->
                        <div style="background: var(--cream); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1rem;">
                            <div class="d-flex align-center justify-between mb-2">
                                <span style="font-size: 0.8rem; font-weight: 600; color: var(--dark-soft);">MATERI</span>
                                <span class="badge badge-purple">{{ $topic->materials_count }} materi</span>
                            </div>
                            @if($topic->materials->isEmpty())
                                <p class="text-muted" style="font-size: 0.85rem; margin: 0;">Belum ada materi</p>
                            @else
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($topic->materials->take(4) as $material)
                                        <span style="padding: 0.25rem 0.75rem; background: var(--white); border-radius: var(--radius-full); font-size: 0.8rem; display: flex; align-items: center; gap: 0.375rem;">
                                            {{ $material->icon ?? '📄' }} {{ Str::limit($material->name, 15) }}
                                        </span>
                                    @endforeach
                                    @if($topic->materials->count() > 4)
                                        <span style="padding: 0.25rem 0.75rem; background: var(--primary); color: white; border-radius: var(--radius-full); font-size: 0.8rem;">
                                            +{{ $topic->materials->count() - 4 }} lagi
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="d-flex align-center justify-between" style="border-top: 2px solid var(--cream); padding-top: 1rem;">
                            <div>
                                <span style="font-size: 1.25rem; font-weight: 800; color: var(--dark);">{{ $topic->questions_count }}</span>
                                <span class="text-muted" style="font-size: 0.85rem;"> pertanyaan total</span>
                            </div>
                            <a href="{{ route('admin.topics.materials.index', $topic) }}" class="btn btn-primary btn-sm">
                                <i data-feather="layers"></i>
                                Kelola Materi
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($topics->hasPages())
            <div class="pagination mt-4">
                {{ $topics->links() }}
            </div>
        @endif
    @endif

    <!-- Add Topic Modal -->
    <div class="modal-overlay" id="addTopicModal">
        <div class="modal" style="display: flex; flex-direction: column; max-height: 85vh;">
            <div class="modal-header" style="flex-shrink: 0;">
                <h3 class="modal-title">
                    <i data-feather="folder-plus"></i>
                    Tambah Topik Baru
                </h3>
                <button type="button" class="modal-close" onclick="closeModal('addTopicModal')">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form action="{{ route('admin.topics.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                @csrf
                <div class="modal-body" style="overflow-y: auto; flex: 1;">
                    <div class="form-group">
                        <label class="form-label" for="add_name">Nama Topik *</label>
                        <input type="text" id="add_name" name="name" class="form-control" placeholder="Contoh: Matematika Dasar" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add_subject">Mata Pelajaran *</label>
                        <div class="custom-dropdown" id="addSubjectDropdown">
                            <input type="text" id="add_subject" name="subject" class="form-control" placeholder="Pilih atau ketik mata pelajaran..." autocomplete="off" required>
                            <div class="custom-dropdown-icon">
                                <i data-feather="chevron-down"></i>
                            </div>
                            <div class="dropdown-menu" id="addSubjectMenu">
                                @foreach($subjects as $subject)
                                    <div class="dropdown-item" data-value="{{ $subject }}">{{ $subject }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add_description">Deskripsi</label>
                        <textarea id="add_description" name="description" class="form-control" placeholder="Deskripsi singkat topik ini..." rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pilih Ikon</label>
                        <div class="icon-preview">
                            <div class="icon-preview-display" id="add_icon_preview">📚</div>
                            <span class="text-muted" style="font-size: 0.85rem;">Klik salah satu ikon di bawah</span>
                        </div>
                        <input type="hidden" name="icon" id="add_icon" value="📚">
                        <div class="icon-picker" id="add_icon_picker"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add_color">Warna Tema</label>
                        <input type="color" id="add_color" name="color" class="form-control" value="#FF9B50" style="height: 48px; padding: 0.25rem; cursor: pointer;">
                    </div>
                </div>
                <div class="modal-footer" style="flex-shrink: 0;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addTopicModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Topic Modal -->
    <div class="modal-overlay" id="editTopicModal">
        <div class="modal" style="display: flex; flex-direction: column; max-height: 85vh;">
            <div class="modal-header" style="flex-shrink: 0;">
                <h3 class="modal-title">
                    <i data-feather="edit-2"></i>
                    Edit Topik
                </h3>
                <button type="button" class="modal-close" onclick="closeModal('editTopicModal')">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="editTopicForm" method="POST" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                @csrf
                @method('PUT')
                <div class="modal-body" style="overflow-y: auto; flex: 1;">
                    <div class="form-group">
                        <label class="form-label" for="edit_name">Nama Topik *</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_subject">Mata Pelajaran *</label>
                        <div class="custom-dropdown" id="editSubjectDropdown">
                            <input type="text" id="edit_subject" name="subject" class="form-control" placeholder="Pilih atau ketik mata pelajaran..." autocomplete="off" required>
                            <div class="custom-dropdown-icon">
                                <i data-feather="chevron-down"></i>
                            </div>
                            <div class="dropdown-menu" id="editSubjectMenu">
                                @foreach($subjects as $subject)
                                    <div class="dropdown-item" data-value="{{ $subject }}">{{ $subject }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_description">Deskripsi</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pilih Ikon</label>
                        <div class="icon-preview">
                            <div class="icon-preview-display" id="edit_icon_preview">📚</div>
                            <span class="text-muted" style="font-size: 0.85rem;">Klik salah satu ikon di bawah</span>
                        </div>
                        <input type="hidden" name="icon" id="edit_icon" value="📚">
                        <div class="icon-picker" id="edit_icon_picker"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_color">Warna Tema</label>
                        <input type="color" id="edit_color" name="color" class="form-control" style="height: 48px; padding: 0.25rem; cursor: pointer;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editTopicModal')">Batal</button>
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

    // Icon pack for topics
    const topicIcons = [
        '📚', '📖', '📕', '📗', '📘', '📙', '📓', '📔',
        '🔢', '➕', '➖', '✖️', '➗', '🔣', '📐', '📏',
        '🌍', '🌎', '🌏', '🗺️', '🧭', '🏔️', '🌋', '🏝️',
        '🔬', '🧪', '🧫', '⚗️', '🔭', '🧲', '⚡', '🔋',
        '🇮🇩', '🗣️', '✍️', '📝', '🔤', '🔠', '📰', '📄',
        '🎨', '🎵', '🎭', '🎬', '🏃', '⚽', '🏀', '🎯',
        '💻', '🖥️', '📱', '🤖', '🧠', '💡', '⭐', '🏆'
    ];

    function renderIconPicker(containerId, inputId, previewId, selectedIcon) {
        const container = document.getElementById(containerId);
        container.innerHTML = topicIcons.map(icon => `
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

    function openAddTopicModal() {
        renderIconPicker('add_icon_picker', 'add_icon', 'add_icon_preview', '📚');
        document.getElementById('add_icon').value = '📚';
        document.getElementById('add_icon_preview').textContent = '📚';
        openModal('addTopicModal');
    }

    function openEditTopicModal(id, name, subject, description, icon, color) {
        const form = document.getElementById('editTopicForm');
        form.action = `/admin/topics/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_subject').value = subject;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_icon').value = icon;
        document.getElementById('edit_icon_preview').textContent = icon;
        document.getElementById('edit_color').value = color;
        renderIconPicker('edit_icon_picker', 'edit_icon', 'edit_icon_preview', icon);
        openModal('editTopicModal');
    }

    // Custom Dropdown Logic
    function initCustomDropdown(containerId) {
        const container = document.getElementById(containerId);
        const input = container.querySelector('input');
        const menu = container.querySelector('.dropdown-menu');
        const items = Array.from(menu.querySelectorAll('.dropdown-item'));

        input.addEventListener('focus', () => {
            container.classList.add('active');
            menu.classList.add('active');
            filterItems();
        });

        input.addEventListener('input', () => {
            container.classList.add('active');
            menu.classList.add('active');
            filterItems();
        });

        function filterItems() {
            const val = input.value.toLowerCase();
            let hasResults = false;
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(val)) {
                    item.style.display = 'flex';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Handle no results
            const existingNoResults = menu.querySelector('.dropdown-no-results');
            if (!hasResults && val !== '') {
                if (!existingNoResults) {
                    const noResults = document.createElement('div');
                    noResults.className = 'dropdown-no-results';
                    noResults.textContent = 'Tekan Enter untuk judul baru';
                    menu.appendChild(noResults);
                }
            } else if (existingNoResults) {
                existingNoResults.remove();
            }
        }

        menu.addEventListener('click', (e) => {
            const item = e.target.closest('.dropdown-item');
            if (item) {
                input.value = item.dataset.value;
                closeDropdown();
            }
        });

        function closeDropdown() {
            container.classList.remove('active');
            menu.classList.remove('active');
        }

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    // Initialize dropdowns
    document.addEventListener('DOMContentLoaded', () => {
        initCustomDropdown('addSubjectDropdown');
        initCustomDropdown('editSubjectDropdown');
    });
</script>

<style>
    /* Modern Orange Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-top: 2rem;
        padding-bottom: 2rem;
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
