@extends('layouts.auth')

@section('login')
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/util.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/AdminLTE.min.css') }}">
</head>
<body>
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <form action="{{ route('login') }}" method="post" class="login100-form validate-form p-l-55 p-r-55 p-t-178">
                @csrf
                    <span class="login100-form-title">
                        Altari POS
                    </span>

                    <div class="wrap-input100 validate-input m-b-16" data-validate="Please enter username">
                        <input class="input100" type="email" name="email" placeholder="Username" onfocus="handleFocus('user-icon')" onblur="handleBlur('user-icon')">
                        <span class="focus-input100"></span>
                        <i class="fa fa-user fa-lg icon-right" id="user-icon"></i>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="Please enter password">
                        <input class="input100" type="password" name="password" placeholder="Password" onfocus="handleFocus('lock-icon')" onblur="handleBlur('lock-icon')">
                        <span class="focus-input100"></span>
                        <i class="fa fa-lock fa-lg icon-right" id="lock-icon"></i>
                    </div>

                    <div class="text-left p-t-13 p-b-23">
                    @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        @error('password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="container-login100-form-btn">
                        <button type="submit" class="login100-form-btn">
                            Masuk
                        </button>
                    </div>

                    <div class="flex-col-c p-t-120 p-b-40">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .icon-right {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            opacity: 30%;
        }
    </style>

    <script>
        function handleFocus(iconId) {
            const icon = document.getElementById(iconId);
            icon.style.opacity = '100%';
        }

        function handleBlur(iconId) {
            const icon = document.getElementById(iconId);
            icon.style.opacity = '30%';
        }
    </script>

    <!--===============================================================================================-->
    <script src="{{ asset('js/main.js') }}"></script>

</body>
@endsection
