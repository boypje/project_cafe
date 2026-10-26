import { api, cetak, rupiah } from '../lib/util';

const SIMPAN = 'altari.kasir.v1';
const WARNA = ['bg-amber-500', 'bg-sky-500', 'bg-emerald-500', 'bg-rose-500', 'bg-violet-500', 'bg-lime-500', 'bg-orange-500', 'bg-teal-500'];

const orderKosong = () => ({
    meja: '',
    pelanggan: '',
    pengunjung: 1,
    catatan: '',
    diskonTipe: 'rp',
    diskonInput: '',
    metode: 'Tunai',
    diterima: '',
});

function baca() {
    try {
        return JSON.parse(localStorage.getItem(SIMPAN)) || {};
    } catch (e) {
        return {};
    }
}

/**
 * Layar kasir: seluruh pesanan disusun di browser, lalu dikirim sekali
 * saat bayar (satu request, satu DB transaction, stok resep ikut terpotong).
 */
export default (config) => ({
    produk: [],
    stokBahan: {},
    kategori: [],
    loading: true,
    kat: 'semua',
    q: '',
    cart: [],
    order: orderKosong(),
    bayarOpen: false,
    memproses: false,
    hasil: null,
    riwayatOpen: false,
    riwayat: [],
    keranjangMobile: false,
    cetakOtomatis: false,
    pengumuman: '',
    bump: null,
    blokir: !!config.blokir,
    jam: '',

    init() {
        const detak = () => (this.jam = new Date().toLocaleString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' }));
        detak();
        this._jam = setInterval(detak, 30000);
        const s = baca();
        this.cart = s.cart || [];
        this.order = { ...orderKosong(), ...(s.order || {}) };
        this.cetakOtomatis = !!s.cetakOtomatis;
        this.$watch('cart', () => this.simpanLokal());
        this.$watch('order', () => this.simpanLokal());
        this.$watch('cetakOtomatis', () => this.simpanLokal());
        this.muat();
        this._key = (e) => this.tombol(e);
        window.addEventListener('keydown', this._key);
    },

    destroy() {
        window.removeEventListener('keydown', this._key);
        clearInterval(this._jam);
    },

    simpanLokal() {
        try {
            localStorage.setItem(SIMPAN, JSON.stringify({ cart: this.cart, order: this.order, cetakOtomatis: this.cetakOtomatis }));
        } catch (e) {
            /* penyimpanan lokal tidak tersedia: tetap berjalan tanpa pemulihan */
        }
    },

    async muat() {
        try {
            const res = await api(config.urls.menu);
            this.produk = res.produk;
            this.stokBahan = res.stok;
            this.kategori = res.kategori.filter((k) => res.produk.some((p) => p.kategori === k.id));
            // Sinkronkan harga & stok item keranjang yang dipulihkan.
            this.cart = this.cart
                .map((c) => {
                    const p = this.produk.find((x) => x.id === c.id);
                    return p ? { ...c, nama: p.nama, harga: p.harga, stok: p.stok, paket: p.paket } : null;
                })
                .filter(Boolean);
        } catch (e) {
            this.$store.toast.push(e.message, 'error');
        } finally {
            this.loading = false;
        }
    },

    rupiah,

    warna(idKat) {
        const i = this.kategori.findIndex((k) => k.id === idKat);
        return WARNA[(i < 0 ? 0 : i) % WARNA.length];
    },

    namaKategori(idKat) {
        return this.kategori.find((k) => k.id === idKat)?.nama || '';
    },

    jumlahPerKategori(idKat) {
        return this.produk.filter((p) => p.kategori === idKat).length;
    },

    get daftar() {
        const q = this.q.trim().toLowerCase();
        return this.produk.filter(
            (p) => (this.kat === 'semua' || p.kategori === this.kat) && (!q || p.nama.toLowerCase().includes(q) || (p.paket || '').toLowerCase().includes(q))
        );
    },

    diKeranjang(id) {
        return this.cart.find((c) => c.id === id)?.jumlah || 0;
    },

    /** Total pemakaian stok produk/bahan $id oleh seluruh isi keranjang (paket diurai). */
    terpakai(id) {
        return this.cart.reduce((s, c) => {
            const p = this.produk.find((x) => x.id === c.id);
            if (p?.komponen?.length) return s + c.jumlah * (p.komponen.find((k) => k.id === id)?.jumlah || 0);
            return s + (c.id === id ? c.jumlah : 0);
        }, 0);
    },

    sisa(p) {
        if (p.stok === null) return null;
        if (!p.komponen?.length) return p.stok - this.terpakai(p.id);
        return Math.min(...p.komponen.map((k) => Math.floor(((this.stokBahan[k.id] ?? 0) - this.terpakai(k.id)) / k.jumlah)));
    },

    tambah(p) {
        const ada = this.cart.find((c) => c.id === p.id);
        if (ada) ada.jumlah++;
        else this.cart.push({ id: p.id, nama: p.nama, harga: p.harga, jumlah: 1, stok: p.stok, paket: p.paket });

        if (this.sisa(p) !== null && this.sisa(p) < 0) {
            this.$store.toast.push(`Stok ${p.nama} tidak mencukupi.`, 'warning');
        }
        this.bump = p.id;
        setTimeout(() => (this.bump = null), 260);
        this.pengumuman = `${p.nama} ditambahkan, jumlah ${this.diKeranjang(p.id)}`;
    },

    ubahJumlah(item, delta) {
        item.jumlah = Math.max(0, (parseInt(item.jumlah) || 0) + delta);
        if (item.jumlah === 0) this.hapus(item);
    },

    setJumlah(item, nilai) {
        const n = parseInt(nilai);
        if (!n || n < 1) return this.hapus(item);
        item.jumlah = Math.min(n, 999);
    },

    hapus(item) {
        this.cart = this.cart.filter((c) => c !== item);
        this.pengumuman = `${item.nama} dihapus dari pesanan`;
    },

    async kosongkan() {
        if (!this.cart.length) return;
        const ok = await this.$store.confirm.ask({ judul: 'Kosongkan pesanan?', pesan: 'Semua item di pesanan ini akan dihapus.', tombol: 'Kosongkan' });
        if (ok) this.reset();
    },

    reset() {
        this.cart = [];
        const metode = this.order.metode;
        this.order = { ...orderKosong(), metode };
    },

    get jumlahItem() {
        return this.cart.reduce((s, c) => s + c.jumlah, 0);
    },
    get subtotal() {
        return this.cart.reduce((s, c) => s + c.harga * c.jumlah, 0);
    },
    get diskon() {
        const v = Number(this.order.diskonInput) || 0;
        const d = this.order.diskonTipe === '%' ? Math.round((this.subtotal * Math.min(v, 100)) / 100) : v;
        return Math.min(Math.max(d, 0), this.subtotal);
    },
    get pajak() {
        return Math.round(((this.subtotal - this.diskon) * config.pajak) / 100);
    },
    get total() {
        return this.subtotal - this.diskon + this.pajak;
    },
    get diterima() {
        return this.order.metode === 'Tunai' ? Number(this.order.diterima) || 0 : this.total;
    },
    get kembali() {
        return this.diterima - this.total;
    },
    get bisaBayar() {
        return this.cart.length > 0 && !this.memproses && (this.order.metode !== 'Tunai' || this.diterima >= this.total);
    },

    /** Tombol nominal cepat: uang pas + pecahan pembulatan ke atas yang masuk akal. */
    get nominalCepat() {
        const t = this.total;
        if (!t) return [];
        const set = new Set([t]);
        [5000, 10000, 20000, 50000, 100000].forEach((p) => set.add(Math.ceil(t / p) * p));
        [50000, 100000, 200000].forEach((p) => p > t && set.add(p));
        return [...set].filter((v) => v >= t).sort((a, b) => a - b).slice(0, 6);
    },

    proses() {
        if (!this.cart.length) {
            this.$store.toast.push('Pesanan masih kosong.', 'warning');
            return;
        }
        this.keranjangMobile = false;
        this.bayarOpen = true;
        this.$nextTick(() => this.$refs.inputDiterima?.select());
    },

    async bayar() {
        if (!this.bisaBayar) {
            if (this.order.metode === 'Tunai') this.$store.toast.push('Uang diterima kurang dari total.', 'error');
            return;
        }
        this.memproses = true;
        try {
            const res = await api(config.urls.checkout, {
                method: 'POST',
                body: {
                    items: this.cart.map((c) => ({ id: c.id, jumlah: c.jumlah })),
                    diskon: this.diskon,
                    metode: this.order.metode,
                    diterima: this.diterima,
                    pengunjung: Number(this.order.pengunjung) || 1,
                    nama_pelanggan: this.order.pelanggan || null,
                    meja: this.order.meja || null,
                    catatan: this.order.catatan || null,
                },
            });
            this.hasil = { ...res, metode: this.order.metode, item: this.jumlahItem };
            this.bayarOpen = false;
            this.reset();
            if (res.peringatan) this.$store.toast.push(res.peringatan, 'warning', 6000);
            if (this.cetakOtomatis) cetak(res.nota_url);
            this.muat();
            this.$nextTick(() => this.$refs.tombolBaru?.focus());
        } catch (e) {
            this.$store.toast.push(e.message, 'error', 5000);
        } finally {
            this.memproses = false;
        }
    },

    cetakNota(url) {
        cetak(url || this.hasil?.nota_url);
    },

    selesai() {
        this.hasil = null;
        this.$nextTick(() => this.$refs.cari?.focus());
    },

    async bukaRiwayat() {
        this.riwayatOpen = true;
        try {
            this.riwayat = await api(config.urls.riwayat);
        } catch (e) {
            this.$store.toast.push(e.message, 'error');
        }
    },

    tambahPertama() {
        if (this.daftar.length) {
            this.tambah(this.daftar[0]);
            this.q = '';
        }
    },

    tombol(e) {
        const ketik = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName);
        if (this.hasil) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.selesai();
            } else if (e.key.toLowerCase() === 'p' && !ketik) {
                this.cetakNota();
            }
            return;
        }
        if (e.key === 'F2' || (e.key === '/' && !ketik)) {
            e.preventDefault();
            this.$refs.cari?.focus();
        } else if (e.key === 'F9' || (e.key === 'Enter' && (e.ctrlKey || e.metaKey))) {
            e.preventDefault();
            this.bayarOpen ? this.bayar() : this.proses();
        }
    },
});
