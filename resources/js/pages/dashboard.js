import { api, rupiah, angka, persen, tanggal, ringkas } from '../lib/util';
import { grafik, WARNA, sumbuRupiah, tooltipRupiah, tooltipDonat } from '../lib/chart';

export default (url) => {
    const g = grafik();
    return {
        d: null,
        loading: true,
        rupiah,
        angka,
        ringkas,

        async init() {
            await this.muat();
        },

        destroy() {
            g.hancurkan();
        },

        async muat() {
            this.loading = true;
            try {
                this.d = await api(url);
                this.$nextTick(() => this.gambar());
            } catch (e) {
                this.$store.toast.push(e.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        delta(a, b) {
            return persen(a, b);
        },

        gambar() {
            const t = this.d.tren;
            g.gambar('tren', this.$refs.tren, {
                type: 'bar',
                data: {
                    labels: t.map((r) => tanggal(r.tanggal).replace(/ \d{4}$/, '')),
                    datasets: [
                        { type: 'line', label: 'Laba kotor', data: t.map((r) => r.laba_kotor), borderColor: WARNA.hijau, backgroundColor: WARNA.hijau, tension: 0.35, pointRadius: 0, borderWidth: 2, order: 0 },
                        { label: 'Penjualan bersih', data: t.map((r) => r.bersih), backgroundColor: WARNA.brand, borderRadius: 6, maxBarThickness: 22, order: 1 },
                    ],
                },
                options: { scales: { y: sumbuRupiah, x: { grid: { display: false } } }, plugins: { tooltip: tooltipRupiah } },
            });

            const jam = this.d.jam_hari_ini;
            const mulai = Math.max(0, jam.findIndex((j) => j.transaksi > 0) - 1);
            const akhir = 23 - Math.max(0, [...jam].reverse().findIndex((j) => j.transaksi > 0) - 1);
            const rentang = jam.map((j, i) => ({ ...j, i })).filter((j) => (jam.some((x) => x.transaksi) ? j.i >= mulai && j.i <= akhir : j.i >= 8 && j.i <= 22));
            g.gambar('jam', this.$refs.jam, {
                type: 'bar',
                data: {
                    labels: rentang.map((j) => String(j.i).padStart(2, '0') + ':00'),
                    datasets: [{ label: 'Transaksi', satuan: 'angka', data: rentang.map((j) => j.transaksi), backgroundColor: WARNA.ink, borderRadius: 5, maxBarThickness: 18 }],
                },
                options: { plugins: { legend: { display: false }, tooltip: tooltipRupiah }, scales: { y: { ticks: { precision: 0 }, border: { display: false } }, x: { grid: { display: false } } } },
            });

            const m = this.d.metode_bulan_ini;
            g.gambar('metode', this.$refs.metode, {
                type: 'doughnut',
                data: { labels: m.map((x) => x.metode), datasets: [{ data: m.map((x) => x.total), backgroundColor: WARNA.seri, borderWidth: 0 }] },
                options: { cutout: '68%', plugins: { legend: { position: 'bottom' }, tooltip: tooltipDonat } },
            });
        },
    };
};
