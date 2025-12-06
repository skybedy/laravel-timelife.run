<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden sm:rounded-lg border p-8 border-blue-300 shadow-lg">

                <!-- Nadpis (Logo) -->
                <div class="flex items-center justify-center mb-6">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b0/Apple_Pay_logo.svg" alt="Apple Pay" class="h-12">
                </div>
                <p class="text-center text-gray-600 mb-8">
                    Částka: <strong class="text-2xl text-blue-600">{{ $amount }} Kč</strong>
                </p>

                <!-- Apple Pay Button -->
                <div class="mb-6">
                    <div id="apple-pay-button" style="min-height: 48px; min-width: 200px;">
                        <!-- Apple Pay button se zobrazí zde -->
                    </div>
                    <div id="applepay-errors" class="text-red-600 text-sm mt-4 text-center" role="alert"></div>
                    <div id="loading-message" class="text-gray-600 text-center mt-4">
                        Načítám Apple Pay...
                    </div>
                </div>

                <!-- Info -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">
                    <p class="mb-2">
                        <strong>Apple Pay:</strong> Rychlá a bezpečná platba pomocí vašeho Apple zařízení.
                    </p>
                    <p>
                        Pokud Apple Pay není k dispozici (např. nepoužíváte Safari nebo iOS), použijte prosím platbu kartou.
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

    @push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('{{ $stripeKey }}');
        let elements;

        document.addEventListener('DOMContentLoaded', async () => {
            await initializeApplePay();
        });

        async function initializeApplePay() {
            console.log('[APPLE PAY] Initializing...');

            // Create Payment Intent
            console.log('[APPLE PAY] Creating Payment Intent...');
            const response = await fetch('{{ route('registration.payment-intent.create') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    amount: {{ $amount }},
                    event_id: 10,
                    payment_recipient_id: 3,
                    donor_email: '{{ $donorEmail ?? '' }}',
                    donor_name: '{{ $donorName ?? '' }}',
                })
            });

            console.log('[APPLE PAY] Response status:', response.status);
            const { clientSecret, error } = await response.json();

            if (error) {
                console.error('[APPLE PAY] Payment Intent error:', error);
                showError(error);
                document.getElementById('loading-message').classList.add('hidden');
                return;
            }

            console.log('[APPLE PAY] Client secret received:', clientSecret ? 'YES' : 'NO');

            // Create Elements instance
            const appearance = {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#3b82f6',
                }
            };

            console.log('[APPLE PAY] Creating Elements...');
            elements = stripe.elements({ clientSecret, appearance });

            // Create Payment Request (Apple Pay)
            console.log('[APPLE PAY] Creating Payment Request...');
            const paymentRequest = stripe.paymentRequest({
                country: 'CZ',
                currency: 'czk',
                total: {
                    label: 'Celkem',
                    amount: {{ $amount }} * 100,
                },
                displayItems: [
                    {
                        label: 'Jitka Dvořáčková a 100 půlmaratonů za 100 dní',
                        amount: {{ $amount }} * 100,
                    }
                ],
                requestPayerName: true,
                requestPayerEmail: true,
            });

            console.log('[APPLE PAY] Creating Payment Request Button element...');
            const prButton = elements.create('paymentRequestButton', {
                paymentRequest: paymentRequest,
            });

            // Check availability
            console.log('[APPLE PAY] Checking if Apple Pay is available...');
            const result = await paymentRequest.canMakePayment();
            console.log('[APPLE PAY] canMakePayment result:', result);

            document.getElementById('loading-message').classList.add('hidden');

            if (result && !result.googlePay) {
                // Zobrazíme tlačítko, pokud je dostupné cokoliv kromě Google Pay
                // (To pokryje Apple Pay i případné fallbacky na iOS)
                console.log('[APPLE PAY] Wallet available. Mounting button...');
                prButton.mount('#apple-pay-button');
                console.log('[APPLE PAY] Button mounted successfully!');
            } else if (result && result.googlePay) {
                // Detekováno Google Pay na stránce Apple Pay
                console.warn('[APPLE PAY] Google Pay detected instead.');
                document.getElementById('applepay-errors').innerHTML =
                    'Detekovali jsme <strong>Google Pay</strong>. Prosím, přejděte na <a href="/donation/pay-googlepay?amount={{ $amount }}&donor_name={{ $donorName ?? '' }}&donor_email={{ $donorEmail ?? '' }}" class="underline font-bold hover:text-blue-800">stránku pro Google Pay</a> nebo použijte platbu kartou.';
            } else {
                // Nic není dostupné (prázdný Wallet nebo nepodporovaný prohlížeč)
                console.warn('[APPLE PAY] Not available in this browser');
                document.getElementById('applepay-errors').innerHTML =
                    'Apple Pay není k dispozici. Ujistěte se, že:<br>' +
                    '<ul class="list-disc list-inside mt-2 mb-2 text-left inline-block">' +
                    '<li>Máte přidanou kartu v <strong>Apple Wallet</strong>.</li>' +
                    '<li>Používáte prohlížeč <strong>Safari</strong> (ne Facebook/Instagram prohlížeč).</li>' +
                    '</ul><br>' +
                    'Případně použijte <a href="/donation/pay-card?amount={{ $amount }}&donor_name={{ $donorName ?? '' }}&donor_email={{ $donorEmail ?? '' }}" class="underline font-bold hover:text-red-800">platbu kartou</a>.';
            }

            // Handle payment
            paymentRequest.on('paymentmethod', async (ev) => {
                const {paymentIntent, error: confirmError} = await stripe.confirmPayment({
                    clientSecret: clientSecret,
                    confirmParams: {
                        payment_method: ev.paymentMethod.id,
                        return_url: '{{ route('payment.success') }}',
                    },
                    redirect: 'if_required',
                });

                if (confirmError) {
                    ev.complete('fail');
                    showError(confirmError.message);
                } else {
                    ev.complete('success');
                    window.location.href = '{{ route('payment.success') }}?payment_intent=' + paymentIntent.id;
                }
            });
        }

        function showError(message) {
            const errorElement = document.getElementById('applepay-errors');
            errorElement.textContent = message;
        }
    </script>
    @endpush
</x-app-layout>
