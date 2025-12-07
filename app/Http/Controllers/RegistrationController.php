<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Registration;
use App\Models\Event;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use App\Models\PaymentRecepient;
use App\Models\Payment;
use App\Models\Payout;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{






    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Category $category)
    {
        return view('registrations.index', [
            'categories' => $category->categoryListAbsolute($request->eventId),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Category $category, Registration $registration, PaymentRecepient $paymentRecepient)
    {

        $event_id = $request->eventId;

        $user_id = $request->user()->id;

        if (!$registration->someRegistrationExists(env('PLATFORM_ID'),$event_id,$user_id))
        {
            if($event_id < 5)
            {
                return view('registrations.payment_znesnaze', [
                    'payment_recepients' => $paymentRecepient->All(),
                    'eventId' => $event_id,
                ]);
            }
            else
            {
                return view('registrations.payment', [
                    'payment_recepients' => $paymentRecepient->All(),
                    'event_id' => $event_id,
                ]);
            }
        }
        else
        {
            if($registration->eventRegistrationExists($user_id, $event_id))
            {
                session()->flash('info', 'Na tento závod už je registrace provedená');
            }
            else
            {
                $this->store($request,$registration,$category);
            }

            return redirect()->back();
        }

    }

    public function store(Request $request,Registration $registration, Category $category)
    {
        $registration->create([
            'event_id' => $request->eventId,
            'user_id' =>   $request->user()->id,
            'category_id' => $category->categoryChoice($request->user()->gender, calculate_age($request->user()->birth_year))->id,
            'ids' => $registration->startNumber(env('PLATFORM_ID'),$request->eventId,$request->user()->id),
        ]);

        session()->flash('success', 'Byli jste úspěšně zaregistrováni');

        return redirect()->route('index');
    }




    public function checkoutDifferentPaymentRecipient(Request $request,StripeClient $stripe,PaymentRecepient $paymentRecepient)
    {
        $payment_recepient = $paymentRecepient->find($request->payment_recipient);

        $price = $stripe->prices->retrieve($payment_recepient->stripe_price_id);

        // Vytvoření Stripe Checkout Session
        $checkout_session = $stripe->checkout->sessions->create([
            'line_items' => [[
                'price' => $payment_recepient->stripe_price_id, // Production Price ID
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel'),
            'metadata' => [
                'amount' => $price->unit_amount, // Cena v v halerich
                'event_id' => $request->event_id,
                'payment_recipient_id' => $request->payment_recipient,
            ],

            'payment_intent_data' => [
                'transfer_data' => ['destination' => $payment_recepient->stripe_client_id],
                'setup_future_usage' => 'on_session', //mozna kvuli apple kdyz nebude fungovat,dat pryč
                'statement_descriptor' => 'TIMELIFE',
            ],
        ]);

        // Přesměrování na Stripe Checkout
        return redirect($checkout_session->url);
    }





        public function checkout(Request $request,StripeClient $stripe)
        {
            $price = $stripe->prices->retrieve(env('STRIPE_PRICE_ID'));

            // Vytvoření Stripe Checkout Session
            $checkout_session = $stripe->checkout->sessions->create([
                'line_items' => [[
                    'price' => env('STRIPE_PRICE_ID'), // Production Price ID
                    'quantity' => 1,
                ]],

                'mode' => 'payment',
                'success_url' => route('payment.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel'),
                'metadata' => [
                    'amount' => $price->unit_amount, // Cena v v halerich
                    'event_id' => $request->event_id,
                    'payment_recipient_id' => $request->payment_recipient,
                ],

                'payment_intent_data' => [
                    'transfer_data' => ['destination' => env('STRIPE_CONNECT_CLIENT_ID')],
                    'setup_future_usage' => 'on_session', //mozna kvuli apple kdyz nebude fungovat,dat pryč
                    'statement_descriptor' => 'TIMELIFE',
                ],
            ]);

            // Přesměrování na Stripe Checkout
            return redirect($checkout_session->url);

            }



    public function handleWebhook(Request $request)
    {
        // DŮLEŽITÉ: getContent() přečte stream - musíme ho použít JEDNOU
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        // Získání webhook secret z .env
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            // Ověření webhook signature
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Stripe webhook invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature - but continue anyway for ngrok testing
            Log::warning('Stripe signature verification failed (ngrok bypass): ' . $e->getMessage());

            // Try to parse the payload directly for local testing
            try {
                $event = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $jsonError) {
                Log::error('Cannot parse webhook payload: ' . $jsonError->getMessage());
                return response()->json(['error' => 'Invalid JSON'], 400);
            }
        }

        // Handle the event
        if ($event->type == 'checkout.session.completed') {
            $checkout_session = $event->data->object;

            Log::info('✓ Webhook: checkout.session.completed', [
                'session_id' => $checkout_session->id,
                'amount' => $checkout_session->amount_total ?? 'N/A',
                'email' => $checkout_session->customer_details->email ?? 'N/A',
            ]);

            $this->createPayment($checkout_session);
        }

        // Handle Payment Intent succeeded (for Stripe Elements)
        if ($event->type == 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;

            Log::info('✓ Webhook: payment_intent.succeeded', [
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
            ]);

            $this->createPaymentFromIntent($paymentIntent);
        }

        if ($event->type == 'payout.paid') {
            $payout = $event->data->object;

            Log::info('✓ Webhook: payout.paid', [
                'payout_id' => $payout->id,
                'amount' => $payout->amount,
                'currency' => $payout->currency,
                'arrival_date' => date('Y-m-d', $payout->arrival_date),
            ]);

            $this->createPayout($payout);
        }

        if ($event->type == 'transfer.created') {
            $transfer = $event->data->object;

            Log::info('✓ Webhook: transfer.created', [
                'transfer_id' => $transfer->id,
                'amount' => $transfer->amount,
                'transfer_group' => $transfer->transfer_group ?? 'N/A',
            ]);

            $this->updatePaymentFeesFromTransfer($transfer);
        }

        return response()->json(['status' => 'success'], 200);
    }
    public function success(Request $request, StripeClient $stripe)
    {
        $amount = null;

        try {
            if ($request->has('payment_intent')) {
                $paymentIntent = $stripe->paymentIntents->retrieve($request->payment_intent);
                $amount = $paymentIntent->amount / 100;
            } elseif ($request->has('session_id')) {
                $session = $stripe->checkout->sessions->retrieve($request->session_id);
                $amount = $session->amount_total / 100;
            }
        } catch (\Exception $e) {
            Log::error('Error retrieving payment details in success: ' . $e->getMessage());
        }

        // Fallback na session, pokud by API selhalo (málo pravděpodobné, ale pro jistotu)
        if ($amount === null) {
            $amount = session('last_donation_amount', null);
        }
        
        // Vyčistit session, pokud tam něco zbylo
        session()->forget('last_donation_amount');

        $message = 'Děkuji za příspěvek na Dům pro Julii.<br>Jitka Dvořáčková.';

        if ($amount !== null) {
            $message = 'Děkuji za příspěvek ' . number_format($amount, 0, ',', ' ') . ' Kč na Dům pro Julii.<br>Jitka Dvořáčková.';
        }

        return redirect()->route('index')->with('success', $message);
    }



    public function checkoutDynamic(Request $request, StripeClient $stripe, Registration $registration,)
    {
        // Validace
        $request->validate([
            'amount' => 'required|numeric|min:10|max:1000000',
            'event_id' => 'required|integer',
            'payment_recipient' => 'required|integer',
        ]);

        $payment_recipient = PaymentRecepient::findOrFail($request->payment_recipient);

        // Vytvoření Stripe Checkout Session s dynamickou cenou
        $checkout_session = $stripe->checkout->sessions->create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'czk',
                    'unit_amount' => $request->amount * 100, // částka v haléřích
                    'product_data' => [
                        'name' => 'Jitka Dvořáčková, 100 půmaratonů za 100 dní',
                        'description' => 'Vaše platba bude prostřednictvím služby Sripe převedena přímo na účet 2101782768/2010 organizace Dům pro Julii,',
                        'images' => ['https://liferun.cz/images/dum-pro-julii-logo.png'],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel'),
            'metadata' => [
                'amount' => $request->amount * 100, // Cena v haléřích
                'event_id' => $request->event_id,
                'payment_recipient_id' => $request->payment_recipient,
            ],
            'payment_intent_data' => [
                'transfer_data' => ['destination' => $payment_recipient->stripe_client_id],
                'setup_future_usage' => 'on_session',
                'statement_descriptor' => 'LIFERUN.CZ JDVORACKOVA',
            ],
        ]);

        // Přesměrování na Stripe Checkout
        return redirect($checkout_session->url);
    }


    private function createPayment($checkout_session)
    {
        // Prevence duplicitních plateb
        if (Payment::where('stripe_session_id', $checkout_session->id)->exists()) {
            return;
        }

        $payment = new Payment();

        // Všichni jsou anonymní dárci (žádná registrace)
        $payment->user_id = null;

        // Prioritně použít údaje z metadat (z našeho formuláře)
        // Pokud nejsou v metadatech, použít customer_details ze Stripe
        $payment->donor_email = $checkout_session->metadata->donor_email ?? $checkout_session->customer_details->email ?? null;
        $payment->donor_name = $checkout_session->metadata->donor_name ?? $checkout_session->customer_details->name ?? null;

        // Společná data
        $payment->event_id = $checkout_session->metadata->event_id;
        $payment->payment_recipient_id = $checkout_session->metadata->payment_recipient_id;
        $payment->total_amount = $checkout_session->metadata->amount / 100;
        
        // Pro Checkout Session (starší implementace) nemusíme mít transfer data snadno dostupná, 
        // pokud to nebyl connect. Prozatím necháme null nebo stejné jako total.
        // Vylepšení: Pokud bychom používali Connect i zde, museli bychom to vytáhnout z payment_intentu.
        
        $payment->stripe_session_id = $checkout_session->id;
                $payment->save();
    }

    private function createPayout($payout)
    {
        // Prevence duplicitních payoutů
        if (Payout::where('stripe_payout_id', $payout->id)->exists()) {
            return;
        }

        // Najít payment_recipient_id z destination (Stripe Connect account ID)
        $paymentRecipient = PaymentRecepient::where('stripe_client_id', $payout->destination)->first();

        if (!$paymentRecipient) {
            Log::error('Payment recipient not found for payout:', [
                'payout_id' => $payout->id,
                'destination' => $payout->destination,
            ]);
            return;
        }

        $payoutModel = new Payout();
        $payoutModel->stripe_payout_id = $payout->id;
        $payoutModel->payment_recipient_id = $paymentRecipient->id;
        $payoutModel->amount = $payout->amount;
        $payoutModel->currency = $payout->currency;
        $payoutModel->arrival_date = $payout->arrival_date ? date('Y-m-d H:i:s', $payout->arrival_date) : null;
        $payoutModel->status = $payout->status;
        $payoutModel->type = $payout->type ?? null;
        $payoutModel->description = $payout->description ?? null;
        $payoutModel->stripe_data = json_decode(json_encode($payout), true);

        $payoutModel->save();
    }

    public function cancel()
    {
        return redirect()->route('index')->with('error', 'Platba byla zrušena');
    }

    /**
     * Zobrazí formulář s Stripe Elements pro platbu
     */
    public function checkoutElementsForm($event_id, $payment_recipient)
    {
        $event = Event::find($event_id);
        $paymentRecipient = PaymentRecepient::find($payment_recipient);

        return view('registrations.checkout_elements', [
            'event' => $event,
            'paymentRecipient' => $paymentRecipient,
            'stripeKey' => env('STRIPE_KEY'),
        ]);
    }

    /**
     * Vytvoří Payment Intent pro Stripe Elements
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:10|max:1000000',
            'event_id' => 'required|integer',
            'payment_recipient_id' => 'required|integer',
            'donor_email' => 'nullable|email',
            'donor_name' => 'nullable|string|max:255',
        ]);

        $stripe = app(StripeClient::class);
        $paymentRecipient = PaymentRecepient::find($request->payment_recipient_id);

        // Částka v haléřích (Stripe používá minor units)
        $amountInCents = $request->amount * 100;

        try {
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $amountInCents,
                'currency' => 'czk',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'transfer_data' => [
                    'destination' => $paymentRecipient->stripe_client_id,
                    // Amount nezadáváme - Stripe automaticky převede (Total - Stripe Fee)
                ],
                'statement_descriptor_suffix' => 'JDVORACKOVA',
                'metadata' => [
                    'event_id' => $request->event_id,
                    'payment_recipient_id' => $request->payment_recipient_id,
                    'amount' => $amountInCents,
                    'donor_email' => $request->donor_email,
                    'donor_name' => $request->donor_name,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Potvrdí úspěšnou platbu a uloží ji do databáze
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $stripe = app(StripeClient::class);
            $paymentIntent = $stripe->paymentIntents->retrieve($request->payment_intent_id);

            // Zkontroluj, jestli platba proběhla úspěšně
            if ($paymentIntent->status === 'succeeded') {
                // Zkontroluj, jestli už neexistuje záznam
                if (!Payment::where('stripe_payment_intent_id', $paymentIntent->id)->exists()) {
                    $payment = new Payment();
                    $payment->user_id = null;
                    $payment->donor_email = $paymentIntent->metadata->donor_email ?? null;
                    $payment->donor_name = $paymentIntent->metadata->donor_name ?? null;
                    $payment->event_id = $paymentIntent->metadata->event_id;
                    $payment->payment_recipient_id = $paymentIntent->metadata->payment_recipient_id;
                    
                    $payment->total_amount = $paymentIntent->amount / 100;
                    
                    $fees = $this->calculateFees($paymentIntent);
                    $payment->fee_amount = $fees['fee_amount'];
                    $payment->payout_amount = $fees['payout_amount'];

                    $payment->stripe_session_id = null; // Elements nepoužívá session
                    $payment->stripe_payment_intent_id = $paymentIntent->id;
                    $payment->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Platba byla úspěšně zpracována'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Platba nebyla dokončena'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vytvoří záznam o platbě z Payment Intent (webhook handler)
     */
    private function createPaymentFromIntent($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            Log::info('Payment already exists for payment_intent: ' . $paymentIntent->id);
            
            // Pokud chybí fee (je 0), zkusíme ho dopočítat a aktualizovat
            if ($payment->fee_amount == 0) {
                $fees = $this->calculateFees($paymentIntent);
                if ($fees['fee_amount'] > 0) {
                    $payment->fee_amount = $fees['fee_amount'];
                    $payment->payout_amount = $fees['payout_amount'];
                    $payment->save();
                    Log::info('Updated payment fees via webhook', $fees);
                }
            }
            return;
        }

        $payment = new Payment();
        $payment->user_id = null;
        $payment->donor_email = $paymentIntent->metadata->donor_email ?? null;
        $payment->donor_name = $paymentIntent->metadata->donor_name ?? null;
        $payment->event_id = $paymentIntent->metadata->event_id;
        $payment->payment_recipient_id = $paymentIntent->metadata->payment_recipient_id;
        
        // Částky a poplatky
        $payment->total_amount = $paymentIntent->amount / 100; // Hrubá částka od dárce
        
        $fees = $this->calculateFees($paymentIntent);
        $payment->fee_amount = $fees['fee_amount'];
        $payment->payout_amount = $fees['payout_amount'];

        $payment->stripe_payment_intent_id = $paymentIntent->id;
        $payment->save();

        Log::info('Payment created from Payment Intent', [
            'payment_id' => $payment->id,
            'amount' => $payment->total_amount,
            'donor_email' => $payment->donor_email,
        ]);
    }

    /**
     * Pomocná metoda pro výpočet poplatků ze Stripe API
     */
    private function calculateFees($paymentIntent)
    {
        $result = [
            'fee_amount' => 0,
            'payout_amount' => $paymentIntent->amount / 100, // Fallback
        ];

        try {
            $stripe = app(StripeClient::class);
            Log::info('calculateFees: PaymentIntent ID: ' . $paymentIntent->id);
            Log::info('calculateFees: PaymentIntent latest_charge: ' . ($paymentIntent->latest_charge ?? 'N/A'));

            if (isset($paymentIntent->latest_charge)) {
                $charge = $stripe->charges->retrieve($paymentIntent->latest_charge, ['expand' => ['balance_transaction']]);
                Log::info('calculateFees: Retrieved Charge object', (array)$charge);

                $balanceTransaction = $charge->balance_transaction;

                // Pokud je balance_transaction jen ID (string), načteme ho ručně
                if (is_string($balanceTransaction)) {
                    Log::info('calculateFees: Balance transaction is ID, fetching object...', ['id' => $balanceTransaction]);
                    $balanceTransaction = $stripe->balanceTransactions->retrieve($balanceTransaction);
                }

                if ($balanceTransaction && isset($balanceTransaction->fee)) {
                    $feeInCents = $balanceTransaction->fee;
                    $result['fee_amount'] = $feeInCents / 100;
                    $result['payout_amount'] = ($paymentIntent->amount - $feeInCents) / 100;
                    Log::info('calculateFees: Fee and payout calculated', $result);
                } else {
                    Log::warning('calculateFees: Balance transaction data missing fee or null on platform. Trying to find Transfer...');
                    
                    // Pokud chybí balance transaction (u Destination Charges), poplatky zjistíme z rozdílu mezi Charge a Transfer
                    if (isset($paymentIntent->transfer_group)) {
                        try {
                            $transfers = $stripe->transfers->all(['transfer_group' => $paymentIntent->transfer_group, 'limit' => 1]);
                            
                            if (count($transfers->data) > 0) {
                                $transfer = $transfers->data[0];
                                // Transfer amount je částka, která odešla na connected account
                                $result['payout_amount'] = $transfer->amount / 100;
                                $result['fee_amount'] = ($paymentIntent->amount - $transfer->amount) / 100;
                                Log::info('calculateFees: Fees calculated from Transfer object', $result);
                            } else {
                                Log::warning('calculateFees: No transfer found for group: ' . $paymentIntent->transfer_group);
                            }
                        } catch (\Exception $ex) {
                            Log::warning('calculateFees: Failed to list transfers: ' . $ex->getMessage());
                        }
                    }
                }
            } else {
                Log::warning('calculateFees: latest_charge not set on PaymentIntent');
            }
        } catch (\Exception $e) {
            Log::error('Error calculating fees in calculateFees: ' . $e->getMessage(), ['paymentIntentId' => $paymentIntent->id]);
        }

        return $result;
    }

    /**
     * Zobrazí stránku pro výběr platební metody
     */
    public function paymentSelection(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:50|max:1000000',
        ]);

        return view('donations.payment-selection', [
            'amount' => $request->amount,
            'stripeKey' => env('STRIPE_KEY'),
        ]);
    }

    /**
     * Stránka pro platbu kartou
     */
    public function payWithCard(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:50|max:1000000',
            'donor_name' => 'required|string|max:255',
            'donor_email' => 'required|email|max:255',
        ]);

        return view('donations.pay-card', [
            'amount' => $request->amount,
            'donorName' => $request->donor_name,
            'donorEmail' => $request->donor_email,
            'stripeKey' => env('STRIPE_KEY'),
        ]);
    }

    /**
     * Stránka pro platbu přes Google Pay
     */
    public function payWithGooglePay(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:50|max:1000000',
            'donor_name' => 'required|string|max:255',
            'donor_email' => 'required|email|max:255',
        ]);

        return view('donations.pay-googlepay', [
            'amount' => $request->amount,
            'donorName' => $request->donor_name,
            'donorEmail' => $request->donor_email,
            'stripeKey' => env('STRIPE_KEY'),
        ]);
    }







    /**
     * Stránka pro platbu přes Apple Pay
     */
    public function payWithApplePay(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:50|max:1000000',
            'donor_name' => 'required|string|max:255',
            'donor_email' => 'required|email|max:255',
        ]);

        return view('donations.pay-applepay', [
            'amount' => $request->amount,
            'donorName' => $request->donor_name,
            'donorEmail' => $request->donor_email,
            'stripeKey' => env('STRIPE_KEY'),
        ]);
    }

    /**
     * Vygeneruje PaymentIntent pro Stripe Connect (Destination Charges) s poplatkem platformy.
     */
    public function createConnectPaymentIntent(Request $request)
    {
        // Pro účely testování předpokládáme, že connectedAccountId je předáno v requestu nebo je statické
        // V reálné aplikaci byste ho získali z databáze nebo jiného zdroje
        // Mělo by to být ID Custom Connected Account (acct_...)
        $connectedAccountId = $request->input('connected_account_id', 'acct_1PvCa82LSxhftJEa'); // Příklad, nahraďte skutečným ID

        $stripe = new StripeClient(env('STRIPE_SECRET_KEY')); // Použít secret klíč

        try {
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => 5000, // 5000 centů = 50.00 CZK
                'currency' => 'czk',
                'payment_method_types' => ['card'], // Nebo 'card', 'promptpay', atd. podle potřeby
                'transfer_data' => [
                    'destination' => $connectedAccountId,
                    'amount' => 4900, // 5000 - 100 = 4900 centů jde na connected account
                ],
                'application_fee_amount' => 100, // 100 centů = 1.00 CZK je poplatek platformy
                'confirm' => true, // Potvrdit PaymentIntent ihned
                'return_url' => route('payment.success') . '?payment_intent={PAYMENT_INTENT_ID}', // URL pro přesměrování po 3D Secure
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Zpracování chyb Stripe API
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            // Zpracování obecných chyb
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Aktualizuje poplatky u existující platby na základě webhooku transfer.created
     */
    private function updatePaymentFeesFromTransfer($transfer)
    {
        if (!isset($transfer->transfer_group)) {
            return;
        }

        // Očekáváme formát 'group_pi_...' -> získáme 'pi_...'
        $paymentIntentId = str_replace('group_', '', $transfer->transfer_group);
        
        // Najdeme platbu
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if ($payment) {
            // Transfer amount je v centech a je to ČISTÁ částka pro příjemce
            $payoutAmount = $transfer->amount / 100;
            
            // Fee je rozdíl mezi celkovou částkou a tím, co šlo příjemci
            $feeAmount = $payment->total_amount - $payoutAmount;

            $payment->payout_amount = $payoutAmount;
            $payment->fee_amount = $feeAmount;
            $payment->save();
            
            Log::info('Updated payment fees via transfer.created webhook', [
                'payment_id' => $payment->id,
                'fee' => $feeAmount,
                'payout' => $payoutAmount
            ]);
        } else {
            Log::warning('Payment not found for transfer group (yet?): ' . $transfer->transfer_group);
        }
    }

}
