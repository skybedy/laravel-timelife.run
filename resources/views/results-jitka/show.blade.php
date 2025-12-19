@inject('carbon', 'Carbon\Carbon')

@php
    $dateFormatted = date('d.m.Y', strtotime($result->finish_time_date));
    $finishTime = substr($result->finish_time, 1);
    
    $title = "Půlmaraton #{$raceNumber}/{$totalRaces} - Jitka Dvořáčková";
    $description = "Datum: {$dateFormatted} | Čas: {$finishTime} | Tempo: {$result->pace_km}/km";
    
    // Titulek a popis pro sociální sítě
    $socialTitle = "Jitka Dvořáčková, 1/2maraton #{$raceNumber} | Čas: {$finishTime}";
    $socialDescription = "100 1/2maratonů za 100 dní pro dětský hospic Dům pro Julii";
    

    // DŮLEŽITÉ: Aby Facebook načetl správný obrázek, musí URL ukazovat na tuto stránku
    $currentUrl = url()->current();

    // DŮLEŽITÉ: Přidání času (?v=...) donutí FB stáhnout obrázek znovu a nebrat starý z paměti
    $ogImageUrl = request()->getSchemeAndHttpHost() . route('results-jitka.og-image', $result->id, false) . '?v=' . time();
@endphp

@section('title', "| {$title}")

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:title" content="{{ $socialTitle }}">
    <meta property="og:description" content="{{ $socialDescription }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="LifeRun.cz">


    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-app-layout>
        <div class="pb-5">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">
                    <div class="text-center">
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4 mb-4">
                            <a href="{{ route('results-jitka.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Zpět na všechny výsledky
                            </a>
                            <span class="hidden sm:inline text-gray-400">|</span>
                            <a href="{{ route('index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Hlavní stránka
                            </a>
                        </div>

                        <h1 class="text-4xl sm:text-5xl font-black text-gray-900 mb-4">
                            Půlmaraton #{{ $raceNumber }}/100
                        </h1>
                        <p class="text-2xl text-gray-700 mb-2">
                            Jitka Dvořáčková
                        </p>
                        <p class="text-lg text-gray-600">
                            100 půlmaratonů za 100 dní
                        </p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <div class="bg-gray-700 rounded-xl p-6 text-white text-center">
                            <div class="flex items-center justify-center gap-2 mb-2">
                                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <div class="font-bold text-sm">Datum</div>
                            </div>
                            <div class="text-3xl font-black">{{ $dateFormatted }}</div>
                        </div>

                        <div class="bg-gray-700 rounded-xl p-6 text-white text-center">
                            <div class="flex items-center justify-center gap-2 mb-2">
                                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="font-bold text-sm">Čas</div>
                            </div>
                            <div class="text-3xl font-black">{{ $finishTime }}</div>
                        </div>

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

                    <div class="mt-8 text-center">
                        <a href="{{ route('result.map', $result->id) }}" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-6 h-6">
                                <path fill-rule="evenodd" d="m7.539 14.841.003.003.002.002a.755.755 0 0 0 .912 0l.002-.002.003-.003.012-.009a5.57 5.57 0 0 0 .19-.153 15.588 15.588 0 0 0 2.046-2.082c1.101-1.362 2.291-3.342 2.291-5.597A5 5 0 0 0 3 7c0 2.255 1.19 4.235 2.292 5.597a15.591 15.591 0 0 0 2.046 2.082 8.916 8.916 0 0 0 .189.153l.012.01ZM8 8.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" clip-rule="evenodd" />
                            </svg>
                            Zobrazit mapu
                        </a>
                    </div>
                </div>

                <!-- Box s platbou -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Příspěvek na dětský hospic Dům pro Julii</h2>

                    <!-- Šedý box s textem a formulářem -->
                    <div class="bg-gray-700 rounded-2xl p-6 sm:p-8">

                        <!-- Motivační text -->
                        <div class="text-white text-lg sm:text-xl leading-relaxed mb-6 space-y-4">
                            <p class="flex items-start gap-3">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-red-400 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                                <span>I ta nejmenší částka má svůj dopad, počet běžců v různých českých FB skupinách je více než 100.000 lidí a pokud by jen cca 1% z nás věnovalo zcela zanedbatelných 50 Kč, Jitka vybere prostřednictvím tohoto kanálu okolo 50.000 Kč na věc, jejíž existenci si pravděpodobně většina z nás nechce ani příliš představovat.</span>
                            </p>

                            <!-- Oddělení mezi odstavci -->
                            <div class="border-t border-white/20 my-4"></div>

                            <p class="flex items-start gap-3">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-green-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span>Kromě nezbytných poplatků platebnímu operátorovi jdou veškeré prostředky jeho prostřednictvím na účet <strong>2101782768/2010</strong> organizace Dům pro Julii, a jak hlavní protagonistka Jitka Dvořáčková, tak provozovatel této webové stránky si z ní nenechávají ani haléř.</span>
                            </p>
                        </div>

                        <!-- Oddělení -->
                        <div class="border-t border-white/30 my-6"></div>

                        <!-- Platební formulář - Přizpůsobený pro užší stránku -->
                        <div class="w-full">
                            <form action="{{ route('donation.payment-selection') }}" method="GET">
                                <div class="flex flex-col items-center gap-4">
                                    <!-- Ikona + Label -->
                                    <div class="flex items-center gap-2">
                                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        <label class="text-xl sm:text-2xl text-white font-bold whitespace-nowrap">Chci přispět:</label>
                                    </div>

                                    <!-- Input -->
                                    <input
                                        type="number"
                                        name="amount"
                                        min="50"
                                        step="1"
                                        placeholder="Min. 50 Kč"
                                        required
                                        class="w-full max-w-xs px-6 py-4 rounded-lg text-gray-900 font-bold text-2xl focus:outline-none focus:ring-2 focus:ring-white text-center"
                                    >

                                    <!-- Payment Icons -->
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        <!-- Stripe Badge -->
                                        <div class="bg-white rounded h-10 px-3 flex items-center justify-center">
                                            <svg class="h-5 w-auto" viewBox="0 0 60 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.10 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.36 0 2.72.2 4.09.75v3.88a9.23 9.23 0 0 0-4.1-1.06c-.86 0-1.44.25-1.44.9 0 1.85 6.29.97 6.29 5.88z" fill="#374151"/>
                                            </svg>
                                        </div>

                                        <!-- Visa/Card Badge -->
                                        <div class="bg-white rounded h-10 px-2 flex items-center justify-center">
                                            <img src="{{ asset('visa.png') }}" alt="Visa" class="h-7 w-auto">
                                        </div>

                                        <!-- Google Pay Badge -->
                                        <div class="bg-white rounded h-10 px-1 flex items-center justify-center">
                                            <img src="{{ asset('gpay-icon.png') }}" alt="Google Pay" class="h-8 w-auto">
                                        </div>

                                        <!-- Apple Pay Badge -->
                                        <div class="bg-white rounded h-10 flex items-center justify-center px-2">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b0/Apple_Pay_logo.svg" alt="Apple Pay" class="h-6 w-auto">
                                        </div>
                                    </div>

                                    <!-- Tlačítko -->
                                    <button
                                        type="submit"
                                        class="w-full max-w-xs bg-white text-gray-500 font-bold rounded-lg hover:bg-gray-100 transition-colors text-xl px-8 py-4"
                                    >
                                        Odeslat
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 text-center">Sdílet výsledek</h2>

                    <div class="flex flex-wrap justify-center gap-4">
                        <button onclick="shareOnFacebook('{{ $currentUrl }}')"
                                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Sdílet na Facebooku
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </x-app-layout>

    <script>
        function shareOnFacebook(url) {
            // Facebook Share Dialog - podporuje sdílení do skupin
            const shareUrl = 'https://www.facebook.com/dialog/share?' +
                'app_id={{ config('services.facebook.app_id', '1103768987825478') }}&' +
                'display=popup&' +
                'href=' + encodeURIComponent(url) +
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
    </script>
</body>
</html>