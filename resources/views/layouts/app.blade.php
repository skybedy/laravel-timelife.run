<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>LifeRun @yield('title')</title>

        <!-- Google tag (gtag.js) -->
        @if(app()->environment('production') && config('services.google_analytics.id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', '{{ config('services.google_analytics.id') }}');
        </script>
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
              crossorigin=""/>

        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                crossorigin=""></script>

        <script src="https://js.stripe.com/v3/"></script>

        <style>
            #m {
                width: 100%;
                height: 100%;
            }

            input[type=file] {
  widh: 100%;
  mx-width: 100%;
  color: #444;
  padding: 5px;
  background: #fff;
  border-radius: 6px;
  bordr: 1px solid blue;
}

input[type=file]::file-selector-button {
  margin-right: 20px;
  border: none;
  background: #084cdf;
  padding: 4px 20px;
  border-radius: 6px;
  color: #fff;
  cursor: pointer;
  transition: background .2s ease-in-out;
}

input[type=file]::file-selector-button:hover {
  background: #0d45a5;
}
        </style>



    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100">
            @include('layouts.navigation')


            <!-- Page Content -->
            <main>
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
            @include('layouts.footer')
        </div>

        @stack('scripts')
    </body>
</html>
