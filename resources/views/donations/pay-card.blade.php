<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden sm:rounded-lg border p-8 border-blue-300 shadow-lg">

                <!-- Nadpis -->
                <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Platba kartou</h2>
                <p class="text-center text-gray-600 mb-8">
                    Částka: <strong class="text-2xl text-blue-600">{{ $amount }} Kč</strong>
                </p>

                <!-- Stripe Payment Element -->
                <form id="payment-form">
                    @csrf
                    <div id="payment-element" class="mb-4">
                        <!-- Stripe.js vloží platební formulář zde -->
                    </div>

                    <div id="payment-errors" class="text-red-600 text-sm mb-4" role="alert"></div>

                    <button
                        type="submit"
                        id="submit-button"
                        class="w-full bg-gradient-to-b from-blue-400 to-blue-500 text-white font-bold py-4 px-6 rounded-md shadow-lg hover:from-blue-500 hover:to-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-lg"
                    >
                        <span id="button-text">Zaplatit {{ $amount }} Kč</span>
                        <span id="spinner" class="hidden">Zpracovávám platbu...</span>
                    </button>
                </form>

                <!-- Info -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">
                    <p class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <strong>Bezpečná platba</strong> zpracovaná přes Stripe
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
        console.log('[DEBUG] Script loaded');
        console.log('[DEBUG] Stripe key:', '{{ $stripeKey }}');

        const stripe = Stripe('{{ $stripeKey }}');
        console.log('[DEBUG] Stripe initialized:', stripe);

        let elements;
        let paymentElement;

        document.addEventListener('DOMContentLoaded', async () => {
            console.log('[DEBUG] DOMContentLoaded fired');
            await initializeStripe();
        });

        async function initializeStripe() {
            console.log('[DEBUG] initializeStripe() called');

            try {
                // Create Payment Intent
                console.log('[DEBUG] Creating Payment Intent with amount:', {{ $amount }});

                const response = await fetch('{{ route('registration.payment-intent.create') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                    },
                    body: JSON.stringify({
                        amount: {{ $amount }},
                        event_id: 10,
                        payment_recipient_id: 3,
                        donor_email: '{{ $donorEmail ?? '' }}',
                        donor_name: '{{ $donorName ?? '' }}',
                    })
                });

                console.log('[DEBUG] Payment Intent response status:', response.status);

                const responseData = await response.json();
                console.log('[DEBUG] Payment Intent response data:', responseData);

                const { clientSecret, error } = responseData;

                if (error) {
                    console.error('[DEBUG] Payment Intent error:', error);
                    showError(error);
                    return;
                }

                console.log('[DEBUG] Client secret received:', clientSecret ? 'YES' : 'NO');

                // Create Elements instance
                const appearance = {
                    theme: 'stripe',
                    variables: {
                        colorPrimary: '#3b82f6',
                    }
                };

                console.log('[DEBUG] Creating Elements with appearance:', appearance);
                elements = stripe.elements({ clientSecret, appearance });
                console.log('[DEBUG] Elements created:', elements);

                console.log('[DEBUG] Creating payment element');
                paymentElement = elements.create('payment');
                console.log('[DEBUG] Payment element created:', paymentElement);

                console.log('[DEBUG] Mounting payment element to #payment-element');
                paymentElement.mount('#payment-element');
                console.log('[DEBUG] Payment element mounted successfully');

            } catch (err) {
                console.error('[DEBUG] Exception in initializeStripe:', err);
                showError('Chyba při inicializaci platby: ' + err.message);
            }
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

        function showError(message) {
            const errorElement = document.getElementById('payment-errors');
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
    </script>
    @endpush
</x-app-layout>
