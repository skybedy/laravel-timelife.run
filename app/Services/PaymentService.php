<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentRecepient;
use App\Models\Payout;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\ApiErrorException;

class PaymentService
{
    protected $stripe;

    public function __construct(StripeClient $stripe)
    {
        $this->stripe = $stripe;
    }

    public function createCheckoutSession(array $data)
    {
        $lineItems = [[
            'quantity' => 1,
        ]];

        if (isset($data['price_id'])) {
            $lineItems[0]['price'] = $data['price_id'];
        } elseif (isset($data['amount'])) {
            $lineItems[0]['price_data'] = [
                'currency' => 'czk',
                'unit_amount' => $data['amount'] * 100,
                'product_data' => [
                    'name' => 'Jitka Dvořáčková, 100 půmaratonů za 100 dní',
                    'description' => 'Vaše platba bude prostřednictvím služby Sripe převedena přímo na účet 2101782768/2010 organizace Dům pro Julii,',
                    'images' => ['https://liferun.cz/images/dum-pro-julii-logo.png'],
                ],
            ];
        }

        $sessionData = [
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('payment.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel'),
            'metadata' => [
                'event_id' => $data['event_id'],
                'payment_recipient_id' => $data['payment_recipient_id'],
                'donor_email' => $data['donor_email'] ?? null,
                'donor_name' => $data['donor_name'] ?? null,
            ],
            'payment_intent_data' => [
                'setup_future_usage' => 'on_session',
                'statement_descriptor' => 'TIMELIFE',
            ],
        ];
        
        // Add amount to metadata if available (for static price it might need fetch, but usually we know it)
        if (isset($data['amount_cents'])) {
             $sessionData['metadata']['amount'] = $data['amount_cents'];
        }

        if (isset($data['transfer_destination'])) {
            $sessionData['payment_intent_data']['transfer_data'] = ['destination' => $data['transfer_destination']];
        }

        return $this->stripe->checkout->sessions->create($sessionData);
    }

    public function createPaymentIntent(array $data)
    {
        $intentData = [
            'amount' => $data['amount'] * 100,
            'currency' => 'czk',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'event_id' => $data['event_id'],
                'payment_recipient_id' => $data['payment_recipient_id'],
                'donor_email' => $data['donor_email'] ?? null,
                'donor_name' => $data['donor_name'] ?? null,
            ],
        ];

        if (isset($data['on_behalf_of'])) {
            $intentData['on_behalf_of'] = $data['on_behalf_of'];
        }

        if (isset($data['transfer_destination'])) {
             $intentData['transfer_data'] = ['destination' => $data['transfer_destination']];
        }
        
        if (isset($data['statement_descriptor_suffix'])) {
            $intentData['statement_descriptor_suffix'] = $data['statement_descriptor_suffix'];
        }

        return $this->stripe->paymentIntents->create($intentData);
    }

    public function handleWebhook($payload, $sigHeader, $endpointSecret)
    {
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Webhook validation failed: ' . $e->getMessage());
            throw $e;
        }

        if ($event->type == 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $this->createPaymentPlaceholder($paymentIntent);
            $this->updatePaymentActualFees($paymentIntent->id);
        }
        
        if ($event->type == 'transfer.created') {
             $transfer = $event->data->object;
             $this->updatePaymentFeesFromTransfer($transfer);
        }

        return $event;
    }

    public function createPaymentPlaceholder($intent)
    {
        $metadata = $intent->metadata;
        
        $payment = new Payment();
        $payment->stripe_payment_intent_id = $intent->id;
        $payment->payment_recipient_id = $metadata['payment_recipient_id'];
        $payment->event_id = $metadata['event_id'];
        $payment->donor_email = $metadata['donor_email'] ?? 'N/A';
        $payment->donor_name = $metadata['donor_name'] ?? 'N/A';
        
        $payment->total_amount = $intent->amount / 100;
        $payment->fee_amount = 0;
        $payment->payout_amount = $intent->amount / 100;
        $payment->is_live = $intent->livemode;
        
        $payment->save();

        Log::info("✅ Placeholder platby ID {$payment->id} vytvořen pro příjemce {$payment->payment_recipient_id}.");
        return $payment;
    }

    public function updatePaymentActualFees($paymentIntentId)
    {
        try {
            sleep(5); // Wait for Stripe

            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            $chargeId = $paymentIntent->latest_charge;
            $charge = $this->stripe->charges->retrieve($chargeId, ['expand' => ['balance_transaction']]);
            
            if ($charge->balance_transaction && isset($charge->balance_transaction->fee)) {
                $exactFee = $charge->balance_transaction->fee / 100;
                
                $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();
                
                if ($payment) {
                    $payment->fee_amount = $exactFee;
                    $payment->payout_amount = $payment->total_amount - $exactFee;
                    $payment->save(); 

                    Log::info("✅ ZAPSÁNO NATVRDO DO DB: Částka {$payment->total_amount}, Poplatek {$exactFee}");
                }
            }
        } catch (\Exception $e) {
            Log::error("❌ Chyba při ukládání poplatku: " . $e->getMessage());
        }
    }
    
    public function updatePaymentFeesFromTransfer($transfer)
    {
        if (!isset($transfer->transfer_group)) {
            Log::warning('Transfer nemá transfer_group:', (array)$transfer);
            return;
        }

        $paymentIntentId = str_replace('group_', '', $transfer->transfer_group);
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if ($payment) {
            $payoutAmount = $transfer->amount / 100;
            $feeAmount = $payment->total_amount - $payoutAmount;

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

    public function confirmPayment($paymentIntentId)
    {
        $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

        if ($paymentIntent->status === 'succeeded') {
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

                $payment->stripe_session_id = null;
                $payment->stripe_payment_intent_id = $paymentIntent->id;
                $payment->save();
            }
            return true;
        }
        return false;
    }

    public function calculateFees($paymentIntent)
    {
        $amountInCzk = $paymentIntent->amount / 100;
        
        // Default estimate
        $estimatedFee = ($amountInCzk * 0.014) + 6.50; 

        $result = [
            'fee_amount' => round($estimatedFee, 2),
            'payout_amount' => round($amountInCzk - $estimatedFee, 2),
        ];

        try {
            $transfers = $this->stripe->transfers->all([
                'transfer_group' => $paymentIntent->transfer_group ?? $paymentIntent->id,
                'limit' => 1
            ]);

            if (count($transfers->data) > 0) {
                $transfer = $transfers->data[0];
                $result['payout_amount'] = $transfer->amount / 100;
                $result['fee_amount'] = $amountInCzk - $result['payout_amount'];
                Log::info('calculateFees: Získáno přesně z API', $result);
            }
        } catch (\Exception $e) {
            Log::warning('calculateFees Error, použit odhad: ' . $e->getMessage());
        }

        return $result;
    }
    public function retrievePaymentIntent($id)
    {
        return $this->stripe->paymentIntents->retrieve($id);
    }

    public function retrieveCheckoutSession($id)
    {
        return $this->stripe->checkout->sessions->retrieve($id);
    }
}
