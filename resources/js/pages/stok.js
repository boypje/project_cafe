import { api, ymd, angka, rupiah, debounce, unduhCsv } from '../lib/util';

/** Halaman stok: form mutasi multi-item (masuk/keluar/opname) + riwayat mutasi. */
export default (config) => ({
    produk: config.produk,
    tipeLabel: config.tipe,
    angka,
    rupiah,

    // riwayat
    rows: [],
    loading: true,
    page: 1,
    lastPage: 1,
    total: 0,
    f: { awal: ymd(new Date(Date.now() - 6 * 864e5)), akhir: ymd(), produk: '', tipe: '' },

    // form
    open: false,
    tipe: 'masuk',
    items: [],
    keterangan: '',
    cari: '',
    saving: false,

    init() {
        this.load();
        this.$watch('f', debounce(() => {
            this.page = 1;
            this.load();
        }, 250));
    },

    async load() {
        this.loading = true;
        try {
            const res = await api(config.urls.mutasi, { params: { ...this.f, page: this.page } });
            this.rows = res.data;
            this.lastPage = res.last_page;
            this.total = res.total;
        } catch (e) {
            this.$store.toast.push(e.message, 'error');
        } finally {
            this.loading = false;
        }
    },

    goto(p) {
        if (p < 1 || p > this.lastPage) return;
        this.page = p;
        this.load();
    },

    buka(tipe) {
        this.tipe = tipe;
        this.items = [];
        this.keterangan = '';
        this.cari = '';
        this.open = true;
        this.$nextTick(() => this.$refs.cariProduk?.focus());
    },

    get pilihan() {
        const q = this.cari.toLowerCase();
        return this.produk.filter((p) => !this.items.some((i) => i.id === p.id) && (!q || p.nama.toLowerCase().includes(q))).slice(0, 8);
    },

    pilih(p) {
        this.items.push({ id: p.id, nama: p.nama, satuan: p.satuan, stok: p.stok, jumlah: this.tipe === 'koreksi' ? p.stok : '', harga_pokok: p.harga_pokok || '' });
        this.cari = '';
        this.$nextTick(() => this.$refs.cariProduk?.focus());
    },

    /** Opname untuk semua item sekaligus: isi daftar dengan stok sistem saat ini. */
    opnameSemua() {
        this.items = this.produk.map((p) => ({ id: p.id, nama: p.nama, satuan: p.satuan, stok: p.stok, jumlah: p.stok, harga_pokok: '' }));
    },

    async simpan() {
        if (!this.items.length) {
            this.$store.toast.push('Pilih minimal satu produk.', 'warning');
            return;
        }
        this.saving = true;
        try {
            const res = await api(config.urls.store, {
                method: 'POST',
                body: {
                    tipe: this.tipe,
                    keterangan: this.keterangan,
                    items: this.items.map((i) => ({ id: i.id, jumlah: Number(i.jumlah) || 0, harga_pokok: Number(i.harga_pokok) || null })),
                },
            });
            // perbarui stok lokal agar form berikutnya akurat
            this.items.forEach((i) => {
                const p = this.produk.find((x) => x.id === i.id);
                const n = Number(i.jumlah) || 0;
                if (p) p.stok = this.tipe === 'masuk' ? p.stok + n : this.tipe === 'keluar' ? p.stok - n : n;
            });
            this.$store.toast.push(res.message);
            this.open = false;
            this.load();
        } catch (e) {
            this.$store.toast.push(e.message, 'error');
        } finally {
            this.saving = false;
        }
    },

    selisih(i) {
        return (Number(i.jumlah) || 0) - i.stok;
    },

    ekspor() {
        unduhCsv(`stok-${ymd()}`, [
            { label: 'Produk', key: 'nama' },
            { label: 'Satuan', key: 'satuan' },
            { label: 'Stok', key: 'stok' },
            { label: 'Harga pokok', key: 'harga_pokok' },
            { label: 'Nilai', value: (r) => r.stok * r.harga_pokok },
        ], this.produk);
    },
});
