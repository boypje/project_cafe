import crud from './crud';
import { rupiah, angka } from '../lib/util';

/** Gabungkan objek komponen tanpa mengeksekusi getter (spread biasa akan membekukannya). */
export function gabung(...objek) {
    const hasil = {};
    objek.forEach((o) => Object.defineProperties(hasil, Object.getOwnPropertyDescriptors(o)));
    return hasil;
}

const kosong = () => ({
    nama: '',
    id_category: '',
    harga_jual: '',
    harga_pokok: '',
    stok: 0,
    satuan: 'porsi',
    stok_minimum: 5,
    kelola_stok: true,
    tampil_kasir: true,
    komponen: [],
});

export default () =>
    gabung(
        crud({
            base: '/produk',
            kosong,
            label: 'produk',
            cari: ['nama', 'kategori'],
            map: (r) => ({
                nama: r.nama,
                id_category: r.id_category,
                harga_jual: r.harga_jual,
                harga_pokok: r.harga_pokok || '',
                stok: r.stok,
                satuan: r.satuan || 'porsi',
                stok_minimum: r.stok_minimum,
                kelola_stok: r.kelola_stok,
                tampil_kasir: r.tampil_kasir,
                komponen: r.komponen.map((k) => ({ ...k })),
            }),
            payload: (f) => ({
                ...f,
                harga_pokok: Number(f.harga_pokok) || 0,
                stok: f.komponen.length ? null : Number(f.stok) || 0,
                komponen: f.komponen.map((k) => ({ id: k.id, jumlah: Number(k.jumlah) || 1 })),
            }),
        }),
        {
            rupiah,
            angka,
            kat: '',
            status: '',
            cariKomponen: '',

            get daftar() {
                return this.tampil.filter(
                    (p) =>
                        (!this.kat || p.id_category == this.kat) &&
                        (!this.status ||
                            (this.status === 'paket' && p.komponen.length) ||
                            (this.status === 'menipis' && p.tersedia !== null && p.tersedia <= p.stok_minimum) ||
                            (this.status === 'nonaktif' && !p.tampil_kasir))
                );
            },

            get ringkas() {
                const r = this.rows;
                return {
                    total: r.length,
                    paket: r.filter((p) => p.komponen.length).length,
                    menipis: r.filter((p) => p.tersedia !== null && p.tersedia <= p.stok_minimum).length,
                    tanpaHpp: r.filter((p) => !p.hpp).length,
                };
            },

            get pilihanKomponen() {
                const q = this.cariKomponen.toLowerCase();
                return this.rows
                    .filter((p) => p.id !== this.editId && !p.komponen.length && !this.form.komponen.some((k) => k.id === p.id))
                    .filter((p) => !q || p.nama.toLowerCase().includes(q))
                    .slice(0, 8);
            },

            tambahKomponen(p) {
                this.form.komponen.push({ id: p.id, nama: p.nama, jumlah: 1 });
                this.cariKomponen = '';
            },

            hapusKomponen(i) {
                this.form.komponen.splice(i, 1);
            },

            get hppKomponen() {
                return this.form.komponen.reduce((s, k) => s + (this.rows.find((p) => p.id === k.id)?.hpp || 0) * (Number(k.jumlah) || 0), 0);
            },

            get marginForm() {
                const hpp = Number(this.form.harga_pokok) || (this.form.komponen.length ? this.hppKomponen : 0);
                const jual = Number(this.form.harga_jual) || 0;
                return jual && hpp ? ((jual - hpp) / jual) * 100 : null;
            },

            statusStok(p) {
                if (p.tersedia === null) return { kelas: 'badge-muted', teks: 'Tidak dihitung' };
                if (p.tersedia <= 0) return { kelas: 'badge-bad', teks: 'Habis' };
                if (p.tersedia <= p.stok_minimum) return { kelas: 'badge-warn', teks: `${p.tersedia} · menipis` };
                return { kelas: 'badge-ok', teks: `${p.tersedia} ${p.satuan || ''}` };
            },
        }
    );
