<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">


        <title>Virtual Charity Run @yield('title')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">


        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script src="https://api.mapy.cz/loader.js"></script>
        <script type="text/javascript">Loader.load();</script>
        <script src="https://js.stripe.com/v3/"></script>
    </head>
    <body class="font-sans antialiased">

        <div class="min-h-screen flex flex-col bg-gradient-to-b from-sky-400 to-sky-600">
            @include('layouts.navigation')


            <!-- Page Content -->
            <main class="flex-grow px-3 sm:px-0">
                    @if (session('success'))
                        <x-flash-message type="success" :message="session('success')" />
                    @endif

                    @if (session('error'))
                        <x-flash-message type="error" :message="session('error')" />
                    @endif

                    @if (session('warning'))
                        <x-flash-message type="warning" :message="session('warning')" />
                    @endif

                    @if (session('info'))
                        <x-flash-message type="info" :message="session('info')" />
                    @endif


                {{ $slot }}
            </main>
             <!-- Footer -->
        <footer class="bg-[#fefdf9] text-gray-300 text-xl text-center  h-32 py-8 border-t border-orange-400">
            <div class="w-5/6 mx-auto flex justify-between text-xs sm:text-sm md:text:base lg:text-lg">
                <div><a href="">Privacy Policy</a> | <a href="/">Terms of Service</a> | <a href="/">Contact</a>
                </div>
                <img class="w-20 sm:w-48 h-auto" src="{{ asset('images/strava-logo.png') }}" alt="Strava">
                <div>
                <p class="mt-2">&copy; 2024 Virtual Charity Run. All rights reserved.</p>
                <p class="mt-2"><a href="mailto://virtual.run.cz@gmail.com">virtual.run.cz@gmail.com</a>, +042 776131313</p>
                </div>
            </div>
        </footer>

        </div>
      
    </body>
</html>
