import { api, debounce } from '../lib/util';

/**
 * Komponen tabel + form modal generik untuk halaman master.
 *
 * config: {
 *   base: '/kategori',          // GET base/data, POST base, PUT/DELETE base/{id}
 *   kosong: { nama: '' },       // nilai awal form
 *   filters: { ... },           // dikirim sebagai query (server-side)
 *   paginate: false,            // true bila endpoint mengembalikan {data, page, last_page}
 *   cari: ['nama'],             // kolom pencarian lokal (non-paginate)
 *   label: 'kategori',          // untuk pesan konfirmasi hapus
 *   map: (row) => form,         // ubah row menjadi isi form saat edit
 * }
 */
export default (config) => ({
    rows: [],
    meta: {},
    loading: true,
    saving: false,
    open: false,
    editId: null,
    form: { ...config.kosong },
    errors: {},
    q: '',
    page: 1,
    lastPage: 1,
    total: 0,
    filters: { ...(config.filters || {}) },

    init() {
        this.load();
        this.$watch('filters', debounce(() => {
            this.page = 1;
            this.load();
        }, 300));
        this.$watch('q', debounce(() => {
            if (config.paginate) {
                this.page = 1;
                this.load();
            }
        }, 300));
    },

    async load() {
        this.loading = true;
        try {
            const params = { ...this.filters };
            if (config.paginate) Object.assign(params, { page: this.page, cari: this.q });
            const res = await api(`${config.base}/data`, { params });
            if (config.paginate) {
                this.rows = res.data;
                this.lastPage = res.last_page;
                this.total = res.total;
                this.meta = res;
            } else {
                this.rows = res;
            }
        } catch (e) {
            this.$store.toast.push(e.message, 'error');
        } finally {
            this.loading = false;
        }
    },

    get tampil() {
        if (config.paginate || !this.q) return this.rows;
        const q = this.q.toLowerCase();
        return this.rows.filter((r) => (config.cari || ['nama']).some((k) => String(r[k] ?? '').toLowerCase().includes(q)));
    },

    goto(p) {
        if (p < 1 || p > this.lastPage) return;
        this.page = p;
        this.load();
    },

    tambah() {
        this.editId = null;
        this.form = typeof config.kosong === 'function' ? config.kosong() : JSON.parse(JSON.stringify(config.kosong));
        this.errors = {};
        this.open = true;
    },

    ubah(row) {
        this.editId = row.id;
        this.form = config.map ? config.map(row) : { ...row };
        this.errors = {};
        this.open = true;
    },

    async simpan() {
        this.saving = true;
        this.errors = {};
        try {
            const body = config.payload ? config.payload(this.form) : this.form;
            const res = this.editId
                ? await api(`${config.base}/${this.editId}`, { method: 'PUT', body })
                : await api(config.base, { method: 'POST', body });
            this.$store.toast.push(res?.message || 'Tersimpan.');
            this.open = false;
            this.load();
            this.$dispatch('tersimpan');
        } catch (e) {
            this.errors = e.errors || {};
            this.$store.toast.push(e.message, 'error');
        } finally {
            this.saving = false;
        }
    },

    async hapus(row) {
        const ok = await this.$store.confirm.ask({
            judul: `Hapus ${config.label || 'data'}?`,
            pesan: `"${row.nama || row.name || row.deskripsi || ''}" akan dihapus permanen.`,
            tombol: 'Hapus',
        });
        if (!ok) return;
        try {
            const res = await api(`${config.base}/${row.id}`, { method: 'DELETE' });
            this.$store.toast.push(res?.message || 'Dihapus.');
            this.load();
        } catch (e) {
            this.$store.toast.push(e.message, 'error');
        }
    },

    err(field) {
        return this.errors[field]?.[0];
    },
});
