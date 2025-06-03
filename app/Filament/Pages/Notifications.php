<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Notifications\CustomTextNotification;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\ButtonAction;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class Notifications extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Send Text Notification';
    protected static string $view = 'filament.pages.notifications';

    public ?string $notificationTitle = '';
    public ?string $message = '';

    public function mount(): void
    {
        $this->form->fill([
            'notificationTitle' => '',
            'message' => '',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('notificationTitle')
                ->label('Notification Title')
                ->required(),

            Textarea::make('message')
                ->label('Notification Message')
                ->required()
                ->rows(5),
        ];
    }

    public function send(): void
    {
        $data = $this->form->getState();

        NotificationFacade::send(
            Customer::whereNotNull('fcm_token')->get(),
            new CustomTextNotification($data['notificationTitle'], $data['message'])
        );

        Notification::make()
            ->title('Notification Sent')
            ->body('Your message has been sent to all customers.')
            ->success()
            ->send();

        $this->form->fill([
            'notificationTitle' => '',
            'message' => '',
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            ButtonAction::make('Send')
                ->label('Send')
                ->action('send')
                ->color('primary'),
        ];
    }
}
