<?php

namespace App\Jobs;

use App\Mail\InvoiceCustomerMail;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(public Transaction $transaction)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Skip kalau nggak ada email
        if (empty($this->transaction->email_pelanggan)) {
            Log::warning('No email for transaction', ['id' => $this->transaction->id]);
            return;
        }

        // Load relations yang dipake di view invoice
        $this->transaction->load(['product', 'user', 'addons']);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.invoice_customer', [
            'transaction' => $this->transaction
        ]);

        // Kirim email
        Mail::to($this->transaction->email_pelanggan)
            ->send(new InvoiceCustomerMail($this->transaction, $pdf->output()));
        
        // Optional: update status di DB
        $this->transaction->update([
            'invoice_sent_at' => now(),
            'invoice_email_status' => 'sent'
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Invoice email job failed', [
            'transaction_id' => $this->transaction->id,
            'error' => $e->getMessage()
        ]);
    }
}
