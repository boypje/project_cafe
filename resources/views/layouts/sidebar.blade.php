<aside class="main-sidebar">
    <!-- Sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ url(auth()->user()->foto ?? '') }}" class="img-circle img-profil" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ auth()->user()->name }}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> On Service</a>
            </div>
        </div>

        <!-- Sidebar menu: style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree">
            <li>
                <a href="{{ route('dashboard') }}">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>

            @if (auth()->user()->level == 1)
                <li class="header">RESOURCE</li>

                <li>
                    <a href="{{ route('category.index') }}">
                        <i class="fa fa-tag"></i> <span>Kategori</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('produk.index') }}">
                        <i class="fa fa-cutlery"></i> <span>Menu</span>
                    </a>
                </li>

                <li class="header">TRANSAKSI</li>
                <li>
                    <a href="{{ route('pengeluaran.index') }}">
                        <i class="fa fa-money"></i> <span>Pengeluaran</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('penjualan.index') }}">
                        <i class="fa fa-upload"></i> <span>Penjualan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('transaksi.baru') }}">
                        <i class="fa fa-cart-arrow-down"></i> <span>Transaksi Baru</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('transaksi.index') }}">
                        <i class="fa fa-cart-arrow-down"></i> <span>Transaksi Aktif</span>
                    </a>
                </li>

                <li class="header">REPORT</li>
                <li>
                    <a href="{{ route('laporan.index') }}">
                        <i class="fa fa-file-pdf-o"></i> <span>Keuangan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('laporan_stok.index') }}">
                        <i class="fa fa-book"></i> <span>Penjualan Kasir</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('performa.index') }}">
                        <i class="fa fa-file-text"></i> <span>Performa Kasir</span>
                    </a>
                </li>
                <li class="header">SYSTEM</li>
                <li>
                    <a href="{{ route('user.index') }}">
                        <i class="fa fa-users"></i> <span>Kasir</span>
                    </a>
                </li>
            @else
                <li>
                    <a href="{{ route('produk.index') }}">
                        <i class="fa fa-cutlery"></i> <span>Menu</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('pengeluaran.index') }}">
                        <i class="fa fa-money"></i> <span>Pengeluaran</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('transaksi.baru') }}">
                        <i class="fa fa-cart-arrow-down"></i> <span>Transaksi Baru</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('transaksi.index') }}">
                        <i class="fa fa-cart-arrow-down"></i> <span>Transaksi Aktif</span>
                    </a>
                </li>
            @endif
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>
