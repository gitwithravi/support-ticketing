<?php

namespace App\Filament\Widgets;

use App\Enums\Tickets\TicketType;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketTypeChart extends ChartWidget
{
    protected static ?string $heading = 'Tickets by type';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $query = Ticket::query();
        $currentUser = auth()->user();

        // Apply user-based filtering
        if ($currentUser) {
            if ($currentUser->isBuildingSupervisor()) {
                // Building supervisors see only tickets from buildings they supervise
                $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('id');
                $query->whereIn('building_id', $supervisedBuildingIds);
            } elseif ($currentUser->isAgent()) {
                // Agents see only tickets assigned to them
                $query->where('assignee_id', $currentUser->id);
            }
            // Note: Category supervisors and admin users see all tickets
        }

        $counts = $query
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $ticketTypes = TicketType::cases();

        $data = collect($ticketTypes)
            ->map(fn ($ticketType) => $counts->get($ticketType->value, 0))
            ->toArray();

        $labels = collect($ticketTypes)
            ->map(fn ($ticketType) => $ticketType->getLabel())
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets by type',
                    'data' => $data,
                    'backgroundColor' => [
                        '#60a5fa',
                        '#fbbf24',
                        '#f87171',
                        '#4ade80',
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
