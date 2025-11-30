<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Registration;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use App\Models\PaymentRecepient;
use App\Models\Payment;

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

        // Generovat unikátní payment reference ID PŘED Stripe
        $payment_reference_id = 'PAY-' . time() . '-' . uniqid();

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
                'payment_reference_id' => $payment_reference_id,
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

            // Generovat unikátní payment reference ID PŘED Stripe
            $payment_reference_id = 'PAY-' . time() . '-' . uniqid();

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
                    'payment_reference_id' => $payment_reference_id,
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

    public function success(Request $request, StripeClient $stripe)
    {
        $session_id = $request->get('session_id');

        $checkout_session = $stripe->checkout->sessions->retrieve($session_id);

        $this->createPayment($checkout_session);

        // Vždy redirect na homepage s poděkováním (žádná registrace)
        return redirect()->route('index')->with('success', 'Děkujeme za váš příspěvek!');
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
        $payment->donor_email = $checkout_session->customer_details->email ?? null;
        $payment->donor_name = $checkout_session->customer_details->name ?? null;

        // Společná data
        $payment->event_id = $checkout_session->metadata->event_id;
        $payment->payment_recipient_id = $checkout_session->metadata->payment_recipient_id;
        $payment->amount = $checkout_session->metadata->amount / 100;
        $payment->stripe_session_id = $checkout_session->id;
        $payment->payment_reference_id = $checkout_session->metadata->payment_reference_id ?? null;

        $payment->save();
    }

    public function cancel()
    {
        return redirect()->route('index')->with('error', 'Platba byla zrušena');
    }

    public function checkoutDynamic(Request $request, StripeClient $stripe)
    {
        // Validace
        $request->validate([
            'amount' => 'required|numeric|min:10|max:1000000',
            'event_id' => 'required|integer',
            'payment_recipient' => 'required|integer',
        ]);

        $payment_recipient = PaymentRecepient::findOrFail($request->payment_recipient);

        // Generovat unikátní payment reference ID PŘED Stripe
        $payment_reference_id = 'PAY-' . time() . '-' . uniqid();

        // Vytvoření Stripe Checkout Session s dynamickou cenou
        $checkout_session = $stripe->checkout->sessions->create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'czk',
                    'unit_amount' => $request->amount * 100, // částka v haléřích
                    'product_data' => [
                        'name' => 'Příspěvek - 100 půlmaratonů pro Jitku',
                        'description' => 'Dobrovolný příspěvek na podporu',
                        // Placeholder pro testování - na produkci změnit na: asset('images/dum-pro-julii.png')
                        'images' => ['https://via.placeholder.com/512x512.png?text=Logo'],
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
                'payment_reference_id' => $payment_reference_id,
            ],
            'payment_intent_data' => [
                'transfer_data' => ['destination' => $payment_recipient->stripe_client_id],
                'setup_future_usage' => 'on_session',
                'statement_descriptor' => 'TIMELIFE',
            ],
        ]);

        // Přesměrování na Stripe Checkout
        return redirect($checkout_session->url);
    }




















}
