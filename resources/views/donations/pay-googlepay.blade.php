<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden sm:rounded-lg border p-8 border-blue-300 shadow-lg">

                <!-- Nadpis (Logo) -->
                <div class="flex items-center justify-center mb-6">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/f2/Google_Pay_Logo.svg" alt="Google Pay" class="h-12">
                </div>
                <p class="text-center text-gray-600 mb-8">
                    Částka: <strong class="text-2xl text-blue-600">{{ $amount }} Kč</strong>
                </p>

                <!-- Google Pay Button -->
                <div class="mb-6">
                    <div id="google-pay-button" style="min-height: 48px; min-width: 200px;">
                        <!-- Google Pay button se zobrazí zde -->
                    </div>
                    <div id="googlepay-errors" class="text-red-600 text-sm mt-4 text-center" role="alert"></div>
                    <div id="loading-message" class="text-gray-600 text-center mt-4">
                        Načítám Google Pay...
                    </div>
                </div>

                <!-- Info -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">
                    <p class="mb-2">
                        <strong>Google Pay:</strong> Rychlá a bezpečná platba pomocí vašeho Google účtu.
                    </p>
                    <p>
                        Pokud Google Pay není k dispozici, použijte prosím platbu kartou.
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
            await initializeGooglePay();
        });

        async function initializeGooglePay() {
            console.log('[GOOGLE PAY] Initializing...');

            // Create Payment Intent
            console.log('[GOOGLE PAY] Creating Payment Intent...');
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

            console.log('[GOOGLE PAY] Response status:', response.status);
            const { clientSecret, error } = await response.json();

            if (error) {
                console.error('[GOOGLE PAY] Payment Intent error:', error);
                showError(error);
                document.getElementById('loading-message').classList.add('hidden');
                return;
            }

            console.log('[GOOGLE PAY] Client secret received:', clientSecret ? 'YES' : 'NO');

            // Create Elements instance
            const appearance = {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#3b82f6',
                }
            };

            console.log('[GOOGLE PAY] Creating Elements...');
            elements = stripe.elements({ clientSecret, appearance });

            // Create Payment Request Button (Google Pay)
            console.log('[GOOGLE PAY] Creating Payment Request...');
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

            console.log('[GOOGLE PAY] Creating Payment Request Button element...');
            const prButton = elements.create('paymentRequestButton', {
                paymentRequest: paymentRequest,
            });

            // Check availability
            console.log('[GOOGLE PAY] Checking if Google Pay is available...');
            const result = await paymentRequest.canMakePayment();
            console.log('[GOOGLE PAY] canMakePayment result:', result);

            document.getElementById('loading-message').classList.add('hidden');

            if (result) {
                console.log('[GOOGLE PAY] Mounting button to #google-pay-button...');
                prButton.mount('#google-pay-button');
                console.log('[GOOGLE PAY] Button mounted successfully!');
            } else {
                console.warn('[GOOGLE PAY] Not available in this browser/environment');
                document.getElementById('googlepay-errors').textContent =
                    'Google Pay není k dispozici v tomto prohlížeči. Použijte prosím platbu kartou.';
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
            const errorElement = document.getElementById('googlepay-errors');
            errorElement.textContent = message;
        }
    </script>
    @endpush
</x-app-layout>
