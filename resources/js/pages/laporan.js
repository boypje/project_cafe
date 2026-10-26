import { api, rupiah, angka, persen, tanggal, bulan, ymd, unduhCsv, ringkas, HARI, potong, jumlahHalaman } from '../lib/util';
import { grafik, WARNA, sumbuRupiah, tooltipRupiah, tooltipDonat } from '../lib/chart';

const PRESET = {
    hari: () => [new Date(), new Date()],
    '7': () => [geser(-6), new Date()],
    '30': () => [geser(-29), new Date()],
    bulan: () => {
        const n = new Date();
        return [new Date(n.getFullYear(), n.getMonth(), 1), n];
    },
    bulanLalu: () => {
        const n = new Date();
        return [new Date(n.getFullYear(), n.getMonth() - 1, 1), new Date(n.getFullYear(), n.getMonth(), 0)];
    },
    kuartal: () => [geser(-89), new Date()],
    tahun: () => {
        const n = new Date();
        return [new Date(n.getFullYear(), 0, 1), n];
    },
};

function geser(hari) {
    const d = new Date();
    d.setDate(d.getDate() + hari);
    return d;
}

export default (url) => {
    const g = grafik();
    return {
        awal: ymd(PRESET.bulan()[0]),
        akhir: ymd(),
        preset: 'bulan',
        tab: 'ringkasan',
        d: null,
        loading: true,
        urutProduk: 'omzet',
        hal: { harian: 1, produk: 1, stok: 1 },
        perHal: 15,

        halAktif(list, kunci) {
            return Math.min(this.hal[kunci], jumlahHalaman(list, this.perHal));
        },
        bagian(list, kunci) {
            return potong(list, this.halAktif(list, kunci), this.perHal);
        },
        pindah(n, list, kunci) {
            this.hal[kunci] = Math.min(Math.max(n, 1), jumlahHalaman(list, this.perHal));
        },
        rupiah,
        angka,
        tanggal,
        bulan,
        ringkas,
        HARI,

        init() {
            const h = new URLSearchParams(location.search);
            if (h.get('awal') && h.get('akhir')) {
                this.awal = h.get('awal');
                this.akhir = h.get('akhir');
                this.preset = '';
            }
            this.muat();
            this.$watch('tab', () => this.$nextTick(() => this.gambar()));
            this.$watch('urutProduk', () => (this.hal.produk = 1));
        },

        destroy() {
            g.hancurkan();
        },

        pilihPreset(p) {
            this.preset = p;
            const [a, b] = PRESET[p]();
            this.awal = ymd(a);
            this.akhir = ymd(b);
            this.muat();
        },

        ubahTanggal() {
            this.preset = '';
            if (this.awal > this.akhir) [this.awal, this.akhir] = [this.akhir, this.awal];
            this.muat();
        },

        async muat() {
            this.loading = true;
            this.hal = { harian: 1, produk: 1, stok: 1 };
            history.replaceState(history.state, '', `?awal=${this.awal}&akhir=${this.akhir}`);
            try {
                this.d = await api(url, { params: { awal: this.awal, akhir: this.akhir } });
                this.$nextTick(() => this.gambar());
            } catch (e) {
                this.$store.toast.push(e.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        get r() {
            return this.d?.ringkasan || {};
        },
        get rl() {
            return this.d?.ringkasan_lalu || {};
        },
        delta(k) {
            return persen(this.r[k], this.rl[k]);
        },

        get kpi() {
            return [
                { k: 'bersih', label: 'Penjualan bersih', fmt: rupiah, info: 'Setelah diskon, tanpa pajak' },
                { k: 'laba_kotor', label: 'Laba kotor', fmt: rupiah, info: `Margin ${angka(this.r.margin, 1)}%` },
                { k: 'laba_bersih', label: 'Laba bersih', fmt: rupiah, info: 'Laba kotor − pengeluaran' },
                { k: 'transaksi', label: 'Transaksi', fmt: angka, info: `${angka(this.r.item_per_trx, 1)} item / transaksi` },
                { k: 'aov', label: 'Rata-rata transaksi', fmt: rupiah, info: 'Average order value' },
                { k: 'pengunjung', label: 'Pengunjung', fmt: angka, info: `${rupiah(this.r.per_pengunjung)} / orang` },
                { k: 'pengeluaran', label: 'Pengeluaran', fmt: rupiah, info: 'Operasional & belanja', kebalik: true },
                { k: 'diskon', label: 'Diskon diberikan', fmt: rupiah, info: `${angka((this.r.diskon / (this.r.kotor || 1)) * 100, 1)}% dari kotor`, kebalik: true },
            ];
        },

        get produkUrut() {
            const p = [...(this.d?.produk.produk || [])];
            return p.sort((a, b) => b[this.urutProduk] - a[this.urutProduk]);
        },

        get kelasAbc() {
            const p = this.d?.produk.produk || [];
            return ['A', 'B', 'C'].map((k) => {
                const x = p.filter((r) => r.kelas === k);
                return { kelas: k, jumlah: x.length, omzet: x.reduce((s, r) => s + r.omzet, 0) };
            });
        },

        get hariTerbaik() {
            const h = (this.d?.harian || []).filter((r) => r.transaksi);
            if (!h.length) return null;
            return h.reduce((a, b) => (b.bersih > a.bersih ? b : a));
        },

        get jamSibuk() {
            const j = this.d?.pola.jam || [];
            let best = -1;
            j.forEach((x, i) => {
                if (x.transaksi && (best < 0 || x.transaksi > j[best].transaksi)) best = i;
            });
            return best < 0 ? null : best;
        },

        get maxHeat() {
            return Math.max(1, ...(this.d?.pola.matriks || []).flat());
        },

        heat(v) {
            if (!v) return 'background:#F7F4F1';
            const a = 0.15 + (v / this.maxHeat) * 0.85;
            return `background:rgba(147,50,42,${a.toFixed(2)});color:${a > 0.55 ? '#fff' : '#2A1F19'}`;
        },

        gambar() {
            if (!this.d) return;
            const h = this.d.harian;
            const label = h.map((r) => tanggal(r.tanggal).replace(/ \d{4}$/, ''));

            if (this.tab === 'ringkasan') {
                g.gambar('tren', this.$refs.tren, {
                    type: 'bar',
                    data: {
                        labels: label,
                        datasets: [
                            { type: 'line', label: 'Laba bersih', data: h.map((r) => r.laba_bersih), borderColor: WARNA.hijau, backgroundColor: WARNA.hijau, tension: 0.35, pointRadius: h.length > 45 ? 0 : 2, borderWidth: 2, order: 0 },
                            { label: 'Penjualan bersih', data: h.map((r) => r.bersih), backgroundColor: WARNA.brand, borderRadius: 5, maxBarThickness: 26, order: 1 },
                            { label: 'Pengeluaran', data: h.map((r) => r.pengeluaran), backgroundColor: WARNA.abu, borderRadius: 5, maxBarThickness: 26, order: 2 },
                        ],
                    },
                    options: { interaction: { mode: 'index', intersect: false }, scales: { y: sumbuRupiah, x: { grid: { display: false } } }, plugins: { tooltip: tooltipRupiah } },
                });
                const m = this.d.metode;
                g.gambar('metode', this.$refs.metode, {
                    type: 'doughnut',
                    data: { labels: m.map((x) => x.metode), datasets: [{ data: m.map((x) => x.total), backgroundColor: WARNA.seri, borderWidth: 0 }] },
                    options: { cutout: '68%', plugins: { legend: { position: 'bottom' }, tooltip: tooltipDonat } },
                });
                const k = this.d.produk.kategori;
                g.gambar('kategori', this.$refs.kategori, {
                    type: 'doughnut',
                    data: { labels: k.map((x) => x.kategori), datasets: [{ data: k.map((x) => x.omzet), backgroundColor: WARNA.seri, borderWidth: 0 }] },
                    options: { cutout: '68%', plugins: { legend: { position: 'bottom' }, tooltip: tooltipDonat } },
                });
            }

            if (this.tab === 'penjualan') {
                const b = this.d.bulanan;
                const pakaiBulan = b.length > 1;
                g.gambar('trx', this.$refs.trx, {
                    type: 'line',
                    data: {
                        labels: pakaiBulan ? b.map((r) => bulan(r.bulan)) : label,
                        datasets: [
                            { label: 'Transaksi', satuan: 'angka', data: (pakaiBulan ? b : h).map((r) => r.transaksi), borderColor: WARNA.ink, backgroundColor: WARNA.inkSoft, fill: true, tension: 0.35, pointRadius: 2, yAxisID: 'y' },
                            { label: 'Pengunjung', satuan: 'angka', data: (pakaiBulan ? b : h).map((r) => r.pengunjung), borderColor: WARNA.brand, borderDash: [4, 4], tension: 0.35, pointRadius: 0, yAxisID: 'y' },
                        ],
                    },
                    options: { interaction: { mode: 'index', intersect: false }, scales: { y: { ticks: { precision: 0 }, border: { display: false } }, x: { grid: { display: false } } }, plugins: { tooltip: tooltipRupiah } },
                });
            }

            if (this.tab === 'produk') {
                const top = this.produkUrut.slice(0, 12);
                g.gambar('top', this.$refs.top, {
                    type: 'bar',
                    data: {
                        labels: top.map((p) => p.nama),
                        datasets: [
                            { label: 'Omzet', data: top.map((p) => p.omzet), backgroundColor: WARNA.brand, borderRadius: 5 },
                            { label: 'Laba', data: top.map((p) => p.laba), backgroundColor: WARNA.hijau, borderRadius: 5 },
                        ],
                    },
                    options: { indexAxis: 'y', scales: { x: sumbuRupiah, y: { grid: { display: false } } }, plugins: { tooltip: tooltipRupiah } },
                });
            }

            if (this.tab === 'pola') {
                const j = this.d.pola.jam;
                g.gambar('jam', this.$refs.jam, {
                    type: 'bar',
                    data: {
                        labels: j.map((_, i) => String(i).padStart(2, '0')),
                        datasets: [{ label: 'Penjualan', data: j.map((x) => x.bersih), backgroundColor: WARNA.brand, borderRadius: 4 }],
                    },
                    options: { plugins: { legend: { display: false }, tooltip: tooltipRupiah }, scales: { y: sumbuRupiah, x: { grid: { display: false } } } },
                });
                const hr = this.d.pola.hari;
                g.gambar('hari', this.$refs.hari, {
                    type: 'bar',
                    data: {
                        labels: HARI,
                        datasets: [{ label: 'Rata-rata penjualan per hari', data: hr.map((x) => x.rata_bersih), backgroundColor: WARNA.ink, borderRadius: 6, maxBarThickness: 40 }],
                    },
                    options: { plugins: { legend: { display: false }, tooltip: tooltipRupiah }, scales: { y: sumbuRupiah, x: { grid: { display: false } } } },
                });
            }

            if (this.tab === 'keuangan') {
                const k = this.d.pengeluaran.kategori;
                g.gambar('biaya', this.$refs.biaya, {
                    type: 'doughnut',
                    data: { labels: k.map((x) => x.kategori), datasets: [{ data: k.map((x) => x.total), backgroundColor: WARNA.seri, borderWidth: 0 }] },
                    options: { cutout: '65%', plugins: { legend: { position: 'bottom' }, tooltip: tooltipDonat } },
                });
            }
        },

        // ---- Ekspor ----
        nama(bagian) {
            return `laporan-${bagian}-${this.awal}_${this.akhir}`;
        },

        ekspor() {
            const d = this.d;
            if (!d) return;
            const peta = {
                ringkasan: () =>
                    unduhCsv(this.nama('ringkasan'), [{ label: 'Indikator', key: 'label' }, { label: 'Periode ini', key: 'ini' }, { label: 'Periode lalu', key: 'lalu' }],
                        Object.keys(this.r).map((k) => ({ label: k, ini: this.r[k], lalu: this.rl[k] }))),
                penjualan: () =>
                    unduhCsv(this.nama('harian'), [
                        { label: 'Tanggal', key: 'tanggal' }, { label: 'Transaksi', key: 'transaksi' }, { label: 'Pengunjung', key: 'pengunjung' },
                        { label: 'Penjualan bersih', key: 'bersih' }, { label: 'HPP', key: 'hpp' }, { label: 'Laba kotor', key: 'laba_kotor' },
                        { label: 'Pengeluaran', key: 'pengeluaran' }, { label: 'Laba bersih', key: 'laba_bersih' },
                    ], d.harian),
                produk: () =>
                    unduhCsv(this.nama('produk'), [
                        { label: 'Produk', key: 'nama' }, { label: 'Kategori', key: 'kategori' }, { label: 'Qty', key: 'qty' }, { label: 'Omzet', key: 'omzet' },
                        { label: 'HPP', key: 'hpp' }, { label: 'Laba', key: 'laba' }, { label: 'Margin %', key: 'margin' }, { label: 'Kontribusi %', key: 'kontribusi' }, { label: 'Kelas ABC', key: 'kelas' },
                    ], this.produkUrut),
                pola: () =>
                    unduhCsv(this.nama('pola-jam'), [{ label: 'Jam', key: 'jam' }, { label: 'Transaksi', key: 'transaksi' }, { label: 'Penjualan', key: 'bersih' }],
                        d.pola.jam.map((x, i) => ({ jam: `${String(i).padStart(2, '0')}:00`, ...x }))),
                kasir: () =>
                    unduhCsv(this.nama('kasir'), [
                        { label: 'Kasir', key: 'nama' }, { label: 'Transaksi', key: 'transaksi' }, { label: 'Penjualan', key: 'bersih' },
                        { label: 'Rata-rata', key: 'aov' }, { label: 'Pengunjung', key: 'pengunjung' }, { label: 'Diskon', key: 'diskon' },
                    ], d.kasir),
                keuangan: () =>
                    unduhCsv(this.nama('pengeluaran'), [{ label: 'Kategori', key: 'kategori' }, { label: 'Jumlah catatan', key: 'jumlah' }, { label: 'Total', key: 'total' }], d.pengeluaran.kategori),
                stok: () =>
                    unduhCsv(this.nama('stok'), [
                        { label: 'Produk', key: 'nama' }, { label: 'Kategori', key: 'kategori' }, { label: 'Stok', key: 'stok' }, { label: 'Minimum', key: 'minimum' },
                        { label: 'Terpakai', key: 'terpakai' }, { label: 'Rata-rata/hari', key: 'per_hari' }, { label: 'Perkiraan habis (hari)', key: 'hari_tersisa' },
                        { label: 'Nilai persediaan', key: 'nilai' }, { label: 'Status', key: 'status' },
                    ], d.stok.items),
            };
            peta[this.tab]?.();
        },

        cetak() {
            window.print();
        },
    };
};
