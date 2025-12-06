<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden sm:rounded-lg border p-8 border-blue-300 shadow-lg">

                <!-- Nadpis -->
                <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Dokončení daru</h2>
                <p class="text-center text-gray-600 mb-8">
                    Vybrali jste částku: <strong class="text-2xl text-blue-600">{{ $amount }} Kč</strong>
                </p>

                <!-- Formulář pro údaje dárce -->
                <form id="donor-form" class="mb-8">
                    <div class="bg-gray-50 rounded-lg p-6 space-y-4">
                        <h3 class="font-semibold text-lg mb-4">Vaše údaje (volitelné)</h3>

                        <div>
                            <label for="donor-name" class="block text-sm font-medium text-gray-700 mb-1">Jméno a příjmení</label>
                            <input
                                type="text"
                                id="donor-name"
                                name="donor_name"
                                placeholder="Jan Novák"
                                maxlength="255"
                                class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="donor-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                id="donor-email"
                                name="donor_email"
                                placeholder="jan.novak@example.com"
                                class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            <p class="text-xs text-gray-500 mt-1">Pro zaslání potvrzení o platbě</p>
                        </div>
                    </div>
                </form>

                <!-- Výběr platební metody -->
                <div>
                    <h3 class="font-semibold text-lg mb-4 text-center">Vyberte způsob platby</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                        <!-- Platba kartou -->
                        <button
                            onclick="selectPaymentMethod('card')"
                            class="payment-method-btn flex flex-col items-center justify-center p-8 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all cursor-pointer"
                        >
                            <svg class="w-20 h-20 text-blue-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            <span class="font-semibold text-xl">Platební karta</span>
                            <span class="text-sm text-gray-500 mt-2">Visa, Mastercard</span>
                        </button>

                        <!-- Google Pay -->
                        <button
                            onclick="selectPaymentMethod('googlepay')"
                            class="payment-method-btn flex flex-col items-center justify-center p-8 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all cursor-pointer"
                        >
                            <svg class="w-20 h-20 mb-4" viewBox="0 0 48 48" fill="none">
                                <path d="M24 9.5c-7.72 0-14 6.28-14 14s6.28 14 14 14c7.72 0 14-6.28 14-14s-6.28-14-14-14zm0 25.2c-6.18 0-11.2-5.02-11.2-11.2S17.82 12.3 24 12.3s11.2 5.02 11.2 11.2-5.02 11.2-11.2 11.2z" fill="#EA4335"/>
                                <path d="M29.87 23.5h-2.24v2.24h-7v-2.24h-2.24v-7h2.24v-2.24h7v2.24h2.24v7z" fill="#34A853"/>
                                <path d="M20.63 16.26h7v2.24h-7v-2.24z" fill="#4285F4"/>
                                <path d="M20.63 25.74h7v2.24h-7v-2.24z" fill="#FBBC04"/>
                            </svg>
                            <span class="font-semibold text-xl">Google Pay</span>
                            <span class="text-sm text-gray-500 mt-2">Rychlá platba</span>
                        </button>
                    </div>
                </div>

                <!-- Info box -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">
                    <p class="mb-2">
                        <strong>Bezpečná platba:</strong> Platby kartou a Google Pay jsou zpracovávány přes Stripe.
                    </p>
                    <p>
                        <strong>Transparentnost:</strong> Kromě nezbytných poplatků platebnímu operátorovi jdou veškeré prostředky na účet <strong>2101782768/2010</strong> organizace Dům pro Julii.
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script>
        const amount = {{ $amount }};

        function selectPaymentMethod(method) {
            const donorName = document.getElementById('donor-name').value;
            const donorEmail = document.getElementById('donor-email').value;

            // Build query string
            const params = new URLSearchParams({
                amount: amount,
                donor_name: donorName,
                donor_email: donorEmail,
            });

            if (method === 'card') {
                // Redirect to card payment page
                window.location.href = `/donation/pay-card?${params.toString()}`;
            } else if (method === 'googlepay') {
                // Redirect to Google Pay page
                window.location.href = `/donation/pay-googlepay?${params.toString()}`;
            }
        }
    </script>
</x-app-layout>
