<?php

namespace App\Jobs;

use App\Models\BalanceImportEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Mail balance import event users a balance update.
 */
class BalanceImportEventMailUpdates implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Preferred queue constant.
     */
    const QUEUE = 'low';

    private $event_id;
    private $mail_unregistered_users;
    private $mail_non_joined_users;
    private $mail_joined_users;
    private $message;
    private $invite_to_bar_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $event_id, bool $mail_unregistered_users, bool $mail_non_joined_users, bool $mail_joined_users, $message, $invite_to_bar_id) {
        // Set queue
        $this->onQueue(Self::QUEUE);

        $this->event_id = $event_id;
        $this->mail_unregistered_users = $mail_unregistered_users;
        $this->mail_non_joined_users = $mail_non_joined_users;
        $this->mail_joined_users = $mail_joined_users;
        $this->message = $message;
        $this->invite_to_bar_id = $invite_to_bar_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        // Get the event
        $event = BalanceImportEvent::find($this->event_id);
        if($event == null)
            return;

        $self = $this;
        DB::transaction(function() use($event, $self) {
            // Walk through all changes in this event
            $changes = $event->changes()->approved()->get();
            foreach($changes as $change) {
                // Dispatch background jobs to send update to change user
                BalanceImportEventMailUpdate::dispatch(
                    $change->id,
                    $self->mail_unregistered_users,
                    $self->mail_non_joined_users,
                    $self->mail_joined_users,
                    $self->message,
                    $self->invite_to_bar_id,
                );
            }
        });
    }
}
