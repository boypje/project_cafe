// Navigasi antarhalaman lewat Turbo Drive (tanpa reload penuh),
// interaksi per halaman lewat komponen Alpine.
import * as Turbo from '@hotwired/turbo';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

// DM Sans: pengganti terbuka yang paling mirip Maison Neue (font Gojek, berlisensi).
import '@fontsource/dm-sans/400.css';
import '@fontsource/dm-sans/500.css';
import '@fontsource/dm-sans/600.css';
import '@fontsource/dm-sans/700.css';

import registerStores from './lib/stores';
import * as util from './lib/util';
import crud from './pages/crud';
import pos from './pages/pos';
import dashboard from './pages/dashboard';
import laporan from './pages/laporan';
import produk from './pages/produk';
import stok from './pages/stok';
import penjualan from './pages/penjualan';
import tutup from './pages/tutup';

Turbo.setProgressBarDelay(120);

window.Alpine = Alpine;
window.util = util;

Alpine.plugin(focus);
registerStores(Alpine);

Alpine.data('crud', crud);
Alpine.data('pos', pos);
Alpine.data('dashboard', dashboard);
Alpine.data('laporan', laporan);
Alpine.data('produk', produk);
Alpine.data('stok', stok);
Alpine.data('penjualan', penjualan);
Alpine.data('tutup', tutup);

Alpine.magic('rp', () => util.rupiah);

Alpine.start();
