@inject('carbon', 'Carbon\Carbon')

@php
    $dateFormatted = date('d.m.Y', strtotime($result->finish_time_date));
    $finishTime = substr($result->finish_time, 1);
    $title = "Půlmaraton #{$raceNumber}/{$totalRaces} - Jitka Dvořáčková";
    $description = "Datum: {$dateFormatted} | Čas: {$finishTime} | Tempo: {$result->pace_km}/km";
    $currentUrl = route('results-jitka.show', $result->id);
@endphp

@section('title', "| {$title}")

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    <!-- Open Graph Meta Tags pro Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:site_name" content="TimeLife.run">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-app-layout>
        <div class="pb-5">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <!-- Nadpis stránky -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">
                    <div class="text-center">
                        <a href="{{ route('results-jitka.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Zpět na všechny výsledky
                        </a>

                        <h1 class="text-4xl sm:text-5xl font-black text-gray-900 mb-4">
                            Půlmaraton #{{ $raceNumber }}/{{ $totalRaces }}
                        </h1>
                        <p class="text-2xl text-gray-700 mb-2">
                            Jitka Dvořáčková
                        </p>
                        <p class="text-lg text-gray-600">
                            100 půlmaratonů za 100 dní
                        </p>
                    </div>
                </div>

                <!-- Detaily výsledku -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Datum -->
                        <div class="bg-gray-700 rounded-xl p-6 text-white text-center">
                            <div class="flex items-center justify-center gap-2 mb-2">
                                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <div class="font-bold text-sm">Datum</div>
                            </div>
                            <div class="text-3xl font-black">{{ $dateFormatted }}</div>
                        </div>

                        <!-- Čas -->
                        <div class="bg-gray-700 rounded-xl p-6 text-white text-center">
                            <div class="flex items-center justify-center gap-2 mb-2">
                                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="font-bold text-sm">Čas</div>
                            </div>
                            <div class="text-3xl font-black">{{ $finishTime }}</div>
                        </div>

                        <!-- Tempo -->
                        <div class="bg-gray-700 rounded-xl p-6 text-white text-center">
                            <div class="flex items-center justify-center gap-2 mb-2">
                                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <div class="font-bold text-sm">Tempo</div>
                            </div>
                            <div class="text-3xl font-black">{{ $result->pace_km }}/km</div>
                        </div>

                    </div>

                    <!-- Mapa -->
                    <div class="mt-8 text-center">
                        <a href="{{ route('result.map', $result->id) }}" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-6 h-6">
                                <path fill-rule="evenodd" d="m7.539 14.841.003.003.002.002a.755.755 0 0 0 .912 0l.002-.002.003-.003.012-.009a5.57 5.57 0 0 0 .19-.153 15.588 15.588 0 0 0 2.046-2.082c1.101-1.362 2.291-3.342 2.291-5.597A5 5 0 0 0 3 7c0 2.255 1.19 4.235 2.292 5.597a15.591 15.591 0 0 0 2.046 2.082 8.916 8.916 0 0 0 .189.153l.012.01ZM8 8.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" clip-rule="evenodd" />
                            </svg>
                            Zobrazit mapu
                        </a>
                    </div>
                </div>

                <!-- Sdílení -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 text-center">Sdílet výsledek</h2>

                    <div class="flex flex-wrap justify-center gap-4">
                        <!-- Facebook Share Button -->
                        <button onclick="shareOnFacebook('{{ $currentUrl }}')"
                                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Sdílet na Facebooku
                        </button>

                        <!-- Twitter -->
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($title . ' - ' . $description) }}&url={{ urlencode($currentUrl) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                            Sdílet na Twitteru
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </x-app-layout>

    <script>
        function shareOnFacebook(url) {
            // Detekce mobilního zařízení
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

            if (isMobile) {
                // Na mobilu použijeme Share Dialog
                const shareUrl = 'https://www.facebook.com/dialog/share?' +
                    'app_id={{ config('services.facebook.app_id', '1103768987825478') }}&' +
                    'display=touch&' +
                    'href=' + encodeURIComponent(url) +
                    '&redirect_uri=' + encodeURIComponent(url);

                window.location.href = shareUrl;
            } else {
                // Na desktopu použijeme Feed Dialog - má více možností
                const shareUrl = 'https://www.facebook.com/dialog/feed?' +
                    'app_id={{ config('services.facebook.app_id', '1103768987825478') }}&' +
                    'display=popup&' +
                    'link=' + encodeURIComponent(url) +
                    '&redirect_uri=' + encodeURIComponent(url);

                // Otevře popup okno
                const width = 650;
                const height = 450;
                const left = (screen.width / 2) - (width / 2);
                const top = (screen.height / 2) - (height / 2);

                window.open(
                    shareUrl,
                    'facebook-share-dialog',
                    'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left
                );
            }
        }
    </script>
</body>
</html>
