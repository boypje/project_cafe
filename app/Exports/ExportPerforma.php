<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;
use App\Models\Penjualan;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportPerforma implements FromView, ShouldAutoSize, WithStyles
{
    private $awal;
    private $akhir;
    private $user_id;

    public function __construct($awal, $akhir, $user_id)
    {
        $this->awal = $awal;
        $this->akhir = $akhir;
        $this->user_id = $user_id;
    }

    public function view(): View
    {
        $data = $this->getData($this->awal, $this->akhir, $this->user_id);

        return view('exports.laporanperforma', ['data' => $data]);
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

    public function getData($awal, $akhir, $user_id)
    {
        $selectColumns = [
            DB::raw('DATE(penjualan.created_at) AS Tanggal'),
            'users.name AS kasir',
            DB::raw('COUNT(DISTINCT CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.id_penjualan ELSE 0 END) AS total_transaksi'),
            DB::raw('COUNT(DISTINCT CASE WHEN penjualan.metode IS NOT NULL AND penjualan.status = "SUKSES" THEN penjualan.id_penjualan END) AS transaksi_sukses'),
            DB::raw('COUNT(DISTINCT CASE WHEN penjualan.metode IS NOT NULL AND penjualan.status = "SALAH" THEN penjualan.id_penjualan END) AS transaksi_salah'),
            DB::raw('SUM(CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.pengunjung ELSE 0 END) AS total_pengunjung'),
        ];

        $trx = DB::table('penjualan')
            ->select($selectColumns)
            ->leftJoin('users', 'users.id', '=', 'penjualan.id_user')
            ->whereDate("penjualan.created_at", ">=", $awal)
            ->whereDate("penjualan.created_at", "<=", $akhir)
            ->where('penjualan.total_harga', '!=', 0);

        if ($user_id == 'all') {
            $trx->where('penjualan.id_user', '!=', 0);
        } else {
            for ($i = 0; $i <= 500; $i++) {
                if ($user_id == $i) {
                    $trx->where('penjualan.id_user', $user_id);
                }
            }
        }

        $trx = $trx->groupBy(DB::raw('DATE(penjualan.created_at)'), 'kasir')
            ->get();

        $trx->transform(function ($item, $key) {
            $item->Nomor = $key + 1;
            $item->Tanggal = tanggal_indonesia($item->Tanggal, false);
            return $item;
        });

        return $trx;
    }
}
