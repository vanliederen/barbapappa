<?php

namespace App\Jobs;

use App\Models\BunqAccount;
use BarPay\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use bunq\Model\Generated\Endpoint\BunqMeTab;
use bunq\Model\Generated\Endpoint\BunqMeTabEntry;
use bunq\Model\Generated\Object\Amount;

class CreateBunqMeTabPayment implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Preferred queue constant.
     */
    const QUEUE = 'high';

    /**
     * The number of seconds to wait before retrying the job.
     * The bunq API has a 30-second cooldown when throttling.
     *
     * @var int
     */
    public $retryAfter = 32;

    /**
     * The ID of the bunq account, which the money is sent from.
     *
     * @var int
     */
    private $account_id;

    /**
     * The bunq payment model ID.
     *
     * @var int
     */
    private $payment_id;

    /**
     * The amount of money to send.
     *
     * @var Amount
     */
    private $amount;

    /**
     * Create a new job instance.
     *
     * @param BunqAccount $account The bunq account to send the money from.
     * @param Payment $payment The payment model to create this payment for.
     * @param Amount $amount The amount.
     *
     * @return void
     */
    public function __construct(BunqAccount $account, Payment $payment, Amount $amount) {
        // Set queue
        $this->onQueue(Self::QUEUE);

        $this->account_id = $account->id;
        $this->payment_id = $payment->id;
        $this->amount = $amount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        // Gather some facts
        // TODO: also find deleted accounts/payments
        $account = BunqAccount::findOrFail($this->account_id);
        $payment = Payment::findOrFail($this->payment_id);
        $paymentable = $payment->paymentable;

        // Load bunq API context
        $account->loadBunqContext();

        // Build the BunqMe Tab entry
        $bunqMeTabEntry = new BunqMeTabEntry(
            $this->amount,
            $payment->getReference() . "\n" . config('app.name') . ': ' . __('barpay::payment.bunqmetab.paymentForWalletTopUp'),
            route('payment.pay', ['paymentId' => $this->payment_id, 'returned' => true])
        );

        // Create the BunqMe Tab entry
        $bunqMeTabId = BunqMeTab::create(
            $bunqMeTabEntry,
            $account->monetary_account_id,
            null,
            []
        )->getValue();

        // Fetch details for created BunqMe Tab
        $bunqMeTab = BunqMeTab::get(
            $bunqMeTabId,
            $account->monetary_account_id,
            []
        )->getValue();

        // Update payment model details
        $paymentable->bunq_tab_id = $bunqMeTabId;
        $paymentable->bunq_tab_url = $bunqMeTab->getBunqmeTabShareUrl();
        $paymentable->save();

        // Immediately cancel if payment is not in progress anymore
        if(!$payment->isInProgress())
            CancelBunqMeTabPayment::dispatch($account, $bunqMeTabId);
    }
}