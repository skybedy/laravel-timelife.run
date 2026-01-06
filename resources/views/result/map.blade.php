@inject('carbon', 'Carbon\Carbon')

@php
    $dateFormatted = date('d.m.Y', strtotime($result->finish_time_date));
    $finishTime = substr($result->finish_time, 1);
@endphp

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mapa trasy - {{ $dateFormatted }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>


</head>
<body>
    <x-app-layout>
        <div class="pb-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">
                    <div class="mb-4">
                        <button onclick="history.back()" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Zpět
                        </button>
                    </div>

                    <h1 class="text-3xl font-bold text-gray-900 mb-2 text-center">Mapa trasy</h1>
                    <p class="text-lg text-gray-600 mb-6 text-center">
                        {{ $dateFormatted }} | Čas: {{ $finishTime }} | Tempo: {{ $result->pace_km }}/km
                    </p>

                    <div id="map" class="rounded-lg shadow-lg"></div>
                </div>

            </div>
        </div>
    </x-app-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trackPoints = @json($trackPoints);

            if (trackPoints.length === 0) {
                document.getElementById('map').innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Žádná data trasy k zobrazení</div>';
                return;
            }

            // Převedeme data na formát pro Leaflet
            const coordinates = trackPoints.map(point => [
                parseFloat(point.latitude),
                parseFloat(point.longitude)
            ]);

            // Vytvoříme mapu s centrem na první souřadnici
            const map = L.map('map').setView(coordinates[0], 13);

            // Přidáme mapovou vrstvu (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);

            // Vytvoříme polyline (čáru) z všech bodů
            const polyline = L.polyline(coordinates, {
                color: 'red',
                weight: 4,
                opacity: 0.7
            }).addTo(map);

            // Přidáme značku na start (zelená)
            L.marker(coordinates[0], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map).bindPopup('Start');

            // Přidáme značku na cíl (červená)
            L.marker(coordinates[coordinates.length - 1], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map).bindPopup('Cíl');

            // Přizpůsobíme zoom, aby byla vidět celá trasa
            map.fitBounds(polyline.getBounds(), {
                padding: [50, 50]
            });
        });
    </script>
</body>
</html>
