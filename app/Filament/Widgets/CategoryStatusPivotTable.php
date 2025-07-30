<?php

namespace App\Filament\Widgets;

use App\Enums\Tickets\TicketStatus;
use App\Models\Category;
use App\Models\Ticket;
use ArberMustafa\FilamentGoogleCharts\Widgets\GoogleChartWidget;

class CategoryStatusPivotTable extends GoogleChartWidget
{
    protected static ?string $heading = 'Category-wise Ticket Status Summary';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 20;

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

        // Get all active categories
        $categoriesQuery = Category::where('is_active', true)->orderBy('name');

        // Apply user-based filtering if needed
        if ($currentUser && $currentUser->isCategorySupervisor()) {
            // Category supervisors see only their supervised categories
            $categoriesQuery->where('category_supervisor_id', $currentUser->id);
        } elseif ($currentUser && $currentUser->isBuildingSupervisor()) {
            // Building supervisors see tickets from their buildings only
            $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('buildings.id');
            $categoriesQuery->whereHas('tickets', function ($query) use ($supervisedBuildingIds) {
                $query->whereIn('building_id', $supervisedBuildingIds);
            });
        } elseif ($currentUser && $currentUser->isAgent()) {
            // Agents see categories from tickets assigned to them
            $categoriesQuery->whereHas('tickets', function ($query) use ($currentUser) {
                $query->where('assignee_id', $currentUser->id);
            });
        }

        $categories = $categoriesQuery->get();

        // Get all ticket statuses
        $statuses = TicketStatus::cases();

        // Build the header row
        $header = [
            ['label' => 'Category', 'type' => 'string'],
        ];

        foreach ($statuses as $status) {
            $header[] = ['label' => $status->getLabel(), 'type' => 'number'];
        }

        // Add total column
        $header[] = ['label' => 'Total', 'type' => 'number'];

        $data = [$header];

        // Get ticket counts for each category and status combination
        $ticketQuery = Ticket::selectRaw('category_id, status, COUNT(*) as count')
            ->whereNotNull('category_id')
            ->groupBy('category_id', 'status');

        // Apply user-based filtering to ticket counts
        if ($currentUser && $currentUser->isBuildingSupervisor()) {
            $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('buildings.id');
            $ticketQuery->whereIn('building_id', $supervisedBuildingIds);
        } elseif ($currentUser && $currentUser->isAgent()) {
            $ticketQuery->where('assignee_id', $currentUser->id);
        }

        $rawCounts = $ticketQuery->get();

        // Organize counts by category and status
        $ticketCounts = [];
        foreach ($rawCounts as $record) {
            $ticketCounts[$record->category_id][$record->status->value] = $record->count;
        }

        // Build data rows
        foreach ($categories as $category) {
            $row = [$category->name];
            $total = 0;

            $categoryCounts = $ticketCounts[$category->id] ?? [];

            foreach ($statuses as $status) {
                $count = $categoryCounts[$status->value] ?? 0;
                $row[] = $count;
                $total += $count;
            }

            // Add total
            $row[] = $total;
            $data[] = $row;
        }

        // Add summary row with totals for each status
        if (count($categories) > 1) {
            $summaryRow = ['<strong>Total</strong>'];
            $grandTotal = 0;

            foreach ($statuses as $status) {
                $statusTotal = 0;
                foreach ($ticketCounts as $categoryCounts) {
                    $statusTotal += $categoryCounts[$status->value] ?? 0;
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
        return static::$heading ?? 'Category Status Pivot Table';
    }
}
