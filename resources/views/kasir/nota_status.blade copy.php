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
    
    <table width="100%" style="border: 0;">
        <tr>
            <td>Paket Nasi+Ayam Endul:</td>
            <td class="text-right">{{$payam}}</td>
        </tr>
        
        <tr>
            <td>Paket Nasi+Bebek Endul:</td>
            <td class="text-right">{{$pbebek}}</td>
        </tr>
        <tr>
            <td>Paket Nasi 3T:</td>
            <td class="text-right">{{$tigat}}</td>
        </tr>
        <tr>
            <td>Paket Nasi 4T:</td>
            <td class="text-right">{{$empat}}</td>
        </tr>


        <tr>
            <td>Paket Nasi Lele:</td>
            <td class="text-right">{{$nasilele}}</td>
        </tr>

        <tr>
            <td>Lele Goreng:</td>
            <td class="text-right">{{$lele}}</td>
        </tr>

        <tr>
            <td>Bebek Endul:</td>
            <td class="text-right">{{$bebek}}</td>
        </tr>
        <tr>
            <td>Ayam Endul:</td>
            <td class="text-right">{{$ayam}}</td>
        </tr>
        <tr>
            <td>Mie Instan:</td>
            <td class="text-right">{{$mie}}</td>
        </tr>
        <tr>
            <td>Nasi:</td>
            <td class="text-right">{{$nasi}}</td>
        </tr>
        <tr>
            <td>Tahu:</td>
            <td class="text-right">{{$tahu}}</td>
        </tr>
        <tr>
            <td>Tempe:</td>
            <td class="text-right">{{$tempe}}</td>
        </tr>
        <tr>
            <td>Terong:</td>
            <td class="text-right">{{$terong}}</td>
        </tr>
        <tr>
            <td>Telor:</td>
            <td class="text-right">{{$telor}}</td>
        </tr>
        <tr>
            <td>Mendoan:</td>
            <td class="text-right">{{$mendoan}}</td>
        </tr>
        <tr>
            <td>Teh:</td>
            <td class="text-right">{{$teh}}</td>
        </tr>
        <tr>
            <td>Teh Tawar:</td>
            <td class="text-right">{{$tehtawar}}</td>
        </tr>
        <tr>
            <td>Jeruk:</td>
            <td class="text-right">{{$jeruk}}</td>
        </tr>
        <tr>
            <td>Kopi Lanang:</td>
            <td class="text-right">{{$kopilanang}}</td>
        </tr>
        <tr>
            <td>Kopi Lanang Spesial:</td>
            <td class="text-right">{{$kopispesial}}</td>
        </tr>
        <tr>
            <td>Snack:</td>
            <td class="text-right">{{$snack}}</td>
        </tr>
        <tr>
            <td>Le Minerale 600ml:</td>
            <td class="text-right">{{$mineral}}</td>
        </tr>
        <tr>
            <td>Aqua 600ml:</td>
            <td class="text-right">{{$aquab}}</td>
        </tr>
        <tr>
            <td>Aqua 220ml:</td>
            <td class="text-right">{{$aquag}}</td>
        </tr>
        <tr>
            <td>Teh Pucuk Harum 350ml:</td>
            <td class="text-right">{{$tehpucuk}}</td>
        </tr><tr>
            <td>Teh Botol 250ml:</td>
            <td class="text-right">{{$tehbotol}}</td>
        </tr>
        <tr>
            <td>Dimsum:</td>
            <td class="text-right">{{$dimsum}}</td>
        </tr>
        <tr>
            <td>Box:</td>
            <td class="text-right">{{$box}}</td>
        </tr>
        <tr>
            <td>Kepala Bebek:</td>
            <td class="text-right">{{$kepala}}</td>
        </tr>
        <tr>
            <td>Total Terjual:</td>
            <td class="text-right">{{$total}}</td>
        </tr>
        
    </table>

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