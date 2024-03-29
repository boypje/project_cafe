<header class="main-header">
    <!-- Logo -->
    <div class="logo">
        <!-- Mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>POS</b></span>
        <!-- Logo for regular state and mobile devices -->
        <span class="logo-lg"><b>Altari POS</b></span>
    </div>
    <!-- Header Navbar: Style can be found in header.less -->
    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button -->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- User Account: Style can be found in dropdown.less -->
                @if (auth()->user()->level == 1)
                    <li>
                        <a href="{{ route('setting.index') }}"><i class="fa fa-gears"></i></a>
                    </li>
                @endif
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ url(auth()->user()->foto ?? '') }}" class="user-image img-profil" alt="User Image">
                        <span class="hidden-xs">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="{{ url(auth()->user()->foto ?? '') }}" class="img-circle img-profil"
                                alt="User Image">
                            <p>
                                {{ auth()->user()->name }} - {{ auth()->user()->email }}
                            </p>
                        </li>
                        <!-- Menu Footer -->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="{{ route('user.profil') }}" class="btn btn-default btn-flat">Profil</a>
                            </div>
                            <div class="pull-right">
                                <a href="#" class="btn btn-default btn-flat" onclick="$('#logout-form').submit()">Keluar</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>

<form action="{{ route('logout') }}" method="post" id="logout-form" style="display: none;">
    @csrf
</form>

<style>
    .main-header .user-menu .user-image {
        border: 2px solid white;
    }
</style>
