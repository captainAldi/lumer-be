<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
//use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\LelangApproveEmail;
use Illuminate\Support\Facades\Mail;

class SendEmailLelangApproveJob extends Job
{
    use InteractsWithQueue, Queueable, SerializesModels;
    public $details;
    public $dataToSend;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details, $dataToSend)
    {
        $this->details = $details;
        $this->dataToSend = $dataToSend;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->details)->send(new LelangApproveEmail($this->dataToSend));
    }
}
