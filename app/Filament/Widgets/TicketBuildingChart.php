<?php

namespace App\Filament\Widgets;

use App\Models\Building;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketBuildingChart extends ChartWidget
{
    protected static ?string $heading = 'Tickets by building';

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
            ->selectRaw('building_id, COUNT(*) as total')
            ->whereNotNull('building_id')
            ->groupBy('building_id')
            ->pluck('total', 'building_id');

        $buildings = Building::active()->ordered()->get();

        $data = $buildings
            ->map(fn ($building) => $counts->get($building->id, 0))
            ->toArray();

        $labels = $buildings
            ->map(fn ($building) => $building->full_name ?? $building->name)
            ->toArray();

        // Generate colors for buildings
        $colors = $buildings
            ->map(function ($building, $index) {
                $colorPalette = [
                    '#60a5fa', '#fbbf24', '#f87171', '#4ade80',
                    '#a78bfa', '#fb7185', '#fcd34d', '#34d399',
                    '#f472b6', '#38bdf8', '#fbbf24', '#fb923c',
                    '#6366f1', '#ef4444', '#10b981', '#f59e0b',
                    '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'
                ];
                
                return $colorPalette[$index % count($colorPalette)];
            })
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets by building',
                    'data' => $data,
                    'backgroundColor' => $colors,
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