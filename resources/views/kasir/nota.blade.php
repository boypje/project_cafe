<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Nota {{ $penjualan->nomor }}</title>
    <style>
        /* Dioptimalkan untuk printer thermal 58mm (lebar cetak ±48mm). */
        @page { size: 58mm auto; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 4mm 3mm; width: 58mm; font: 11px/1.35 "Courier New", ui-monospace, monospace; color: #000; }
        .c { text-align: center; } .r { text-align: right; } .b { font-weight: bold; }
        .judul { font-size: 14px; font-weight: bold; }
        hr { border: 0; border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 1px 0; }
        .total td { font-size: 13px; font-weight: bold; padding-top: 3px; }
        .kecil { font-size: 10px; }
        @media screen { body { margin: 16px auto; box-shadow: 0 2px 12px rgba(0,0,0,.15); background: #fff; } html { background: #eee; } }
    </style>
</head>
<body>
    <div class="c">
        <div class="judul">{{ $setting->nama_usaha }}</div>
        @if ($setting->alamat)<div class="kecil">{{ $setting->alamat }}{{ $setting->kota ? ', ' . $setting->kota : '' }}</div>@endif
        @if ($setting->telepon)<div class="kecil">Telp. {{ $setting->telepon }}</div>@endif
    </div>
    <hr>
    <table class="kecil">
        <tr><td>No</td><td class="r">{{ $penjualan->nomor }}</td></tr>
        <tr><td>Waktu</td><td class="r">{{ $penjualan->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td class="r">{{ optional($penjualan->user)->name }}</td></tr>
        @if ($penjualan->meja)<tr><td>Meja</td><td class="r">{{ $penjualan->meja }}</td></tr>@endif
        @if ($penjualan->nama_pelanggan)<tr><td>Pelanggan</td><td class="r">{{ $penjualan->nama_pelanggan }}</td></tr>@endif
    </table>
    <hr>
    <table>
        @foreach ($penjualan->detail as $d)
            <tr><td colspan="2">{{ optional($d->produk)->nama_produk ?? '-' }}</td></tr>
            <tr>
                <td class="kecil">&nbsp;{{ $d->jumlah }} x {{ number_format($d->harga_jual, 0, ',', '.') }}</td>
                <td class="r">{{ number_format($d->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>
    <hr>
    <table>
        <tr><td>Subtotal ({{ $penjualan->total_item }} item)</td><td class="r">{{ number_format($penjualan->total_harga, 0, ',', '.') }}</td></tr>
        @if ($penjualan->diskon > 0)
            <tr><td>Diskon</td><td class="r">-{{ number_format($penjualan->diskon, 0, ',', '.') }}</td></tr>
        @endif
        @if ($penjualan->pajak > 0)
            <tr><td>Pajak</td><td class="r">{{ number_format($penjualan->pajak, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="total"><td>TOTAL</td><td class="r">{{ number_format($penjualan->bayar, 0, ',', '.') }}</td></tr>
        <tr><td>{{ $penjualan->metode }}</td><td class="r">{{ number_format($penjualan->diterima, 0, ',', '.') }}</td></tr>
        @if ($penjualan->metode === 'Tunai')
            <tr><td>Kembali</td><td class="r">{{ number_format(max($penjualan->diterima - $penjualan->bayar, 0), 0, ',', '.') }}</td></tr>
        @endif
    </table>
    @if ($penjualan->catatan)
        <hr><div class="kecil">Catatan: {{ $penjualan->catatan }}</div>
    @endif
    <hr>
    <div class="c kecil">{{ $setting->footer_nota ?: 'Terima kasih atas kunjungan Anda' }}</div>
</body>
</html>
