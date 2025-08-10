<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\File;

class LogViewer extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.log-viewer';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = ' Logs';
    protected static ?string $title = ' Logs';

    public $logContent;

    public function mount(): void
    {
        $path = storage_path('logs/laravel.log');

        if (File::exists($path)) {

            $lines = explode("\n", File::get($path));

            $this->logContent = implode("\n", array_slice($lines, -500));
        } else {
            $this->logContent = "No log file found.";
        }
    }
}
