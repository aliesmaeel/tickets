<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\View\View;

class ScanQRCode extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static string $view = 'filament.pages.scan-q-r-code';

    public $qrData = null;

    protected $listeners = ['autoVerify' => 'setQrData'];

    public function setQrData($data)
    {
        $this->qrData = $data;
        $this->verifyScan();
    }


    public function autoVerify($data)
    {
        $this->qrData = $data;
        $this->verifyScan();
    }

    public function verifyScan()
    {
        try {
            if (preg_match('/\d+/', $this->qrData, $matches)) {
                $number = $matches[0];
            }
            $this->qrData=$number;
            $ticket = Ticket::find($number);

            if ($ticket->status !== 'upcoming') {



                Notification::make()
                    ->title("Ticket Status Issue")
                    ->body("Ticket #{$ticket->id} is not in 'upcoming' status. Current status: {$ticket->status}")
                    ->danger()
                    ->send();
                return;
            }

            Notification::make()
                ->title("Ticket Verified")
                ->body("Ticket #{$ticket->id} is valid and ready to be marked as attended.")
                ->success()
                ->send();

        } catch (ValidationException $e) {
            Notification::make()
                ->title("Validation Error")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function markAsAttended()
    {
        try {
            $ticket = Ticket::find($this->qrData);

            if ($ticket->status !== 'upcoming') {
                Notification::make()
                    ->title("Cannot Mark as Attended")
                    ->body("Ticket #{$ticket->id} is not in 'upcoming' status. Current status: {$ticket->status}")
                    ->danger()
                    ->send();
                return;
            }

            $ticket->update(['status' => 'attended']);

            Notification::make()
                ->title("Attendance Recorded")
                ->body("Ticket #{$ticket->id} has been successfully marked as attended.")
                ->success()
                ->send();

            $this->reset('qrData');

        } catch (ValidationException $e) {
            Notification::make()
                ->title("Validation Error")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

}
