@extends('layouts.app')

@section('title', 'Kalender Penugasan & Agenda')

@section('content')
<!-- Calendar Card -->
<div class="card" style="padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 class="card-title" style="margin: 0 0 4px 0; font-weight: 500; font-size: 22px; color: var(--text-primary);">Kalender Penugasan & Agenda</h2>
            <p style="color: var(--text-secondary); margin-bottom: 0; font-size: 14px;">Pantau tenggat waktu laporan, agenda kantor, dan hari libur kerja.</p>
        </div>
        @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
        <div>
            <button type="button" class="btn btn-primary" onclick="openAddAgendaModal()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 6px; font-weight: 500;">
                <i class="material-icons" style="font-size: 20px;">add</i> Tambah Agenda
            </button>
        </div>
        @endif
    </div>
    
    <div id="calendar"></div>
</div>

<!-- Offcanvas Panel (Sliding from left) -->
<div id="calendar-offcanvas" class="calendar-offcanvas">
    <div class="offcanvas-header">
        <h3 style="margin: 0; font-weight: 500; font-size: 20px; color: var(--text-primary);">Detail Tanggal</h3>
        <button class="offcanvas-close" onclick="closeOffcanvas()">
            <i class="material-icons">close</i>
        </button>
    </div>
    <div class="offcanvas-body" id="offcanvas-content">
        <p style="color: var(--text-secondary); text-align: center; margin-top: 24px;">Memuat data...</p>
    </div>
</div>
<!-- Offcanvas Backdrop -->
<div id="calendar-backdrop" class="calendar-backdrop" onclick="closeOffcanvas()"></div>

<!-- Modal Tambah / Edit Agenda (Material Design White) -->
<div id="agenda-modal-backdrop" class="agenda-modal-backdrop" onclick="handleBackdropClick(event)">
    <div class="agenda-modal-dialog">
        <div class="agenda-modal-header">
            <h4 id="agenda-modal-title" style="margin: 0; font-weight: 500; font-size: 18px; color: var(--text-primary);">Tambah Agenda Baru</h4>
            <button type="button" class="offcanvas-close" onclick="closeAgendaModal()">
                <i class="material-icons">close</i>
            </button>
        </div>
        <form id="agenda-form" onsubmit="handleAgendaSubmit(event)" data-no-progress="true" data-ajax="true">
            @csrf
            <input type="hidden" id="agenda-id" name="agenda_id" value="">
            <input type="hidden" id="agenda-method" name="_method" value="POST">

            <div class="agenda-modal-body">
                <!-- Judul Agenda -->
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="agenda-title" style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; color: var(--text-primary);">Nama Agenda / Kegiatan <span style="color: #f44336;">*</span></label>
                    <input type="text" id="agenda-title" name="title" class="form-control" placeholder="Contoh: Rapat Koordinasi Triwulan / Libur Cuti Bersama" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--divider); border-radius: 6px; font-size: 14px; background: var(--surface-color); color: var(--text-primary);">
                </div>

                <!-- Rentang Tanggal -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label for="agenda-start-date" style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; color: var(--text-primary);">Tanggal Mulai <span style="color: #f44336;">*</span></label>
                        <input type="date" id="agenda-start-date" name="start_date" class="form-control" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--divider); border-radius: 6px; font-size: 14px; background: var(--surface-color); color: var(--text-primary);" onchange="handleStartDateChange()">
                    </div>
                    <div class="form-group">
                        <label for="agenda-end-date" style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; color: var(--text-primary);">Tanggal Selesai (Opsional)</label>
                        <input type="date" id="agenda-end-date" name="end_date" class="form-control" style="width: 100%; padding: 10px 12px; border: 1px solid var(--divider); border-radius: 6px; font-size: 14px; background: var(--surface-color); color: var(--text-primary);">
                    </div>
                </div>

                <!-- Checkbox Libur / Pengecualian Hari Kerja -->
                <div style="background: #fdfbf7; border: 1px solid #ffe082; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin-bottom: 0;">
                        <input type="checkbox" id="agenda-is-holiday" name="is_holiday" value="1" style="margin-top: 3px; width: 18px; height: 18px; accent-color: #d32f2f;" onchange="handleHolidayToggle(this)">
                        <div>
                            <span style="font-weight: 500; font-size: 14px; color: #b71c1c;">Tandai sebagai Hari Libur / Non-Kerja</span>
                            <p style="margin: 4px 0 0 0; font-size: 12px; color: #5d4037; line-height: 1.4;">
                                Jika dicentang, tanggal ini otomatis dikecualikan pada fungsi perhitungan deadline penugasan laporan (hari kerja di-skip).
                            </p>
                        </div>
                    </label>
                </div>

                <!-- Deskripsi -->
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="agenda-description" style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; color: var(--text-primary);">Keterangan / Rincian</label>
                    <textarea id="agenda-description" name="description" rows="3" class="form-control" placeholder="Tambahkan catatan lokasi, detail kegiatan, atau dasar surat/keputusan..." style="width: 100%; padding: 10px 12px; border: 1px solid var(--divider); border-radius: 6px; font-size: 14px; background: var(--surface-color); color: var(--text-primary); resize: vertical;"></textarea>
                </div>

                <!-- Pilihan Warna -->
                <div class="form-group" style="margin-bottom: 8px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 8px; color: var(--text-primary);">Aksen Warna Agenda</label>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <label class="color-radio-label" title="Biru (Kegiatan)">
                            <input type="radio" name="color_preset" value="#1e88e5" checked onchange="selectColor('#1e88e5')">
                            <span class="color-swatch" style="background: #1e88e5;"></span>
                        </label>
                        <label class="color-radio-label" title="Merah (Libur / Urgensi)">
                            <input type="radio" name="color_preset" value="#e53935" onchange="selectColor('#e53935')">
                            <span class="color-swatch" style="background: #e53935;"></span>
                        </label>
                        <label class="color-radio-label" title="Hijau (Pencapaian / Sosialisasi)">
                            <input type="radio" name="color_preset" value="#43a047" onchange="selectColor('#43a047')">
                            <span class="color-swatch" style="background: #43a047;"></span>
                        </label>
                        <label class="color-radio-label" title="Oranye (Rapat / Diskusi)">
                            <input type="radio" name="color_preset" value="#fb8c00" onchange="selectColor('#fb8c00')">
                            <span class="color-swatch" style="background: #fb8c00;"></span>
                        </label>
                        <label class="color-radio-label" title="Ungu (Acara Khusus)">
                            <input type="radio" name="color_preset" value="#8e24aa" onchange="selectColor('#8e24aa')">
                            <span class="color-swatch" style="background: #8e24aa;"></span>
                        </label>
                        <label class="color-radio-label" title="Teal (Dinas Luar)">
                            <input type="radio" name="color_preset" value="#00897b" onchange="selectColor('#00897b')">
                            <span class="color-swatch" style="background: #00897b;"></span>
                        </label>
                        <input type="hidden" id="agenda-color" name="color" value="#1e88e5">
                    </div>
                </div>
            </div>

            <div class="agenda-modal-footer">
                <button type="button" class="btn btn-flat" onclick="closeAgendaModal()" style="border: 1px solid var(--divider); padding: 8px 16px; border-radius: 6px;">Batal</button>
                <button type="submit" id="agenda-submit-btn" class="btn btn-primary" style="padding: 8px 20px; border-radius: 6px; font-weight: 500;">Simpan Agenda</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
    /* Styling adjustments for FullCalendar minimalistic */
    .fc-theme-standard .fc-scrollgrid {
        border-color: var(--divider);
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: var(--divider);
    }
    .fc-col-header-cell {
        background-color: var(--background-color);
        padding: 8px 0 !important;
        font-weight: 500;
    }
    .fc-daygrid-day-number {
        color: var(--text-primary);
        text-decoration: none;
        font-weight: 500;
    }
    .fc-day-today {
        background-color: #e3f2fd !important;
    }
    
    /* Column Highlights for Friday (WFH) and Weekend - Minimalist Material */
    .fc-col-header-cell.fc-day-fri .fc-col-header-cell-cushion {
        color: #2e7d32; /* Material Green 800 */
    }
    .fc-col-header-cell.fc-day-fri .fc-scrollgrid-sync-inner::after {
        content: "WFH";
        display: block;
        font-size: 10px;
        font-weight: 500;
        color: #2e7d32;
        background: #e8f5e9;
        padding: 2px 6px;
        border-radius: 12px;
        width: fit-content;
        margin: 0 auto 4px auto;
    }

    .fc-day-sat, .fc-day-sun {
        background-color: rgba(0, 0, 0, 0.015) !important;
    }
    .fc-col-header-cell.fc-day-sat .fc-col-header-cell-cushion, 
    .fc-col-header-cell.fc-day-sun .fc-col-header-cell-cushion,
    .fc-daygrid-day.fc-day-sat .fc-daygrid-day-number,
    .fc-daygrid-day.fc-day-sun .fc-daygrid-day-number {
        color: var(--text-secondary);
    }
    .fc-col-header-cell.fc-day-sat .fc-scrollgrid-sync-inner::after,
    .fc-col-header-cell.fc-day-sun .fc-scrollgrid-sync-inner::after {
        content: "Libur";
        display: block;
        font-size: 10px;
        font-weight: 500;
        color: #757575;
        background: #eeeeee;
        padding: 2px 6px;
        border-radius: 12px;
        width: fit-content;
        margin: 0 auto 4px auto;
    }
    
    /* Events Styling */
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        border: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        padding: 2px 6px;
        margin-bottom: 3px;
        font-size: 0.82em;
        transition: transform 0.18s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .fc-event:hover {
        transform: scale(1.02);
        box-shadow: var(--elevation-2);
        z-index: 10 !important;
    }

    /* FullCalendar Toolbar Buttons Material */
    .fc-button-primary {
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
        border-radius: 4px !important;
        font-family: 'Roboto', sans-serif !important;
        font-weight: 500 !important;
        box-shadow: var(--elevation-1) !important;
        transition: background-color 0.2s, box-shadow 0.2s, transform 0.15s !important;
    }
    .fc-button-primary:hover {
        background-color: var(--primary-dark) !important;
        border-color: var(--primary-dark) !important;
        box-shadow: var(--elevation-2) !important;
    }
    .fc-button-primary:active {
        transform: scale(0.97) !important;
    }
    .fc-button-active {
        background-color: var(--primary-dark) !important;
        border-color: var(--primary-dark) !important;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.2) !important;
    }
    
    /* Offcanvas Styling */
    .calendar-offcanvas {
        position: fixed;
        top: 0;
        left: -440px;
        width: 440px;
        max-width: 90vw;
        height: 100vh;
        background-color: var(--surface-color);
        box-shadow: 2px 0 16px rgba(0,0,0,0.25);
        z-index: 1050;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    .calendar-offcanvas.open {
        left: 0;
    }
    .calendar-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0,0,0,0.4);
        backdrop-filter: blur(2px);
        z-index: 1040;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    }
    .calendar-backdrop.open {
        display: block;
        opacity: 1;
    }
    .offcanvas-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--divider);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .offcanvas-close {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        border-radius: 50%;
        transition: background 0.2s, transform 0.15s;
    }
    .offcanvas-close:hover {
        background: var(--divider);
        color: var(--text-primary);
        transform: rotate(90deg);
    }
    .offcanvas-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }
    
    /* Event Item inside offcanvas */
    .detail-card {
        background: #fff;
        border: 1px solid var(--divider);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        position: relative;
        animation: cardFadeIn 0.28s cubic-bezier(0.0, 0.0, 0.2, 1) forwards;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .detail-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--elevation-2);
    }
    @keyframes cardFadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .detail-card.holiday-card {
        background: #fff8f8;
        border: 1px solid #ffcdd2;
        border-left: 4px solid #d32f2f;
    }
    .detail-card.agenda-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    
    /* Status Badges */
    .badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
        transition: transform 0.15s;
    }
    .badge-pending { background-color: #e3f2fd; color: #1976d2; }
    .badge-submitted { background-color: #fff3e0; color: #f57c00; }
    .badge-approved { background-color: #e8f5e9; color: #388e3c; }
    .badge-revision { background-color: #ffebee; color: #d32f2f; }
    .badge-holiday { background-color: #ffebee; color: #c62828; }
    .badge-agenda { background-color: #e0f2fe; color: #0284c7; }

    /* Modal Styling */
    .agenda-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0,0,0,0.5);
        backdrop-filter: blur(2px);
        z-index: 1060;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .agenda-modal-backdrop.show {
        opacity: 1;
        visibility: visible;
    }
    .agenda-modal-dialog {
        background: var(--surface-color);
        width: 100%;
        max-width: 520px;
        border-radius: 8px;
        box-shadow: var(--elevation-3);
        overflow: hidden;
        margin: 16px;
        transform: translateY(24px) scale(0.96);
        opacity: 0;
        transition: transform 0.28s cubic-bezier(0.0, 0.0, 0.2, 1), opacity 0.28s cubic-bezier(0.0, 0.0, 0.2, 1);
    }
    .agenda-modal-backdrop.show .agenda-modal-dialog {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    .agenda-modal-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--divider);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .agenda-modal-body {
        padding: 24px;
        max-height: calc(85vh - 130px);
        overflow-y: auto;
    }
    .agenda-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--divider);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: #fafafa;
    }

    /* Color Swatch Radio */
    .color-radio-label {
        cursor: pointer;
        display: inline-block;
        position: relative;
    }
    .color-radio-label input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }
    .color-swatch {
        display: block;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid transparent;
        transition: transform 0.15s, border-color 0.15s;
    }
    .color-radio-label input:checked + .color-swatch {
        transform: scale(1.15);
        border-color: #333;
        box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>

<script>
var calendar;
var currentSelectedDate = null;
const userRole = '{{ Auth::user()->role }}';
const canManageAgenda = (userRole === 'admin' || userRole === 'superadmin');

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu'
        },
        events: '{{ route("calendar.events") }}',
        eventClick: function(info) {
            var dateStr = info.event.startStr.split('T')[0];
            showDateDetails(dateStr);
        },
        dateClick: function(info) {
            showDateDetails(info.dateStr);
        }
    });

    calendar.render();

    // Automatically update calendar dimensions when sidebar toggles or window resizes
    window.addEventListener('resize', function() {
        if (calendar) calendar.updateSize();
    });

    const mainContentEl = document.querySelector('.main-content');
    if (mainContentEl) {
        mainContentEl.addEventListener('transitionend', function(e) {
            if (e.propertyName === 'margin-left' || e.propertyName === 'width') {
                if (calendar) calendar.updateSize();
            }
        });
    }
});

function openOffcanvas() {
    document.getElementById('calendar-offcanvas').classList.add('open');
    document.getElementById('calendar-backdrop').classList.add('open');
    if (calendar) calendar.updateSize();
}

function closeOffcanvas() {
    document.getElementById('calendar-offcanvas').classList.remove('open');
    document.getElementById('calendar-backdrop').classList.remove('open');
    if (calendar) calendar.updateSize();
}

function showDateDetails(dateStr) {
    currentSelectedDate = dateStr;
    openOffcanvas();
    
    var container = document.getElementById('offcanvas-content');
    
    var d = new Date(dateStr + 'T00:00:00');
    var dateDisplay = d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
    var html = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h4 style="margin: 0; color: var(--primary); font-size: 16px; font-weight: 600;">${dateDisplay}</h4>
            </div>
            ${canManageAgenda ? `
            <button type="button" class="btn btn-sm" onclick="openAddAgendaModal('${dateStr}')" style="background: #e3f2fd; color: #1565c0; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                <i class="material-icons" style="font-size: 16px;">add</i> Tambah Agenda
            </button>
            ` : ''}
        </div>
    `;
    
    var allEvents = calendar.getEvents();
    var eventsOnDate = allEvents.filter(function(event) {
        var evStart = event.startStr ? event.startStr.split('T')[0] : '';
        var evEnd = event.endStr ? event.endStr.split('T')[0] : null;
        
        if (evEnd) {
            return dateStr >= evStart && dateStr < evEnd;
        }
        return evStart === dateStr;
    });
    
    if (eventsOnDate.length === 0) {
        html += `
            <div style="text-align: center; color: var(--text-secondary); margin-top: 48px;">
                <i class="material-icons" style="font-size: 48px; opacity: 0.4;">event_available</i>
                <p style="margin-top: 16px; font-size: 14px;">Tidak ada agenda atau tugas pada hari ini.</p>
                ${canManageAgenda ? `
                <button type="button" class="btn btn-primary" onclick="openAddAgendaModal('${dateStr}')" style="margin-top: 12px; padding: 8px 16px; border-radius: 6px; font-size: 13px;">
                    + Buat Agenda untuk Hari Ini
                </button>
                ` : ''}
            </div>
        `;
    } else {
        // Categorize events
        var holidays = [];
        var agendas = [];
        var tasks = [];

        eventsOnDate.forEach(function(event) {
            var props = event.extendedProps;
            if (props.is_agenda) {
                agendas.push({ event: event, props: props });
            } else if (props.is_holiday) {
                holidays.push({ event: event, props: props });
            } else {
                tasks.push({ event: event, props: props });
            }
        });

        // 1. Render Holidays & Holiday Agendas
        if (holidays.length > 0) {
            html += '<div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #c62828; margin-bottom: 8px;">Hari Libur Nasional</div>';
            holidays.forEach(function(item) {
                html += `
                    <div class="detail-card holiday-card">
                        <div style="display: flex; align-items: center; gap: 8px; color: #c62828; font-weight: 600; font-size: 14px;">
                            <i class="material-icons" style="font-size: 20px;">event_busy</i>
                            <span>${item.props.name}</span>
                        </div>
                    </div>
                `;
            });
        }

        // 2. Render Agendas
        if (agendas.length > 0) {
            html += '<div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin: 16px 0 8px 0;">Agenda Kantor</div>';
            agendas.forEach(function(item) {
                var isHol = item.props.is_holiday;
                var borderColor = item.props.color || (isHol ? '#e53935' : '#1e88e5');
                var agendaDataJson = encodeURIComponent(JSON.stringify(item.props));

                html += `
                    <div class="detail-card ${isHol ? 'holiday-card' : 'agenda-card'}" style="border-left: 4px solid ${borderColor};">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                            <div>
                                <span class="badge ${isHol ? 'badge-holiday' : 'badge-agenda'}" style="margin-bottom: 6px;">
                                    ${isHol ? '📌 Libur Kantor / Non-Kerja' : '📅 Agenda Kegiatan'}
                                </span>
                                <div style="font-size: 15px; font-weight: 600; color: var(--text-primary); margin-top: 2px;">
                                    ${item.props.title}
                                </div>
                            </div>
                            ${canManageAgenda ? `
                            <div style="display: flex; gap: 4px;">
                                <button type="button" class="btn-icon" onclick="openEditAgendaModal('${agendaDataJson}')" title="Edit Agenda" style="background: none; border: none; cursor: pointer; color: var(--text-secondary); padding: 4px;">
                                    <i class="material-icons" style="font-size: 18px;">edit</i>
                                </button>
                                <button type="button" class="btn-icon" onclick="confirmDeleteAgenda(${item.props.agenda_id})" title="Hapus Agenda" style="background: none; border: none; cursor: pointer; color: #e53935; padding: 4px;">
                                    <i class="material-icons" style="font-size: 18px;">delete</i>
                                </button>
                            </div>
                            ` : ''}
                        </div>
                        ${item.props.description ? `
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: var(--text-secondary); line-height: 1.4;">
                            ${item.props.description}
                        </p>
                        ` : ''}
                        ${item.props.end_date && item.props.end_date !== item.props.start_date ? `
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 8px; display: flex; align-items: center; gap: 4px;">
                            <i class="material-icons" style="font-size: 14px;">date_range</i> ${item.props.start_date} s.d. ${item.props.end_date}
                        </div>
                        ` : ''}
                    </div>
                `;
            });
        }

        // 3. Render Tasks
        if (tasks.length > 0) {
            html += '<div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin: 16px 0 8px 0;">Tenggat Waktu Laporan</div>';
            tasks.forEach(function(item) {
                var props = item.props;
                var event = item.event;

                let badgeClass = 'badge-pending';
                let statusText = 'Belum Dikerjakan';
                if (props.status === 'submitted') { badgeClass = 'badge-submitted'; statusText = 'Menunggu Review'; }
                else if (props.status === 'approved') { badgeClass = 'badge-approved'; statusText = 'Disetujui'; }
                else if (props.status === 'revision') { badgeClass = 'badge-revision'; statusText = 'Revisi'; }

                let url = "{{ url('user-tasks') }}/" + props.assignment_id;
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'superadmin')
                    url = "{{ url('reviews') }}/" + props.assignment_id;
                @endif

                html += `
                    <div class="detail-card" style="border-left: 4px solid ${event.backgroundColor || '#2196f3'};">
                        <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 2px;">Tugas Laporan (${props.period || ''})</div>
                        <div style="font-size: 15px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">
                            ${event.title}
                        </div>
                        
                        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'superadmin')
                        ${props.pic ? `
                        <div style="font-size: 13px; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                            <i class="material-icons" style="font-size: 16px;">person</i> ${props.pic}
                        </div>
                        ` : ''}
                        @endif
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; gap: 8px;">
                            <span class="badge ${badgeClass}">${statusText}</span>
                            <a href="${url}" class="btn btn-sm btn-flat" style="border: 1px solid var(--divider); padding: 4px 12px; font-size: 12px; border-radius: 4px; text-decoration: none;">Buka Detail</a>
                        </div>
                    </div>
                `;
            });
        }
    }
    
    container.innerHTML = html;
}

// Modal Handlers
function openAddAgendaModal(dateStr = null) {
    document.getElementById('agenda-form').reset();
    document.getElementById('agenda-id').value = '';
    document.getElementById('agenda-method').value = 'POST';
    document.getElementById('agenda-modal-title').innerText = 'Tambah Agenda Baru';
    document.getElementById('agenda-submit-btn').innerText = 'Simpan Agenda';

    var targetDate = dateStr || currentSelectedDate || new Date().toISOString().split('T')[0];
    document.getElementById('agenda-start-date').value = targetDate;
    document.getElementById('agenda-end-date').value = targetDate;
    
    document.getElementById('agenda-is-holiday').checked = false;
    selectColor('#1e88e5');

    var modal = document.getElementById('agenda-modal-backdrop');
    modal.classList.add('show');
    if (calendar) calendar.updateSize();
}

function openEditAgendaModal(agendaDataJson) {
    var data = JSON.parse(decodeURIComponent(agendaDataJson));
    document.getElementById('agenda-form').reset();
    document.getElementById('agenda-id').value = data.agenda_id;
    document.getElementById('agenda-method').value = 'PUT';
    document.getElementById('agenda-modal-title').innerText = 'Edit Agenda';
    document.getElementById('agenda-submit-btn').innerText = 'Perbarui Agenda';

    document.getElementById('agenda-title').value = data.title;
    document.getElementById('agenda-start-date').value = data.start_date;
    document.getElementById('agenda-end-date').value = data.end_date || data.start_date;
    document.getElementById('agenda-description').value = data.description || '';
    document.getElementById('agenda-is-holiday').checked = !!data.is_holiday;
    
    selectColor(data.color || (data.is_holiday ? '#e53935' : '#1e88e5'));

    var modal = document.getElementById('agenda-modal-backdrop');
    modal.classList.add('show');
    if (calendar) calendar.updateSize();
}

function closeAgendaModal() {
    var modal = document.getElementById('agenda-modal-backdrop');
    modal.classList.remove('show');
    if (calendar) calendar.updateSize();
}

function handleBackdropClick(e) {
    if (e.target.id === 'agenda-modal-backdrop') {
        closeAgendaModal();
    }
}

function handleStartDateChange() {
    var startDate = document.getElementById('agenda-start-date').value;
    var endDateInput = document.getElementById('agenda-end-date');
    if (!endDateInput.value || endDateInput.value < startDate) {
        endDateInput.value = startDate;
    }
}

function handleHolidayToggle(checkbox) {
    if (checkbox.checked) {
        selectColor('#e53935');
    } else {
        selectColor('#1e88e5');
    }
}

function selectColor(colorCode) {
    document.getElementById('agenda-color').value = colorCode;
    var radios = document.getElementsByName('color_preset');
    for (var i = 0; i < radios.length; i++) {
        if (radios[i].value === colorCode) {
            radios[i].checked = true;
        }
    }
}

function handleAgendaSubmit(e) {
    e.preventDefault();
    var form = document.getElementById('agenda-form');
    var agendaId = document.getElementById('agenda-id').value;
    var method = document.getElementById('agenda-method').value;
    
    var url = '{{ route("agendas.store") }}';
    if (agendaId && method === 'PUT') {
        url = '{{ url("agendas") }}/' + agendaId;
    }

    var submitBtn = document.getElementById('agenda-submit-btn');
    var originalText = submitBtn.innerText;
    submitBtn.disabled = true;
    submitBtn.innerText = 'Menyimpan...';

    var payload = {
        title: document.getElementById('agenda-title').value,
        start_date: document.getElementById('agenda-start-date').value,
        end_date: document.getElementById('agenda-end-date').value || null,
        description: document.getElementById('agenda-description').value,
        is_holiday: document.getElementById('agenda-is-holiday').checked ? 1 : 0,
        color: document.getElementById('agenda-color').value,
    };

    if (window.TopProgress) window.TopProgress.start();

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(({ status, body }) => {
        if (window.TopProgress) window.TopProgress.done();
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;

        if (status === 200 || status === 201) {
            showToast(body.message || 'Agenda berhasil disimpan.', 'success');
            closeAgendaModal();
            calendar.refetchEvents();
            setTimeout(function() {
                if (currentSelectedDate) {
                    showDateDetails(currentSelectedDate);
                }
            }, 400);
        } else {
            var msg = body.message || 'Terjadi kesalahan saat menyimpan agenda.';
            if (body.errors) {
                var firstKey = Object.keys(body.errors)[0];
                msg = body.errors[firstKey][0];
            }
            showToast(msg, 'error');
        }
    })
    .catch(err => {
        if (window.TopProgress) window.TopProgress.done();
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;
        showToast('Koneksi bermasalah: ' + err.message, 'error');
    });
}

function confirmDeleteAgenda(agendaId) {
    showMDModal(
        'Hapus Agenda?',
        'Apakah Anda yakin ingin menghapus agenda ini? Jika agenda ini adalah hari libur, deadline tugas akan dikalkulasi ulang.',
        function() {
            if (window.TopProgress) window.TopProgress.start();
            fetch('{{ url("agendas") }}/' + agendaId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (window.TopProgress) window.TopProgress.done();
                showToast(data.message || 'Agenda berhasil dihapus.', 'success');
                calendar.refetchEvents();
                setTimeout(function() {
                    if (currentSelectedDate) {
                        showDateDetails(currentSelectedDate);
                    }
                }, 400);
            })
            .catch(err => {
                if (window.TopProgress) window.TopProgress.done();
                showToast('Gagal menghapus agenda: ' + err.message, 'error');
            });
        },
        true
    );
}
</script>
@endpush
