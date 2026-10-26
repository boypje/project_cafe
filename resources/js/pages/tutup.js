import { api, ymd, rupiah, angka, tanggal } from '../lib/util';

/** Rekap tutup kasir per tanggal (dan per kasir untuk admin). */
export default (url) => ({
    tanggal: ymd(),
    user: '',
    d: null,
    loading: true,
    rupiah,
    angka,
    tgl: tanggal,

    init() {
        this.load();
        this.$watch('tanggal', () => this.load());
        this.$watch('user', () => this.load());
    },

    async load() {
        this.loading = true;
        try {
            this.d = await api(url, { params: { tanggal: this.tanggal, user: this.user } });
        } catch (e) {
            this.$store.toast.push(e.message, 'error');
        } finally {
            this.loading = false;
        }
    },

    get kelompok() {
        const g = {};
        (this.d?.items || []).forEach((i) => {
            const k = i.kategori || 'Lainnya';
            (g[k] ||= { nama: k, items: [], qty: 0, total: 0 }).items.push(i);
            g[k].qty += Number(i.qty);
            g[k].total += Number(i.total);
        });
        return Object.values(g);
    },
});
