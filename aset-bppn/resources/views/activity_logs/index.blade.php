@extends('layouts.app')

@section('title', 'Log Aktifitas Sistem')

@section('content')
<div class="row" style="margin-top: 15px;">
    <div class="col s12">
        <!-- Header Page -->
        <div class="card-panel white z-depth-1" style="border-radius: 8px; padding: 18px 24px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 8px; background: #e0f2f1; display: flex; align-items: center; justify-content: center;">
                        <i class="material-icons teal-text text-darken-2" style="font-size: 26px;">history</i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 1.5rem; font-weight: 600; color: #004d40;">Log Aktifitas Sistem</h4>
                        <p style="margin: 2px 0 0 0; color: #757575; font-size: 0.95rem;">Riwayat rekam jejak mutasi data, penambahan, pengubahan, dan penghapusan aset eks BPPN.</p>
                    </div>
                </div>
                <div>
                    <span class="chip teal white-text" style="font-weight: 500;">
                        Total Log: {{ $activities->total() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="card z-depth-1" style="border-radius: 8px; margin-bottom: 20px;">
            <div class="card-content" style="padding: 18px 24px;">
                <form action="{{ route('activity-logs.index') }}" method="GET" id="filter-activity-form">
                    <div class="row" style="margin-bottom: 0;">
                        <!-- Kata Kunci Pencarian -->
                        <div class="input-field col s12 m4" style="margin-top: 5px;">
                            <i class="material-icons prefix grey-text">search</i>
                            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Cari kode aset, nama user, atau rincian...">
                            <label for="search" class="active">Kata Kunci Pencarian</label>
                        </div>

                        <!-- Filter Event / Jenis Aksi -->
                        <div class="input-field col s12 m3" style="margin-top: 5px;">
                            <select name="event" id="event">
                                <option value="">Semua Jenis Aksi</option>
                                <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Penambahan (Created)</option>
                                <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Pembaruan (Updated)</option>
                                <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Penghapusan (Deleted)</option>
                            </select>
                            <label for="event">Jenis Aksi</label>
                        </div>

                        <!-- Filter User / Pelaku -->
                        <div class="input-field col s12 m3" style="margin-top: 5px;">
                            <select name="user_id" id="user_id">
                                <option value="">Semua User / Pelaku</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <label for="user_id">User / Pelaku</label>
                        </div>

                        <!-- Tombol Aksi Filter -->
                        <div class="col s12 m2 right-align" style="margin-top: 15px; display: flex; gap: 8px; justify-content: flex-end;">
                            <button type="submit" class="btn blue darken-3 waves-effect waves-light" style="border-radius: 6px; padding: 0 16px;">
                                <i class="material-icons left" style="margin-right: 6px;">filter_list</i> Filter
                            </button>
                            @if(request()->hasAny(['search', 'event', 'user_id', 'start_date', 'end_date']))
                                <a href="{{ route('activity-logs.index') }}" class="btn-flat grey lighten-3 waves-effect" title="Reset Filter" style="border-radius: 6px; padding: 0 12px;">
                                    <i class="material-icons">refresh</i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Filter Tanggal (Collapsible / Secondary Row) -->
                    <div class="row" style="margin-bottom: 0; margin-top: 10px; border-top: 1px solid #f0f0f0; padding-top: 10px;">
                        <div class="input-field col s12 m3" style="margin-top: 0;">
                            <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}">
                            <label for="start_date" class="active">Dari Tanggal</label>
                        </div>
                        <div class="input-field col s12 m3" style="margin-top: 0;">
                            <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}">
                            <label for="end_date" class="active">Sampai Tanggal</label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Log Aktifitas -->
        <div class="card z-depth-1" style="border-radius: 8px; overflow: hidden;">
            <div class="card-content" style="padding: 0;">
                <div style="overflow-x: auto;">
                    <table class="highlight minimal-table" style="width: 100%; border-collapse: collapse;">
                        <thead style="background-color: #fafafa; border-bottom: 1px solid #e0e0e0;">
                            <tr>
                                <th style="width: 50px; text-align: center; font-weight: 600; color: #424242;">No</th>
                                <th style="font-weight: 600; color: #424242; width: 180px;">User / Pelaku</th>
                                <th style="font-weight: 600; color: #424242;">Aktifitas & Objek Aset</th>
                                <th style="font-weight: 600; color: #424242; width: 180px;">Tanggal & Waktu</th>
                                <th style="font-weight: 600; color: #424242; text-align: center; width: 160px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $index => $activity)
                                @php
                                    $causerName = $activity->causer ? $activity->causer->name : 'Sistem';
                                    $causerRole = $activity->causer ? ($activity->causer->role ?? 'User') : 'System';
                                    $subjectId = $activity->subject_id;
                                    $relatedAsset = $subjectId ? ($assetsLookup[$subjectId] ?? null) : null;
                                    
                                    $assetCode = $relatedAsset ? $relatedAsset->kode_aset : ('ID: ' . $subjectId);
                                    if (!$relatedAsset) {
                                        if (isset($activity->properties['old']['kode_aset'])) {
                                            $assetCode = $activity->properties['old']['kode_aset'];
                                        } elseif (isset($activity->properties['attributes']['kode_aset'])) {
                                            $assetCode = $activity->properties['attributes']['kode_aset'];
                                        }
                                    }

                                    $isTrashed = $relatedAsset ? $relatedAsset->trashed() : false;
                                    if ($activity->event === 'deleted' && !$relatedAsset) {
                                        $isTrashed = true;
                                    }

                                    // Badge color & Action name
                                    $eventColor = 'blue';
                                    $eventIcon = 'edit';
                                    $actionLabel = 'Pembaruan Data';
                                    if ($activity->event === 'created') {
                                        $eventColor = 'green';
                                        $eventIcon = 'add_circle';
                                        $actionLabel = 'Penambahan Aset';
                                    } elseif ($activity->event === 'deleted') {
                                        $eventColor = 'red';
                                        $eventIcon = 'delete';
                                        $actionLabel = 'Penghapusan Aset';
                                    }
                                @endphp
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="text-align: center; color: #757575;">
                                        {{ $activities->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 34px; height: 34px; border-radius: 50%; background: #e3f2fd; color: #1565c0; font-weight: bold; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                                {{ strtoupper(substr($causerName, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: #212121; font-size: 0.95rem;">{{ $causerName }}</div>
                                                <div style="font-size: 0.8rem; color: #757575;">{{ ucfirst($causerRole) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap;">
                                            <span class="chip {{ $eventColor }} lighten-5 {{ $eventColor }}-text text-darken-3" style="font-weight: 600; font-size: 11px; height: 24px; line-height: 24px; margin: 0; padding: 0 10px; border-radius: 4px;">
                                                <i class="material-icons left" style="font-size: 14px; margin-right: 4px; line-height: 24px;">{{ $eventIcon }}</i>
                                                {{ $actionLabel }}
                                            </span>
                                            
                                            <strong style="color: #0d47a1; font-size: 0.95rem;">{{ $assetCode }}</strong>
                                            
                                            @if($isTrashed)
                                                <span class="chip red lighten-5 red-text text-darken-3" style="font-weight: 600; font-size: 10px; height: 20px; line-height: 20px; margin: 0; padding: 0 8px; border-radius: 4px;">
                                                    Aset Terhapus
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div style="font-size: 0.88rem; color: #424242;">
                                            {{ ucfirst($activity->description) }}
                                            @if($relatedAsset && $relatedAsset->alamat_namajalan)
                                                - <span style="color: #757575;">{{ $relatedAsset->alamat_namajalan }}</span>
                                            @endif
                                        </div>

                                        @if(isset($activity->properties['attributes']) && is_array($activity->properties['attributes']) && $activity->event === 'updated')
                                            @php
                                                $changedKeys = array_keys($activity->properties['attributes']);
                                                // Exclude updated_at
                                                $changedKeys = array_filter($changedKeys, fn($k) => $k !== 'updated_at');
                                            @endphp
                                            @if(count($changedKeys) > 0)
                                                <div style="margin-top: 4px; font-size: 0.78rem; color: #888;">
                                                    Kolom diubah: <span style="color: #455a64; font-family: monospace;">{{ implode(', ', array_slice($changedKeys, 0, 4)) }}{{ count($changedKeys) > 4 ? '...' : '' }}</span>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight: 500; color: #212121; font-size: 0.9rem;">
                                            {{ $activity->created_at->translatedFormat('d M Y, H:i') }} WIB
                                        </div>
                                        <div style="font-size: 0.8rem; color: #9e9e9e;">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td style="text-align: center; white-space: nowrap;">
                                        @if($subjectId)
                                            <div style="display: inline-flex; gap: 6px;">
                                                <a href="{{ route('assets.show', $subjectId) }}" class="btn-small blue darken-3 waves-effect waves-light tooltipped" data-position="top" data-tooltip="Lihat Detail Profil Aset" style="border-radius: 4px; padding: 0 10px; height: 30px; line-height: 30px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                                                    <i class="material-icons" style="font-size: 16px;">visibility</i> Detail
                                                </a>

                                                @if($isTrashed && in_array(auth()->user()->role, ['admin', 'superadmin']))
                                                    <button type="button" onclick="confirmRestoreAsset({{ $subjectId }}, '{{ $assetCode }}')" class="btn-small green darken-2 waves-effect waves-light tooltipped" data-position="top" data-tooltip="Kembalikan / Restore Aset" style="border-radius: 4px; padding: 0 8px; height: 30px; line-height: 30px; font-size: 12px; display: inline-flex; align-items: center; gap: 2px;">
                                                        <i class="material-icons" style="font-size: 16px;">restore_from_trash</i> Undo
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            <span class="grey-text" style="font-size: 12px;">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="center-align" style="padding: 48px 20px;">
                                        <i class="material-icons grey-text text-lighten-1" style="font-size: 56px;">history_toggle_off</i>
                                        <h5 class="grey-text text-darken-1" style="font-size: 1.2rem; margin-top: 12px;">Tidak Ada Log Aktifitas Ditemukan</h5>
                                        <p class="grey-text" style="margin-top: 4px; font-size: 0.9rem;">Coba sesuaikan kata kunci pencarian atau reset filter di atas.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($activities->hasPages())
                <div class="card-action white" style="border-top: 1px solid #f0f0f0; padding: 14px 24px;">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Restore Asset -->
<div id="modalRestoreAsset" class="modal" style="max-width: 480px; border-radius: 8px;">
    <div class="modal-content" style="padding: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="width: 42px; height: 42px; border-radius: 50%; background: #e8f5e9; display: flex; align-items: center; justify-content: center;">
                <i class="material-icons green-text text-darken-2" style="font-size: 24px;">restore_from_trash</i>
            </div>
            <h5 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #2e7d32;">Kembalikan Aset?</h5>
        </div>
        <p style="color: #424242; font-size: 0.95rem; line-height: 1.5; margin-bottom: 0;">
            Apakah Anda yakin ingin memulihkan aset <strong id="restore-asset-code" class="blue-text text-darken-3"></strong>? Data aset akan kembali aktif di sistem dan dapat dikelola seperti semula.
        </p>
        <form id="restore-form" method="POST" action="">
            @csrf
        </form>
    </div>
    <div class="modal-footer" style="background: #fafafa; padding: 12px 24px;">
        <a href="#!" class="modal-close waves-effect waves-grey btn-flat" style="color: #616161;">Batal</a>
        <button type="button" id="btn-submit-restore" class="waves-effect waves-light btn green darken-2" style="border-radius: 6px;">
            <i class="material-icons left" style="margin-right: 6px;">check</i> Ya, Pulihkan Aset
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elemsSelect = document.querySelectorAll('select');
        M.FormSelect.init(elemsSelect);

        var elemsModal = document.querySelectorAll('.modal');
        M.Modal.init(elemsModal);

        var elemsTooltip = document.querySelectorAll('.tooltipped');
        M.Tooltip.init(elemsTooltip);
    });

    let targetRestoreId = null;

    function confirmRestoreAsset(assetId, assetCode) {
        targetRestoreId = assetId;
        document.getElementById('restore-asset-code').innerText = assetCode;
        let modal = M.Modal.getInstance(document.getElementById('modalRestoreAsset'));
        modal.open();
    }

    document.getElementById('btn-submit-restore').addEventListener('click', function() {
        if (targetRestoreId) {
            let form = document.getElementById('restore-form');
            form.action = '{{ url("assets") }}/' + targetRestoreId + '/restore';
            form.submit();
        }
    });
</script>
@endpush
