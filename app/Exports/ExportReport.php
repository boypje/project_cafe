<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;
use App\Models\Produk;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportReport implements FromView, ShouldAutoSize, WithStyles
{
    private $startDate;
    private $endDate;
    private $productIds;

    public function __construct($startDate, $endDate, $productIds)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->productIds = $productIds;
    }

    public function view(): View
    {
        $data = $this->getData($this->startDate, $this->endDate, $this->productIds);
        $products = Produk::whereIn('id_produk', $this->productIds)->get();
        return view('exports.laporanstok', ['data' => $data, 'products' => $products]);
    }

    public function styles(Worksheet $sheet)
    {
        // Define the row number with data to be highlighted
        $rowToHighlight = 1; // Change this to the row number where your data starts

        // Apply yellow background color to the specified row with data
        $sheet->getStyle("A{$rowToHighlight}:" . $sheet->getHighestColumn() . $rowToHighlight)
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle("A{$rowToHighlight}:" . $sheet->getHighestColumn() . $rowToHighlight)
            ->getFill()
            ->getStartColor()
            ->setARGB('FFFF00');

        // Apply all borders to the entire sheet
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Set column widths to 150 pixels for all columns
        for ($col = 'A'; $col <= $sheet->getHighestColumn(); $col++) {
            $sheet->getColumnDimension($col)->setWidth(150);
        }

        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    }

    public function getData($startDate, $endDate, $productIds)
    {
        $selectColumns = [
            DB::raw('DATE(penjualan.created_at) AS Tanggal'),
            'users.name AS kasir',
            DB::raw('COUNT(DISTINCT CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.id_penjualan ELSE 0 END) AS total_transaksi'),
            DB::raw('SUM(DISTINCT CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.pengunjung ELSE 0 END) AS total_pengunjung'),
        ];

        foreach ($productIds as $productId) {
            $selectColumns[] = DB::raw("SUM(CASE WHEN penjualandetail.id_produk = $productId THEN penjualandetail.jumlah ELSE 0 END) as total_produk_" . $productId . "_terjual");
        }

        $selectColumns[] = DB::raw('SUM(penjualandetail.jumlah) AS total_semua_produk_terjual');
        $trx = DB::table('penjualan')
            ->select($selectColumns)
            ->join('users', 'users.id', '=', 'penjualan.id_user')
            ->join('penjualandetail', 'penjualandetail.id_penjualan', '=', 'penjualan.id_penjualan')
            ->whereIn('penjualandetail.id_produk', $productIds)
            ->whereDate("penjualan.created_at", ">=", $startDate)
            ->whereDate("penjualan.created_at", "<=", $endDate)
            ->where('penjualan.total_harga', '!=', 0)
            ->groupBy(DB::raw('DATE(penjualan.created_at)'),'kasir')
            ->get();
        $totalColumns = array_map(function ($productId) {
            return "total_produk_" . $productId . "_terjual";
        }, $productIds);

        $totals = [];
        foreach ($totalColumns as $column) {
            $totals[$column] = $trx->sum($column);
        }
        $totals['total_transaksi'] = $trx->sum("total_transaksi");
        $totals['total_pengunjung'] = $trx->sum("total_pengunjung");
        $totals['total_semua_produk_terjual'] = $trx->sum("total_semua_produk_terjual");

        $trx->map(function ($nilai) {
            $nilai->Tanggal = tanggal_indonesia($nilai->Tanggal, false);
            return $nilai;
        });

        $totals['Tanggal'] = '';
        $totals['kasir'] = 'Total';

        $trx[] = $totals;
        return $trx;
    }
}