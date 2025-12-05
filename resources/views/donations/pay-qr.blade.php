<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden sm:rounded-lg border p-8 border-blue-300 shadow-lg">

                <!-- Nadpis -->
                <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Platba QR kódem</h2>
                <p class="text-center text-gray-600 mb-8">
                    Částka: <strong class="text-2xl text-blue-600">{{ $amount }} Kč</strong>
                </p>

                <!-- Instrukce -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-semibold text-lg mb-2">Jak zaplatit:</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li>Otevřete mobilní aplikaci vaší banky</li>
                        <li>Vyberte funkci "Platba QR kódem" nebo "Naskenovat QR"</li>
                        <li>Nasměrujte kameru na QR kód níže</li>
                        <li>Zkontrolujte částku a potvrďte platbu</li>
                    </ol>
                </div>

                <!-- QR kód -->
                <div class="flex justify-center mb-6 bg-gray-50 p-8 rounded-lg">
                    <canvas id="qr-code"></canvas>
                </div>

                <!-- Údaje pro ruční platbu -->
                <div class="border-t pt-6 mt-6">
                    <h3 class="font-semibold text-lg mb-4 text-center">Údaje pro ruční platbu:</h3>
                    <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium">Číslo účtu:</span>
                            <span class="font-mono">2101782768/2010</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Název příjemce:</span>
                            <span>Dům pro Julii</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Částka:</span>
                            <span class="font-bold text-blue-600">{{ $amount }} Kč</span>
                        </div>
                        @if($donorName)
                        <div class="flex justify-between">
                            <span class="font-medium">Zpráva pro příjemce:</span>
                            <span>{{ $donorName }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Upozornění -->
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="font-semibold text-sm text-yellow-800 mb-1">Důležité upozornění:</p>
                            <p class="text-sm text-yellow-700">
                                Platba bankovním převodem může trvat 1-2 pracovní dny. Potvrzení o připsání platby
                                @if($donorEmail)
                                    vám bude zasláno na email <strong>{{ $donorEmail }}</strong>.
                                @else
                                    nebude zasláno, protože jste nezadali email.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Info box -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">
                    <p class="mb-2">
                        <strong>Transparentnost:</strong> Veškeré prostředky jdou přímo na účet <strong>2101782768/2010</strong> organizace Dům pro Julii.
                    </p>
                    <p>
                        <strong>Žádné poplatky:</strong> Při platbě převodem neplatíte žádné poplatky třetím stranám.
                    </p>
                </div>

                <!-- Back button -->
                <div class="mt-6 text-center">
                    <a href="javascript:history.back()" class="text-blue-600 hover:text-blue-800">
                        ← Zpět na výběr platby
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        console.log('QR Code script loaded');

        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, generating QR code...');
            generateQRCode();
        });

        function generateQRCode() {
            const amount = {{ $amount }};
            const accountNumber = '2101782768/2010';
            const message = '{{ $donorName ?? "Dar pro Jitku" }}';

            console.log('Amount:', amount);
            console.log('Account:', accountNumber);
            console.log('Message:', message);

            // Czech QR payment format (Short Payment Descriptor)
            // Format: SPD*1.0*ACC:<account>*AM:<amount>*CC:CZK*MSG:<message>
            const qrData = `SPD*1.0*ACC:${accountNumber}*AM:${amount}*CC:CZK*MSG:${encodeURIComponent(message)}`;
            console.log('QR Data:', qrData);

            const canvas = document.getElementById('qr-code');
            console.log('Canvas element:', canvas);

            if (!canvas) {
                console.error('Canvas element not found!');
                return;
            }

            if (typeof QRCode === 'undefined') {
                console.error('QRCode library not loaded!');
                canvas.parentElement.innerHTML = '<p class="text-red-600">QR kód knihovna se nenačetla. Zkuste obnovit stránku.</p>';
                return;
            }

            QRCode.toCanvas(canvas, qrData, {
                width: 300,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#ffffff'
                }
            }, (error) => {
                if (error) {
                    console.error('QR Code generation error:', error);
                    canvas.parentElement.innerHTML = '<p class="text-red-600">Nepodařilo se vygenerovat QR kód: ' + error.message + '</p>';
                } else {
                    console.log('QR Code generated successfully!');
                }
            });
        }
    </script>
</x-app-layout>
