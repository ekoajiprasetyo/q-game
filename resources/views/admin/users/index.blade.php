@extends('layouts.admin')

@section('title', 'Manajemen User')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div class="page-title-icon">
                <i data-feather="users"></i>
            </div>
            <div>
                <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; line-height: 1.2; color: var(--dark);">Manajemen User</h1>
                <p class="page-subtitle" style="margin: 4px 0 0 0; line-height: 1.2;">Kelola pengguna dan hak akses aplikasi</p>
            </div>
        </div>
        <button type="button" onclick="openCreateModal()" class="btn btn-primary">
            <i data-feather="plus"></i>
            <span>Tambah User</span>
        </button>
    </div>
</div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon">
                <i data-feather="users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalUsers }}</div>
                <div class="stat-label">Total User</div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon">
                <i data-feather="briefcase"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalTeachers }}</div>
                <div class="stat-label">Guru</div>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-icon">
                <i data-feather="shield"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalAdmins }}</div>
                <div class="stat-label">Administrator</div>
            </div>
        </div>
    </div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tanggal Dibuat</th>
                    <th style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 32px; height: 32px; background: var(--cream-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; color: var(--primary-dark);">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <span style="font-weight: 500;">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->role === 'admin')
                            <span class="badge badge-purple">Administrator</span>
                        @else
                            <span class="badge badge-green">Guru</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d M Y') }}</td>
                    <td style="text-align: center;">
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-ghost btn-icon" onclick="openEditModal({{ json_encode($user) }})" title="Edit">
                                <i data-feather="edit-2"></i>
                            </button>
                            
                            @if($user->id !== auth()->id())
                            <button type="button" class="btn btn-ghost btn-icon" style="color: var(--danger);" 
                                onclick="confirmDelete('{{ route('admin.users.destroy', $user->id) }}')" title="Hapus">
                                <i data-feather="trash-2"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i data-feather="users"></i>
                            </div>
                            <h3 class="empty-state-title">Belum ada user</h3>
                            <p class="empty-state-text">Silakan tambahkan user guru baru.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="pagination-container">
        {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

<!-- Create User Modal -->
<div class="modal-overlay" id="createUserModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <i data-feather="user-plus"></i>
                Tambah User Baru
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('createUserModal')">
                <i data-feather="x"></i>
            </button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST" onsubmit="handleUserSubmit(event, this)">
            @csrf
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" required>
                    <div class="invalid-feedback" style="display: none; color: var(--danger); font-size: 0.85em; margin-top: 0.25rem;"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@sekolah.sch.id" required>
                    <div class="invalid-feedback" style="display: none; color: var(--danger); font-size: 0.85em; margin-top: 0.25rem;"></div>
                </div>
                <div class="grid-2 mb-3">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="create_password" class="form-control" required style="padding-right: 2.5rem;">
                            <button type="button" onclick="togglePasswordModal('create_password')" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: var(--gray); display: flex; align-items: center;">
                                <i data-feather="eye" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" style="display: none; color: var(--danger); font-size: 0.85em; margin-top: 0.25rem;"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi</label>
                        <div style="position: relative;">
                            <input type="password" name="password_confirmation" id="create_password_confirm" class="form-control" required style="padding-right: 2.5rem;">
                            <button type="button" onclick="togglePasswordModal('create_password_confirm')" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: var(--gray); display: flex; align-items: center;">
                                <i data-feather="eye" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <div class="custom-dropdown">
                        <select name="role" class="form-control" required>
                            <option value="teacher">Guru</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <div class="invalid-feedback" style="display: none; color: var(--danger); font-size: 0.85em; margin-top: 0.25rem;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSaveCreate">Simpan User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <i data-feather="edit"></i>
                Edit User
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('editUserModal')">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="editUserForm" method="POST" onsubmit="handleUserSubmit(event, this)">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                    <div class="invalid-feedback" style="display: none; color: var(--danger); font-size: 0.85em; margin-top: 0.25rem;"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                    <div class="invalid-feedback" style="display: none; color: var(--danger); font-size: 0.85em; margin-top: 0.25rem;"></div>
                </div>
                <div class="grid-2 mb-3">
                    <div class="form-group">
                        <label class="form-label">Password Baru <span style="font-weight:normal; font-size: 0.8em;" class="text-muted">(Opsional)</span></label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="edit_password" class="form-control" placeholder="Kosongkan jika tetap" style="padding-right: 2.5rem;">
                            <button type="button" onclick="togglePasswordModal('edit_password')" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: var(--gray); display: flex; align-items: center;">
                                <i data-feather="eye" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" style="display: none; color: var(--danger); font-size: 0.85em; margin-top: 0.25rem;"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi</label>
                        <div style="position: relative;">
                            <input type="password" name="password_confirmation" id="edit_password_confirm" class="form-control" placeholder="Ulangi password" style="padding-right: 2.5rem;">
                            <button type="button" onclick="togglePasswordModal('edit_password_confirm')" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: var(--gray); display: flex; align-items: center;">
                                <i data-feather="eye" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <div class="custom-dropdown">
                        <select name="role" id="edit_role" class="form-control" required>
                            <option value="teacher">Guru</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <div class="invalid-feedback" style="display: none; color: var(--danger); font-size: 0.85em; margin-top: 0.25rem;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSaveEdit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateModal() {
        openModal('createUserModal');
    }

    function openEditModal(user) {
        const form = document.getElementById('editUserForm');
        // Construct clean URL without relying on blade parsing in JS string
        const baseUrl = "{{ route('admin.users.index') }}"; 
        // admin.users.index is .../admin/users. Update needs .../admin/users/{id}
        form.action = baseUrl + '/' + user.id;

        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        
        // Reset password fields
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_confirm').value = '';

        openModal('editUserModal');
    }

    function togglePasswordModal(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-feather', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-feather', 'eye');
        }
        feather.replace();
    }

    async function handleUserSubmit(event, form) {
        event.preventDefault();
        
        // Clear previous errors
        const errorElements = form.querySelectorAll('.invalid-feedback');
        errorElements.forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });
        const inputElements = form.querySelectorAll('.is-invalid');
        inputElements.forEach(el => el.classList.remove('is-invalid'));

        // Disable submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                // Success
                window.location.reload();
            } else if (response.status === 422) {
                // Validation Error
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            // Find the feedback div - usually the next sibling or inside the parent
                            let feedback = input.nextElementSibling;
                            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                                // Try finding inside parent (for password group)
                                feedback = input.closest('.form-group').querySelector('.invalid-feedback');
                                // Or next to custom dropdown
                                if (!feedback && input.closest('.custom-dropdown')) {
                                     feedback = input.closest('.custom-dropdown').querySelector('.invalid-feedback');
                                }
                            }
                            
                            if (feedback) {
                                feedback.textContent = data.errors[key][0];
                                feedback.style.display = 'block';
                            }
                        }
                    });
                } else {
                     alert('Terjadi kesalahan validasi.');
                }
            } else {
                alert(data.message || 'Terjadi kesalahan sistem.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Gagal menghubungi server.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    }
</script>

<style>
    /* Table Header Solid Orange */
    .table thead th {
        background-color: #E58B45 !important; /* Primary Dark Orange */
        color: #FFFFFF !important;
        border-bottom: none !important;
        padding: 1rem 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-size: 0.85rem;
        vertical-align: middle;
    }

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
        background: #E58B45 !important; /* Solid Orange Match Header */
        color: white !important;
        box-shadow: 0 4px 10px rgba(229, 139, 69, 0.4);
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
