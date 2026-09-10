@extends('layouts.app')
@inject('taskSyncService', 'App\Services\TaskSyncService')

@section('title', 'Daftar Tugas Induk')

@section('content')
<style>
    .dropdown-menu a:hover {
        background-color: rgba(0, 0, 0, 0.04);
    }
</style>
<div class="card">
    <div class="d-flex justify-between align-center mb-3">
        <h2 class="card-title" style="margin: 0;">Tugas Induk</h2>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <i class="material-icons" style="font-size: 18px; margin-right: 4px;">add</i> Tambah Tugas
        </a>
    </div>



    <div style="margin-bottom: 24px; padding: 16px; background: var(--surface-color); border-radius: 8px;">
        <form action="{{ route('tasks.index') }}" method="GET" class="d-flex" style="gap: 16px; flex-wrap: wrap; align-items: flex-end;">
            
            <div style="flex: 1; min-width: 200px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="user_id" class="md-input">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <label class="md-label">Filter User PIC</label>
                    <div class="md-bar"></div>
                </div>
            </div>
            
            <div style="flex: 2; min-width: 250px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <input type="text" name="search" value="{{ request('search') }}" class="md-input" placeholder=" ">
                    <label class="md-label">Cari Nama Tugas...</label>
                    <div class="md-bar"></div>
                </div>
            </div>

            <div style="flex: 1; min-width: 120px; max-width: 150px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="per_page" class="md-input">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 baris</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 baris</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 baris</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 baris</option>
                    </select>
                    <label class="md-label">Tampilkan</label>
                    <div class="md-bar"></div>
                </div>
            </div>
            
            <div>
                <button type="submit" class="btn btn-primary ripple-surface" style="height: 48px; padding: 0 16px;">
                    <i class="material-icons" style="font-size: 20px; margin-right: 8px;">search</i> Terapkan
                </button>
            </div>
        </form>
    </div>

    <form action="{{ route('tasks.bulk-action') }}" method="POST" id="bulkActionForm">
        @csrf
        <div id="bulkActionContainer" class="d-flex align-items-center mb-3" style="gap: 8px; display: none !important; flex-wrap: wrap;">
            <button type="button" class="btn btn-secondary ripple-surface" onclick="openBulkAssignModal()" id="btnBulkAssign">
                <i class="material-icons" style="font-size: 18px; margin-right: 4px;">group_add</i> Assign Terpilih
            </button>
            <button type="button" id="btnBulkAktifkan" class="btn btn-flat ripple-surface" style="background: #E8F5E9; color: #2E7D32; display: none;" onclick="showMDModal('Aktifkan', 'Yakin ingin mengaktifkan tugas terpilih?', () => { const f = document.getElementById('bulkActionForm'); const i = document.createElement('input'); i.type = 'hidden'; i.name = 'bulk_action'; i.value = 'aktifkan'; f.appendChild(i); f.submit(); })">
                <i class="material-icons" style="font-size: 18px; margin-right: 4px;">play_circle_filled</i> <span id="btnBulkAktifkanText">Aktifkan Terpilih</span>
            </button>
            <button type="button" id="btnBulkNonaktifkan" class="btn btn-flat ripple-surface" style="background: #FFF3E0; color: #E65100; display: none;" onclick="showMDModal('Nonaktifkan', 'Yakin ingin menonaktifkan tugas terpilih?', () => { const f = document.getElementById('bulkActionForm'); const i = document.createElement('input'); i.type = 'hidden'; i.name = 'bulk_action'; i.value = 'nonaktifkan'; f.appendChild(i); f.submit(); }, true)">
                <i class="material-icons" style="font-size: 18px; margin-right: 4px;">power_settings_new</i> <span id="btnBulkNonaktifkanText">Nonaktifkan Terpilih</span>
            </button>
            <button type="button" class="btn btn-flat ripple-surface" style="background: #FFEBEE; color: #C62828;" onclick="showMDModal('Hapus', 'Yakin ingin menghapus tugas terpilih?', () => { const f = document.getElementById('bulkActionForm'); const i = document.createElement('input'); i.type = 'hidden'; i.name = 'bulk_action'; i.value = 'hapus'; f.appendChild(i); f.submit(); }, true)">
                <i class="material-icons" style="font-size: 18px; margin-right: 4px;">delete</i> Hapus Terpilih
            </button>
            <span id="selectedCount" style="color: var(--text-secondary); font-size: 14px; margin-left: auto;">0 tugas dipilih</span>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--divider); text-align: left;">
                    <th style="padding: 12px 8px; width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes(this)" style="width: 18px; height: 18px; accent-color: var(--primary);">
                    </th>
                    <th style="padding: 12px 8px;">No</th>
                    <th style="padding: 12px 8px;">Nama Tugas</th>
                    <th style="padding: 12px 8px;">User PIC (Banyak)</th>
                    <th style="padding: 12px 8px;">Periode (Tipe)</th>
                    <th style="padding: 12px 8px;">Periode Berjalan</th>
                    <th style="padding: 12px 8px;">Deadline</th>
                    <th style="padding: 12px 8px;">Status</th>
                    <th style="padding: 12px 8px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $index => $task)
                <tr style="border-bottom: 1px solid var(--divider);">
                    <td style="padding: 12px 8px;">
                        <input type="checkbox" name="task_ids[]" value="{{ $task->id }}" class="task-checkbox" data-user-ids='@json($task->users->pluck("id"))' data-active="{{ $task->is_active ? '1' : '0' }}" onchange="updateSelectedCount()" style="width: 18px; height: 18px; accent-color: var(--primary);">
                    </td>
                    <td style="padding: 12px 8px;">{{ $tasks->firstItem() + $index }}</td>
                    <td style="padding: 12px 8px;">
                        <strong>{{ $task->title }}</strong><br>
                        <small style="color: var(--text-secondary)">{{ Str::limit($task->description, 50) }}</small>
                    </td>
                    <td style="padding: 12px 8px;">
                        <span style="background: #E3F2FD; color: #1976D2; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                            {{ $task->users->count() }} PIC
                        </span>
                    </td>
                    <td style="padding: 12px 8px;">
                        <span style="background: #E0F2F1; color: var(--primary-dark); padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                            {{ ucfirst($task->period_type) }}
                        </span>
                    </td>
                    <td style="padding: 12px 8px;">
                        @php
                            $currentPeriod = $taskSyncService->getCurrentPeriodData($task);
                        @endphp
                        <strong style="color: var(--text-primary); font-size: 13px;">
                            {{ $currentPeriod ? $currentPeriod['label'] : 'Menunggu Penugasan Manual' }}
                        </strong>
                    </td>
                    <td style="padding: 12px 8px;">
                        <span style="color: var(--text-secondary); font-size: 13px;">
                            @if($task->period_type === 'custom' && $task->is_recurring)
                                {{ date('d M', strtotime($task->custom_end_date)) }} (Berulang)
                            @elseif($task->period_type === 'custom')
                                {{ date('d M Y', strtotime($task->custom_end_date)) }}
                            @elseif($task->period_type === 'tidak rutin')
                                {{ $task->deadline_rule ? date('d M Y', strtotime($task->deadline_rule)) : '-' }}
                            @else
                                {{ $currentPeriod && $currentPeriod['deadline_date'] ? date('d M Y', strtotime($currentPeriod['deadline_date'])) : 'Tgl ' . $task->deadline_rule }}
                            @endif
                        </span>
                    </td>
                    <td style="padding: 12px 8px;">
                        @if($task->is_active)
                            <span style="color: #4CAF50; font-weight: 500;"><i class="material-icons" style="font-size: 14px; vertical-align: middle;">check_circle</i> Aktif</span>
                        @else
                            <span style="color: #F44336; font-weight: 500;"><i class="material-icons" style="font-size: 14px; vertical-align: middle;">cancel</i> Nonaktif</span>
                        @endif
                    </td>
                    <td style="padding: 12px 8px; overflow: visible;">
                        <div class="dropdown" style="position: relative; display: inline-block;">
                            <button type="button" class="btn btn-flat dropdown-toggle" style="min-width: unset; padding: 0 8px;" onclick="toggleDropdown('dropdown-{{ $task->id }}')">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <div id="dropdown-{{ $task->id }}" class="dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; background-color: var(--surface-color); min-width: 160px; box-shadow: var(--elevation-2); z-index: 999; border-radius: var(--radius); padding: 8px 0; border: 1px solid var(--divider);">
                                <a href="{{ route('tasks.show', $task->id) }}" style="display: flex; align-items: center; padding: 10px 16px; color: var(--text-primary); text-decoration: none; font-size: 14px; transition: background 0.2s;">
                                    <i class="material-icons" style="font-size: 18px; margin-right: 12px; color: var(--text-secondary);">visibility</i> Detail
                                </a>
                                <a href="{{ route('tasks.edit', $task->id) }}" style="display: flex; align-items: center; padding: 10px 16px; color: var(--text-primary); text-decoration: none; font-size: 14px; transition: background 0.2s;">
                                    <i class="material-icons" style="font-size: 18px; margin-right: 12px; color: var(--text-secondary);">edit</i> Edit
                                </a>
                                <a href="#" style="display: flex; align-items: center; padding: 10px 16px; color: {{ $task->is_active ? '#E65100' : '#4CAF50' }}; text-decoration: none; font-size: 14px; transition: background 0.2s;" onclick="event.preventDefault(); showMDModal('{{ $task->is_active ? 'Nonaktifkan' : 'Aktifkan' }}', 'Yakin ingin {{ $task->is_active ? 'menonaktifkan' : 'mengaktifkan' }} tugas ini?', () => { document.getElementById('toggle-form-{{ $task->id }}').submit(); }, {{ $task->is_active ? 'true' : 'false' }})">
                                    <i class="material-icons" style="font-size: 18px; margin-right: 12px;">{{ $task->is_active ? 'power_settings_new' : 'play_circle_filled' }}</i> {{ $task->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </a>
                                <a href="#" style="display: flex; align-items: center; padding: 10px 16px; color: #C62828; text-decoration: none; font-size: 14px; transition: background 0.2s;" onclick="event.preventDefault(); showMDModal('Hapus Permanen', 'Yakin ingin menghapus tugas ini secara permanen? Riwayat laporan tetap dipertahankan.', () => { document.getElementById('delete-form-{{ $task->id }}').submit(); }, true)">
                                    <i class="material-icons" style="font-size: 18px; margin-right: 12px;">delete</i> Hapus
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 64px 32px; text-align: center; color: var(--text-secondary); background: transparent;">
                        <i class="material-icons" style="font-size: 72px; color: #E0E0E0; margin-bottom: 16px;">folder_open</i>
                        <h3 style="font-size: 20px; color: var(--text-primary); margin-bottom: 8px; font-weight: 500;">Tugas Induk Kosong</h3>
                        <p style="font-size: 14px; max-width: 400px; margin: 0 auto;">Belum ada tugas induk yang dibuat atau ditemukan. Anda dapat membuat tugas baru melalui menu Tambah Tugas.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </form>

    @foreach($tasks as $task)
        <form id="toggle-form-{{ $task->id }}" action="{{ route('tasks.toggle-active', $task->id) }}" method="POST" style="display: none;">
            @csrf
        </form>
        <form id="delete-form-{{ $task->id }}" action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endforeach


    <div style="margin-top: 16px;">
        {{ $tasks->links() }}
    </div>
</div>

<!-- Modal Bulk Assign -->
<div id="bulkAssignModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <form action="{{ route('tasks.bulk-assign') }}" method="POST" id="bulkAssignForm" style="background: var(--surface-color); padding: 24px; border-radius: 8px; max-width: 550px; width: 100%;">
        @csrf
        <div id="modalBulkUserInputs"></div>
        <h3 style="margin-top: 0;">Assign / Un-assign User PIC</h3>
        <p style="color: var(--text-secondary); margin-bottom: 16px; font-size: 13px;">
            Atur User PIC untuk tugas-tugas terpilih. Centang untuk meng-assign, hilangkan centang untuk meng-unassign.
        </p>
        
        <div style="max-height: 250px; overflow-y: auto; border: 1px solid var(--divider); border-radius: 4px; padding: 10px; margin-bottom: 16px;">
            @php 
                // Fetch users once for modal — only show PIC users, hide admin & superadmin
                \App\Models\User::syncFromSso();
                $allUsers = \App\Models\User::where('role', 'user')->orderBy('name', 'asc')->get();
            @endphp
            @forelse($allUsers as $user)
                <div style="margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #F0F0F0;">
                    <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; width: 100%;">
                        <div style="display: flex; align-items: center;">
                            <input type="checkbox" value="{{ $user->id }}" class="modal-user-checkbox" style="margin-right: 8px; width: 18px; height: 18px; accent-color: var(--primary);">
                            <span>{{ $user->name }} <small style="color: var(--text-secondary);">({{ $user->email }})</small></span>
                        </div>
                        <span class="user-assign-badge" id="user-badge-{{ $user->id }}" style="font-size: 11px; font-weight: 500; padding: 2px 6px; border-radius: 4px; display: none;"></span>
                    </label>
                </div>
            @empty
                <div style="color: var(--text-secondary); text-align: center;">Belum ada user SSO yang tersedia.</div>
            @endforelse
        </div>
        
        <div class="d-flex justify-content-end" style="gap: 8px;">
            <button type="button" class="btn btn-flat" onclick="closeBulkAssignModal()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitBulkAssign()">Terapkan & Simpan</button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    function toggleAllCheckboxes(source) {
        let checkboxes = document.querySelectorAll('.task-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.task-checkbox:checked');
        const count = checkboxes.length;
        const container = document.getElementById('bulkActionContainer');
        const countDisplay = document.getElementById('selectedCount');
        const btnAktifkan = document.getElementById('btnBulkAktifkan');
        const btnNonaktifkan = document.getElementById('btnBulkNonaktifkan');
        const btnAktifkanText = document.getElementById('btnBulkAktifkanText');
        const btnNonaktifkanText = document.getElementById('btnBulkNonaktifkanText');
        
        if (count > 0) {
            container.style.setProperty('display', 'flex', 'important');
            countDisplay.textContent = count + ' tugas dipilih';
            
            // Count active vs inactive among selected
            let activeCount = 0, inactiveCount = 0;
            checkboxes.forEach(cb => {
                if (cb.getAttribute('data-active') === '1') activeCount++;
                else inactiveCount++;
            });
            
            // Show/hide buttons based on what's selected
            if (inactiveCount > 0) {
                btnAktifkan.style.display = 'inline-flex';
                btnAktifkanText.textContent = 'Aktifkan' + (activeCount > 0 ? ' (' + inactiveCount + ' tugas nonaktif)' : ' Terpilih');
            } else {
                btnAktifkan.style.display = 'none';
            }
            
            if (activeCount > 0) {
                btnNonaktifkan.style.display = 'inline-flex';
                btnNonaktifkanText.textContent = 'Nonaktifkan' + (inactiveCount > 0 ? ' (' + activeCount + ' tugas aktif)' : ' Terpilih');
            } else {
                btnNonaktifkan.style.display = 'none';
            }
        } else {
            container.style.setProperty('display', 'none', 'important');
            document.getElementById('selectAll').checked = false;
        }
    }

    function openBulkAssignModal() {
        const selectedTaskCheckboxes = document.querySelectorAll('.task-checkbox:checked');
        const totalSelectedTasks = selectedTaskCheckboxes.length;

        if (totalSelectedTasks === 0) {
            alert('Pilih setidaknya satu tugas terlebih dahulu.');
            return;
        }

        // Count assignment frequency per user across selected tasks
        const userTaskCounts = {};
        selectedTaskCheckboxes.forEach(taskCb => {
            let userIds = [];
            try {
                userIds = JSON.parse(taskCb.getAttribute('data-user-ids') || '[]');
            } catch(e) {
                userIds = [];
            }
            userIds.forEach(uid => {
                userTaskCounts[uid] = (userTaskCounts[uid] || 0) + 1;
            });
        });

        // Update modal checkboxes and badges
        document.querySelectorAll('.modal-user-checkbox').forEach(userCb => {
            const uid = parseInt(userCb.value);
            const assignedCount = userTaskCounts[uid] || 0;
            const badgeSpan = document.getElementById('user-badge-' + uid);

            if (assignedCount === totalSelectedTasks) {
                // Fully assigned across all selected tasks
                userCb.checked = true;
                if (badgeSpan) {
                    badgeSpan.textContent = 'Semua';
                    badgeSpan.style.background = '#E8F5E9';
                    badgeSpan.style.color = '#2E7D32';
                    badgeSpan.style.display = 'inline-block';
                }
            } else if (assignedCount > 0) {
                // Partially assigned
                userCb.checked = true;
                if (badgeSpan) {
                    badgeSpan.textContent = `(Sebagian: ${assignedCount}/${totalSelectedTasks})`;
                    badgeSpan.style.background = '#FFF3E0';
                    badgeSpan.style.color = '#E65100';
                    badgeSpan.style.display = 'inline-block';
                }
            } else {
                // Not assigned to any selected tasks
                userCb.checked = false;
                if (badgeSpan) {
                    badgeSpan.style.display = 'none';
                }
            }
        });

        document.getElementById('bulkAssignModal').style.display = 'flex';
    }

    function closeBulkAssignModal() {
        document.getElementById('bulkAssignModal').style.display = 'none';
    }

    function submitBulkAssign() {
        let selectedUsers = document.querySelectorAll('.modal-user-checkbox:checked');
        
        const proceedSubmit = () => {
            let container = document.getElementById('modalBulkUserInputs');
            if (!container) return;
            container.innerHTML = '';
            
            // Copy user IDs
            selectedUsers.forEach(userCb => {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = userCb.value;
                container.appendChild(input);
            });
            
            // Append action type
            let actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'bulk_action';
            actionInput.value = 'assign';
            container.appendChild(actionInput);
            
            // Copy selected task IDs from main table to modal form
            let selectedTasks = document.querySelectorAll('.task-checkbox:checked');
            selectedTasks.forEach(taskCb => {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'task_ids[]';
                input.value = taskCb.value;
                container.appendChild(input);
            });
            
            document.getElementById('bulkAssignForm').submit();
        };

        if (selectedUsers.length === 0) {
            showMDModal('Un-assign Semua PIC?', 'Anda tidak memilih user PIC mana pun. Aksi ini akan melepaskan (un-assign) semua user PIC dari tugas-tugas terpilih. Lanjutkan?', proceedSubmit, true);
        } else {
            proceedSubmit();
        }
    }

    function toggleDropdown(id) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu.id !== id) menu.style.display = 'none';
        });
        const menu = document.getElementById(id);
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    window.onclick = function(event) {
        if (!event.target.closest('.dropdown-toggle')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    }
</script>
@endpush
