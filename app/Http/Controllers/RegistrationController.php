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
    $payload = $request->getContent();
    $sig_header = $request->header('Stripe-Signature');
    $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
    } catch (\Exception $e) {
        Log::error('Webhook validation failed: ' . $e->getMessage());
        return response()->json(['error' => 'Invalid'], 400);
    }

$stripeSecret = config('services.stripe.secret');

if (!$stripeSecret) {
    Log::error('Kritická chyba: Stripe Secret Key nebyl v konfiguraci nalezen!');
    return; // Zabráníme pádu aplikace
}

    $stripe = new \Stripe\StripeClient($stripeSecret);
    if ($event->type == 'payment_intent.succeeded') {
        $paymentIntent = $event->data->object;

        // 1. KROK: Vytvoříme prázdný záznam v DB (Placeholder)
        $this->createPaymentPlaceholder($paymentIntent);

        // 2. KROK: Hned se pokusíme zjistit a doplnit poplatky
        $this->updatePaymentActualFees($paymentIntent->id, $stripe);
    }

    return response()->json(['status' => 'success'], 200);
}

protected function createPaymentPlaceholder($intent)
{
    $metadata = $intent->metadata;
    
    $payment = new \App\Models\Payment();
    $payment->stripe_payment_intent_id = $intent->id;
    $payment->payment_recipient_id = $metadata['payment_recipient_id']; // Interní ID (např. 10)
    $payment->event_id = $metadata['event_id'];
    $payment->donor_email = $metadata['donor_email'] ?? 'N/A';
    $payment->donor_name = $metadata['donor_name'] ?? 'N/A';
    
    $payment->total_amount = $intent->amount / 100;
    $payment->fee_amount = 0; // Zatím nula, doplníme v druhém kroku
    $payment->payout_amount = $intent->amount / 100; // Zatím plná částka
    $payment->is_live = $intent->livemode; //jestli je live
    
    $payment->save();

    Log::info("✅ Placeholder platby ID {$payment->id} vytvořen pro příjemce {$payment->payment_recipient_id}.");
}

protected function updatePaymentActualFees($paymentIntentId, $stripe)
{
    try {
        // Počkáme 5 vteřin na Stripe
        sleep(5); 

        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
        $chargeId = $paymentIntent->latest_charge;
        $charge = $stripe->charges->retrieve($chargeId, ['expand' => ['balance_transaction']]);
        
        if ($charge->balance_transaction && isset($charge->balance_transaction->fee)) {
            $exactFee = $charge->balance_transaction->fee / 100;
            
            // Najdeme platbu v DB
            $payment = \App\Models\Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();
            
            if ($payment) {
                // PŘÍMÝ ZÁPIS (jistota pro MariaDB)
                $payment->fee_amount = $exactFee;
                $payment->payout_amount = $payment->total_amount - $exactFee;
                
                // Uložení bez ohledu na to, co si myslí $fillable
                $payment->save(); 

                Log::info("✅ ZAPSÁNO NATVRDO DO DB: Částka {$payment->total_amount}, Poplatek {$exactFee}");
            }
        }
    } catch (\Exception $e) {
        Log::error("❌ Chyba při ukládání poplatku: " . $e->getMessage());
    }
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
        'payment_recipient_id' => 'required|integer', // Interní číslo ID (např. 10)
        'donor_email' => 'nullable|email',
        'donor_name' => 'nullable|string|max:255',
    ]);

    $stripe = app(\Stripe\StripeClient::class);
    
    // Částka v haléřích
    $amountInCents = $request->amount * 100;
    
    // VAŠE TESTOVACÍ ID NATVRDO
    $testRecipientId = $testRecipientId = config('services.stripe.connect_client_id');

    try {
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $amountInCents,
            'currency' => 'czk',
            'automatic_payment_methods' => ['enabled' => true],
            
            // 1. Nastavuje odpovědnost za platbu (kvůli fee a verifikaci)
            'on_behalf_of' => $testRecipientId, 
            
            // 2. KLÍČOVÉ PRO DASHBOARD: Fyzicky pošle peníze příjemci
            'transfer_data' => [
                'destination' => $testRecipientId,
            ],
            
            'statement_descriptor_suffix' => 'JDVORACKOVA',
            'metadata' => [
                'event_id' => $request->event_id,
                // TADY OPRAVA PRO SQL: Musí tam jít to číslo z requestu (např. 10)
                'payment_recipient_id' => $request->payment_recipient_id, 
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

    protected function createPaymentFromIntent($paymentIntent, $stripe)
{
    try {
        // 1. Získáme CHARGE ID (to je ta reálná transakce na platformě)
        $chargeId = $paymentIntent->latest_charge;
        
        // 2. Musíme se doptat na Balance Transaction, abychom viděli stržený poplatek
        $charge = $stripe->charges->retrieve($chargeId, ['expand' => ['balance_transaction']]);
        $bt = $charge->balance_transaction;

        // 3. TADY JE TA REALITA: BT má v sobě 'fee', které strhl Stripe na platformě
        $exactFee = $bt->fee / 100; 
        $totalAmount = $paymentIntent->amount / 100;
        $payoutAmount = $totalAmount - $exactFee;

        Log::info('calculateFees: Získáno přesně z Balance Transaction', [
            'fee_amount' => $exactFee,
            'payout_amount' => $payoutAmount
        ]);

        // ... zbytek ukládání do DB ...
    } catch (\Exception $e) {
        Log::error('Chyba při vytahování poplatku: ' . $e->getMessage());
    }
}

    private function calculateFees($paymentIntent)
{
    $amountInCzk = $paymentIntent->amount / 100;
    
    // Default: Odhad poplatku (Stripe Standard: 1.4% + 6.50 Kč pro EU karty)
    // Pokud je platba 200 Kč, poplatek je cca (200 * 0.014) + 6.5 = 2.8 + 6.5 = 9.3 Kč
    $estimatedFee = ($amountInCzk * 0.014) + 6.50; 

    $result = [
        'fee_amount' => round($estimatedFee, 2),
        'payout_amount' => round($amountInCzk - $estimatedFee, 2),
    ];

    try {
        $stripe = app(\Stripe\StripeClient::class);

        // Pokus o získání reálných dat z transferu
        $transfers = $stripe->transfers->all([
            'transfer_group' => $paymentIntent->transfer_group ?? $paymentIntent->id,
            'limit' => 1
        ]);

        if (count($transfers->data) > 0) {
            $transfer = $transfers->data[0];
            $result['payout_amount'] = $transfer->amount / 100;
            $result['fee_amount'] = $amountInCzk - $result['payout_amount'];
            Log::info('calculateFees: Získáno přesně z API', $result);
        } else {
            Log::info('calculateFees: API Transfer neposkytlo, používám odhad poplatku', $result);
        }
    } catch (\Exception $e) {
        Log::warning('calculateFees Error, použit odhad: ' . $e->getMessage());
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
 * Vygeneruje PaymentIntent pro Stripe Connect (Destination Charges).
 * Peníze tečou přímo příjemci a Stripe mu automaticky strhne poplatky.
 */
public function createConnectPaymentIntent(Request $request)
{
    // 1. Validace příchozích dat
    $request->validate([
        'amount' => 'required|integer',
        'event_id' => 'required|integer',
        'payment_recipient_id' => 'required|integer', // Interní ID příjemce (číslo 10)
    ]);

    $connectedAccountId = $testRecipientId = config('services.stripe.connect_client_id');

    $stripe = new \Stripe\StripeClient(config('stripe.secret'));

    try {
        // Výpočet v centech (50 CZK = 5000)
        $amountInCents = $request->amount * 100;

        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $amountInCents,
            'currency' => 'czk',
            
            // KLÍČOVÉ: Uděláme platbu JMÉNEM připojeného účtu.
            // Stripe strhne poplatky jemu, ne platformě.
            'on_behalf_of' => $connectedAccountId,

            // Zajišťuje fyzický přesun čisté částky po stržení poplatků
            'transfer_data' => [
                'destination' => $connectedAccountId,
            ],

            'metadata' => [
                'event_id' => $request->event_id,
                'payment_recipient_id' => $request->payment_recipient_id, // Interní ID (číslo 10) pro DB
                'donor_email' => $request->donor_email,
                'donor_name' => $request->donor_name,
            ],
            
            // Konfigurace platebních metod (včetně Google Pay, pokud je v dash. povolen)
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);

    } catch (\Stripe\Exception\ApiErrorException $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    private function updatePaymentFeesFromTransfer($transfer)
{
    if (!isset($transfer->transfer_group)) {
        Log::warning('Transfer nemá transfer_group:', (array)$transfer);
        return;
    }

    // KLÍČOVÝ BOD: Stripe u Connectu často posílá "group_pi_3Sbf..." 
    // My v DB máme uloženo čisté "pi_3Sbf..."
    $paymentIntentId = str_replace('group_', '', $transfer->transfer_group);
    
    // Najdeme platbu, kterou vytvořil předchozí webhook payment_intent.succeeded
    $payment = \App\Models\Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

    if ($payment) {
        $payoutAmount = $transfer->amount / 100; // Částka, co reálně dostane příjemce
        $feeAmount = $payment->total_amount - $payoutAmount; // Dopočítaný poplatek Stripe

        $payment->payout_amount = $payoutAmount;
        $payment->fee_amount = $feeAmount;
        $payment->save();
        
        Log::info('⚡ ÚSPĚCH: Platba ' . $payment->id . ' aktualizována poplatky z transferu.', [
            'fee' => $feeAmount,
            'payout' => $payoutAmount
        ]);
    } else {
        Log::warning('⚠️ Webhook transfer.created dorazil, ale v DB nebyla nalezena platba s ID: ' . $paymentIntentId);
    }
}

}
