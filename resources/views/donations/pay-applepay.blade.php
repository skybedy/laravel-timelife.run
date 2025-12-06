<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden sm:rounded-lg border p-8 border-blue-300 shadow-lg">

                <!-- Nadpis -->
                <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Apple Pay</h2>
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

            if (result && result.applePay) {
                console.log('[APPLE PAY] Apple Pay available! Mounting button...');
                prButton.mount('#apple-pay-button');
                console.log('[APPLE PAY] Button mounted successfully!');
            } else if (result) {
                 // Fallback - might be Google Pay or Browser Payment, but we are on Apple Pay page
                 // Show button anyway, as it might be a valid wallet on this device
                 console.log('[APPLE PAY] Wallet available (maybe not strictly Apple Pay, but compatible). Mounting...');
                 prButton.mount('#apple-pay-button');
            } else {
                console.warn('[APPLE PAY] Not available in this browser/environment');
                document.getElementById('applepay-errors').textContent =
                    'Apple Pay není k dispozici v tomto prohlížeči. Použijte prosím Safari na macOS/iOS nebo platbu kartou.';
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
