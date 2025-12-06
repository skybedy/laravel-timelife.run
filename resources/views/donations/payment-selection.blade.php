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
                        <h3 class="font-semibold text-lg mb-4">Vaše údaje</h3>

                        <div>
                            <label for="donor-name" class="block text-sm font-medium text-gray-700 mb-1">Jméno a příjmení</label>
                            <input
                                type="text"
                                id="donor-name"
                                name="donor_name"
                                placeholder="Jan Novák"
                                maxlength="255"
                                class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                            @error('donor_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="donor-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                id="donor-email"
                                name="donor_email"
                                placeholder="jan.novak@example.com"
                                class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                            @error('donor_email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1"></p>
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
                            class="payment-method-btn flex flex-col items-center justify-center p-8 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all cursor-pointer group"
                        >
                            <div class="flex items-center gap-4 mb-4">
                                <!-- Visa -->
                                <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" alt="Visa" class="h-10">
                                <!-- Mastercard -->
                                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" class="h-10">
                            </div>
                            <span class="font-semibold text-xl">Platební karta</span>
                            <span class="text-sm text-gray-500 mt-2">Visa, Mastercard</span>
                        </button>

                        <!-- Google Pay -->
                        <button
                            onclick="selectPaymentMethod('googlepay')"
                            class="payment-method-btn flex flex-col items-center justify-center p-8 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all cursor-pointer group"
                        >
                            <img src="https://upload.wikimedia.org/wikipedia/commons/f/f2/Google_Pay_Logo.svg" alt="Google Pay" class="h-12 mb-4">
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
                    <p class="mb-2">
                        <strong>Proč Stripe?</strong> Na webu používáme Stripe, protože jako jedna z mála platebních služeb umožňuje, aby platby kartou, Google Pay a další šly přímo na účet příjemce, aniž by procházely přes můj účet jako provozovatele, nebo Jitčin účet jako hlavní aktérky a zároveň nabízí jednoduchou implementaci bez zbytečných administrativních podmínek, které běžně vyžadují některé české platební brány.
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
            const form = document.getElementById('donor-form');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

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
