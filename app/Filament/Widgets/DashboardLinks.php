<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardLinks extends BaseWidget
{

    protected static ?int $sort = -3; // Lower numbers appear higher on the page

    protected function getStats(): array
    {
        return [
            Stat::make('Appointment Form', 'Public View')
                ->description('Go to the public booking page')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info')
                ->url(route('appointment.create')),

            Stat::make('TV Display', 'Queue View')
                ->description('Open the live queue monitor')
                ->descriptionIcon('heroicon-m-tv')
                ->color('success')
                ->url(route('tv.display')),

            Stat::make('Receptionist Table', 'Management')
                ->description('View all recorded appointments')
                ->descriptionIcon('heroicon-m-table-cells')
                ->color('warning')
                ->url(route('appointments.index')),
        ];
    }
}
