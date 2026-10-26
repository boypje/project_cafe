import crud from './crud';
import { gabung } from './produk';
import { api, cetak, ymd, rupiah, angka, unduhCsv } from '../lib/util';

/** Riwayat penjualan (admin): filter, detail, cetak ulang, batalkan transaksi. */
export default () => {
    const n = new Date();
    return gabung(
        crud({
            base: '/penjualan',
            kosong: {},
            paginate: true,
            filters: { awal: ymd(new Date(n.getFullYear(), n.getMonth(), 1)), akhir: ymd(), metode: '', user: '' },
        }),
        {
            rupiah,
            angka,
            detail: null,
            detailOpen: false,

            async lihat(row) {
                this.detailOpen = true;
                this.detail = null;
                try {
                    this.detail = await api(`/penjualan/${row.id}`);
                } catch (e) {
                    this.$store.toast.push(e.message, 'error');
                    this.detailOpen = false;
                }
            },

            cetak(url) {
                cetak(url);
            },

            async batalkan(row) {
                const ok = await this.$store.confirm.ask({
                    judul: `Batalkan ${row.nomor}?`,
                    pesan: `Transaksi senilai ${rupiah(row.bayar)} akan dihapus dan stok item dikembalikan. Tindakan ini tidak bisa dibatalkan.`,
                    tombol: 'Batalkan transaksi',
                });
                if (!ok) return;
                try {
                    const res = await api(`/penjualan/${row.id}`, { method: 'DELETE' });
                    this.$store.toast.push(res.message);
                    this.detailOpen = false;
                    this.load();
                } catch (e) {
                    this.$store.toast.push(e.message, 'error');
                }
            },

            ekspor() {
                unduhCsv(`penjualan-${this.filters.awal}_${this.filters.akhir}-hal${this.page}`, [
                    { label: 'Nomor', key: 'nomor' }, { label: 'Waktu', key: 'waktu' }, { label: 'Kasir', key: 'kasir' },
                    { label: 'Meja', key: 'meja' }, { label: 'Pelanggan', key: 'pelanggan' }, { label: 'Metode', key: 'metode' },
                    { label: 'Item', key: 'item' }, { label: 'Subtotal', key: 'total_harga' }, { label: 'Diskon', key: 'diskon' },
                    { label: 'Pajak', key: 'pajak' }, { label: 'Total', key: 'bayar' },
                ], this.rows);
            },
        }
    );
};
