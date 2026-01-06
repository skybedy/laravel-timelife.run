<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Registration;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\PaymentRecepient;
use App\Services\PaymentService;
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




    public function checkoutDifferentPaymentRecipient(Request $request, PaymentService $paymentService, PaymentRecepient $paymentRecepient)
    {
        $payment_recipient = $paymentRecepient->find($request->payment_recipient);

        // This fetch might be redundant if we trust metadata, but keeping for price object logic
        // Ideally helper fetch price but lets simplify to raw passing if ID known
        
        $session = $paymentService->createCheckoutSession([
            'price_id' => $payment_recipient->stripe_price_id,
            'event_id' => $request->event_id,
            'payment_recipient_id' => $request->payment_recipient,
            'transfer_destination' => $payment_recipient->stripe_client_id,
        ]);

        return redirect($session->url);
    }





    public function checkout(Request $request, PaymentService $paymentService)
    {
        // Using env variables directly here as in original code
        $session = $paymentService->createCheckoutSession([
            'price_id' => env('STRIPE_PRICE_ID'),
            'event_id' => $request->event_id,
            'payment_recipient_id' => $request->payment_recipient,
            'transfer_destination' => env('STRIPE_CONNECT_CLIENT_ID'),
        ]);

        return redirect($session->url);
    }



    public function handleWebhook(Request $request, PaymentService $paymentService)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $paymentService->handleWebhook($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid webhook'], 400);
        }

        return response()->json(['status' => 'success'], 200);
    }



    
    
    public function success(Request $request, PaymentService $paymentService)
    {
        $amount = null;

        try {
            if ($request->has('payment_intent')) {
                $paymentIntent = $paymentService->retrievePaymentIntent($request->payment_intent);
                $amount = $paymentIntent->amount / 100;
            } elseif ($request->has('session_id')) {
                $session = $paymentService->retrieveCheckoutSession($request->session_id);
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



    public function checkoutDynamic(Request $request, PaymentService $paymentService)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:1000000',
            'event_id' => 'required|integer',
            'payment_recipient' => 'required|integer',
        ]);

        $payment_recipient = PaymentRecepient::findOrFail($request->payment_recipient);

        $session = $paymentService->createCheckoutSession([
            'amount' => $request->amount,
            'event_id' => $request->event_id,
            'payment_recipient_id' => $request->payment_recipient,
            'transfer_destination' => $payment_recipient->stripe_client_id,
            'amount_cents' => $request->amount * 100, // For metadata
        ]);

        return redirect($session->url);
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
    public function createPaymentIntent(Request $request, PaymentService $paymentService)
    {
        $request->validate([
            'amount' => 'required|integer|min:10|max:1000000',
            'event_id' => 'required|integer',
            'payment_recipient_id' => 'required|integer',
            'donor_email' => 'nullable|email',
            'donor_name' => 'nullable|string|max:255',
        ]);

        try {
            $paymentIntent = $paymentService->createPaymentIntent([
                'amount' => $request->amount,
                'event_id' => $request->event_id,
                'payment_recipient_id' => $request->payment_recipient_id,
                'donor_email' => $request->donor_email,
                'donor_name' => $request->donor_name,
                'on_behalf_of' => config('services.stripe.connect_client_id'),
                'transfer_destination' => config('services.stripe.connect_client_id'),
                'statement_descriptor_suffix' => 'JDVORACKOVA', 
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
    public function confirmPayment(Request $request, PaymentService $paymentService)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $success = $paymentService->confirmPayment($request->payment_intent_id);

            if ($success) {
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
    public function createConnectPaymentIntent(Request $request, PaymentService $paymentService)
    {
        $request->validate([
            'amount' => 'required|integer',
            'event_id' => 'required|integer',
            'payment_recipient_id' => 'required|integer',
        ]);

        try {
            $paymentIntent = $paymentService->createPaymentIntent([
                'amount' => $request->amount,
                'event_id' => $request->event_id,
                'payment_recipient_id' => $request->payment_recipient_id,
                'donor_email' => $request->donor_email,
                'donor_name' => $request->donor_name,
                'on_behalf_of' => config('services.stripe.connect_client_id'),
                'transfer_destination' => config('services.stripe.connect_client_id'),
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
