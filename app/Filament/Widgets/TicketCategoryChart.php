<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Tickets by category';

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
            ->selectRaw('category_id, COUNT(*) as total')
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $categories = Category::active()->ordered()->get();

        $data = $categories
            ->map(fn ($category) => $counts->get($category->id, 0))
            ->toArray();

        $labels = $categories
            ->map(fn ($category) => $category->name)
            ->toArray();

        // Generate colors for categories
        $colors = $categories
            ->map(function ($category, $index) {
                // Use category color if available, otherwise generate from predefined palette
                if (!empty($category->color)) {
                    return $category->color;
                }
                
                $colorPalette = [
                    '#60a5fa', '#fbbf24', '#f87171', '#4ade80',
                    '#a78bfa', '#fb7185', '#fcd34d', '#34d399',
                    '#f472b6', '#38bdf8', '#fbbf24', '#fb923c'
                ];
                
                return $colorPalette[$index % count($colorPalette)];
            })
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets by category',
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