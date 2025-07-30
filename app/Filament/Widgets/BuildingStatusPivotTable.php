<?php

namespace App\Filament\Widgets;

use App\Enums\Tickets\TicketStatus;
use App\Models\Building;
use App\Models\Ticket;
use ArberMustafa\FilamentGoogleCharts\Widgets\GoogleChartWidget;

class BuildingStatusPivotTable extends GoogleChartWidget
{
    protected static ?string $heading = 'Building-wise Ticket Status Summary';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 50;

    protected static ?array $options = [
        'width' => '100%',
        'height' => 400,
        'alternatingRowStyle' => false,
        'cssClassNames' => [
            'headerRow' => 'header-style',
            'tableRow' => 'table-row',
            'oddTableRow' => 'odd-row',
        ],
        'allowHtml' => true,
        'sort' => 'enable',
    ];

    protected function getType(): string
    {
        return 'Table';
    }

    protected function getData(): array
    {
        $currentUser = auth()->user();

        // Get all active buildings
        $buildingsQuery = Building::active()->ordered();

        // Apply user-based filtering if needed
        if ($currentUser && $currentUser->isBuildingSupervisor()) {
            // Building supervisors see only their supervised buildings
            $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('id');
            $buildingsQuery->whereIn('id', $supervisedBuildingIds);
        } elseif ($currentUser && $currentUser->isAgent()) {
            // Agents see buildings from tickets assigned to them
            $buildingsQuery->whereHas('tickets', function ($query) use ($currentUser) {
                $query->where('assignee_id', $currentUser->id);
            });
        }

        $buildings = $buildingsQuery->get();

        // Get all ticket statuses
        $statuses = TicketStatus::cases();

        // Build the header row
        $header = [
            ['label' => 'Building', 'type' => 'string'],
        ];

        foreach ($statuses as $status) {
            $header[] = ['label' => $status->getLabel(), 'type' => 'number'];
        }

        // Add total column
        $header[] = ['label' => 'Total', 'type' => 'number'];

        $data = [$header];

        // Get ticket counts for each building and status combination
        $ticketQuery = Ticket::selectRaw('building_id, status, COUNT(*) as count')
            ->whereNotNull('building_id')
            ->groupBy('building_id', 'status');

        // Apply user-based filtering to ticket counts
        if ($currentUser && $currentUser->isBuildingSupervisor()) {
            $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('id');
            $ticketQuery->whereIn('building_id', $supervisedBuildingIds);
        } elseif ($currentUser && $currentUser->isAgent()) {
            $ticketQuery->where('assignee_id', $currentUser->id);
        }

        $rawCounts = $ticketQuery->get();

        // Organize counts by building and status
        $ticketCounts = [];
        foreach ($rawCounts as $record) {
            $ticketCounts[$record->building_id][$record->status->value] = $record->count;
        }

        // Build data rows
        foreach ($buildings as $building) {
            $buildingName = $building->full_name ?? $building->name;
            $row = [$buildingName];
            $total = 0;

            $buildingCounts = $ticketCounts[$building->id] ?? [];

            foreach ($statuses as $status) {
                $count = $buildingCounts[$status->value] ?? 0;
                $row[] = $count;
                $total += $count;
            }

            // Add total
            $row[] = $total;
            $data[] = $row;
        }

        // Add summary row with totals for each status
        if (count($buildings) > 1) {
            $summaryRow = ['<strong>Total</strong>'];
            $grandTotal = 0;

            foreach ($statuses as $status) {
                $statusTotal = 0;
                foreach ($ticketCounts as $buildingCounts) {
                    $statusTotal += $buildingCounts[$status->value] ?? 0;
                }
                $summaryRow[] = $statusTotal;
                $grandTotal += $statusTotal;
            }

            $summaryRow[] = $grandTotal;
            $data[] = $summaryRow;
        }

        return $data;
    }

    protected function getOptions(): ?array
    {
        return array_merge(parent::getOptions() ?? [], [
            'width' => '100%',
            'height' => 400,
            'alternatingRowStyle' => false,
            'cssClassNames' => [
                'headerRow' => 'bg-gray-100 font-bold',
                'tableRow' => 'hover:bg-gray-50',
                'oddTableRow' => 'bg-gray-25',
            ],
            'allowHtml' => true,
            'sort' => 'enable',
            'sortColumn' => -1, // Sort by total column by default
            'sortAscending' => false,
            'page' => 'enable',
            'pageSize' => 10,
            'pagingSymbols' => [
                'prev' => 'Previous',
                'next' => 'Next',
            ],
            'pagingButtonsConfiguration' => 'auto',
        ]);
    }

    protected function getHeading(): string
    {
        return static::$heading ?? 'Building Status Pivot Table';
    }
}
