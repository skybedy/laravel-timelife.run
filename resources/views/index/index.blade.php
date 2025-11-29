@inject('carbon', 'Carbon\Carbon')

@section('title', '| Hlavní strana')

<x-app-layout>
    <div class="smxxx:py-24 pb-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

       
                    <!-- Hlavní event box -->
                    <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-8 mb-8 p-8">

                        <!-- Vnitřní šedý box -->
                        <div class="bg-gray-700 sm:rounded-2xl overflow-hidden">

                            <!-- Fotka -->
                            <div class="w-full h-48 sm:h-64 md:h-80 lg:h-96 relative overflow-hidden sm:rounded-t-2xl">
                                <img src="{{ asset('images/jitka.png') }}"
                                     alt="{{ $events[0]->name }}"
                                     class="w-full h-full object-cover object-center shadow-inner">
                                <div class="absolute inset-0 shadow-inner pointer-events-none"></div>
                            </div>

                            <!-- Název závodu -->
                            <div class="text-white text-center font-black bg-gray-700 px-6 py-8">
                                <div class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl border-b border-white pb-4 mb-6">
                                    {{ $events[0]->name }}
                                </div>

                                @if($events[0]->second_name)
                                    <div class="text-2xl sm:text-3xl md:text-4xl mb-6 text-gray-300">
                                        {{ $events[0]->second_name }}
                                    </div>
                                @endif

                                <!-- Oddělení -->
                                <div class="border-t border-white my-8"></div>

                                <!-- Info údaje -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                                    <!-- Začátek běhání -->
                                    <div class="flex items-center gap-3 bg-transparent border-2 border-white rounded-xl px-4 py-3">
                                        <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <div class="text-lg sm:text-xl md:text-2xl text-white">
                                            <span class="font-bold">Začátek běhání:</span> <span class="font-extrabold">1.11.25</span>
                                        </div>
                                    </div>

                                    <!-- Konec běhání -->
                                    <div class="flex items-center gap-3 bg-transparent border-2 border-white rounded-xl px-4 py-3">
                                        <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <div class="text-lg sm:text-xl md:text-2xl text-white">
                                            <span class="font-bold">Konec běhání:</span> <span class="font-extrabold">2.2.2026</span>
                                        </div>
                                    </div>

                                    <!-- Počet půlmaratonů -->
                                    <div class="flex items-center gap-3 bg-transparent border-2 border-white rounded-xl px-4 py-3">
                                        <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                        </svg>
                                        <div class="text-lg sm:text-xl md:text-2xl text-white">
                                            <span class="font-bold">Počet půlmaratonů:</span> <span class="font-extrabold">{{ $totalRaces }}/100</span>
                                        </div>
                                    </div>

                                    <!-- Celkový počet km -->
                                    <div class="flex items-center gap-3 bg-transparent border-2 border-white rounded-xl px-4 py-3">
                                        <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                        <div class="text-lg sm:text-xl md:text-2xl text-white">
                                            <span class="font-bold">Celková vzdálenost:</span> <span class="font-extrabold">{{ $totalKm }} km</span>
                                        </div>
                                    </div>

                                    <!-- Celkový čas -->
                                    <div class="flex items-center gap-3 bg-transparent border-2 border-white rounded-xl px-4 py-3">
                                        <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div class="text-lg sm:text-xl md:text-2xl text-white">
                                            <span class="font-bold">Celkový čas:</span> <span class="font-extrabold">{{ $totalTime }}</span>
                                        </div>
                                    </div>

                                    <!-- Průměrné tempo -->
                                    <div class="flex items-center gap-3 bg-transparent border-2 border-white rounded-xl px-4 py-3">
                                        <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        <div class="text-lg sm:text-xl md:text-2xl text-white">
                                            <span class="font-bold">Průměrné tempo:</span> <span class="font-extrabold">{{ $avgPace }}/km</span>
                                        </div>
                                    </div>


                                    <!-- Stripe platba -->
                                    <div class="flex items-center gap-3 bg-transparent border-2 border-white rounded-xl px-4 py-3 hover:bg-white hover:bg-opacity-10 transition-all duration-300 cursor-pointer group">
                                        <svg class="w-6 h-6 text-white flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        <div class="text-lg sm:text-xl md:text-2xl text-white">
                                            <span class="font-bold">Podpořit:</span> <a class="" href="{{ route('registration.checkout.stripe.payment_recipient',['event_id' => 10,'payment_recipient' => 3]) }}">Platba</a>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
        

                <!-- Tabulka s výsledky -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-8 mb-8 p-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Výsledky</h2>

                    <div class="overflow-auto">
                        <!-- Desktop tabulka -->
                        <table id="result_table" class="hidden md:table table-auto border-collapse w-full">
                            <thead>
                                <tr class="bg-gray-700 text-white">
                                    <th class="border-none text-center px-4 py-3">Datum</th>
                                    <th class="border-none text-center px-4 py-3">Místo/Mapa</th>
                                    <th class="border-none text-center px-4 py-3">Tempo</th>
                                    <th class="border-none text-center px-4 py-3">Čas</th>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

        </div>
    </div>
</x-app-layout>
