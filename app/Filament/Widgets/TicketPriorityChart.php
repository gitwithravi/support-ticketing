<?php

namespace App\Filament\Widgets;

use App\Enums\Tickets\TicketPriority;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketPriorityChart extends ChartWidget
{
    protected static ?string $heading = 'Tickets by priority';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $query = Ticket::query();
        $currentUser = auth()->user();

        // Apply user-based filtering
        if ($currentUser && $currentUser->isBuildingSupervisor()) {
            // Building supervisors see only tickets from buildings they supervise
            $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('id');
            $query->whereIn('building_id', $supervisedBuildingIds);
        }
        // Note: Category supervisors and other user types see all tickets

        $counts = $query
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $ticketPriorities = TicketPriority::cases();

        $data = collect($ticketPriorities)
            ->map(fn ($ticketPriority) => $counts->get($ticketPriority->value, 0))
            ->toArray();

        $labels = collect($ticketPriorities)
            ->map(fn ($ticketPriority) => $ticketPriority->getLabel())
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets by priority',
                    'data' => $data,
                    'backgroundColor' => [
                        '#4ade80',
                        '#60a5fa',
                        '#fbbf24',
                        '#f87171',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
