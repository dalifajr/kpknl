import React, { useState, useEffect, useRef, useCallback } from 'react';
import { createRoot } from 'react-dom/client';
import './bootstrap';

const APP_BASE = window.__SSO_CONFIG__?.app_url ? window.__SSO_CONFIG__.app_url.replace(/\/+$/, '') : '/peminjaman/public';
const API_BASE = `${APP_BASE}/api`;

// Status badge helper
const getStatusBadge = (status) => {
    switch (status) {
        case 'tersedia':
            return <span className="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i className="fa-solid fa-circle-check me-1"></i> Tersedia</span>;
        case 'dipinjam':
            return <span className="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1"><i className="fa-solid fa-clock me-1"></i> Dipinjam</span>;
        case 'Proses Peminjaman':
            return <span className="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1"><i className="fa-solid fa-file-signature me-1"></i> Proses Permohonan</span>;
        case 'Menunggu Konfirmasi':
            return <span className="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i className="fa-solid fa-handshake me-1"></i> Menunggu Ambil Fisik</span>;
        case 'Sedang Dipinjam':
            return <span className="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1"><i className="fa-solid fa-box-archive me-1"></i> Sedang Dipinjam</span>;
        case 'Proses Pengembalian':
            return <span className="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i className="fa-solid fa-arrow-rotate-left me-1"></i> Proses Pengembalian</span>;
        case 'Sudah Dikembalikan':
            return <span className="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i className="fa-solid fa-check-double me-1"></i> Sudah Dikembalikan</span>;
        default:
            return <span className="badge bg-light text-dark border px-2 py-1">{status}</span>;
    }
};

export default function App() {
    // Current user state
    const [user, setUser] = useState(window.__USER__ || null);
    const [userLoading, setUserLoading] = useState(true);

    // Mobile sidebar toggle state
    const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);

    // Active Navigation Tab
    const [activeTab, setActiveTab] = useState('dashboard');

    // Dashboard Data
    const [dashboardStats, setDashboardStats] = useState(null);
    const [statsLoading, setStatsLoading] = useState(false);

    // Chart references
    const chartKomposisiRef = useRef(null);
    const chartKetersediaanRef = useRef(null);
    const chartKomposisiInstance = useRef(null);
    const chartKetersediaanInstance = useRef(null);

    // Catalog State
    const [catalogItems, setCatalogItems] = useState([]);
    const [catalogLoading, setCatalogLoading] = useState(false);
    const [catalogPage, setCatalogPage] = useState(1);
    const [catalogTotalPages, setCatalogTotalPages] = useState(1);
    const [catalogTotalItems, setCatalogTotalItems] = useState(0);
    const [catalogType, setCatalogType] = useState('all');
    const [catalogStatus, setCatalogStatus] = useState('all');
    const [catalogSearch, setCatalogSearch] = useState('');
    const [selectedCart, setSelectedCart] = useState([]); // Selected items for borrowing

    // Peminjaman State
    const [peminjamanList, setPeminjamanList] = useState([]);
    const [peminjamanLoading, setPeminjamanLoading] = useState(false);
    const [peminjamanStatusFilter, setPeminjamanStatusFilter] = useState('all');
    const [peminjamanSearch, setPeminjamanSearch] = useState('');
    const [peminjamanPage, setPeminjamanPage] = useState(1);
    const [peminjamanTotalPages, setPeminjamanTotalPages] = useState(1);
    const [selectedLoanDetail, setSelectedLoanDetail] = useState(null);

    // Validasi (Admin) State
    const [pendingList, setPendingList] = useState([]);
    const [pendingLoading, setPendingLoading] = useState(false);
    const [activeValidateItem, setActiveValidateItem] = useState(null);
    const [lemariInput, setLemariInput] = useState('Lemari Arsip Utama');
    const [boxInput, setBoxInput] = useState('Box Lelang 01');

    // Revisi State
    const [revisiList, setRevisiList] = useState([]);
    const [revisiLoading, setRevisiLoading] = useState(false);
    const [activeRejectItem, setActiveRejectItem] = useState(null);
    const [catatanRevisi, setCatatanRevisi] = useState('');
    const [activeEditRevisi, setActiveEditRevisi] = useState(null);

    // New Registration State
    const [newRisalah, setNewRisalah] = useState({
        no_risalah: '',
        jenis: 'minuta',
        tgl_risalah: new Date().toISOString().split('T')[0],
        nama_pelelang: '',
        pemohon_lelang: '',
    });

    // Multi-item Loan Form Modal State
    const [loanModalOpen, setLoanModalOpen] = useState(false);
    const [loanForm, setLoanForm] = useState({
        nama_peminjam: '',
        tgl_peminjaman: new Date().toISOString().split('T')[0],
        tgl_pengembalian: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        keperluan: '',
    });

    // Clean display name (remove brackets/extra titles)
    const rawName = user?.name || user?.username || 'Pengguna';
    const cleanName = rawName.replace(/\s*\(.*?\)\s*/g, '').trim();

    // Fetch Current User
    const fetchUser = useCallback(async () => {
        try {
            const res = await fetch(`${API_BASE}/auth/me`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.authenticated) {
                setUser(data.user);
                setLoanForm(prev => ({
                    ...prev,
                    nama_peminjam: data.user.name || data.user.username
                }));
                setNewRisalah(prev => ({
                    ...prev,
                    nama_pelelang: data.user.name || data.user.username
                }));
            }
        } catch (err) {
            console.error('Failed to fetch user profile', err);
        } finally {
            setUserLoading(false);
        }
    }, []);

    // Fetch Dashboard Stats
    const fetchStats = useCallback(async () => {
        setStatsLoading(true);
        try {
            const res = await fetch(`${API_BASE}/dashboard/stats`, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await res.json();
            if (result.status === 'success') {
                setDashboardStats(result.data);
            }
        } catch (err) {
            console.error('Error fetching dashboard stats', err);
        } finally {
            setStatsLoading(false);
        }
    }, []);

    // Fetch Catalog Items
    const fetchCatalog = useCallback(async (page = 1) => {
        setCatalogLoading(true);
        try {
            const params = new URLSearchParams({
                type: catalogType,
                status: catalogStatus,
                search: catalogSearch,
                page: page
            });
            const res = await fetch(`${API_BASE}/risalah?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await res.json();
            if (result.status === 'success') {
                setCatalogItems(result.data.data);
                setCatalogPage(result.data.current_page);
                setCatalogTotalPages(result.data.last_page);
                setCatalogTotalItems(result.data.total);
            }
        } catch (err) {
            console.error('Error fetching catalog', err);
        } finally {
            setCatalogLoading(false);
        }
    }, [catalogType, catalogStatus, catalogSearch]);

    // Fetch Peminjaman List
    const fetchPeminjaman = useCallback(async (page = 1) => {
        setPeminjamanLoading(true);
        try {
            const params = new URLSearchParams({
                status: peminjamanStatusFilter,
                search: peminjamanSearch,
                page: page
            });
            const res = await fetch(`${API_BASE}/peminjaman?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await res.json();
            if (result.status === 'success') {
                setPeminjamanList(result.data.data);
                setPeminjamanPage(result.data.current_page);
                setPeminjamanTotalPages(result.data.last_page);
            }
        } catch (err) {
            console.error('Error fetching peminjaman', err);
        } finally {
            setPeminjamanLoading(false);
        }
    }, [peminjamanStatusFilter, peminjamanSearch]);

    // Fetch Pending Risalah (Admin)
    const fetchPending = useCallback(async () => {
        setPendingLoading(true);
        try {
            const res = await fetch(`${API_BASE}/risalah/pending`, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await res.json();
            if (result.status === 'success') {
                setPendingList(result.data);
            }
        } catch (err) {
            console.error('Error fetching pending risalah', err);
        } finally {
            setPendingLoading(false);
        }
    }, []);

    // Fetch Revisi Risalah
    const fetchRevisi = useCallback(async () => {
        setRevisiLoading(true);
        try {
            const res = await fetch(`${API_BASE}/risalah/revisi`, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await res.json();
            if (result.status === 'success') {
                setRevisiList(result.data);
            }
        } catch (err) {
            console.error('Error fetching revisi risalah', err);
        } finally {
            setRevisiLoading(false);
        }
    }, []);

    // Initial load
    useEffect(() => {
        fetchUser();
        fetchStats();
    }, [fetchUser, fetchStats]);

    // Load data based on active tab
    useEffect(() => {
        if (activeTab === 'dashboard') {
            fetchStats();
        } else if (activeTab === 'katalog') {
            fetchCatalog(1);
        } else if (activeTab === 'peminjaman') {
            fetchPeminjaman(1);
        } else if (activeTab === 'validasi') {
            fetchPending();
        } else if (activeTab === 'revisi') {
            fetchRevisi();
        }
    }, [activeTab, fetchStats, fetchCatalog, fetchPeminjaman, fetchPending, fetchRevisi]);

    // Initialize Chart.js when stats are updated
    useEffect(() => {
        if (activeTab === 'dashboard' && dashboardStats && window.Chart) {
            // Chart 1: Komposisi
            if (chartKomposisiRef.current) {
                if (chartKomposisiInstance.current) {
                    chartKomposisiInstance.current.destroy();
                }
                const ctx1 = chartKomposisiRef.current.getContext('2d');
                chartKomposisiInstance.current = new window.Chart(ctx1, {
                    type: 'doughnut',
                    data: {
                        labels: dashboardStats.charts.komposisi.labels,
                        datasets: [{
                            data: dashboardStats.charts.komposisi.values,
                            backgroundColor: dashboardStats.charts.komposisi.colors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: { family: "'Google Sans', 'Plus Jakarta Sans', sans-serif", size: 11, weight: '500' },
                                    padding: 14
                                }
                            }
                        }
                    }
                });
            }

            // Chart 2: Ketersediaan
            if (chartKetersediaanRef.current) {
                if (chartKetersediaanInstance.current) {
                    chartKetersediaanInstance.current.destroy();
                }
                const ctx2 = chartKetersediaanRef.current.getContext('2d');
                chartKetersediaanInstance.current = new window.Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: dashboardStats.charts.ketersediaan.labels,
                        datasets: [{
                            data: dashboardStats.charts.ketersediaan.values,
                            backgroundColor: dashboardStats.charts.ketersediaan.colors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: { family: "'Google Sans', 'Plus Jakarta Sans', sans-serif", size: 11, weight: '500' },
                                    padding: 14
                                }
                            }
                        }
                    }
                });
            }
        }
    }, [activeTab, dashboardStats]);

    // Handle Switch Role (Demo Simulation)
    const handleSwitchRole = async (targetRole) => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`${APP_BASE}/auth/sso/switch-role`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ role: targetRole })
            });
            const result = await res.json();
            if (result.status === 'success') {
                setUser(result.user);
                window.Swal.fire({
                    icon: 'success',
                    title: 'Role Berhasil Diubah',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                fetchStats();
            }
        } catch (err) {
            console.error('Error switching role', err);
        }
    };

    // Cart Management for Borrowing
    const toggleCartItem = (item) => {
        const exists = selectedCart.some(c => c.id === item.id && c.jenis === item.jenis);
        if (exists) {
            setSelectedCart(prev => prev.filter(c => !(c.id === item.id && c.jenis === item.jenis)));
        } else {
            if (selectedCart.length >= 5) {
                window.Swal.fire({
                    icon: 'warning',
                    title: 'Batas Maksimum Tercapai',
                    text: 'Maksimum peminjaman sekaligus adalah 5 berkas risalah.',
                    confirmButtonColor: '#0c306b'
                });
                return;
            }
            setSelectedCart(prev => [...prev, item]);
        }
    };

    const isItemSelected = (item) => {
        return selectedCart.some(c => c.id === item.id && c.jenis === item.jenis);
    };

    // Workflow Actions: Loan Creation
    const handleCreateLoan = async (e) => {
        e.preventDefault();
        if (selectedCart.length === 0) {
            window.Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Pilih minimal satu berkas risalah untuk dipinjam.',
                confirmButtonColor: '#0c306b'
            });
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const payload = {
                ...loanForm,
                items: selectedCart.map(i => ({
                    risalah_id: i.id,
                    jenis_risalah: i.jenis
                }))
            };

            const res = await fetch(`${API_BASE}/peminjaman`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Permohonan Diajukan!',
                    text: result.message,
                    confirmButtonColor: '#0c306b'
                });
                setSelectedCart([]);
                setLoanModalOpen(false);
                setActiveTab('peminjaman');
                fetchPeminjaman(1);
            } else {
                window.Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengajukan',
                    text: result.message,
                    confirmButtonColor: '#0c306b'
                });
            }
        } catch (err) {
            console.error('Error submitting loan', err);
        }
    };

    // Workflow Actions: Approval / Receipt / Return
    const handleApproveLoan = async (id) => {
        const confirm = await window.Swal.fire({
            title: 'Setujui Permohonan Pinjam?',
            text: 'Berkas akan disiapkan dan peminjam dapat mengambil berkas fisik di ruang arsip.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0c306b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        });
        if (!confirm.isConfirmed) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`${API_BASE}/peminjaman/${id}/approve`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }
            });
            const result = await res.json();
            if (result.status === 'success') {
                window.Swal.fire({ icon: 'success', title: 'Disetujui', text: result.message, confirmButtonColor: '#0c306b' });
                fetchPeminjaman(peminjamanPage);
            } else {
                window.Swal.fire({ icon: 'error', title: 'Gagal', text: result.message, confirmButtonColor: '#0c306b' });
            }
        } catch (err) {
            console.error(err);
        }
    };

    const handleConfirmReceiveLoan = async (id) => {
        const confirm = await window.Swal.fire({
            title: 'Konfirmasi Penerimaan Fisik Berkas?',
            text: 'Pastikan Anda telah memeriksa dan memegang berkas fisik risalah lelang.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Berkas Fisik Diterima',
            cancelButtonText: 'Batal'
        });
        if (!confirm.isConfirmed) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`${API_BASE}/peminjaman/${id}/confirm-receive`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }
            });
            const result = await res.json();
            if (result.status === 'success') {
                window.Swal.fire({ icon: 'success', title: 'Berhasil Dikonfirmasi', text: result.message, confirmButtonColor: '#0c306b' });
                fetchPeminjaman(peminjamanPage);
            } else {
                window.Swal.fire({ icon: 'error', title: 'Gagal', text: result.message, confirmButtonColor: '#0c306b' });
            }
        } catch (err) {
            console.error(err);
        }
    };

    const handleRequestReturnLoan = async (id) => {
        const confirm = await window.Swal.fire({
            title: 'Ajukan Pengembalian Berkas?',
            text: 'Serahkan berkas fisik ke Admin Seksi Hukum & Informasi untuk diverifikasi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Serahkan Kembali',
            cancelButtonText: 'Batal'
        });
        if (!confirm.isConfirmed) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`${API_BASE}/peminjaman/${id}/return-request`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }
            });
            const result = await res.json();
            if (result.status === 'success') {
                window.Swal.fire({ icon: 'success', title: 'Diajukan', text: result.message, confirmButtonColor: '#0c306b' });
                fetchPeminjaman(peminjamanPage);
            } else {
                window.Swal.fire({ icon: 'error', title: 'Gagal', text: result.message, confirmButtonColor: '#0c306b' });
            }
        } catch (err) {
            console.error(err);
        }
    };

    const handleVerifyReturnLoan = async (id) => {
        const confirm = await window.Swal.fire({
            title: 'Verifikasi Pengembalian Fisik?',
            text: 'Berkas akan diperiksa dan status risalah dalam katalog akan kembali menjadi Tersedia.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Verifikasi Lengkap',
            cancelButtonText: 'Batal'
        });
        if (!confirm.isConfirmed) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`${API_BASE}/peminjaman/${id}/verify-return`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }
            });
            const result = await res.json();
            if (result.status === 'success') {
                window.Swal.fire({ icon: 'success', title: 'Selesai Dikembalikan', text: result.message, confirmButtonColor: '#0c306b' });
                fetchPeminjaman(peminjamanPage);
            } else {
                window.Swal.fire({ icon: 'error', title: 'Gagal', text: result.message, confirmButtonColor: '#0c306b' });
            }
        } catch (err) {
            console.error(err);
        }
    };

    // Validasi Risalah Pending (Admin)
    const handleValidatePending = async (e) => {
        e.preventDefault();
        if (!activeValidateItem) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`${API_BASE}/risalah/${activeValidateItem.id}/validate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    lemari: lemariInput,
                    box: boxInput
                })
            });
            const result = await res.json();
            if (result.status === 'success') {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Risalah Divalidasi!',
                    text: result.message,
                    confirmButtonColor: '#0c306b'
                });
                setActiveValidateItem(null);
                fetchPending();
            } else {
                window.Swal.fire({ icon: 'error', title: 'Gagal', text: result.message, confirmButtonColor: '#0c306b' });
            }
        } catch (err) {
            console.error(err);
        }
    };

    // Reject / Request Revision on Pending Risalah (Admin)
    const handleRejectPending = async (e) => {
        e.preventDefault();
        if (!activeRejectItem || !catatanRevisi) {
            window.Swal.fire({ icon: 'warning', title: 'Catatan Wajib Diisi', text: 'Mohon cantumkan poin kekurangan atau revisi risalah.', confirmButtonColor: '#0c306b' });
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`${API_BASE}/risalah/${activeRejectItem.id}/reject-revision`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    catatan: catatanRevisi
                })
            });
            const result = await res.json();
            if (result.status === 'success') {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Dikembalikan ke Pejabat Lelang',
                    text: result.message,
                    confirmButtonColor: '#0c306b'
                });
                setActiveRejectItem(null);
                setCatatanRevisi('');
                fetchPending();
            } else {
                window.Swal.fire({ icon: 'error', title: 'Gagal', text: result.message, confirmButtonColor: '#0c306b' });
            }
        } catch (err) {
            console.error(err);
        }
    };

    // Store New Risalah (Pelelang)
    const handleCreateRisalah = async (e) => {
        e.preventDefault();
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`${API_BASE}/risalah/store`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(newRisalah)
            });
            const result = await res.json();
            if (result.status === 'success') {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil!',
                    text: result.message,
                    confirmButtonColor: '#0c306b'
                });
                setNewRisalah({
                    no_risalah: '',
                    jenis: 'minuta',
                    tgl_risalah: new Date().toISOString().split('T')[0],
                    nama_pelelang: user?.name || user?.username || '',
                    pemohon_lelang: '',
                });
                setActiveTab('katalog');
            } else {
                window.Swal.fire({ icon: 'error', title: 'Gagal Mendaftar', text: result.message, confirmButtonColor: '#0c306b' });
            }
        } catch (err) {
            console.error(err);
        }
    };

    // Resubmit Revisi Risalah (Pelelang)
    const handleResubmitRevisi = async (e) => {
        e.preventDefault();
        if (!activeEditRevisi) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`${API_BASE}/risalah/${activeEditRevisi.id}/resubmit-revisi`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(activeEditRevisi)
            });
            const result = await res.json();
            if (result.status === 'success') {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Dikirim Ulang',
                    text: result.message,
                    confirmButtonColor: '#0c306b'
                });
                setActiveEditRevisi(null);
                fetchRevisi();
            } else {
                window.Swal.fire({ icon: 'error', title: 'Gagal', text: result.message, confirmButtonColor: '#0c306b' });
            }
        } catch (err) {
            console.error(err);
        }
    };

    return (
        <div className="bg-body-tertiary min-vh-100 position-relative">
            {/* 1. TOPBAR NAVIGATION (Exact Dashboard Pengelolaan BMN Style) */}
            <nav className="navbar navbar-expand fixed-top shadow-sm px-3 bg-body border-bottom" style={{ zIndex: 1030 }}>
                <div className="d-flex align-items-center gap-3 w-100">
                    {/* Mobile Toggle Button */}
                    <button
                        className="btn btn-light border-0 d-lg-none me-2"
                        type="button"
                        id="sidebarToggle"
                        onClick={() => setMobileSidebarOpen(!mobileSidebarOpen)}
                    >
                        <i className="fas fa-bars text-secondary fs-5"></i>
                    </button>

                    {/* Brand Logo & Executive Title */}
                    <div className="navbar-brand d-flex align-items-center gap-2 text-primary fw-bold m-0">
                        <img
                            src={`${APP_BASE}/images/logo-kpknl.png`}
                            alt="Logo KPKNL"
                            height="36"
                            onError={(e) => { e.currentTarget.style.display = 'none'; }}
                        />
                        <div className="d-flex flex-column">
                            <span className="d-none d-sm-inline fs-6 text-dark fw-bold">
                                SISTEM INFORMASI PEMINJAMAN <span className="fw-light text-secondary fs-6">| Risalah Lelang</span>
                            </span>
                            <span className="d-inline d-sm-none fs-6 text-dark fw-bold">Peminjaman Risalah</span>
                        </div>
                    </div>

                    {/* Right Actions: Live Pulse, Role Switcher Demo, SSO Portal, Logout */}
                    <div className="ms-auto d-flex align-items-center gap-2">
                        {/* Live Sync / Status Badge */}
                        <div className="d-none d-lg-flex align-items-center bg-light border rounded-pill px-3 py-1 text-muted small shadow-2xs">
                            <span className="pulse-beacon me-2" title="Koneksi Sistem Aktif"></span>
                            <i className="fa-solid fa-server text-success me-1"></i>
                            <span>Database & SSO:</span>
                            <strong className="text-dark ms-1">Terhubung Aktif</strong>
                        </div>

                        {/* Interactive Role Switcher Demo */}
                        <div className="dropdown">
                            <button
                                className="btn btn-sm btn-outline-primary dropdown-toggle text-capitalize px-2.5 py-1.5 rounded-pill"
                                style={{ fontSize: '0.82rem' }}
                                type="button"
                                data-bs-toggle="dropdown"
                            >
                                <i className="fa-solid fa-sliders me-1 text-warning"></i> Role: <strong>{user?.role || '...'}</strong>
                            </button>
                            <ul className="dropdown-menu dropdown-menu-end shadow-sm border-0" style={{ fontSize: '0.82rem' }}>
                                <li className="dropdown-header text-uppercase fw-bold text-muted" style={{ fontSize: '0.68rem' }}>Simulasi Hak Akses Aplikasi</li>
                                <li>
                                    <button className="dropdown-item" onClick={() => handleSwitchRole('admin')}>
                                        <i className="fa-solid fa-user-shield me-2 text-primary"></i> Admin / Seksi HI
                                    </button>
                                </li>
                                <li>
                                    <button className="dropdown-item" onClick={() => handleSwitchRole('pelelang')}>
                                        <i className="fa-solid fa-gavel me-2 text-warning"></i> Pejabat Lelang
                                    </button>
                                </li>
                                <li>
                                    <button className="dropdown-item" onClick={() => handleSwitchRole('peminjam')}>
                                        <i className="fa-solid fa-book-reader me-2 text-success"></i> Peminjam / Pegawai
                                    </button>
                                </li>
                                <li><hr className="dropdown-divider" /></li>
                                <li>
                                    <a className="dropdown-item text-muted" href="http://localhost/sso-kpknl-palembang/public/admin/applications/8" target="_blank" rel="noreferrer">
                                        <i className="fa-solid fa-cog me-2"></i> Kelola Role di SSO Portal
                                    </a>
                                </li>
                            </ul>
                        </div>

                        {/* Back to SSO Portal Button */}
                        <a
                            href={window.__SSO_CONFIG__?.sso_dashboard_url || 'http://localhost/sso-kpknl-palembang/public/dashboard'}
                            className="btn btn-sm btn-outline-primary d-flex align-items-center gap-1.5 rounded-pill px-3 shadow-sm"
                            title="Kembali ke Dashboard Portal SSO"
                            style={{ fontSize: '0.82rem' }}
                        >
                            <i className="fa-solid fa-cubes"></i>
                            <span className="d-none d-sm-inline">Portal SSO</span>
                        </a>

                        {/* Direct Logout Button */}
                        <a
                            href={`${APP_BASE}/logout`}
                            className="btn btn-sm btn-outline-danger d-flex align-items-center gap-1.5 rounded-pill px-3 shadow-sm"
                            title="Logout dan Kembali ke Login SSO"
                            style={{ fontSize: '0.82rem' }}
                        >
                            <i className="fa-solid fa-sign-out-alt"></i>
                            <span className="d-none d-sm-inline">Logout</span>
                        </a>
                    </div>
                </div>
            </nav>

            {/* Mobile Drawer Overlay */}
            <div
                className={`sidebar-overlay ${mobileSidebarOpen ? 'show' : ''}`}
                onClick={() => setMobileSidebarOpen(false)}
            ></div>

            {/* 2. APP CONTAINER (Offset for Fixed Sidebar) */}
            <div className="app-container">
                {/* Fixed Left Sidebar (280px, Exact BMN Design) */}
                <div className={`sidebar ${mobileSidebarOpen ? 'show' : ''}`} id="sidebar">
                    {/* User Header Profile */}
                    <div className="sidebar-header d-flex align-items-center gap-3">
                        <div
                            className="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm"
                            style={{ width: '42px', height: '42px', fontSize: '1.1rem', flexShrink: 0 }}
                        >
                            {cleanName ? cleanName.charAt(0).toUpperCase() : 'U'}
                        </div>
                        <div className="d-flex flex-column" style={{ lineHeight: 1.25, minWidth: 0, flex: 1 }}>
                            <span className="fw-bold text-body" style={{ wordBreak: 'break-word', overflowWrap: 'break-word', whiteSpace: 'normal' }}>
                                {cleanName}
                            </span>
                            <small className="text-secondary mt-1">
                                <span className={`badge ${user?.role_badge_class || 'bg-primary-subtle text-primary border border-primary-subtle'}`} style={{ fontSize: '0.65rem' }}>
                                    {user?.role_label || user?.role || 'Guest'}
                                </span>
                            </small>
                        </div>
                    </div>

                    {/* Grouped Monochrome Menu Navigation */}
                    <div className="py-3 px-2 flex-grow-1">
                        <div className="menu-group">
                            <div className="menu-header">Menu Utama</div>

                            {/* 1. Monitoring Risalah Lelang */}
                            <button
                                className={`menu-item ${activeTab === 'dashboard' ? 'active' : ''}`}
                                onClick={() => { setActiveTab('dashboard'); setMobileSidebarOpen(false); }}
                            >
                                <i className="fas fa-chart-pie"></i>
                                <span>Monitoring Risalah</span>
                            </button>

                            {/* 2. Katalog Risalah Lelang */}
                            <button
                                className={`menu-item ${activeTab === 'katalog' ? 'active' : ''}`}
                                onClick={() => { setActiveTab('katalog'); setMobileSidebarOpen(false); }}
                            >
                                <i className="fas fa-boxes-stacked"></i>
                                <span className="flex-grow-1">Katalog Risalah</span>
                                {selectedCart.length > 0 && (
                                    <span className="badge bg-warning text-dark rounded-pill px-2 py-0.5" style={{ fontSize: '0.68rem' }}>
                                        {selectedCart.length}
                                    </span>
                                )}
                            </button>

                            {/* 3. Peminjaman Berkas */}
                            <button
                                className={`menu-item ${activeTab === 'peminjaman' ? 'active' : ''}`}
                                onClick={() => { setActiveTab('peminjaman'); setMobileSidebarOpen(false); }}
                            >
                                <i className="fas fa-handshake-angle"></i>
                                <span>Peminjaman Berkas</span>
                            </button>

                            <div className="menu-header mt-3">Manajemen Risalah</div>

                            {/* 4. Validasi Risalah (Admin) */}
                            {user?.role === 'admin' && (
                                <button
                                    className={`menu-item ${activeTab === 'validasi' ? 'active' : ''}`}
                                    onClick={() => { setActiveTab('validasi'); setMobileSidebarOpen(false); }}
                                >
                                    <i className="fas fa-file-circle-check"></i>
                                    <span className="flex-grow-1">Validasi Risalah</span>
                                    {dashboardStats?.counts?.pending > 0 && (
                                        <span className="badge bg-danger rounded-pill px-2 py-0.5" style={{ fontSize: '0.68rem' }}>
                                            {dashboardStats.counts.pending}
                                        </span>
                                    )}
                                </button>
                            )}

                            {/* 5. Pendaftaran Baru (Pelelang & Admin) */}
                            {(user?.role === 'pelelang' || user?.role === 'admin') && (
                                <button
                                    className={`menu-item ${activeTab === 'pendaftaran' ? 'active' : ''}`}
                                    onClick={() => { setActiveTab('pendaftaran'); setMobileSidebarOpen(false); }}
                                >
                                    <i className="fas fa-file-circle-plus"></i>
                                    <span>Pendaftaran Baru</span>
                                </button>
                            )}

                            {/* 6. Revisi Risalah (Pelelang & Admin) */}
                            {(user?.role === 'pelelang' || user?.role === 'admin') && (
                                <button
                                    className={`menu-item ${activeTab === 'revisi' ? 'active' : ''}`}
                                    onClick={() => { setActiveTab('revisi'); setMobileSidebarOpen(false); }}
                                >
                                    <i className="fas fa-file-pen"></i>
                                    <span className="flex-grow-1">Revisi Risalah</span>
                                    {dashboardStats?.counts?.revisi > 0 && (
                                        <span className="badge bg-warning text-dark rounded-pill px-2 py-0.5" style={{ fontSize: '0.68rem' }}>
                                            {dashboardStats.counts.revisi}
                                        </span>
                                    )}
                                </button>
                            )}

                            <div className="menu-header mt-3">Akses & SSO</div>

                            {/* Portal SSO Link */}
                            <a
                                href={window.__SSO_CONFIG__?.sso_dashboard_url || 'http://localhost/sso-kpknl-palembang/public/dashboard'}
                                className="menu-item text-secondary"
                            >
                                <i className="fas fa-cubes"></i>
                                <span>Portal SSO Terpusat</span>
                            </a>

                            {/* Manage Role in SSO */}
                            <a
                                href="http://localhost/sso-kpknl-palembang/public/admin/applications/8"
                                target="_blank"
                                rel="noreferrer"
                                className="menu-item text-secondary"
                            >
                                <i className="fas fa-shield-halved"></i>
                                <span>Kelola Hak Akses (SSO)</span>
                            </a>
                        </div>
                    </div>

                    {/* Quick Cart Floating Panel in Sidebar */}
                    {selectedCart.length > 0 && (
                        <div className="p-3 border-top bg-light">
                            <div className="d-flex align-items-center justify-content-between mb-2">
                                <strong className="text-dark small">
                                    <i className="fa-solid fa-cart-shopping text-warning me-1"></i> Keranjang ({selectedCart.length})
                                </strong>
                                <button className="btn btn-sm btn-link text-danger p-0 text-decoration-none small" onClick={() => setSelectedCart([])}>
                                    Reset
                                </button>
                            </div>
                            <button className="btn btn-sm btn-warning w-100 fw-bold shadow-sm" onClick={() => setLoanModalOpen(true)}>
                                Ajukan Peminjaman
                            </button>
                        </div>
                    )}
                </div>

                {/* 3. MAIN CONTENT AREA (Hero Banner + Tab Views) */}
                <div className="main-content position-relative" id="mainContentArea">
                    {/* Deep Navy Blue Gradient Hero Backdrop */}
                    <div className="main-background"></div>

                    {/* Container Fluid with z-index 1 */}
                    <div className="container-fluid position-relative px-3 px-md-4 py-4" style={{ zIndex: 1 }}>
                        {/* Page Header on Top of Hero Gradient */}
                        <div className="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 text-white">
                            <div>
                                <h3 className="fw-bold mb-1 text-white">
                                    {activeTab === 'dashboard' && 'Monitoring Risalah Lelang'}
                                    {activeTab === 'katalog' && 'Katalog Risalah Lelang KPKNL Palembang'}
                                    {activeTab === 'peminjaman' && 'Tata Kelola Peminjaman Berkas'}
                                    {activeTab === 'validasi' && 'Antrean Validasi Risalah Masuk'}
                                    {activeTab === 'pendaftaran' && 'Pendaftaran Risalah Lelang Baru'}
                                    {activeTab === 'revisi' && 'Perbaikan & Revisi Risalah Lelang'}
                                </h3>
                                <p className="text-white-50 mb-0 small">
                                    Sistem Informasi Peminjaman dan Validasi Dokumen Risalah Lelang &bull; KPKNL Palembang
                                </p>
                            </div>
                            <div className="d-flex align-items-center gap-2 mt-3 mt-md-0">
                                {activeTab === 'katalog' && selectedCart.length > 0 && (
                                    <button
                                        className="btn btn-warning btn-sm d-flex align-items-center gap-2 fw-semibold text-dark shadow-sm"
                                        onClick={() => setLoanModalOpen(true)}
                                    >
                                        <i className="fa-solid fa-cart-shopping"></i> Pinjam ({selectedCart.length} Berkas)
                                    </button>
                                )}
                                <button
                                    className="btn btn-light btn-sm d-flex align-items-center gap-2 text-primary fw-semibold shadow-sm"
                                    onClick={() => {
                                        fetchStats();
                                        if (activeTab === 'katalog') fetchCatalog(catalogPage);
                                        if (activeTab === 'peminjaman') fetchPeminjaman(peminjamanPage);
                                        if (activeTab === 'validasi') fetchPending();
                                        if (activeTab === 'revisi') fetchRevisi();
                                    }}
                                >
                                    <i className="fa-solid fa-arrows-rotate"></i> Segarkan Data
                                </button>
                            </div>
                        </div>

                        {/* ========================================================= */}
                        {/* TAB 1: MONITORING RISALAH LELANG                          */}
                        {/* ========================================================= */}
                        {activeTab === 'dashboard' && (
                            <div>
                                {/* Executive KPI Stat Cards (Exact BMN Interactive Card Pattern) */}
                                <div className="row g-3 mb-4">
                                    {/* Total Risalah */}
                                    <div className="col-12 col-sm-6 col-xl-2">
                                        <div
                                            className="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-primary"
                                            onClick={() => { setActiveTab('katalog'); setCatalogType('all'); }}
                                            title="Klik untuk melihat seluruh katalog risalah"
                                        >
                                            <div className="card-body p-3">
                                                <div className="d-flex justify-content-between align-items-center">
                                                    <small className="text-uppercase text-muted fw-bold" style={{ fontSize: '0.68rem' }}>TOTAL ARSIP</small>
                                                    <span className="interactive-badge-hint"><i className="fas fa-arrow-up-right-from-square"></i></span>
                                                </div>
                                                <div className="d-flex align-items-center mt-1">
                                                    <h3 className="fw-bold text-dark mb-0">
                                                        {dashboardStats?.counts?.total_all?.toLocaleString('id-ID') || '10.483'} <span className="fs-6 text-muted fw-normal">Berkas</span>
                                                    </h3>
                                                    <i className="fas fa-landmark ms-auto text-primary opacity-50 fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Minuta (Laku) */}
                                    <div className="col-12 col-sm-6 col-xl-2">
                                        <div
                                            className="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-info"
                                            onClick={() => { setActiveTab('katalog'); setCatalogType('minuta'); }}
                                            title="Klik untuk melihat risalah Minuta"
                                        >
                                            <div className="card-body p-3">
                                                <div className="d-flex justify-content-between align-items-center">
                                                    <small className="text-uppercase text-muted fw-bold" style={{ fontSize: '0.68rem' }}>MINUTA (LAKU)</small>
                                                    <span className="interactive-badge-hint"><i className="fas fa-arrow-up-right-from-square"></i></span>
                                                </div>
                                                <div className="d-flex align-items-center mt-1">
                                                    <h3 className="fw-bold text-dark mb-0">
                                                        {dashboardStats?.counts?.minuta?.toLocaleString('id-ID') || '8.529'} <span className="fs-6 text-muted fw-normal">Berkas</span>
                                                    </h3>
                                                    <i className="fas fa-gavel ms-auto text-info opacity-50 fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* TAP */}
                                    <div className="col-12 col-sm-6 col-xl-2">
                                        <div
                                            className="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-warning"
                                            onClick={() => { setActiveTab('katalog'); setCatalogType('tap'); }}
                                            title="Klik untuk melihat risalah TAP"
                                        >
                                            <div className="card-body p-3">
                                                <div className="d-flex justify-content-between align-items-center">
                                                    <small className="text-uppercase text-muted fw-bold" style={{ fontSize: '0.68rem' }}>TAP</small>
                                                    <span className="interactive-badge-hint"><i className="fas fa-arrow-up-right-from-square"></i></span>
                                                </div>
                                                <div className="d-flex align-items-center mt-1">
                                                    <h3 className="fw-bold text-dark mb-0">
                                                        {dashboardStats?.counts?.tap?.toLocaleString('id-ID') || '1.340'} <span className="fs-6 text-muted fw-normal">Berkas</span>
                                                    </h3>
                                                    <i className="fas fa-file-excel ms-auto text-warning opacity-50 fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Batal */}
                                    <div className="col-12 col-sm-6 col-xl-2">
                                        <div
                                            className="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-danger"
                                            onClick={() => { setActiveTab('katalog'); setCatalogType('batal'); }}
                                            title="Klik untuk melihat risalah Batal"
                                        >
                                            <div className="card-body p-3">
                                                <div className="d-flex justify-content-between align-items-center">
                                                    <small className="text-uppercase text-muted fw-bold" style={{ fontSize: '0.68rem' }}>BATAL LELANG</small>
                                                    <span className="interactive-badge-hint"><i className="fas fa-arrow-up-right-from-square"></i></span>
                                                </div>
                                                <div className="d-flex align-items-center mt-1">
                                                    <h3 className="fw-bold text-dark mb-0">
                                                        {dashboardStats?.counts?.batal?.toLocaleString('id-ID') || '614'} <span className="fs-6 text-muted fw-normal">Berkas</span>
                                                    </h3>
                                                    <i className="fas fa-ban ms-auto text-danger opacity-50 fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Tersedia Fisik */}
                                    <div className="col-12 col-sm-6 col-xl-2">
                                        <div
                                            className="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-success"
                                            onClick={() => { setActiveTab('katalog'); setCatalogStatus('tersedia'); }}
                                            title="Klik untuk melihat berkas fisik yang tersedia"
                                        >
                                            <div className="card-body p-3">
                                                <div className="d-flex justify-content-between align-items-center">
                                                    <small className="text-uppercase text-muted fw-bold" style={{ fontSize: '0.68rem' }}>TERSEDIA FISIK</small>
                                                    <span className="interactive-badge-hint"><i className="fas fa-arrow-up-right-from-square"></i></span>
                                                </div>
                                                <div className="d-flex align-items-center mt-1">
                                                    <h3 className="fw-bold text-success mb-0">
                                                        {dashboardStats?.counts?.tersedia?.toLocaleString('id-ID') || '10.415'} <span className="fs-6 text-muted fw-normal">Berkas</span>
                                                    </h3>
                                                    <i className="fas fa-vault ms-auto text-success opacity-50 fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Peminjaman Aktif */}
                                    <div className="col-12 col-sm-6 col-xl-2">
                                        <div
                                            className="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-secondary"
                                            onClick={() => { setActiveTab('peminjaman'); }}
                                            title="Klik untuk mengelola peminjaman aktif"
                                        >
                                            <div className="card-body p-3">
                                                <div className="d-flex justify-content-between align-items-center">
                                                    <small className="text-uppercase text-muted fw-bold" style={{ fontSize: '0.68rem' }}>PEMINJAMAN AKTIF</small>
                                                    <span className="interactive-badge-hint"><i className="fas fa-arrow-up-right-from-square"></i></span>
                                                </div>
                                                <div className="d-flex align-items-center mt-1">
                                                    <h3 className="fw-bold text-warning mb-0">
                                                        {dashboardStats?.counts?.peminjaman_aktif?.toLocaleString('id-ID') || '68'} <span className="fs-6 text-muted fw-normal">Berkas</span>
                                                    </h3>
                                                    <i className="fas fa-hand-holding-hand ms-auto text-warning opacity-50 fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Charts & Leaderboards Row */}
                                <div className="row g-4 mb-4">
                                    {/* Chart Komposisi */}
                                    <div className="col-12 col-lg-4">
                                        <div className="card h-100 shadow-sm border-0">
                                            <div className="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                                <div className="fw-bold text-dark">
                                                    <i className="fas fa-chart-pie text-primary me-2"></i> Komposisi Risalah Lelang
                                                </div>
                                                <span className="badge bg-light text-muted border">10.483 Total</span>
                                            </div>
                                            <div className="card-body d-flex flex-column align-items-center justify-content-center p-3" style={{ minHeight: '260px' }}>
                                                <div style={{ width: '100%', height: '220px' }}>
                                                    <canvas ref={chartKomposisiRef} className="chart-interactive"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Chart Ketersediaan */}
                                    <div className="col-12 col-lg-4">
                                        <div className="card h-100 shadow-sm border-0">
                                            <div className="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                                <div className="fw-bold text-dark">
                                                    <i className="fas fa-chart-pie text-success me-2"></i> Status Ketersediaan Fisik
                                                </div>
                                                <span className="badge bg-success-subtle text-success border">Realtime Lemari</span>
                                            </div>
                                            <div className="card-body d-flex flex-column align-items-center justify-content-center p-3" style={{ minHeight: '260px' }}>
                                                <div style={{ width: '100%', height: '220px' }}>
                                                    <canvas ref={chartKetersediaanRef} className="chart-interactive"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Leaderboard Pejabat Lelang */}
                                    <div className="col-12 col-lg-4">
                                        <div className="card h-100 shadow-sm border-0">
                                            <div className="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                                <div className="fw-bold text-dark">
                                                    <i className="fas fa-trophy text-warning me-2"></i> Top Pejabat Lelang
                                                </div>
                                                <span className="badge bg-warning-subtle text-warning-emphasis border">Minuta Terbanyak</span>
                                            </div>
                                            <div className="card-body p-0">
                                                <ul className="list-group list-group-flush">
                                                    {dashboardStats?.leaderboards?.top_pelelang?.map((item, idx) => (
                                                        <li key={idx} className="list-group-item d-flex align-items-center justify-content-between py-2.5 px-3">
                                                            <div className="d-flex align-items-center gap-2">
                                                                <span
                                                                    className={`badge ${idx === 0 ? 'bg-warning text-dark' : idx === 1 ? 'bg-secondary' : 'bg-light text-muted border'} rounded-circle`}
                                                                    style={{ width: '24px', height: '24px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '0.72rem' }}
                                                                >
                                                                    {idx + 1}
                                                                </span>
                                                                <span className="fw-medium text-dark text-truncate" style={{ maxWidth: '180px', fontSize: '0.82rem' }}>
                                                                    {item.nama_pelelang}
                                                                </span>
                                                            </div>
                                                            <span className="badge bg-primary-subtle text-primary fw-bold" style={{ fontSize: '0.75rem' }}>
                                                                {item.total?.toLocaleString('id-ID')} Risalah
                                                            </span>
                                                        </li>
                                                    )) || (
                                                        <li className="list-group-item text-center text-muted py-3">Memuat peringkat pelelang...</li>
                                                    )}
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Recent Loan Requests Card */}
                                <div className="card shadow-sm border-0 mb-4">
                                    <div className="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                        <div className="fw-bold text-dark">
                                            <i className="fas fa-clock-rotate-left text-primary me-2"></i> Permohonan & Peminjaman Berkas Terkini
                                        </div>
                                        <button className="btn btn-sm btn-outline-primary" onClick={() => setActiveTab('peminjaman')}>
                                            Buka Seluruh Peminjaman <i className="fas fa-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                    <div className="table-responsive">
                                        <table className="table table-hover align-middle mb-0" style={{ fontSize: '0.86rem' }}>
                                            <thead className="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Peminjam</th>
                                                    <th>Tgl Pinjam</th>
                                                    <th>Batas Kembali</th>
                                                    <th>Status Alur</th>
                                                    <th className="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {dashboardStats?.recent_peminjaman?.length > 0 ? (
                                                    dashboardStats.recent_peminjaman.map((p, idx) => (
                                                        <tr key={p.id}>
                                                            <td className="fw-bold text-muted">#{p.id}</td>
                                                            <td className="fw-semibold text-dark">{p.nama_peminjam}</td>
                                                            <td className="text-muted">{p.tgl_peminjaman || '-'}</td>
                                                            <td className="text-muted">{p.tgl_pengembalian || '-'}</td>
                                                            <td>{getStatusBadge(p.status)}</td>
                                                            <td className="text-end">
                                                                <button
                                                                    className="btn btn-sm btn-light border py-1 px-2.5"
                                                                    onClick={() => setSelectedLoanDetail(p)}
                                                                >
                                                                    <i className="fa-solid fa-eye me-1"></i> Detail
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    ))
                                                ) : (
                                                    <tr>
                                                        <td colSpan="6" className="text-center py-4 text-muted">Belum ada data peminjaman terkini.</td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* ========================================================= */}
                        {/* TAB 2: KATALOG RISALAH LELANG                             */}
                        {/* ========================================================= */}
                        {activeTab === 'katalog' && (
                            <div className="card shadow-sm border-0 mb-4">
                                <div className="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                                    <div>
                                        <div className="fw-bold text-dark fs-6">
                                            <i className="fas fa-boxes-stacked text-primary me-2"></i> Katalog Risalah Lelang KPKNL Palembang
                                        </div>
                                        <small className="text-muted">
                                            Cari dan pilih berkas risalah lelang fisik (Total {catalogTotalItems?.toLocaleString('id-ID')} berkas tercatat)
                                        </small>
                                    </div>
                                    <div className="d-flex align-items-center gap-2">
                                        {selectedCart.length > 0 && (
                                            <button
                                                className="btn btn-warning text-dark fw-bold btn-sm shadow-sm"
                                                onClick={() => setLoanModalOpen(true)}
                                            >
                                                <i className="fa-solid fa-paper-plane me-1"></i> Ajukan Pinjam ({selectedCart.length} Berkas)
                                            </button>
                                        )}
                                        <button className="btn btn-sm btn-outline-secondary" onClick={() => fetchCatalog(catalogPage)} title="Refresh Data">
                                            <i className="fa-solid fa-arrows-rotate"></i>
                                        </button>
                                    </div>
                                </div>

                                <div className="card-body p-3">
                                    {/* FILTERS & SEARCH ROW */}
                                    <div className="row g-2 mb-3">
                                        {/* Jenis Filter Tabs */}
                                        <div className="col-12 col-md-5">
                                            <div className="btn-group w-100" role="group">
                                                <button
                                                    type="button"
                                                    className={`btn btn-sm ${catalogType === 'all' ? 'btn-primary' : 'btn-outline-secondary'}`}
                                                    onClick={() => { setCatalogType('all'); }}
                                                >
                                                    Semua (10k+)
                                                </button>
                                                <button
                                                    type="button"
                                                    className={`btn btn-sm ${catalogType === 'minuta' ? 'btn-primary' : 'btn-outline-secondary'}`}
                                                    onClick={() => { setCatalogType('minuta'); }}
                                                >
                                                    Minuta
                                                </button>
                                                <button
                                                    type="button"
                                                    className={`btn btn-sm ${catalogType === 'tap' ? 'btn-primary' : 'btn-outline-secondary'}`}
                                                    onClick={() => { setCatalogType('tap'); }}
                                                >
                                                    TAP
                                                </button>
                                                <button
                                                    type="button"
                                                    className={`btn btn-sm ${catalogType === 'batal' ? 'btn-primary' : 'btn-outline-secondary'}`}
                                                    onClick={() => { setCatalogType('batal'); }}
                                                >
                                                    Batal
                                                </button>
                                            </div>
                                        </div>

                                        {/* Status Filter */}
                                        <div className="col-6 col-md-3">
                                            <select
                                                className="form-select form-select-sm"
                                                value={catalogStatus}
                                                onChange={(e) => setCatalogStatus(e.target.value)}
                                            >
                                                <option value="all">Semua Status Fisik</option>
                                                <option value="tersedia">Tersedia di Lemari</option>
                                                <option value="dipinjam">Sedang Dipinjam</option>
                                            </select>
                                        </div>

                                        {/* Search Input */}
                                        <div className="col-6 col-md-4">
                                            <div className="input-group input-group-sm">
                                                <input
                                                    type="text"
                                                    className="form-control"
                                                    placeholder="Cari No Risalah / Pelelang / Pemohon..."
                                                    value={catalogSearch}
                                                    onChange={(e) => setCatalogSearch(e.target.value)}
                                                    onKeyDown={(e) => { if (e.key === 'Enter') fetchCatalog(1); }}
                                                />
                                                <button className="btn btn-primary" type="button" onClick={() => fetchCatalog(1)}>
                                                    <i className="fa-solid fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {/* CATALOG TABLE */}
                                    <div className="table-responsive">
                                        <table className="table table-hover align-middle mb-0" style={{ fontSize: '0.86rem' }}>
                                            <thead className="table-light">
                                                <tr>
                                                    <th style={{ width: '40px' }}>Pilih</th>
                                                    <th>Jenis</th>
                                                    <th>Nomor Risalah</th>
                                                    <th>Tanggal</th>
                                                    <th>Pejabat Lelang</th>
                                                    <th>Pemohon Lelang</th>
                                                    <th>Lokasi Lemari & Box</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {catalogLoading ? (
                                                    <tr>
                                                        <td colSpan="8" className="text-center py-5">
                                                            <div className="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                                            Memuat data katalog risalah lelang...
                                                        </td>
                                                    </tr>
                                                ) : catalogItems.length > 0 ? (
                                                    catalogItems.map((item) => {
                                                        const selected = isItemSelected(item);
                                                        const isAvailable = item.status === 'tersedia';
                                                        return (
                                                            <tr key={`${item.jenis}-${item.id}`} className={selected ? 'table-warning' : ''}>
                                                                <td>
                                                                    <input
                                                                        type="checkbox"
                                                                        className="form-check-input"
                                                                        disabled={!isAvailable}
                                                                        checked={selected}
                                                                        onChange={() => toggleCartItem(item)}
                                                                        title={isAvailable ? 'Centang untuk pinjam' : 'Tidak tersedia'}
                                                                    />
                                                                </td>
                                                                <td>
                                                                    <span className={`badge ${item.jenis === 'minuta' ? 'bg-primary-subtle text-primary' : item.jenis === 'tap' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-danger-subtle text-danger'} text-uppercase`} style={{ fontSize: '0.68rem' }}>
                                                                        {item.jenis_label || item.jenis}
                                                                    </span>
                                                                </td>
                                                                <td className="fw-bold text-dark">{item.no_risalah || '-'}</td>
                                                                <td className="text-muted">{item.tgl_risalah || '-'}</td>
                                                                <td>
                                                                    <div className="fw-medium text-dark">{item.nama_pelelang || '-'}</div>
                                                                </td>
                                                                <td>
                                                                    <div className="text-muted text-truncate" style={{ maxWidth: '220px' }}>
                                                                        {item.pemohon_lelang || '-'}
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span className="badge bg-light text-dark border">
                                                                        <i className="fa-solid fa-box-archive me-1 text-muted"></i>
                                                                        {item.lemari || 'Lemari 01'} &bull; {item.box || 'Box 01'}
                                                                    </span>
                                                                </td>
                                                                <td>{getStatusBadge(item.status)}</td>
                                                            </tr>
                                                        );
                                                    })
                                                ) : (
                                                    <tr>
                                                        <td colSpan="8" className="text-center py-5 text-muted">
                                                            <i className="fa-solid fa-magnifying-glass fa-2x mb-2 text-muted d-block"></i>
                                                            Tidak ditemukan risalah yang sesuai dengan filter atau kata kunci pencarian.
                                                        </td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                    </div>

                                    {/* PAGINATION CONTROLS */}
                                    <div className="d-flex flex-column flex-sm-row align-items-center justify-content-between pt-3 mt-2 border-top gap-2">
                                        <small className="text-muted">
                                            Menampilkan halaman <strong>{catalogPage}</strong> dari <strong>{catalogTotalPages}</strong> (Total {catalogTotalItems?.toLocaleString('id-ID')} berkas)
                                        </small>

                                        <div className="btn-group btn-group-sm">
                                            <button
                                                className="btn btn-outline-secondary"
                                                disabled={catalogPage <= 1 || catalogLoading}
                                                onClick={() => fetchCatalog(1)}
                                            >
                                                &laquo; Awal
                                            </button>
                                            <button
                                                className="btn btn-outline-secondary"
                                                disabled={catalogPage <= 1 || catalogLoading}
                                                onClick={() => fetchCatalog(catalogPage - 1)}
                                            >
                                                &lsaquo; Prev
                                            </button>
                                            <span className="btn btn-light disabled px-3">Hal {catalogPage}</span>
                                            <button
                                                className="btn btn-outline-secondary"
                                                disabled={catalogPage >= catalogTotalPages || catalogLoading}
                                                onClick={() => fetchCatalog(catalogPage + 1)}
                                            >
                                                Next &rsaquo;
                                            </button>
                                            <button
                                                className="btn btn-outline-secondary"
                                                disabled={catalogPage >= catalogTotalPages || catalogLoading}
                                                onClick={() => fetchCatalog(catalogTotalPages)}
                                            >
                                                Akhir &raquo;
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* ========================================================= */}
                        {/* TAB 3: PEMINJAMAN BERKAS WORKFLOW VIEW                    */}
                        {/* ========================================================= */}
                        {activeTab === 'peminjaman' && (
                            <div className="card shadow-sm border-0 mb-4">
                                <div className="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                                    <div>
                                        <div className="fw-bold text-dark fs-6">
                                            <i className="fas fa-handshake-angle text-info me-2"></i> Tata Kelola Peminjaman Berkas Fisik
                                        </div>
                                        <small className="text-muted">
                                            Monitoring 5 tahap alur permohonan, serah terima fisik, hingga verifikasi pengembalian arsip
                                        </small>
                                    </div>
                                    <button className="btn btn-sm btn-outline-secondary" onClick={() => fetchPeminjaman(peminjamanPage)}>
                                        <i className="fa-solid fa-arrows-rotate me-1"></i> Refresh
                                    </button>
                                </div>

                                <div className="card-body p-3">
                                    {/* STATUS WORKFLOW TABS */}
                                    <div className="d-flex gap-1 overflow-x-auto pb-2 mb-3">
                                        {[
                                            { id: 'all', label: 'Semua Status', icon: 'fa-layer-group' },
                                            { id: 'Proses Peminjaman', label: '1. Proses Permohonan', icon: 'fa-file-signature' },
                                            { id: 'Menunggu Konfirmasi', label: '2. Menunggu Ambil Fisik', icon: 'fa-handshake' },
                                            { id: 'Sedang Dipinjam', label: '3. Sedang Dipinjam', icon: 'fa-box-archive' },
                                            { id: 'Proses Pengembalian', label: '4. Proses Kembali', icon: 'fa-arrow-rotate-left' },
                                            { id: 'Sudah Dikembalikan', label: '5. Selesai Kembali', icon: 'fa-check-double' },
                                        ].map(tab => (
                                            <button
                                                key={tab.id}
                                                className={`btn btn-sm text-nowrap ${peminjamanStatusFilter === tab.id ? 'btn-primary fw-bold' : 'btn-outline-secondary'}`}
                                                style={{ fontSize: '0.78rem' }}
                                                onClick={() => setPeminjamanStatusFilter(tab.id)}
                                            >
                                                <i className={`fa-solid ${tab.icon} me-1`}></i> {tab.label}
                                            </button>
                                        ))}
                                    </div>

                                    {/* SEARCH BAR */}
                                    <div className="row g-2 mb-3">
                                        <div className="col-12 col-md-6 col-lg-4">
                                            <div className="input-group input-group-sm">
                                                <input
                                                    type="text"
                                                    className="form-control"
                                                    placeholder="Cari nama peminjam / alasan..."
                                                    value={peminjamanSearch}
                                                    onChange={(e) => setPeminjamanSearch(e.target.value)}
                                                    onKeyDown={(e) => { if (e.key === 'Enter') fetchPeminjaman(1); }}
                                                />
                                                <button className="btn btn-primary" onClick={() => fetchPeminjaman(1)}>
                                                    <i className="fa-solid fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {/* PEMINJAMAN TABLE */}
                                    <div className="table-responsive">
                                        <table className="table table-hover align-middle mb-0" style={{ fontSize: '0.86rem' }}>
                                            <thead className="table-light">
                                                <tr>
                                                    <th>No. Pinjam</th>
                                                    <th>Peminjam</th>
                                                    <th>Tgl Pinjam</th>
                                                    <th>Batas Kembali</th>
                                                    <th>Total Berkas</th>
                                                    <th>Status Alur</th>
                                                    <th className="text-end">Aksi Alur Kerja</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {peminjamanLoading ? (
                                                    <tr>
                                                        <td colSpan="7" className="text-center py-5">
                                                            <div className="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                                            Memuat data peminjaman berkas...
                                                        </td>
                                                    </tr>
                                                ) : peminjamanList.length > 0 ? (
                                                    peminjamanList.map((loan) => (
                                                        <tr key={loan.id}>
                                                            <td className="fw-bold text-muted">#{loan.id}</td>
                                                            <td>
                                                                <div className="fw-semibold text-dark">{loan.nama_peminjam}</div>
                                                                <small className="text-muted text-truncate d-block" style={{ maxWidth: '240px' }}>
                                                                    {loan.alasan_peminjaman || loan.keperluan || 'Keperluan dinas'}
                                                                </small>
                                                            </td>
                                                            <td className="text-muted">{loan.tgl_peminjaman || '-'}</td>
                                                            <td className="text-muted">{loan.tgl_pengembalian || '-'}</td>
                                                            <td>
                                                                <button
                                                                    className="btn btn-sm btn-light border py-0 px-2 text-dark"
                                                                    style={{ fontSize: '0.75rem' }}
                                                                    onClick={() => setSelectedLoanDetail(loan)}
                                                                >
                                                                    <i className="fa-solid fa-list-check me-1 text-primary"></i>
                                                                    {loan.items_count || (loan.items ? loan.items.length : 1)} Berkas
                                                                </button>
                                                            </td>
                                                            <td>{getStatusBadge(loan.status)}</td>
                                                            <td className="text-end">
                                                                {/* STAGE 1: Proses Peminjaman -> Admin Approve */}
                                                                {loan.status === 'Proses Peminjaman' && user?.role === 'admin' && (
                                                                    <button
                                                                        className="btn btn-sm btn-primary py-1 px-2.5"
                                                                        style={{ fontSize: '0.75rem' }}
                                                                        onClick={() => handleApproveLoan(loan.id)}
                                                                    >
                                                                        <i className="fa-solid fa-check me-1"></i> Setujui
                                                                    </button>
                                                                )}

                                                                {/* STAGE 2: Menunggu Konfirmasi -> Peminjam/Admin Confirms Fisik Diambil */}
                                                                {loan.status === 'Menunggu Konfirmasi' && (
                                                                    <button
                                                                        className="btn btn-sm btn-success py-1 px-2.5"
                                                                        style={{ fontSize: '0.75rem' }}
                                                                        onClick={() => handleConfirmReceiveLoan(loan.id)}
                                                                    >
                                                                        <i className="fa-solid fa-handshake me-1"></i> Konfirmasi Ambil
                                                                    </button>
                                                                )}

                                                                {/* STAGE 3: Sedang Dipinjam -> Peminjam/Admin requests return */}
                                                                {loan.status === 'Sedang Dipinjam' && (
                                                                    <button
                                                                        className="btn btn-sm btn-warning text-dark py-1 px-2.5 fw-semibold"
                                                                        style={{ fontSize: '0.75rem' }}
                                                                        onClick={() => handleRequestReturnLoan(loan.id)}
                                                                    >
                                                                        <i className="fa-solid fa-arrow-rotate-left me-1"></i> Ajukan Kembali
                                                                    </button>
                                                                )}

                                                                {/* STAGE 4: Proses Pengembalian -> Admin verifies physical return */}
                                                                {loan.status === 'Proses Pengembalian' && user?.role === 'admin' && (
                                                                    <button
                                                                        className="btn btn-sm btn-success py-1 px-2.5"
                                                                        style={{ fontSize: '0.75rem' }}
                                                                        onClick={() => handleVerifyReturnLoan(loan.id)}
                                                                    >
                                                                        <i className="fa-solid fa-boxes-packing me-1"></i> Verifikasi Kembali
                                                                    </button>
                                                                )}

                                                                {/* STAGE 5: Sudah Dikembalikan */}
                                                                {loan.status === 'Sudah Dikembalikan' && (
                                                                    <span className="text-muted small">
                                                                        <i className="fa-solid fa-circle-check text-success me-1"></i> Selesai
                                                                    </span>
                                                                )}
                                                            </td>
                                                        </tr>
                                                    ))
                                                ) : (
                                                    <tr>
                                                        <td colSpan="7" className="text-center py-5 text-muted">
                                                            <i className="fa-solid fa-folder-open fa-2x mb-2 text-muted d-block"></i>
                                                            Tidak ada data peminjaman dengan filter yang dipilih.
                                                        </td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* ========================================================= */}
                        {/* TAB 4: VALIDASI RISALAH PENDING (KHUSUS ADMIN)             */}
                        {/* ========================================================= */}
                        {activeTab === 'validasi' && user?.role === 'admin' && (
                            <div className="card shadow-sm border-0 mb-4">
                                <div className="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                    <div>
                                        <div className="fw-bold text-dark fs-6">
                                            <i className="fas fa-file-circle-check text-success me-2"></i> Antrean Validasi Risalah Masuk
                                        </div>
                                        <small className="text-muted">
                                            Penetapan lokasi lemari & box arsip untuk risalah baru yang didaftarkan oleh Pejabat Lelang
                                        </small>
                                    </div>
                                    <button className="btn btn-sm btn-outline-secondary" onClick={fetchPending}>
                                        <i className="fa-solid fa-arrows-rotate me-1"></i> Refresh
                                    </button>
                                </div>

                                <div className="card-body p-3">
                                    <div className="table-responsive">
                                        <table className="table table-hover align-middle mb-0" style={{ fontSize: '0.86rem' }}>
                                            <thead className="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>No Risalah</th>
                                                    <th>Jenis</th>
                                                    <th>Tgl Risalah</th>
                                                    <th>Nama Pejabat Lelang</th>
                                                    <th>Pemohon Lelang</th>
                                                    <th className="text-end">Aksi Validasi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {pendingLoading ? (
                                                    <tr>
                                                        <td colSpan="7" className="text-center py-5">
                                                            <div className="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                                            Memeriksa antrean risalah pending...
                                                        </td>
                                                    </tr>
                                                ) : pendingList.length > 0 ? (
                                                    pendingList.map((item, idx) => (
                                                        <tr key={item.id}>
                                                            <td>{idx + 1}</td>
                                                            <td className="fw-bold text-dark">{item.no_risalah}</td>
                                                            <td>
                                                                <span className="badge bg-primary-subtle text-primary text-uppercase" style={{ fontSize: '0.68rem' }}>
                                                                    {item.jenis}
                                                                </span>
                                                            </td>
                                                            <td className="text-muted">{item.tgl_risalah || '-'}</td>
                                                            <td className="fw-semibold text-dark">{item.nama_pelelang}</td>
                                                            <td className="text-muted">{item.pemohon_lelang || '-'}</td>
                                                            <td className="text-end">
                                                                <button
                                                                    className="btn btn-sm btn-success py-1 px-2.5 me-1"
                                                                    style={{ fontSize: '0.75rem' }}
                                                                    onClick={() => setActiveValidateItem(item)}
                                                                >
                                                                    <i className="fa-solid fa-check-circle me-1"></i> Validasi & Arsipkan
                                                                </button>
                                                                <button
                                                                    className="btn btn-sm btn-outline-danger py-1 px-2"
                                                                    style={{ fontSize: '0.75rem' }}
                                                                    onClick={() => {
                                                                        setActiveRejectItem(item);
                                                                        setCatatanRevisi('');
                                                                    }}
                                                                >
                                                                    <i className="fa-solid fa-triangle-exclamation me-1"></i> Revisi
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    ))
                                                ) : (
                                                    <tr>
                                                        <td colSpan="7" className="text-center py-5 text-muted">
                                                            <i className="fa-solid fa-circle-check fa-2x mb-2 text-success d-block"></i>
                                                            Tidak ada risalah pending yang menunggu validasi.
                                                        </td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* ========================================================= */}
                        {/* TAB 5: PENDAFTARAN RISALAH BARU (PELELANG & ADMIN)        */}
                        {/* ========================================================= */}
                        {activeTab === 'pendaftaran' && (
                            <div className="card shadow-sm border-0 mb-4" style={{ maxWidth: '820px' }}>
                                <div className="card-header bg-white py-3 border-bottom">
                                    <div className="fw-bold text-dark fs-6">
                                        <i className="fas fa-file-circle-plus text-primary me-2"></i> Formulir Pendaftaran Risalah Lelang Baru
                                    </div>
                                    <small className="text-muted">
                                        Form pendaftaran berkas risalah hasil pelaksanaan lelang untuk diverifikasi dan diarsipkan oleh Seksi HI
                                    </small>
                                </div>

                                <div className="card-body p-4">
                                    <form onSubmit={handleCreateRisalah}>
                                        <div className="row g-3">
                                            <div className="col-12 col-md-6">
                                                <label className="form-label fw-semibold" style={{ fontSize: '0.82rem' }}>
                                                    Nomor Risalah Lelang <span className="text-danger">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-sm"
                                                    placeholder="Contoh: RL-105/KPKNL-PLG/2026"
                                                    required
                                                    value={newRisalah.no_risalah}
                                                    onChange={(e) => setNewRisalah({ ...newRisalah, no_risalah: e.target.value })}
                                                />
                                            </div>

                                            <div className="col-12 col-md-6">
                                                <label className="form-label fw-semibold" style={{ fontSize: '0.82rem' }}>
                                                    Jenis Risalah <span className="text-danger">*</span>
                                                </label>
                                                <select
                                                    className="form-select form-select-sm"
                                                    value={newRisalah.jenis}
                                                    onChange={(e) => setNewRisalah({ ...newRisalah, jenis: e.target.value })}
                                                >
                                                    <option value="minuta">Minuta (Lelang Laku)</option>
                                                    <option value="tap">TAP (Tidak Ada Penawaran)</option>
                                                    <option value="batal">Batal Lelang</option>
                                                </select>
                                            </div>

                                            <div className="col-12 col-md-6">
                                                <label className="form-label fw-semibold" style={{ fontSize: '0.82rem' }}>
                                                    Tanggal Risalah <span className="text-danger">*</span>
                                                </label>
                                                <input
                                                    type="date"
                                                    className="form-control form-control-sm"
                                                    required
                                                    value={newRisalah.tgl_risalah}
                                                    onChange={(e) => setNewRisalah({ ...newRisalah, tgl_risalah: e.target.value })}
                                                />
                                            </div>

                                            <div className="col-12 col-md-6">
                                                <label className="form-label fw-semibold" style={{ fontSize: '0.82rem' }}>
                                                    Nama Pejabat Lelang <span className="text-danger">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-sm"
                                                    required
                                                    value={newRisalah.nama_pelelang}
                                                    onChange={(e) => setNewRisalah({ ...newRisalah, nama_pelelang: e.target.value })}
                                                />
                                            </div>

                                            <div className="col-12">
                                                <label className="form-label fw-semibold" style={{ fontSize: '0.82rem' }}>
                                                    Pemohon Lelang / Debitur / Penjual <span className="text-danger">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-sm"
                                                    placeholder="Contoh: PT Bank Mandiri (Persero) Tbk / Kantor Bea Cukai"
                                                    required
                                                    value={newRisalah.pemohon_lelang}
                                                    onChange={(e) => setNewRisalah({ ...newRisalah, pemohon_lelang: e.target.value })}
                                                />
                                            </div>

                                            <div className="col-12 mt-4 pt-2 border-top d-flex align-items-center justify-content-end gap-2">
                                                <button
                                                    type="button"
                                                    className="btn btn-sm btn-outline-secondary"
                                                    onClick={() => setActiveTab('dashboard')}
                                                >
                                                    Batal
                                                </button>
                                                <button type="submit" className="btn btn-sm btn-primary fw-semibold px-3">
                                                    <i className="fa-solid fa-paper-plane me-1"></i> Simpan & Kirim ke Admin
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        )}

                        {/* ========================================================= */}
                        {/* TAB 6: REVISI RISALAH (PELELANG & ADMIN)                  */}
                        {/* ========================================================= */}
                        {activeTab === 'revisi' && (
                            <div className="card shadow-sm border-0 mb-4">
                                <div className="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                    <div>
                                        <div className="fw-bold text-dark fs-6">
                                            <i className="fas fa-file-pen text-danger me-2"></i> Daftar Risalah Memerlukan Revisi
                                        </div>
                                        <small className="text-muted">
                                            Berkas risalah yang dikembalikan oleh Admin Seksi HI dengan catatan koreksi
                                        </small>
                                    </div>
                                    <button className="btn btn-sm btn-outline-secondary" onClick={fetchRevisi}>
                                        <i className="fa-solid fa-arrows-rotate me-1"></i> Refresh
                                    </button>
                                </div>

                                <div className="card-body p-3">
                                    <div className="table-responsive">
                                        <table className="table table-hover align-middle mb-0" style={{ fontSize: '0.86rem' }}>
                                            <thead className="table-light">
                                                <tr>
                                                    <th>No Risalah</th>
                                                    <th>Jenis</th>
                                                    <th>Pejabat Lelang</th>
                                                    <th>Pemohon Lelang</th>
                                                    <th>Catatan Revisi dari Seksi HI</th>
                                                    <th className="text-end">Aksi Koreksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {revisiLoading ? (
                                                    <tr>
                                                        <td colSpan="6" className="text-center py-5">
                                                            <div className="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                                            Memeriksa daftar revisi...
                                                        </td>
                                                    </tr>
                                                ) : revisiList.length > 0 ? (
                                                    revisiList.map((item) => (
                                                        <tr key={item.id}>
                                                            <td className="fw-bold text-dark">{item.no_risalah}</td>
                                                            <td>
                                                                <span className="badge bg-danger-subtle text-danger text-uppercase" style={{ fontSize: '0.68rem' }}>
                                                                    {item.jenis}
                                                                </span>
                                                            </td>
                                                            <td>{item.nama_pelelang}</td>
                                                            <td>{item.pemohon_lelang || '-'}</td>
                                                            <td>
                                                                <div className="p-2 rounded bg-danger-subtle text-danger border border-danger-subtle" style={{ fontSize: '0.78rem' }}>
                                                                    <i className="fa-solid fa-triangle-exclamation me-1"></i>
                                                                    {item.catatan || 'Koreksi penulisan dan kelengkapan dokumen'}
                                                                </div>
                                                            </td>
                                                            <td className="text-end">
                                                                <button
                                                                    className="btn btn-sm btn-primary py-1 px-2.5"
                                                                    style={{ fontSize: '0.75rem' }}
                                                                    onClick={() => setActiveEditRevisi(item)}
                                                                >
                                                                    <i className="fa-solid fa-pen-to-square me-1"></i> Koreksi & Kirim Ulang
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    ))
                                                ) : (
                                                    <tr>
                                                        <td colSpan="6" className="text-center py-5 text-muted">
                                                            <i className="fa-solid fa-circle-check fa-2x mb-2 text-success d-block"></i>
                                                            Tidak ada risalah yang berada dalam status revisi.
                                                        </td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Footer */}
                    <footer className="mt-auto py-3 px-4 bg-white border-top text-center text-muted" style={{ fontSize: '0.75rem' }}>
                        <div className="d-flex flex-column flex-md-row align-items-center justify-content-between">
                            <div>
                                <strong>KPKNL Palembang</strong> &bull; Seksi Hukum dan Informasi &bull; Direktorat Jenderal Kekayaan Negara
                            </div>
                            <div className="text-muted mt-1 mt-md-0">
                                Sistem Informasi Peminjaman Risalah Lelang terintegrasi SSO &copy; 2026
                            </div>
                        </div>
                    </footer>
                </div>
            </div>

            {/* ============================================================= */}
            {/* MODALS SECTION                                                */}
            {/* ============================================================= */}

            {/* MODAL 1: AJUKAN MULTI-ITEM PEMINJAMAN */}
            {loanModalOpen && (
                <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}>
                    <div className="modal-dialog modal-dialog-centered modal-lg">
                        <div className="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div className="modal-header bg-primary text-white py-3 px-4">
                                <h6 className="modal-title fw-bold">
                                    <i className="fa-solid fa-paper-plane me-2 text-warning"></i> Formulir Pengajuan Peminjaman Risalah
                                </h6>
                                <button type="button" className="btn-close btn-close-white" onClick={() => setLoanModalOpen(false)}></button>
                            </div>
                            <form onSubmit={handleCreateLoan}>
                                <div className="modal-body p-4">
                                    <div className="alert alert-primary py-2 px-3 mb-3 d-flex align-items-center gap-2" style={{ fontSize: '0.82rem' }}>
                                        <i className="fa-solid fa-circle-info fs-5 text-primary"></i>
                                        <div>
                                            Anda sedang mengajukan peminjaman sebanyak <strong>{selectedCart.length} berkas risalah lelang</strong>. Pastikan fisik berkas dijaga keutuhannya selama masa peminjaman.
                                        </div>
                                    </div>

                                    {/* List of chosen items */}
                                    <div className="mb-3">
                                        <label className="form-label fw-bold text-dark" style={{ fontSize: '0.8rem' }}>
                                            Berkas Risalah yang Dipilih:
                                        </label>
                                        <div className="border rounded-3 p-2 bg-light overflow-y-auto" style={{ maxHeight: '130px' }}>
                                            {selectedCart.map((i, idx) => (
                                                <div key={idx} className="d-flex align-items-center justify-content-between py-1 border-bottom last:border-0" style={{ fontSize: '0.78rem' }}>
                                                    <div>
                                                        <strong className="text-dark me-2">{i.no_risalah}</strong>
                                                        <span className="badge bg-secondary-subtle text-secondary text-uppercase me-2">{i.jenis}</span>
                                                        <span className="text-muted">Pelelang: {i.nama_pelelang}</span>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        className="btn btn-sm btn-link text-danger p-0"
                                                        onClick={() => toggleCartItem(i)}
                                                    >
                                                        <i className="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="row g-3">
                                        <div className="col-12 col-md-4">
                                            <label className="form-label fw-semibold" style={{ fontSize: '0.8rem' }}>Nama Peminjam</label>
                                            <input
                                                type="text"
                                                className="form-control form-control-sm"
                                                required
                                                value={loanForm.nama_peminjam}
                                                onChange={(e) => setLoanForm({ ...loanForm, nama_peminjam: e.target.value })}
                                            />
                                        </div>
                                        <div className="col-12 col-md-4">
                                            <label className="form-label fw-semibold" style={{ fontSize: '0.8rem' }}>Tgl Peminjaman</label>
                                            <input
                                                type="date"
                                                className="form-control form-control-sm"
                                                required
                                                value={loanForm.tgl_peminjaman}
                                                onChange={(e) => setLoanForm({ ...loanForm, tgl_peminjaman: e.target.value })}
                                            />
                                        </div>
                                        <div className="col-12 col-md-4">
                                            <label className="form-label fw-semibold" style={{ fontSize: '0.8rem' }}>Rencana Pengembalian</label>
                                            <input
                                                type="date"
                                                className="form-control form-control-sm"
                                                required
                                                value={loanForm.tgl_pengembalian}
                                                onChange={(e) => setLoanForm({ ...loanForm, tgl_pengembalian: e.target.value })}
                                            />
                                        </div>
                                        <div className="col-12">
                                            <label className="form-label fw-semibold" style={{ fontSize: '0.8rem' }}>
                                                Keperluan / Alasan Peminjaman <span className="text-danger">*</span>
                                            </label>
                                            <textarea
                                                className="form-control form-control-sm"
                                                rows="2"
                                                placeholder="Contoh: Pemeriksaan berkas perkara sengketa lelang di Pengadilan Tata Usaha Negara (PTUN)..."
                                                required
                                                value={loanForm.keperluan}
                                                onChange={(e) => setLoanForm({ ...loanForm, keperluan: e.target.value })}
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div className="modal-footer py-2.5 px-4 border-top">
                                    <button type="button" className="btn btn-sm btn-outline-secondary" onClick={() => setLoanModalOpen(false)}>
                                        Batal
                                    </button>
                                    <button type="submit" className="btn btn-sm btn-primary fw-semibold px-3">
                                        <i className="fa-solid fa-paper-plane me-1"></i> Kirim Permohonan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* MODAL 2: DETAIL PEMINJAMAN BERKAS */}
            {selectedLoanDetail && (
                <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}>
                    <div className="modal-dialog modal-dialog-centered modal-lg">
                        <div className="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div className="modal-header bg-dark text-white py-3 px-4">
                                <h6 className="modal-title fw-bold">
                                    <i className="fa-solid fa-file-lines me-2 text-warning"></i> Detail Peminjaman #{selectedLoanDetail.id}
                                </h6>
                                <button type="button" className="btn-close btn-close-white" onClick={() => setSelectedLoanDetail(null)}></button>
                            </div>
                            <div className="modal-body p-4">
                                <div className="row g-2 mb-3 bg-light p-3 rounded-3 border" style={{ fontSize: '0.82rem' }}>
                                    <div className="col-6 col-md-3">
                                        <span className="text-muted d-block">Peminjam:</span>
                                        <strong>{selectedLoanDetail.nama_peminjam}</strong>
                                    </div>
                                    <div className="col-6 col-md-3">
                                        <span className="text-muted d-block">Tanggal Pinjam:</span>
                                        <strong>{selectedLoanDetail.tgl_peminjaman || '-'}</strong>
                                    </div>
                                    <div className="col-6 col-md-3">
                                        <span className="text-muted d-block">Batas Kembali:</span>
                                        <strong>{selectedLoanDetail.tgl_pengembalian || '-'}</strong>
                                    </div>
                                    <div className="col-6 col-md-3">
                                        <span className="text-muted d-block">Status Alur:</span>
                                        <div>{getStatusBadge(selectedLoanDetail.status)}</div>
                                    </div>
                                    <div className="col-12 mt-2 pt-2 border-top">
                                        <span className="text-muted d-block">Alasan / Keperluan:</span>
                                        <div>{selectedLoanDetail.alasan_peminjaman || selectedLoanDetail.keperluan || 'Keperluan kedinasan'}</div>
                                    </div>
                                </div>

                                <h6 className="fw-bold text-dark mb-2" style={{ fontSize: '0.85rem' }}>
                                    Daftar Berkas Fisik Terkait:
                                </h6>
                                <div className="table-responsive border rounded-3">
                                    <table className="table table-sm table-hover mb-0" style={{ fontSize: '0.82rem' }}>
                                        <thead className="table-light">
                                            <tr>
                                                <th>Jenis</th>
                                                <th>Nomor Risalah</th>
                                                <th>Tanggal Risalah</th>
                                                <th>Pejabat Lelang</th>
                                                <th>Lokasi Simpan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {selectedLoanDetail.items && selectedLoanDetail.items.length > 0 ? (
                                                selectedLoanDetail.items.map((it, idx) => (
                                                    <tr key={idx}>
                                                        <td>
                                                            <span className="badge bg-secondary-subtle text-secondary text-uppercase">{it.jenis_risalah}</span>
                                                        </td>
                                                        <td className="fw-bold">{it.risalah?.no_risalah || `#${it.risalah_id}`}</td>
                                                        <td>{it.risalah?.tgl_risalah || '-'}</td>
                                                        <td>{it.risalah?.nama_pelelang || '-'}</td>
                                                        <td>{it.risalah?.lemari || 'Lemari 01'} - {it.risalah?.box || 'Box 01'}</td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td><span className="badge bg-primary-subtle text-primary">RISALAH</span></td>
                                                    <td className="fw-bold">{selectedLoanDetail.no_risalah || '-'}</td>
                                                    <td>{selectedLoanDetail.tgl_risalah || '-'}</td>
                                                    <td>{selectedLoanDetail.nama_pelelang || '-'}</td>
                                                    <td>{selectedLoanDetail.lemari || 'Lemari 01'} - {selectedLoanDetail.box || 'Box 01'}</td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div className="modal-footer py-2.5 px-4 border-top">
                                <button type="button" className="btn btn-sm btn-secondary" onClick={() => setSelectedLoanDetail(null)}>
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* MODAL 3: VALIDASI & ASSIGN LEMARI/BOX (ADMIN) */}
            {activeValidateItem && (
                <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div className="modal-header bg-success text-white py-3 px-4">
                                <h6 className="modal-title fw-bold">
                                    <i className="fa-solid fa-check-double me-2"></i> Validasi Risalah #{activeValidateItem.no_risalah}
                                </h6>
                                <button type="button" className="btn-close btn-close-white" onClick={() => setActiveValidateItem(null)}></button>
                            </div>
                            <form onSubmit={handleValidatePending}>
                                <div className="modal-body p-4">
                                    <p className="text-muted mb-3" style={{ fontSize: '0.82rem' }}>
                                        Tentukan lokasi fisik penyimpanan arsip di ruang Seksi Hukum dan Informasi KPKNL Palembang:
                                    </p>

                                    <div className="mb-3">
                                        <label className="form-label fw-semibold" style={{ fontSize: '0.8rem' }}>Lokasi Lemari Arsip</label>
                                        <input
                                            type="text"
                                            className="form-control form-control-sm"
                                            required
                                            value={lemariInput}
                                            onChange={(e) => setLemariInput(e.target.value)}
                                        />
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label fw-semibold" style={{ fontSize: '0.8rem' }}>Nomor Box Arsip</label>
                                        <input
                                            type="text"
                                            className="form-control form-control-sm"
                                            required
                                            value={boxInput}
                                            onChange={(e) => setBoxInput(e.target.value)}
                                        />
                                    </div>
                                </div>
                                <div className="modal-footer py-2.5 px-4 border-top">
                                    <button type="button" className="btn btn-sm btn-outline-secondary" onClick={() => setActiveValidateItem(null)}>
                                        Batal
                                    </button>
                                    <button type="submit" className="btn btn-sm btn-success fw-semibold">
                                        <i className="fa-solid fa-vault me-1"></i> Simpan ke Katalog Utama
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* MODAL 4: CATATAN REVISI (ADMIN) */}
            {activeRejectItem && (
                <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div className="modal-header bg-danger text-white py-3 px-4">
                                <h6 className="modal-title fw-bold">
                                    <i className="fa-solid fa-triangle-exclamation me-2"></i> Minta Revisi Risalah #{activeRejectItem.no_risalah}
                                </h6>
                                <button type="button" className="btn-close btn-close-white" onClick={() => setActiveRejectItem(null)}></button>
                            </div>
                            <form onSubmit={handleRejectPending}>
                                <div className="modal-body p-4">
                                    <label className="form-label fw-semibold" style={{ fontSize: '0.8rem' }}>
                                        Catatan Revisi / Alasan Pengembalian <span className="text-danger">*</span>
                                    </label>
                                    <textarea
                                        className="form-control form-control-sm"
                                        rows="3"
                                        placeholder="Jelaskan bagian yang perlu diperbaiki oleh Pejabat Lelang..."
                                        required
                                        value={catatanRevisi}
                                        onChange={(e) => setCatatanRevisi(e.target.value)}
                                    ></textarea>
                                </div>
                                <div className="modal-footer py-2.5 px-4 border-top">
                                    <button type="button" className="btn btn-sm btn-outline-secondary" onClick={() => setActiveRejectItem(null)}>
                                        Batal
                                    </button>
                                    <button type="submit" className="btn btn-sm btn-danger fw-semibold">
                                        <i className="fa-solid fa-paper-plane me-1"></i> Kirim Catatan Revisi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* MODAL 5: EDIT & RESUBMIT REVISI (PELELANG) */}
            {activeEditRevisi && (
                <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div className="modal-header bg-primary text-white py-3 px-4">
                                <h6 className="modal-title fw-bold">
                                    <i className="fa-solid fa-pen-to-square me-2"></i> Perbaiki Risalah #{activeEditRevisi.no_risalah}
                                </h6>
                                <button type="button" className="btn-close btn-close-white" onClick={() => setActiveEditRevisi(null)}></button>
                            </div>
                            <form onSubmit={handleResubmitRevisi}>
                                <div className="modal-body p-4">
                                    <div className="alert alert-danger py-2 px-3 mb-3" style={{ fontSize: '0.78rem' }}>
                                        <strong>Catatan Admin:</strong> {activeEditRevisi.catatan || 'Koreksi data risalah.'}
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label fw-semibold" style={{ fontSize: '0.8rem' }}>Nomor Risalah</label>
                                        <input
                                            type="text"
                                            className="form-control form-control-sm"
                                            required
                                            value={activeEditRevisi.no_risalah}
                                            onChange={(e) => setActiveEditRevisi({ ...activeEditRevisi, no_risalah: e.target.value })}
                                        />
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label fw-semibold" style={{ fontSize: '0.8rem' }}>Tanggal Risalah</label>
                                        <input
                                            type="date"
                                            className="form-control form-control-sm"
                                            required
                                            value={activeEditRevisi.tgl_risalah}
                                            onChange={(e) => setActiveEditRevisi({ ...activeEditRevisi, tgl_risalah: e.target.value })}
                                        />
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label fw-semibold" style={{ fontSize: '0.8rem' }}>Pemohon Lelang</label>
                                        <input
                                            type="text"
                                            className="form-control form-control-sm"
                                            required
                                            value={activeEditRevisi.pemohon_lelang}
                                            onChange={(e) => setActiveEditRevisi({ ...activeEditRevisi, pemohon_lelang: e.target.value })}
                                        />
                                    </div>
                                </div>
                                <div className="modal-footer py-2.5 px-4 border-top">
                                    <button type="button" className="btn btn-sm btn-outline-secondary" onClick={() => setActiveEditRevisi(null)}>
                                        Batal
                                    </button>
                                    <button type="submit" className="btn btn-sm btn-primary fw-semibold">
                                        <i className="fa-solid fa-paper-plane me-1"></i> Kirim Ulang ke Admin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

// Mount React application into #root container
const mountApp = () => {
    const rootEl = document.getElementById('root');
    if (rootEl) {
        const root = createRoot(rootEl);
        root.render(<App />);
    } else {
        console.error('Root container #root not found in document');
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountApp);
} else {
    mountApp();
}
