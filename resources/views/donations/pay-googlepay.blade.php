<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden sm:rounded-lg border p-8 border-blue-300 shadow-lg">

                <!-- Nadpis -->
                <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Google Pay</h2>
                <p class="text-center text-gray-600 mb-8">
                    Částka: <strong class="text-2xl text-blue-600">{{ $amount }} Kč</strong>
                </p>

                <!-- Google Pay Button -->
                <div class="mb-6">
                    <div id="google-pay-button" class="flex justify-center">
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
            // Create Payment Intent
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

            const { clientSecret, error } = await response.json();

            if (error) {
                showError(error);
                document.getElementById('loading-message').classList.add('hidden');
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

            // Create Payment Request Button (Google Pay)
            const paymentRequest = stripe.paymentRequest({
                country: 'CZ',
                currency: 'czk',
                total: {
                    label: 'Příspěvek pro Jitku Dvořáčkovou',
                    amount: {{ $amount }} * 100,
                },
                requestPayerName: true,
                requestPayerEmail: true,
            });

            const prButton = elements.create('paymentRequestButton', {
                paymentRequest: paymentRequest,
            });

            // Check availability
            const result = await paymentRequest.canMakePayment();
            document.getElementById('loading-message').classList.add('hidden');

            if (result) {
                prButton.mount('#google-pay-button');
            } else {
                document.getElementById('googlepay-errors').textContent =
                    'Google Pay není k dispozici v tomto prohlížeči. Použijte prosím platbu kartou.';
            }

            // Handle payment
            paymentRequest.on('paymentmethod', async (ev) => {
                const {error: confirmError} = await stripe.confirmPayment({
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
                    window.location.href = '{{ route('payment.success') }}';
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
