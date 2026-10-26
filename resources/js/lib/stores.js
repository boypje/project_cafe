// Store global: notifikasi (toast) dan dialog konfirmasi.

export default function registerStores(Alpine) {
    Alpine.store('toast', {
        items: [],
        id: 0,
        push(pesan, tipe = 'success', lama = 3500) {
            const id = ++this.id;
            this.items.push({ id, pesan, tipe });
            setTimeout(() => this.close(id), lama);
        },
        close(id) {
            this.items = this.items.filter((t) => t.id !== id);
        },
    });

    Alpine.store('confirm', {
        open: false,
        judul: '',
        pesan: '',
        tombol: 'Ya, lanjutkan',
        bahaya: true,
        _resolve: null,
        ask({ judul = 'Yakin?', pesan = '', tombol = 'Ya, lanjutkan', bahaya = true } = {}) {
            Object.assign(this, { judul, pesan, tombol, bahaya, open: true });
            return new Promise((r) => (this._resolve = r));
        },
        jawab(ya) {
            this.open = false;
            this._resolve?.(ya);
            this._resolve = null;
        },
    });
}
