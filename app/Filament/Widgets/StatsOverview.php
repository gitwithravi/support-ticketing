<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $currentCounts = $this->getCounts();
        $previousCounts = $this->getCounts(Carbon::now()->subDays(30));

        return [
            $this->makeStat(
                'Created tickets',
                $currentCounts['total'],
                $this->getChange($previousCounts['total'], $currentCounts['total'])
            ),
            $this->makeStat('Unsolved tickets',
                $currentCounts['unsolved'],
                $this->getChange($previousCounts['unsolved'], $currentCounts['unsolved'])
            ),
            $this->makeStat('Solved tickets',
                $currentCounts['solved'],
                $this->getChange($previousCounts['solved'], $currentCounts['solved'])
            ),
        ];
    }

    private function getCounts(?Carbon $before = null): array
    {
        $query = Ticket::query();
        $currentUser = auth()->user();

        // Apply user-based filtering
        if ($currentUser) {
            if ($currentUser->isBuildingSupervisor()) {
                // Building supervisors see only tickets from buildings they supervise
                $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('buildings.id');
                $query->whereIn('building_id', $supervisedBuildingIds);
            } elseif ($currentUser->isAgent()) {
                // Agents see only tickets assigned to them
                $query->where('assignee_id', $currentUser->id);
            }
            // Note: Category supervisors and admin users see all tickets
        }

        if ($before) {
            $query->where('created_at', '<', $before);
        }

        return [
            'total' => $query->count(),
            'unsolved' => $query->clone()->unsolved()->count(),
            'solved' => $query->clone()->solved()->count(),
        ];
    }

    private function makeStat(string $label, int $value, array $change): Stat
    {
        return Stat::make($label, number_format($value))
            ->description($change['description'])
            ->descriptionIcon($change['icon'])
            ->color($change['color']);
    }

    private function getChange(int $previous, int $current): array
    {
        if ($previous === 0) {
            return [
                'description' => 'No previous data',
                'icon' => 'heroicon-o-minus',
                'color' => 'gray',
            ];
        }

        $changePercent = (($current - $previous) / $previous) * 100;
        $icon = 'heroicon-o-minus';
        $color = 'gray';
        $description = '0% change';

        if ($changePercent > 0) {
            $icon = 'heroicon-m-arrow-trending-up';
            $color = 'success';
            $description = number_format($changePercent, 2).'% increase';
        } elseif ($changePercent < 0) {
            $icon = 'heroicon-m-arrow-trending-down';
            $color = 'danger';
            $description = number_format(abs($changePercent), 2).'% decrease';
        }

        return [
            'description' => $description,
            'icon' => $icon,
            'color' => $color,
        ];
    }
}
