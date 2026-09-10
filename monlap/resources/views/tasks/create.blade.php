@extends('layouts.app')

@section('title', 'Tambah Tugas Induk')

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h2 class="card-title">Form Tambah Tugas Induk</h2>
    
    <form action="{{ route('tasks.store') }}" method="POST" id="createTaskForm">
        @csrf
        
        <div class="md-input-container">
            <input type="text" name="title" required class="md-input" placeholder=" ">
            <label class="md-label">Nama Tugas</label>
            <div class="md-bar"></div>
        </div>

        <div class="md-input-container">
            <textarea name="description" rows="3" class="md-input" placeholder=" "></textarea>
            <label class="md-label">Deskripsi Tugas (Opsional)</label>
            <div class="md-bar"></div>
        </div>

        <div class="d-flex" style="gap: 16px; margin-bottom: 24px;">
            <div style="flex: 1;">
                <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 14px; color: var(--text-secondary);">User PIC</label>
                <button type="button" class="btn btn-flat ripple-surface" style="width: 100%; text-align: left; padding: 12px; background: #f5f5f5; color: var(--text-primary); border: none; border-bottom: 1px solid var(--text-secondary); border-radius: 4px 4px 0 0; display: flex; justify-content: space-between; align-items: center;" onclick="openUserModal()">
                    <span id="userCountText">Pilih User PIC (0 dipilih)</span>
                    <i class="material-icons" style="font-size: 20px; color: var(--text-secondary);">arrow_drop_down</i>
                </button>
                <div id="hiddenUserInputs"></div>
            </div>
            
            <div style="flex: 1;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="period_type" id="period_type" required class="md-input" onchange="toggleDeadlineRule()">
                        <option value="">-- Pilih Periode --</option>
                        <option value="bulanan">Bulanan</option>
                        <option value="triwulan">Triwulan</option>
                        <option value="semesteran">Semesteran</option>
                        <option value="tahunan">Tahunan</option>
                        <option value="tidak rutin">Tidak Rutin</option>
                        <option value="custom">Custom (Bebas & Berulang)</option>
                    </select>
                    <label class="md-label">Jenis Periode</label>
                    <div class="md-bar"></div>
                </div>
            </div>
        </div>

        <div id="custom_period_group" style="margin-bottom: 24px; display: none; padding: 24px 16px; border-radius: 4px; background: #fff; box-shadow: var(--elevation-1);">
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <div class="md-input-container">
                        <input type="date" name="custom_start_date" id="custom_start_date" min="{{ date('Y') }}-01-01" class="md-input">
                        <label class="md-label">Tanggal Dibuka Pengisian Tugas</label>
                        <div class="md-bar"></div>
                    </div>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <div class="md-input-container">
                        <input type="date" name="custom_end_date" id="custom_end_date" min="{{ date('Y') }}-01-01" class="md-input">
                        <label class="md-label">Tanggal Batas Waktu (Deadline)</label>
                        <div class="md-bar"></div>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; margin-top: -8px;">
                <input type="checkbox" name="is_recurring" id="is_recurring" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);" onchange="toggleRecurringInterval()">
                <label for="is_recurring" style="font-size: 15px; cursor: pointer; user-select: none;">Ulangi Tugas Ini Secara Otomatis</label>
            </div>
            
            <div id="recurring_interval_group" style="display: none; margin-top: 16px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="recurring_interval" id="recurring_interval" class="md-input">
                        <option value="">-- Pilih Interval --</option>
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                        <option value="triwulan">Triwulanan (Per 3 Bulan)</option>
                        <option value="semesteran">Semesteran (Per 6 Bulan)</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                    <label class="md-label">Pola Pengulangan</label>
                    <div class="md-bar"></div>
                </div>
            </div>
        </div>
        <div id="deadline_type_group" style="margin-bottom: 24px; display: none; padding: 24px 16px; border-radius: 4px; background: #fff; box-shadow: var(--elevation-1);">
            <div class="md-input-container">
                <select name="deadline_type" id="deadline_type" class="md-input" onchange="toggleDeadlineRule()">
                    <option value="" disabled selected>Pilih Tipe Deadline</option>
                    <option value="hari_kerja">Deadline Hari Kerja (Tanpa Akhir Pekan & Libur)</option>
                    <option value="fixed">Fixed Deadline (Semua Hari Dihitung)</option>
                </select>
                <label class="md-label">Tipe Deadline</label>
                <div class="md-bar"></div>
            </div>
        </div>

        <div id="deadline_rule_group" style="margin-bottom: 24px; display: none; padding: 24px 16px; border-radius: 4px; background: #fff; box-shadow: var(--elevation-1);">
            <div class="md-input-container" style="margin-bottom: 8px;">
                <input type="number" name="deadline_rule" id="deadline_rule" min="1" max="31" class="md-input" placeholder=" ">
                <input type="date" id="deadline_date" class="md-input" style="display: none;">
                <label class="md-label" id="deadline_label">Batas Waktu (Deadline) Setiap Tanggal</label>
                <div class="md-bar"></div>
            </div>
            <small id="deadline_help" style="color: var(--text-secondary); display: block; margin-bottom: 16px;">Kosongkan jika deadline adalah hari terakhir pada periode tersebut.</small>
            
            <div id="next_month_checkbox_container" style="display: none; align-items: center; gap: 8px;">
                <input type="checkbox" name="deadline_next_month" id="deadline_next_month" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);">
                <label for="deadline_next_month" style="font-size: 15px; cursor: pointer; user-select: none;">Batas waktu berada di bulan berikutnya setelah siklus berakhir</label>
            </div>
            
            <div id="live_preview_panel" style="display: none; margin-top: 16px; padding: 16px; background-color: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 4px;">
                <strong style="display: block; margin-bottom: 8px; color: #0d47a1;">Simulasi Tenggat Waktu (Live Preview)</strong>
                <div style="font-size: 14px; color: #1565c0;">
                    <div style="margin-bottom: 4px;"><strong>Periode:</strong> <span id="preview_period">-</span></div>
                    <div style="margin-bottom: 4px;"><strong>Tanggal Deadline:</strong> <span id="preview_deadline" style="color: #d32f2f; font-weight: bold;">-</span></div>
                    <div id="preview_skipped_container" style="display: none; margin-bottom: 4px;">
                        <strong>Hari yang dilewati:</strong>
                        <ul id="preview_skipped_days" style="margin: 4px 0 0 20px; padding: 0; font-size: 13px;"></ul>
                    </div>
                    <div id="preview_next_period_container" style="display: none; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #64b5f6;">
                        <strong>Siklus Pengulangan Berikutnya:</strong> <span id="preview_next_period">-</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary" onclick="return validateUsers()">Simpan Tugas</button>
            <a href="{{ route('tasks.index') }}" class="btn btn-flat" style="border: 1px solid var(--divider);">Batal</a>
        </div>
    </form>
</div>

<!-- Modal User PIC -->
<div id="userPicModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--surface-color); padding: 24px; border-radius: 8px; max-width: 500px; width: 100%; display: flex; flex-direction: column; max-height: 90vh;">
        <h3 style="margin-top: 0; margin-bottom: 16px;">Pilih User PIC</h3>
        
        <input type="text" id="searchUser" class="form-control" placeholder="Cari nama user..." onkeyup="filterUsers()" style="margin-bottom: 12px; width: 100%; padding: 8px; border: 1px solid var(--divider); border-radius: 4px;">
        
        <div style="margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--divider);">
            <label style="display: flex; align-items: center; cursor: pointer; font-weight: 500;">
                <input type="checkbox" id="selectAllUsers" onchange="toggleAllModalUsers(this)" style="margin-right: 8px; width: 18px; height: 18px; accent-color: var(--primary);">
                Pilih Semua
            </label>
        </div>

        <div id="userListContainer" style="overflow-y: auto; flex: 1; margin-bottom: 16px;">
            @forelse($users as $user)
                <div class="user-item" style="margin-bottom: 8px;" data-name="{{ strtolower($user->name) }}">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" class="modal-user-cb" value="{{ $user->id }}" onchange="updateSelectAllStatus()" style="margin-right: 8px; width: 18px; height: 18px; accent-color: var(--primary);">
                        <span>{{ $user->name }} <small style="color: var(--text-secondary);">({{ $user->email }})</small></span>
                    </label>
                </div>
            @empty
                <div style="color: var(--text-secondary); text-align: center;">Belum ada user SSO yang tersedia.</div>
            @endforelse
        </div>
        
        <div class="d-flex justify-content-end" style="gap: 8px; margin-top: auto;">
            <button type="button" class="btn btn-flat" onclick="closeUserModal()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="saveSelectedUsers()">Simpan Pilihan</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleRecurringInterval() {
    var isRecurring = document.getElementById('is_recurring').checked;
    document.getElementById('recurring_interval_group').style.display = isRecurring ? 'block' : 'none';
}

function toggleDeadlineRule() {
    var periodVal = document.getElementById('period_type').value;
    var deadlineType = document.getElementById('deadline_type').value;
    
    var typeGroup = document.getElementById('deadline_type_group');
    var ruleGroup = document.getElementById('deadline_rule_group');
    var customGroup = document.getElementById('custom_period_group');
    var ruleInput = document.getElementById('deadline_rule');
    var dateInput = document.getElementById('deadline_date');
    var label = document.getElementById('deadline_label');
    var help = document.getElementById('deadline_help');
    var nextMonthContainer = document.getElementById('next_month_checkbox_container');

    ruleGroup.style.display = 'none';
    typeGroup.style.display = 'none';
    if(customGroup) customGroup.style.display = 'none';

    if (periodVal === 'custom') {
        if(customGroup) customGroup.style.display = 'block';
    } else if (periodVal === 'tidak rutin') {
        ruleGroup.style.display = 'block';
        ruleInput.style.display = 'none';
        ruleInput.name = '';
        dateInput.style.display = 'block';
        dateInput.name = 'deadline_rule';
        label.innerText = 'Tanggal Deadline Manual';
        help.innerText = 'Pilih tanggal spesifik untuk tugas tidak rutin ini.';
        if (nextMonthContainer) nextMonthContainer.style.display = 'none';
    } else if (periodVal) {
        typeGroup.style.display = 'block';
        if (deadlineType) {
            ruleGroup.style.display = 'block';
            ruleInput.style.display = 'block';
            ruleInput.name = 'deadline_rule';
            ruleInput.removeAttribute('max');
            dateInput.style.display = 'none';
            dateInput.name = '';
            
            if (deadlineType === 'hari_kerja') {
                label.innerText = 'Jumlah Hari Kerja';
                help.innerText = 'Batas waktu akan otomatis dihitung mundur berdasarkan jumlah hari kerja (melewati akhir pekan & hari libur).';
            } else {
                label.innerText = 'Jumlah Hari (Fixed)';
                help.innerText = 'Batas waktu akan otomatis dihitung berdasarkan jumlah hari penuh.';
            }
            if (nextMonthContainer) nextMonthContainer.style.display = 'flex';
        }
    } else {
        ruleInput.value = '';
        dateInput.value = '';
    }
}

// User Modal Logic
function openUserModal() {
    document.getElementById('userPicModal').style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('userPicModal').style.display = 'none';
}

function filterUsers() {
    var input = document.getElementById('searchUser').value.toLowerCase();
    var items = document.querySelectorAll('.user-item');
    items.forEach(function(item) {
        if (item.getAttribute('data-name').includes(input)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function toggleAllModalUsers(source) {
    var checkboxes = document.querySelectorAll('.modal-user-cb');
    var items = document.querySelectorAll('.user-item');
    // Only check visible ones
    items.forEach(function(item, index) {
        if (item.style.display !== 'none') {
            checkboxes[index].checked = source.checked;
        }
    });
}

function updateSelectAllStatus() {
    var visibleCheckboxes = Array.from(document.querySelectorAll('.user-item'))
        .filter(item => item.style.display !== 'none')
        .map(item => item.querySelector('.modal-user-cb'));
    var allChecked = visibleCheckboxes.every(cb => cb.checked);
    document.getElementById('selectAllUsers').checked = allChecked && visibleCheckboxes.length > 0;
}

function saveSelectedUsers() {
    var selected = document.querySelectorAll('.modal-user-cb:checked');
    var container = document.getElementById('hiddenUserInputs');
    container.innerHTML = ''; // Clear existing
    
    selected.forEach(function(cb) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    
    document.getElementById('userCountText').innerText = 'Pilih User PIC (' + selected.length + ' dipilih)';
    closeUserModal();
}

// Attach listeners for live preview
document.addEventListener("DOMContentLoaded", function() {
    const inputs = ['period_type', 'deadline_type', 'deadline_rule', 'deadline_date', 'deadline_next_month', 'is_recurring', 'recurring_interval', 'custom_start_date', 'custom_end_date'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', fetchDeadlinePreview);
            if (el.tagName === 'INPUT' && (el.type === 'number' || el.type === 'text')) {
                el.addEventListener('keyup', fetchDeadlinePreview);
            }
        }
    });
});

function fetchDeadlinePreview() {
    var periodType = document.getElementById('period_type').value;
    var deadlineType = document.getElementById('deadline_type').value;
    var deadlineRule = document.getElementById('deadline_rule').value;
    if (periodType === 'tidak rutin') {
        deadlineRule = document.getElementById('deadline_date').value;
    }

    if (!periodType) return;
    
    // Only show preview if rule is filled (or not required like custom/tidak rutin)
    if (['bulanan', 'triwulan', 'semesteran', 'tahunan'].includes(periodType) && !deadlineRule) {
        document.getElementById('live_preview_panel').style.display = 'none';
        return;
    }

    var data = {
        _token: '{{ csrf_token() }}',
        period_type: periodType,
        deadline_type: deadlineType,
        deadline_rule: deadlineRule,
        deadline_next_month: document.getElementById('deadline_next_month').checked ? 1 : 0,
        is_recurring: document.getElementById('is_recurring').checked ? 1 : 0,
        recurring_interval: document.getElementById('recurring_interval').value,
        custom_start_date: document.getElementById('custom_start_date') ? document.getElementById('custom_start_date').value : '',
        custom_end_date: document.getElementById('custom_end_date') ? document.getElementById('custom_end_date').value : ''
    };

    fetch('{{ route("tasks.preview-deadline") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(res => {
        if (res && res.deadline_date) {
            document.getElementById('live_preview_panel').style.display = 'block';
            document.getElementById('preview_period').innerText = res.label;
            document.getElementById('preview_deadline').innerText = res.deadline_date;
            
            var skippedContainer = document.getElementById('preview_skipped_container');
            var skippedUl = document.getElementById('preview_skipped_days');
            if (res.skipped_days && res.skipped_days.length > 0) {
                skippedContainer.style.display = 'block';
                skippedUl.innerHTML = '';
                res.skipped_days.forEach(day => {
                    var li = document.createElement('li');
                    li.innerText = day;
                    skippedUl.appendChild(li);
                });
            } else {
                skippedContainer.style.display = 'none';
            }

            var nextContainer = document.getElementById('preview_next_period_container');
            if (res.next_period) {
                nextContainer.style.display = 'block';
                document.getElementById('preview_next_period').innerText = res.next_period;
            } else {
                nextContainer.style.display = 'none';
            }
        } else {
            document.getElementById('live_preview_panel').style.display = 'none';
        }
    });
}

function validateUsers() {
    var selected = document.querySelectorAll('input[name="user_ids[]"]');
    if (selected.length === 0) {
        alert('Mohon pilih setidaknya satu User PIC.');
        openUserModal();
        return false;
    }
    return true;
}
</script>
@endpush
