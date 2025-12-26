import $ from 'jquery';

$(() => {

      $(document).on('click','a.result_map',function(e){
            e.preventDefault();

            var trId =  $(this).closest('tr').attr('id');

            if ($('.tr_map_xhr').length)
            {
                if ($('.'+trId).length)
                {
                    $(".tr_map_xhr").remove();
                    return;
                }
                else
                {
                    $(".tr_map_xhr").remove();
                }
            }

            var url = $(this).attr('href').replace('/map', '/map-data');

            // Vytvoření řádku s mapou - použití unikátního ID pro každou mapu
            var mapId = 'm_' + trId;
            var mapHeight = $(window).width() < 640 ? '200px' : '400px';
            var colspan = $(window).width() < 640 ? '6' : '9';
            var closeButtonStyle = $(window).width() < 640
                ? 'left:5px;top:5px'
                : 'right:100px;top:17px';

            var novyRadek = $('<tr class="tr_map_xhr '+ trId +'" style="position:relative"><td class="text-center" colspan="' + colspan + '"><div id="close_map" style="padding:2px 10px;border:2px solid black;background:white;position:absolute;' + closeButtonStyle + ';z-index:1000;cursor:pointer">Zavřít mapu</div><div id="' + mapId + '" style="height:' + mapHeight + '"></div></td></tr>');

            if($(window).width() < 640) {
                $("#result_table_sm #"+trId).after(novyRadek);
            } else {
                $("#result_table #"+trId).after(novyRadek);
            }

            // Načtení dat a vytvoření mapy
            $.getJSON(url, function(response) {
                // Parsování GPX dat z odpovědi
                var gpxData = zacatek + response + konec;
                var parser = new DOMParser();
                var xmlDoc = parser.parseFromString(gpxData, "text/xml");

                // Získání GPS bodů
                var trackPoints = xmlDoc.getElementsByTagName('trkpt');
                var coordinates = [];

                for (var i = 0; i < trackPoints.length; i++) {
                    var lat = parseFloat(trackPoints[i].getAttribute('lat'));
                    var lon = parseFloat(trackPoints[i].getAttribute('lon'));
                    coordinates.push([lat, lon]);
                }

                if (coordinates.length > 0) {
                    // Vytvoření Leaflet mapy s unikátním ID
                    var map = L.map(mapId).setView(coordinates[0], 13);

                    // Přidání OpenStreetMap tile layer
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(map);

                    // Vytvoření polyline z GPS bodů
                    var polyline = L.polyline(coordinates, {
                        color: 'red',
                        weight: 3,
                        opacity: 0.7
                    }).addTo(map);

                    // Automatické přizpůsobení pohledu na trasu
                    map.fitBounds(polyline.getBounds());

                    // Přidání markerů na začátek a konec
                    L.marker(coordinates[0]).addTo(map)
                        .bindPopup('Start');
                    L.marker(coordinates[coordinates.length - 1]).addTo(map)
                        .bindPopup('Cíl');
                }
            }).fail(function(xhr) {
                console.error('Chyba při načítání dat mapy:', xhr);
            });
      });



      $(document).on('click', '#close_map', function()
      {
            $('.tr_map_xhr').remove();
      });




var zacatek = '<?xml version="1.0" encoding="UTF-8"?><gpx><trk><trkseg>';

var konec = '</trkseg></trk></gpx>';

















$(document).on('click', 'a[href*="/event/result/"]', function(e) {

    e.preventDefault(); // Zabraňte výchozímu chování

    var hrefValue = $(this).attr('href'); // Získání hodnoty atributu href

    var userId = hrefValue.match(/result\/(\d+)/)[1]; // Získání čísla za slovem "result" a za lomítkem


    var trId =  $(this).closest('tr').attr('id');



    $(".tr_map_xhr").remove();



    if($('[id^="dynamic_result_individual_"]').length > 0)
    {

        if ($('.user_' + userId).length)
        {
            $('[id^="dynamic_result_individual_"]').remove();

            return;
        }
        else
        {
            $('[id^="dynamic_result_individual_"]').remove();
        }




       // return;
    }


    $(document).on('click', '[id^="dynamic_result_individual_"]', function(e) {

        //alert();

        //   $('[id^="dynamic_result_individual_"]').remove()

        // return;
    })







    $.getJSON($(this).attr('href'), function(response) {

        var str = "";

        for (var key in response) {

            if (response.hasOwnProperty(key)) {

                var value = response[key];

                str += '<tr id="dynamic_result_individual_'+ key +'" class="dynamic_result_individual_xhr user_'+ userId  +' bg-red-100 text-blue-700">';

                if($(window).width() < 640)
                {
                    str += '<td class="border" colspan="2"></td>';
                    
                    str += '<td class="border px-2 text-center">'+response[key].date+'</td>'

                    str += '<td class="border text-center"><a class="result_map result_map_xhr flex justify-center" href="/result/'+ response[key].id +'/map"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="blue" class="w-6 h-6"><path fill-rule="evenodd" d="m7.539 14.841.003.003.002.002a.755.755 0 0 0 .912 0l.002-.002.003-.003.012-.009a5.57 5.57 0 0 0 .19-.153 15.588 15.588 0 0 0 2.046-2.082c1.101-1.362 2.291-3.342 2.291-5.597A5 5 0 0 0 3 7c0 2.255 1.19 4.235 2.292 5.597a15.591 15.591 0 0 0 2.046 2.082 8.916 8.916 0 0 0 .189.153l.012.01ZM8 8.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" clip-rule="evenodd" /></svg></a></td>';

                    str += '<td class="border text-center">'+response[key].finish_time+'</td>';

                    str += '<td class="border text-center">'+response[key].pace_km+'</td>';
    
                }
                else
                {
                    str += '<td class="border" colspan="4"></td>';
                    
                    str += '<td class="border px-2 text-center">'+response[key].date+'</td>'

                    str += '<td class="border text-center"><a class="result_map result_map_xhr flex justify-center" href="/result/'+ response[key].id +'/map"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="blue" class="w-6 h-6"><path fill-rule="evenodd" d="m7.539 14.841.003.003.002.002a.755.755 0 0 0 .912 0l.002-.002.003-.003.012-.009a5.57 5.57 0 0 0 .19-.153 15.588 15.588 0 0 0 2.046-2.082c1.101-1.362 2.291-3.342 2.291-5.597A5 5 0 0 0 3 7c0 2.255 1.19 4.235 2.292 5.597a15.591 15.591 0 0 0 2.046 2.082 8.916 8.916 0 0 0 .189.153l.012.01ZM8 8.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" clip-rule="evenodd" /></svg></a></td>';

                    str += '<td class="border text-center">'+response[key].pace_km+'</td>';
    
                    str += '<td class="border text-center">'+response[key].finish_time+'</td>';
    
                    str += '<td class="border text-center"></td>';
                }

               

            

                str += '</tr>';

            }
        }





        if($(window).width() < 640)
        {
           $("#result_table_sm #"+trId).after(str);

        }
        else
        {
            $("#result_table #"+trId).after(str);

        }

    }).fail(function(xhr) {
        // Zpracování chyb
        console.error(xhr);
    });
  });










});
