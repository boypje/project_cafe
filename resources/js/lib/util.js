// Utilitas bersama: format angka/tanggal, request JSON, unduh CSV.

const nf = new Intl.NumberFormat('id-ID');

export const rupiah = (n) => 'Rp ' + nf.format(Math.round(Number(n) || 0));
export const angka = (n, digit = 0) =>
    new Intl.NumberFormat('id-ID', { maximumFractionDigits: digit }).format(Number(n) || 0);

/** Format ringkas untuk sumbu grafik: 1,2 jt / 850 rb */
export const ringkas = (n) => {
    n = Number(n) || 0;
    const a = Math.abs(n);
    if (a >= 1e9) return angka(n / 1e9, 1) + ' M';
    if (a >= 1e6) return angka(n / 1e6, 1) + ' jt';
    if (a >= 1e3) return angka(n / 1e3, 0) + ' rb';
    return angka(n);
};

const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const BULAN_PANJANG = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
export const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

export const tanggal = (s, panjang = false) => {
    if (!s) return '-';
    const [y, m, d] = s.slice(0, 10).split('-').map(Number);
    return `${d} ${(panjang ? BULAN_PANJANG : BULAN)[m - 1]} ${y}`;
};
export const bulan = (s) => {
    const [y, m] = s.split('-').map(Number);
    return `${BULAN[m - 1]} ${y}`;
};

/** Tanggal lokal (bukan UTC) dalam format Y-m-d. */
export const ymd = (d = new Date()) => {
    const p = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
};

export const persen = (sekarang, lalu) => {
    if (!lalu) return sekarang ? null : 0;
    return ((sekarang - lalu) / Math.abs(lalu)) * 100;
};

export const debounce = (fn, ms = 250) => {
    let t;
    return (...a) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...a), ms);
    };
};

export class ApiError extends Error {
    constructor(message, status, errors = {}) {
        super(message);
        this.status = status;
        this.errors = errors;
    }
}

/**
 * fetch JSON dengan CSRF. body berupa object (dikirim JSON) atau FormData.
 * Melempar ApiError berisi pesan yang siap ditampilkan.
 */
export async function api(url, { method = 'GET', body, params } = {}) {
    if (params) {
        const q = new URLSearchParams(Object.entries(params).filter(([, v]) => v !== '' && v != null));
        url += (url.includes('?') ? '&' : '?') + q;
    }
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
    };
    let payload = body;
    if (body && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify(body);
    }
    if (body instanceof FormData && method !== 'POST') {
        body.append('_method', method);
        method = 'POST';
    }

    let res;
    try {
        res = await fetch(url, { method, headers, body: payload, credentials: 'same-origin' });
    } catch (e) {
        throw new ApiError('Tidak dapat terhubung ke server. Periksa koneksi.', 0);
    }

    if (res.status === 401 || res.status === 419) {
        window.Alpine?.store('toast').push('Sesi berakhir, silakan masuk kembali.', 'error');
        setTimeout(() => (window.location.href = '/login'), 1200);
        throw new ApiError('Sesi berakhir.', res.status);
    }

    const data = res.status === 204 ? null : await res.json().catch(() => null);
    if (!res.ok) {
        const errors = data?.errors || {};
        const first = Object.values(errors)[0]?.[0];
        throw new ApiError(first || data?.message || `Terjadi kesalahan (${res.status}).`, res.status, errors);
    }
    return data;
}

/** Unduh array objek sebagai CSV (Excel-friendly, pemisah titik koma). */
export function unduhCsv(nama, kolom, baris) {
    const esc = (v) => {
        const s = v == null ? '' : String(v);
        return /[;"\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
    };
    const isi = [kolom.map((k) => esc(k.label)).join(';')]
        .concat(baris.map((r) => kolom.map((k) => esc(typeof k.value === 'function' ? k.value(r) : r[k.key])).join(';')))
        .join('\r\n');
    const blob = new Blob(['﻿' + isi], { type: 'text/csv;charset=utf-8' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = nama.endsWith('.csv') ? nama : nama + '.csv';
    a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 1000);
}

/** Cetak halaman (mis. nota) lewat iframe tersembunyi tanpa meninggalkan halaman. */
export function cetak(url) {
    document.getElementById('frame-cetak')?.remove();
    const f = document.createElement('iframe');
    f.id = 'frame-cetak';
    f.setAttribute('aria-hidden', 'true');
    f.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
    f.src = url;
    f.onload = () => {
        try {
            f.contentWindow.focus();
            f.contentWindow.print();
        } catch (e) {
            window.open(url, '_blank');
        }
    };
    document.body.appendChild(f);
}
