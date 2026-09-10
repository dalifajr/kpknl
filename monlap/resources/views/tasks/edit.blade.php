@extends('layouts.app')

@section('title', 'Edit Tugas Induk')

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h2 class="card-title">Form Edit Tugas Induk</h2>
    
    <form action="{{ route('tasks.update', $task->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="md-input-container">
            <input type="text" name="title" value="{{ $task->title }}" required class="md-input" placeholder=" ">
            <label class="md-label">Nama Tugas</label>
            <div class="md-bar"></div>
        </div>

        <div class="md-input-container">
            <textarea name="description" rows="3" class="md-input" placeholder=" ">{{ $task->description }}</textarea>
            <label class="md-label">Deskripsi Tugas (Opsional)</label>
            <div class="md-bar"></div>
        </div>

        <div class="d-flex" style="gap: 16px; margin-bottom: 24px;">
            <div style="flex: 1;">
                <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 14px; color: var(--text-secondary);">User PIC</label>
                <div style="max-height: 200px; overflow-y: auto; border-bottom: 1px solid var(--text-secondary); border-radius: 4px 4px 0 0; padding: 12px; background: #f5f5f5;">
                    @forelse($users as $user)
                        <div style="margin-bottom: 8px;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                       {{ $task->users->contains($user->id) ? 'checked' : '' }}
                                       style="margin-right: 8px; width: 18px; height: 18px; accent-color: var(--primary);">
                                <span>{{ $user->name }} <small style="color: var(--text-secondary);">({{ $user->email }})</small></span>
                            </label>
                        </div>
                    @empty
                        <div style="color: var(--text-secondary); text-align: center;">Belum ada user SSO yang tersedia.</div>
                    @endforelse
                </div>
                <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Pilih satu atau beberapa user yang akan ditugaskan.</small>
            </div>
            
            <div style="flex: 1;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="period_type" id="period_type" required class="md-input" onchange="toggleDeadlineRule()">
                        <option value="bulanan" {{ $task->period_type == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                        <option value="triwulan" {{ $task->period_type == 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                        <option value="semesteran" {{ $task->period_type == 'semesteran' ? 'selected' : '' }}>Semesteran</option>
                        <option value="tahunan" {{ $task->period_type == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                        <option value="tidak rutin" {{ $task->period_type == 'tidak rutin' ? 'selected' : '' }}>Tidak Rutin</option>
                        <option value="custom" {{ $task->period_type == 'custom' ? 'selected' : '' }}>Custom (Bebas & Berulang)</option>
                    </select>
                    <label class="md-label">Jenis Periode</label>
                    <div class="md-bar"></div>
                </div>
            </div>
        </div>

        <div id="custom_period_group" style="margin-bottom: 24px; display: {{ $task->period_type == 'custom' ? 'block' : 'none' }}; padding: 24px 16px; border-radius: 4px; background: #fff; box-shadow: var(--elevation-1);">
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <div class="md-input-container">
                        <input type="date" name="custom_start_date" id="custom_start_date" value="{{ $task->custom_start_date }}" min="{{ date('Y') }}-01-01" class="md-input">
                        <label class="md-label">Tanggal Dibuka Pengisian Tugas</label>
                        <div class="md-bar"></div>
                    </div>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <div class="md-input-container">
                        <input type="date" name="custom_end_date" id="custom_end_date" value="{{ $task->custom_end_date }}" min="{{ date('Y') }}-01-01" class="md-input">
                        <label class="md-label">Tanggal Batas Waktu (Deadline)</label>
                        <div class="md-bar"></div>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; margin-top: -8px;">
                <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ $task->is_recurring ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);" onchange="toggleRecurringInterval()">
                <label for="is_recurring" style="font-size: 15px; cursor: pointer; user-select: none;">Ulangi Tugas Ini Secara Otomatis</label>
            </div>
            
            <div id="recurring_interval_group" style="display: {{ $task->is_recurring ? 'block' : 'none' }}; margin-top: 16px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="recurring_interval" id="recurring_interval" class="md-input">
                        <option value="">-- Pilih Interval --</option>
                        <option value="daily" {{ $task->recurring_interval == 'daily' ? 'selected' : '' }}>Harian</option>
                        <option value="weekly" {{ $task->recurring_interval == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly" {{ $task->recurring_interval == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                        <option value="triwulan" {{ $task->recurring_interval == 'triwulan' ? 'selected' : '' }}>Triwulanan (Per 3 Bulan)</option>
                        <option value="semesteran" {{ $task->recurring_interval == 'semesteran' ? 'selected' : '' }}>Semesteran (Per 6 Bulan)</option>
                        <option value="yearly" {{ $task->recurring_interval == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                    </select>
                    <label class="md-label">Pola Pengulangan</label>
                    <div class="md-bar"></div>
                </div>
            </div>
        </div>
        <div id="deadline_type_group" style="margin-bottom: 24px; display: {{ in_array($task->period_type, ['custom', 'tidak rutin']) ? 'none' : 'block' }}; padding: 24px 16px; border-radius: 4px; background: #fff; box-shadow: var(--elevation-1);">
            <div class="md-input-container">
                <select name="deadline_type" id="deadline_type" class="md-input" onchange="toggleDeadlineRule()">
                    <option value="" disabled {{ !$task->deadline_type ? 'selected' : '' }}>Pilih Tipe Deadline</option>
                    <option value="hari_kerja" {{ $task->deadline_type == 'hari_kerja' ? 'selected' : '' }}>Deadline Hari Kerja (Tanpa Akhir Pekan & Libur)</option>
                    <option value="fixed" {{ $task->deadline_type == 'fixed' ? 'selected' : '' }}>Fixed Deadline (Semua Hari Dihitung)</option>
                </select>
                <label class="md-label">Tipe Deadline</label>
                <div class="md-bar"></div>
            </div>
        </div>

        <div id="deadline_rule_group" style="margin-bottom: 24px; display: {{ $task->period_type == 'tidak rutin' ? 'none' : 'block' }}; padding: 24px 16px; border-radius: 4px; background: #fff; box-shadow: var(--elevation-1);">
            <div class="md-input-container" style="margin-bottom: 8px;">
                <input type="number" name="deadline_rule" id="deadline_rule" value="{{ $task->deadline_rule }}" min="1" class="md-input" placeholder=" ">
                <input type="date" id="deadline_date" class="md-input" style="display: none;">
                <label class="md-label" id="deadline_label">
                    {{ $task->deadline_type == 'hari_kerja' ? 'Jumlah Hari Kerja' : 'Jumlah Hari (Fixed)' }}
                </label>
                <div class="md-bar"></div>
            </div>
            <small id="deadline_help" style="color: var(--text-secondary); display: block; margin-bottom: 16px;">
                {{ $task->deadline_type == 'hari_kerja' ? 'Batas waktu akan otomatis dihitung mundur berdasarkan jumlah hari kerja (melewati akhir pekan & hari libur).' : 'Batas waktu akan otomatis dihitung berdasarkan jumlah hari penuh.' }}
            </small>
            
            <div id="next_month_checkbox_container" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="deadline_next_month" id="deadline_next_month" value="1" {{ $task->deadline_next_month ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
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
            <button type="submit" class="btn btn-primary ripple-surface">Update Tugas</button>
            <a href="{{ route('tasks.index') }}" class="btn btn-flat" style="border: 1px solid var(--divider);">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleRecurringInterval() {
    var isRecurring = document.getElementById('is_recurring').checked;
    document.getElementById('recurring_interval_group').style.display = isRecurring ? 'block' : 'none';
}

function toggleDeadlineRule() {
    var periodType = document.getElementById('period_type').value;
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
    
    if (periodType === 'custom') {
        if(customGroup) customGroup.style.display = 'block';
    } else if (periodType === 'tidak rutin') {
        ruleGroup.style.display = 'block';
        if (ruleInput) {
            ruleInput.style.display = 'none';
            ruleInput.name = '';
        }
        if (dateInput) {
            dateInput.style.display = 'block';
            dateInput.name = 'deadline_rule';
        }
        if (label) label.innerText = 'Tanggal Deadline Manual';
        if (help) help.innerText = 'Pilih tanggal spesifik untuk tugas tidak rutin ini.';
        if (nextMonthContainer) nextMonthContainer.style.display = 'none';
    } else if (periodType) {
        typeGroup.style.display = 'block';
        if (deadlineType) {
            ruleGroup.style.display = 'block';
            if (ruleInput) {
                ruleInput.style.display = 'block';
                ruleInput.name = 'deadline_rule';
                ruleInput.removeAttribute('max');
            }
            if (dateInput) {
                dateInput.style.display = 'none';
                dateInput.name = '';
            }
            if (label) {
                label.innerText = deadlineType === 'hari_kerja' ? 'Jumlah Hari Kerja' : 'Jumlah Hari (Fixed)';
            }
            if (help) {
                help.innerText = deadlineType === 'hari_kerja' ? 'Batas waktu akan otomatis dihitung mundur berdasarkan jumlah hari kerja (melewati akhir pekan & hari libur).' : 'Batas waktu akan otomatis dihitung berdasarkan jumlah hari penuh.';
            }
            if (nextMonthContainer) nextMonthContainer.style.display = 'flex';
        }
    }
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
    
    // Call once to initialize preview on edit page
    setTimeout(fetchDeadlinePreview, 500);
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
        deadline_next_month: document.getElementById('deadline_next_month') ? (document.getElementById('deadline_next_month').checked ? 1 : 0) : 0,
        is_recurring: document.getElementById('is_recurring') ? (document.getElementById('is_recurring').checked ? 1 : 0) : 0,
        recurring_interval: document.getElementById('recurring_interval') ? document.getElementById('recurring_interval').value : '',
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
    }).catch(err => {
        console.error("Preview error:", err);
    });
}
</script>
@endpush
