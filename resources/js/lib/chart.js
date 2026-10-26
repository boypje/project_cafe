import Chart from 'chart.js/auto';
import { rupiah, ringkas, angka } from './util';

export const WARNA = {
    brand: '#9A5723',
    brandSoft: 'rgba(154,87,35,.12)',
    ink: '#2A1F19',
    inkSoft: 'rgba(42,31,25,.08)',
    hijau: '#2F7D4F',
    merah: '#C2410C',
    abu: '#BFAE9F',
    seri: ['#9A5723', '#2A1F19', '#CC8746', '#2F7D4F', '#7D6656', '#0E7490', '#B45309', '#9F1239', '#4D7C0F', '#6D28D9'],
};

Chart.defaults.font.family = '"Plus Jakarta Sans", system-ui, sans-serif';
Chart.defaults.font.size = 12;
Chart.defaults.color = '#7D6656';
Chart.defaults.borderColor = 'rgba(42,31,25,.06)';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.boxWidth = 8;
Chart.defaults.plugins.tooltip.backgroundColor = '#2A1F19';
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 10;
Chart.defaults.maintainAspectRatio = false;

/**
 * Pengelola grafik untuk komponen Alpine. Instance Chart disimpan di luar
 * proxy reaktif Alpine (Chart.js tidak cocok dengan objek yang di-proxy).
 */
export function grafik() {
    const daftar = new Map();
    return {
        gambar(id, canvas, config) {
            if (!canvas) return;
            daftar.get(id)?.destroy();
            daftar.set(id, new Chart(canvas, config));
        },
        hancurkan() {
            daftar.forEach((c) => c.destroy());
            daftar.clear();
        },
    };
}

export const sumbuRupiah = {
    ticks: { callback: (v) => ringkas(v) },
    grid: { color: 'rgba(42,31,25,.05)' },
    border: { display: false },
};

export const tooltipRupiah = {
    callbacks: {
        label: (c) => `${c.dataset.label ? c.dataset.label + ': ' : ''}${c.dataset.satuan === 'angka' ? angka(c.parsed.y ?? c.parsed) : rupiah(c.parsed.y ?? c.parsed)}`,
    },
};

export const tooltipDonat = {
    callbacks: {
        label: (c) => {
            const total = c.dataset.data.reduce((a, b) => a + b, 0) || 1;
            return ` ${c.label}: ${rupiah(c.parsed)} (${angka((c.parsed / total) * 100, 1)}%)`;
        },
    },
};
