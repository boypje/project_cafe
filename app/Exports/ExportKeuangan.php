<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportKeuangan implements FromView, ShouldAutoSize, WithStyles
{
    private $awal;
    private $akhir;

    public function __construct($awal, $akhir)
    {
        $this->awal = $awal;
        $this->akhir = $akhir;
    }

    public function view(): View
    {
        $data = $this->getData($this->awal, $this->akhir);
        return view('exports.laporankeuangan', ['data' => $data]);
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

        $sheet->getColumnDimension('A')->setWidth(5);

        // Set column widths to 150 pixels for all columns
        for ($col = 'B'; $col <= $sheet->getHighestColumn(); $col++) {
            $sheet->getColumnDimension($col)->setWidth(150);
        }

        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    }


    public function getData($awal, $akhir)
    {
        $no = 1;
        $data = array();
        $pendapatan = 0;
        $total_pendapatan = 0;
        $penjualan_tunai = 0;
        $penjualan_debit = 0;
        $pengeluaran_tunai = 0;
        $pengeluaran_debit = 0;
        $temp = 0;

        while (strtotime($awal) <= strtotime($akhir)) {
            $tanggal = $awal;
            $awal = date('Y-m-d', strtotime("+1 day", strtotime($awal)));

            $penjualan_tunai = Penjualan::where('metode', '=', 'Tunai')
            ->where('created_at', 'LIKE', "%$tanggal%")
            ->where('status', '=', 'SUKSES')
            ->sum('bayar');

            $penjualan_debit = Penjualan::where('metode', '=', 'Debit')
                ->where('created_at', 'LIKE', "%$tanggal%")
                ->where('status', '=', 'SUKSES')
                ->sum('bayar');

            $pengeluaran_tunai = Pengeluaran::where('metode', '=', 'Tunai')
                ->where('created_at', 'LIKE', "%$tanggal%")
                ->sum('nominal');

            $pengeluaran_debit = Pengeluaran::where('metode', '=', 'Debit')
                ->where('created_at', 'LIKE', "%$tanggal%")
                ->sum('nominal');

            $total_penjualan = Penjualan::where('created_at', 'LIKE', "%$tanggal%")
                ->where('status', '=', 'SUKSES')
                ->sum('bayar');

            $total_pengeluaran = Pengeluaran::where('created_at', 'LIKE', "%$tanggal%")
                ->sum('nominal');

            $pendapatan = $total_penjualan - $total_pengeluaran;
            $temp += $pendapatan;
            $total_pendapatan = $temp;

            $row = array();
            $row['DT_RowIndex'] = $no++;
            $row['tanggal'] = tanggal_indonesia($tanggal, false);
            $row['penjualan_tunai'] = format_money($penjualan_tunai);
            $row['penjualan_debit'] = format_money($penjualan_debit);
            $row['penjualan'] = format_money($total_penjualan);
            $row['pengeluaran_tunai'] = format_money($pengeluaran_tunai);
            $row['pengeluaran_debit'] = format_money($pengeluaran_debit);
            $row['pengeluaran'] = format_money($total_pengeluaran);
            $row['pendapatan'] = format_money($pendapatan);

            $data[] = $row;

        }

        $data[] = [
            'DT_RowIndex' => '',
            'tanggal' => '',
            'penjualan_tunai' => '',
            'penjualan_debit' => '',
            'penjualan' => '',
            'pengeluaran_tunai' => '',
            'pengeluaran_debit' => '',
            'pengeluaran' => 'Total Setoran',
            'pendapatan' => format_money($total_pendapatan),
        ];

        return $data;
    }
}