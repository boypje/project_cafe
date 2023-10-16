<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nota Status</title>

    <?php
    $style = '
    <style>
        * {
            font-family: "consolas", sans-serif;
            margin-lect: 10px;
        }
        p {
            display: block;
            margin: 3px;
            font-size: 10pt;
        }
        table td {
            font-size: 9pt;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }

        @media print {
            @page {
                margin: 0;
                size: 50mm 
    ';
    ?>
    <?php 
    $style .= 
        ! empty($_COOKIE['innerHeight'])
            ? $_COOKIE['innerHeight'] .'mm; }'
            : '}';
    ?>
    <?php
    $style .= '
            html, body {
                width: 42mm;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
    ';
    ?>

    {!! $style !!}
</head>
<body onload="window.print()">
    <button class="btn-print" style="position: absolute; right: 1rem; top: rem;" onclick="window.print()">Print</button>
    <div class="text-center">
        <h3 style="margin-bottom: 5px;">{{ strtoupper($setting->nama_usaha) }}</h3>
        <p>{{ strtoupper($setting->alamat) }}</p>
        <p>{{ strtoupper($setting->kota) }}</p>
    </div>
    <br>
    <div>
        <p style="float: left;">{{ date('d-m-Y') }}</p>
        <p style="float: right">{{ strtoupper(auth()->user()->name) }}</p>
    </div>
    <div class="clear-both" style="clear: both;"></div>
    <p class="text-center">====================</p>
    
    <br>
    
    <p class="text-center">--------------------</p>
    <table width="100%" style="border: 0;">
    <tr>
            <td>Total Transaksi    :</td>
            <td class="text-right">{{ $total_transaksi }}</td>
        </tr>
        <tr>
            <td>Total Pengunjung:</td>
            <td class="text-right">{{ $total_pengunjung }}</td>
        </tr>
        <td>Penjualan Tunai     :</td>
            <td class="text-right">{{format_money($tunai)}}</td>
        </tr>
        <td>Penjualan Debit     :</td>
            <td class="text-right">{{format_money($debit)}}</td>
        </tr>
        <td>Pengeluaran Tunai     :</td>
            <td class="text-right">{{format_money($pengeluaran_tunai)}}</td>
        </tr>
        <td>Pengeluaran Debit     :</td>
            <td class="text-right">{{format_money($pengeluaran_debit)}}</td>
        </tr>
    </table>
        
    <p class="text-center">--------------------</p>
    <table width="100%" style="border: 0;">
        <td>Total Penjualan     :</td>
            <td class="text-right">{{format_money($jual)}}</td>
        </tr>
        <td>Total Pengeluaran     :</td>
            <td class="text-right">{{format_money($total_pengeluaran)}}</td>
        </tr>
        <td>Setoran     :</td>
            <td class="text-right">{{format_money($setoran)}}</td>
        </tr>
    
    
    </table>

    <p class="text-center">====================</p>
    <p class="text-center">-- TERIMA KASIH --</p>
    <br>
    <br>

    <p class="text-center">--------------------</p>

    <script>
        let body = document.body;
        let html = document.documentElement;
        let height = Math.max(
                body.scrollHeight, body.offsetHeight,
                html.clientHeight, html.scrollHeight, html.offsetHeight
            );

        document.cookie = "innerHeight=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = "innerHeight="+ ((height + 0) * 0.264583);
    </script>
</body>
</html>