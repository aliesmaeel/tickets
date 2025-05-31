<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\Ticket;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\View\View;

class ScanQRCode extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static string $view = 'filament.pages.scan-q-r-code';

    protected static ?string $title = 'Scan QR Code';
    public $qrData = null;
    public $event;
    public $row;
    public $col;
    public $customer;
    public $price;
    public $event_start_time;

    public $isVerified = false;
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
        $this->isVerified = false;
        try {
            if (preg_match('/\d+/', $this->qrData, $matches)) {
                $number = $matches[0];
            }
            $this->qrData=$number;
            $ticket = Ticket::find($this->qrData);
            if (!$ticket) {
                throw ValidationException::withMessages(['qrData' => 'Ticket not found.']);
            }else{
                $ticket=$ticket->load(['event', 'orderSeat.eventSeat', 'customer']);
            }
            if($ticket->order->reservation_type ==='Cache' && $ticket->order->reservation_status == false){
                throw ValidationException::withMessages(['qrData' => 'The Order is not Placed yet.']);
            }

            $this->event = $ticket->event->name['en'] ;
            $this->row = $ticket->orderSeat->eventSeat->row;
            $this->col = $ticket->orderSeat->eventSeat->col;
            $this->customer = $ticket->customer;
            $this->price = $ticket->event->seatClasses->where('id', $ticket->orderSeat->eventSeat->seat_class_id)->first()->price;
            $this->event_start_time = $ticket->event->start_time;

            if ($ticket->status !== 'upcoming') {
                Notification::make()
                    ->title("Ticket Status Issue")
                    ->body("Ticket #{$ticket->id} is not in 'upcoming' status. Current status: {$ticket->status}")
                    ->danger()
                    ->send();
                return;
            }
            $this->isVerified = true;


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
