@inject('carbon', 'Carbon\Carbon')

@section('title', '| Výsledky')

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Výsledky Jitky Dvořáčkové - TimeLife.run</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<x-app-layout>
    <div class="pb-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Nadpis stránky -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">
                <h1 class="text-4xl sm:text-5xl font-black text-gray-900 text-center mb-6">
                    Výsledky Jitky Dvořáčkové
                </h1>
                <p class="text-xl text-center text-gray-700 mb-4">
                    100 půlmaratonů za 100 dní
                </p>
            </div>

            <!-- Statistiky -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                    <!-- Počet půlmaratonů -->
                    <div class="bg-gray-700 rounded-xl p-6 text-white text-center">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            <div class="font-bold text-sm">Počet půlmaratonů</div>
                        </div>
                        <div class="text-3xl font-black">{{ $totalRaces }}/100</div>
                    </div>

                    <!-- Celková vzdálenost -->
                    <div class="bg-gray-700 rounded-xl p-6 text-white text-center">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <div class="font-bold text-sm">Celková vzdálenost</div>
                        </div>
                        <div class="text-3xl font-black">{{ $totalKm }} km</div>
                    </div>

                    <!-- Celkový čas -->
                    <div class="bg-gray-700 rounded-xl p-6 text-white text-center">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="font-bold text-sm">Celkový čas</div>
                        </div>
                        <div class="text-3xl font-black">{{ $totalTime }}</div>
                    </div>

                    <!-- Průměrné tempo -->
                    <div class="bg-gray-700 rounded-xl p-6 text-white text-center">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <div class="font-bold text-sm">Průměrné tempo</div>
                        </div>
                        <div class="text-3xl font-black">{{ $avgPace }}/km</div>
                    </div>

                </div>
            </div>

            <!-- Tabulka s výsledky -->
            <div id="vysledky" class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-8 mb-8 p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Detailní výsledky</h2>

                <div class="overflow-auto">
                    <!-- Desktop tabulka -->
                    <table id="result_table" class="hidden md:table table-auto border-collapse w-full">
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th class="border-none text-center px-4 py-3">Datum</th>
                                <th class="border-none text-center px-4 py-3">Místo/Mapa</th>
                                <th class="border-none text-center px-4 py-3">Tempo</th>
                                <th class="border-none text-center px-4 py-3">Čas</th>
                                <th class="border-none text-center px-4 py-3">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($results as $result)
                                <tr class="odd:bg-gray-100 even:bg-white hover:bg-gray-200 transition-colors" id="result_{{ $result->id }}">
                                    <td class="border text-center px-4 py-3">{{ $result->date }}</td>
                                    <td class="border px-4 py-3">
                                        <a class="result_map flex justify-center" href="{{ route('result.map',$result->id) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="red" class="w-6 h-6">
                                                <path fill-rule="evenodd" d="m7.539 14.841.003.003.002.002a.755.755 0 0 0 .912 0l.002-.002.003-.003.012-.009a5.57 5.57 0 0 0 .19-.153 15.588 15.588 0 0 0 2.046-2.082c1.101-1.362 2.291-3.342 2.291-5.597A5 5 0 0 0 3 7c0 2.255 1.19 4.235 2.292 5.597a15.591 15.591 0 0 0 2.046 2.082 8.916 8.916 0 0 0 .189.153l.012.01ZM8 8.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    </td>
                                    <td class="border text-center px-4 py-3">{{ $result->pace_km }}</td>
                                    <td class="border text-center px-4 py-3 font-semibold">{{ $result->finish_time }}</td>
                                    <td class="border px-4 py-3">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('results-jitka.show', $result->id) }}"
                                               class="text-gray-600 hover:text-gray-900 transition-colors"
                                               title="Zobrazit detail">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <button onclick="shareResultOnFacebook({{ $result->id }})"
                                                    class="text-blue-600 hover:text-blue-800 transition-colors"
                                                    title="Sdílet na Facebooku">
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Mobile tabulka -->
                    <table id="result_table_sm" class="md:hidden table-auto border-collapse w-full text-sm">
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th class="border-none text-center px-2 py-2">Datum</th>
                                <th class="border-none text-center px-2 py-2">Map</th>
                                <th class="border-none text-center px-2 py-2">Tempo</th>
                                <th class="border-none text-center px-2 py-2">Čas</th>
                                <th class="border-none text-center px-2 py-2">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($results as $result)
                                <tr class="odd:bg-gray-100 even:bg-white" id="result_{{ $result->id }}">
                                    <td class="border text-center px-2 py-2">{{ $result->date }}</td>
                                    <td class="border px-2 py-2">
                                        <a class="result_map flex justify-center" href="{{ route('result.map', $result->id) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="red" class="w-6 h-6">
                                                <path fill-rule="evenodd" d="m7.539 14.841.003.003.002.002a.755.755 0 0 0 .912 0l.002-.002.003-.003.012-.009a5.57 5.57 0 0 0 .19-.153 15.588 15.588 0 0 0 2.046-2.082c1.101-1.362 2.291-3.342 2.291-5.597A5 5 0 0 0 3 7c0 2.255 1.19 4.235 2.292 5.597a15.591 15.591 0 0 0 2.046 2.082 8.916 8.916 0 0 0 .189.153l.012.01ZM8 8.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    </td>
                                    <td class="border text-center px-2 py-2">{{ $result->pace_km }}</td>
                                    <td class="border text-center px-2 py-2">{{ $result->finish_time }}</td>
                                    <td class="border px-2 py-2">
                                        <div class="flex justify-center gap-1">
                                            <a href="{{ route('results-jitka.show', $result->id) }}"
                                               class="text-gray-600"
                                               title="Detail">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <button onclick="shareResultOnFacebook({{ $result->id }})"
                                                    class="text-blue-600"
                                                    title="Facebook">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<script>
    function shareResultOnFacebook(resultId) {
        const url = '{{ url('results-jitka') }}/' + resultId;

        // Facebook Sharer - starší API bez nutnosti app_id
        // Nevyžaduje registrovanou doménu, funguje i na localhost
        const shareUrl = 'https://www.facebook.com/sharer/sharer.php?' +
            'u=' + encodeURIComponent(url) +
            '&display=popup';

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
