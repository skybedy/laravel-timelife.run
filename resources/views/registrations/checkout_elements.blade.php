<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden sm:rounded-lg border p-6 border-blue-300 shadow-lg">

                <x-h2 style="style-1">Podpora: {{ $paymentRecipient->name }}</x-h2>

                <x-p style="style-1">
                    Děkujeme, že chcete podpořit tuto kampaň. Vyberte si způsob platby:
                </x-p>

                <!-- Tabs pro výběr platební metody -->
                <div class="mt-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button onclick="showTab('card')" id="tab-card" class="tab-button border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Platební karta
                            </button>
                            <button onclick="showTab('googlepay')" id="tab-googlepay" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Google Pay
                            </button>
                            <button onclick="showTab('bank')" id="tab-bank" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Bankovní převod (QR kód)
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Formulář pro částku a kontaktní údaje -->
                <div class="mt-6">
                    <form id="payment-form">
                        @csrf

                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                        <input type="hidden" name="payment_recipient_id" value="{{ $paymentRecipient->id }}">

                        <!-- Částka -->
                        <div class="mb-4">
                            <label for="amount" class="block text-sm font-medium text-gray-700">Částka (Kč)</label>
                            <input type="number"
                                   id="amount"
                                   name="amount"
                                   min="10"
                                   max="1000000"
                                   value="100"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Minimální částka: 10 Kč, Maximální: 1 000 000 Kč</p>
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="donor_email" class="block text-sm font-medium text-gray-700">Email (nepovinné)</label>
                            <input type="email"
                                   id="donor_email"
                                   name="donor_email"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Pro zaslání potvrzení o platbě</p>
                        </div>

                        <!-- Jméno -->
                        <div class="mb-4">
                            <label for="donor_name" class="block text-sm font-medium text-gray-700">Jméno (nepovinné)</label>
                            <input type="text"
                                   id="donor_name"
                                   name="donor_name"
                                   maxlength="255"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Tab content - Platební karta -->
                        <div id="content-card" class="tab-content">
                            <div class="bg-blue-50 p-6 rounded-lg mb-4">
                                <h3 class="font-semibold mb-4 text-lg">Platba kartou</h3>

                                <!-- Stripe Payment Element -->
                                <div id="payment-element" class="mb-4">
                                    <!-- Stripe.js injects the Payment Element here -->
                                </div>

                                <div id="payment-errors" class="text-red-600 text-sm mb-4" role="alert"></div>

                                <button type="submit"
                                        id="submit-button"
                                        class="w-full bg-gradient-to-b from-blue-400 to-blue-500 text-white font-bold py-3 px-6 rounded-md shadow-lg hover:from-blue-500 hover:to-blue-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span id="button-text">Zaplatit</span>
                                    <span id="spinner" class="hidden">Zpracovávám...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Tab content - Google Pay -->
                        <div id="content-googlepay" class="tab-content hidden">
                            <div class="bg-blue-50 p-6 rounded-lg mb-4">
                                <h3 class="font-semibold mb-4 text-lg">Google Pay</h3>

                                <!-- Google Pay button se zobrazí zde -->
                                <div id="google-pay-button" class="mb-4">
                                    <!-- Google Pay button will be inserted here -->
                                </div>

                                <div id="googlepay-errors" class="text-red-600 text-sm mb-4" role="alert"></div>
                            </div>
                        </div>

                        <!-- Tab content - QR kód -->
                        <div id="content-bank" class="tab-content hidden">
                            <div class="bg-blue-50 p-6 rounded-lg mb-4">
                                <h3 class="font-semibold mb-4 text-lg">Bankovní převod přes QR kód</h3>

                                <div class="bg-white p-4 rounded-lg">
                                    <p class="mb-4 text-sm text-gray-700">
                                        Naskenujte QR kód vaší bankovní aplikací a potvrďte platbu. QR kód obsahuje číslo účtu a částku.
                                    </p>

                                    <!-- QR kód se vygeneruje zde -->
                                    <div id="qr-code-container" class="flex justify-center mb-4">
                                        <canvas id="qr-code"></canvas>
                                    </div>

                                    <!-- Údaje pro manuální platbu -->
                                    <div class="border-t pt-4 mt-4">
                                        <h4 class="font-semibold mb-2">Údaje pro ruční platbu:</h4>
                                        <div class="text-sm space-y-1">
                                            <p><strong>Číslo účtu:</strong> {{ $paymentRecipient->account_number }}</p>
                                            <p><strong>Částka:</strong> <span id="bank-amount">100</span> Kč</p>
                                            @if($paymentRecipient->reference_number)
                                            <p><strong>Variabilní symbol:</strong> {{ $paymentRecipient->reference_number }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm">
                                        <strong>Upozornění:</strong> Platba bankovním převodem může trvat 1-2 pracovní dny.
                                        Po úspěšném připsání platby vám bude zaslán email s potvrzením (pokud jste vyplnili email).
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Info box -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">
                    <p class="mb-2">
                        <strong>Bezpečná platba:</strong> Platby jsou zpracovávány přes Stripe, certifikovaného poskytovatele platebních služeb.
                    </p>
                    <p>
                        <strong>Poplatky:</strong> Kromě nezbytných poplatků platebnímu operátorovi jdou veškeré prostředky na účet {{ $paymentRecipient->name }}.
                    </p>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>

    <!-- QR Code generator -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

    <script>
        // Inicializace Stripe
        const stripe = Stripe('{{ $stripeKey }}');
        let elements;
        let paymentElement;

        // Tab switching
        function showTab(tabName) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

            // Remove active styling from all tabs
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected content
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Add active styling to selected tab
            const activeTab = document.getElementById('tab-' + tabName);
            activeTab.classList.add('border-blue-500', 'text-blue-600');
            activeTab.classList.remove('border-transparent', 'text-gray-500');

            // Update QR code if bank tab is selected
            if (tabName === 'bank') {
                generateQRCode();
            }
        }

        // Initialize Stripe Elements when page loads
        document.addEventListener('DOMContentLoaded', async () => {
            await initializeStripeElements();
            initializeGooglePay();
            generateQRCode();

            // Update bank amount when amount changes
            document.getElementById('amount').addEventListener('input', (e) => {
                document.getElementById('bank-amount').textContent = e.target.value;
                generateQRCode();
            });
        });

        async function initializeStripeElements() {
            // Get amount
            const amount = document.getElementById('amount').value;

            // Create Payment Intent
            const response = await fetch('{{ route('registration.payment-intent.create') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                },
                body: JSON.stringify({
                    amount: parseInt(amount),
                    event_id: {{ $event->id }},
                    payment_recipient_id: {{ $paymentRecipient->id }},
                    donor_email: document.getElementById('donor_email').value,
                    donor_name: document.getElementById('donor_name').value
                })
            });

            const { clientSecret, error } = await response.json();

            if (error) {
                showError(error);
                return;
            }

            // Create Elements instance
            const appearance = {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#3b82f6',
                }
            };

            elements = stripe.elements({ clientSecret, appearance });

            // Create and mount the Payment Element
            paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');
        }

        function initializeGooglePay() {
            const googlePayButton = document.getElementById('google-pay-button');

            // Create Google Pay payment request
            const paymentRequest = stripe.paymentRequest({
                country: 'CZ',
                currency: 'czk',
                total: {
                    label: 'Příspěvek pro {{ $paymentRecipient->name }}',
                    amount: parseInt(document.getElementById('amount').value) * 100,
                },
                requestPayerName: true,
                requestPayerEmail: true,
            });

            // Check if Google Pay is available
            paymentRequest.canMakePayment().then((result) => {
                if (result && result.googlePay) {
                    const prButton = elements.create('paymentRequestButton', {
                        paymentRequest: paymentRequest,
                    });
                    prButton.mount('#google-pay-button');
                } else {
                    googlePayButton.innerHTML = '<p class="text-gray-500">Google Pay není k dispozici v tomto prohlížeči.</p>';
                }
            });

            // Handle payment
            paymentRequest.on('paymentmethod', async (ev) => {
                // Confirm the payment on the server
                const response = await fetch('{{ route('registration.payment.confirm') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                    },
                    body: JSON.stringify({
                        payment_method_id: ev.paymentMethod.id
                    })
                });

                const data = await response.json();

                if (data.error) {
                    ev.complete('fail');
                    showError(data.error, 'googlepay-errors');
                } else {
                    ev.complete('success');
                    window.location.href = '{{ route('payment.success') }}';
                }
            });
        }

        function generateQRCode() {
            const amount = document.getElementById('amount').value;
            const accountNumber = '{{ $paymentRecipient->account_number }}';
            @if($paymentRecipient->reference_number)
            const variableSymbol = '{{ $paymentRecipient->reference_number }}';
            @else
            const variableSymbol = '';
            @endif

            // Czech QR payment format (Short Payment Descriptor)
            // Format: SPD*1.0*ACC:CZ<account>*AM:<amount>*CC:CZK*VS:<vs>
            const qrData = `SPD*1.0*ACC:${accountNumber}*AM:${amount}*CC:CZK${variableSymbol ? '*VS:' + variableSymbol : ''}`;

            const canvas = document.getElementById('qr-code');
            QRCode.toCanvas(canvas, qrData, {
                width: 300,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#ffffff'
                }
            }, (error) => {
                if (error) console.error(error);
            });
        }

        // Handle form submission
        document.getElementById('payment-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            setLoading(true);

            const { error } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: '{{ route('payment.success') }}',
                },
            });

            if (error) {
                showError(error.message);
                setLoading(false);
            }
        });

        function showError(message, elementId = 'payment-errors') {
            const errorElement = document.getElementById(elementId);
            errorElement.textContent = message;
        }

        function setLoading(isLoading) {
            const submitButton = document.getElementById('submit-button');
            const buttonText = document.getElementById('button-text');
            const spinner = document.getElementById('spinner');

            if (isLoading) {
                submitButton.disabled = true;
                buttonText.classList.add('hidden');
                spinner.classList.remove('hidden');
            } else {
                submitButton.disabled = false;
                buttonText.classList.remove('hidden');
                spinner.classList.add('hidden');
            }
        }

        // Recreate Payment Intent when amount changes
        document.getElementById('amount').addEventListener('change', async () => {
            setLoading(true);
            await initializeStripeElements();
            setLoading(false);
        });
    </script>
    @endpush
</x-app-layout>
