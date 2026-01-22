@extends('layouts.admin')

@section('title', 'Daftar Turnamen')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <div style="flex: 1;">
            <div class="page-title">
                <div class="page-title-icon">
                    <i data-feather="award"></i>
                </div>
                <div>
                    <span>Turnamen</span>
                    <div class="page-subtitle">Kelola kompetisi antar tim/kelas</div>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.tournaments.create') }}" class="btn btn-primary">
            <i data-feather="plus"></i>
            Buat Turnamen
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        @if($tournaments->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th style="width: 140px;">Kode PIN</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 80px;">Tim</th>
                            <th style="width: 180px;">Dibuat Pada</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tournaments as $tournament)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--dark);">
                                        {{ $tournament->title }}
                                    </div>
                                    @if($tournament->description)
                                        <div class="text-muted" style="font-size: 0.8rem; font-weight: normal; margin-top: 2px;">
                                            {{ Str::limit($tournament->description, 50) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($tournament->pin)
                                        <button type="button" 
                                                class="btn btn-sm btn-success pin-copy-btn" 
                                                id="pin-btn-{{ $tournament->id }}"
                                                onclick="copyTournamentPin('{{ $tournament->pin }}', {{ $tournament->id }})"
                                                title="Klik untuk menyalin PIN"
                                                style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">
                                            <i data-feather="copy" style="width: 12px; height: 12px; margin-right: 4px;"></i>
                                            <span class="pin-text">{{ $tournament->pin }}</span>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tournament->status == 'setup')
                                        <span class="badge badge-yellow">Persiapan</span>
                                    @elseif($tournament->status == 'active')
                                        <span class="badge badge-green">Berlangsung</span>
                                    @else
                                        <span class="badge badge-blue">Selesai</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-center gap-1">
                                        <i data-feather="users" style="width: 14px; height: 14px; color: var(--gray);"></i>
                                        <span>{{ $tournament->teams_count ?? $tournament->teams()->count() }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem; color: var(--gray); white-space: nowrap;">
                                        {{ $tournament->created_at->format('d M Y, H:i') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.tournaments.show', $tournament) }}" class="btn btn-ghost btn-icon" title="Detail">
                                            <i data-feather="eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-ghost btn-icon" title="Hapus" style="color: var(--danger);" onclick="confirmDelete('{{ route('admin.tournaments.destroy', $tournament) }}')">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($tournaments->hasPages())
                <div class="pagination-container">
                    {{ $tournaments->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @else
            <div class="empty-state" style="padding: 4rem 2rem;">
                <div class="empty-state-icon">🏆</div>
                <h3 class="empty-state-title">Belum ada turnamen</h3>
                <p class="empty-state-text">Buat turnamen baru untuk memulai kompetisi!</p>
                <a href="{{ route('admin.tournaments.create') }}" class="btn btn-primary">
                    <i data-feather="plus"></i>
                    Buat Turnamen
                </a>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    /* Table Header Solid Orange */
    .table thead th {
        background-color: #E58B45 !important;
        color: #FFFFFF !important;
        border-bottom: none !important;
        padding: 1rem 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-size: 0.85rem;
        vertical-align: middle;
    }
    
    .table tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--cream);
    }
    
    .table tbody tr:hover {
        background: var(--cream);
    }

    /* PIN Copy Button - Same style as dashboard materi terbaru */
    .pin-copy-btn {
        background: #22C55E !important;
        border-color: #22C55E !important;
        color: white !important;
        border-radius: 50px !important;
        font-weight: 600;
        transition: all 0.2s;
    }
    .pin-copy-btn:hover {
        background: #16A34A !important;
        border-color: #16A34A !important;
        transform: scale(1.02);
    }
    
    /* Pagination Container */
    .pagination-container {
        padding: 1.5rem;
        display: flex;
        justify-content: center;
        border-top: 1px solid var(--cream);
    }

    /* Pagination Styles */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .page-item .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        color: #64748B;
        background-color: #fff;
        border: none;
        border-radius: 12px !important;
        font-weight: 700;
        font-size: 0.9rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .page-item:not(.active):not(.disabled) .page-link:hover {
        color: #E58B45;
        background-color: #FFF7ED;
        transform: translateY(-2px);
    }

    .page-item.active .page-link {
        color: #fff !important;
        background-color: #E58B45 !important;
        box-shadow: 0 4px 10px rgba(229, 139, 69, 0.4);
    }

    .page-item.disabled .page-link {
        color: #CBD5E1;
        background-color: transparent;
        box-shadow: none;
    }
</style>
@endpush

@push('scripts')
<script>
    // Use global confirmDelete modal from admin layout
    function confirmDelete(url) {
        document.getElementById('globalDeleteForm').action = url;
        openModal('confirmDeleteModal');
    }

    // Copy tournament PIN - change button text to "Tersalin" temporarily
    function copyTournamentPin(pin, tournamentId) {
        const btn = document.getElementById('pin-btn-' + tournamentId);
        const pinTextEl = btn.querySelector('.pin-text');
        const originalText = pin;
        
        navigator.clipboard.writeText(pin).then(() => {
            // Change text to "Tersalin"
            pinTextEl.textContent = 'Tersalin';
            
            // Revert back after 2 seconds
            setTimeout(() => {
                pinTextEl.textContent = originalText;
            }, 2000);
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = pin;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            pinTextEl.textContent = 'Tersalin';
            setTimeout(() => {
                pinTextEl.textContent = originalText;
            }, 2000);
        });
    }
</script>
@endpush
@endsection
