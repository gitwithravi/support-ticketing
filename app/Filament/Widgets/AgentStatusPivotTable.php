<?php

namespace App\Filament\Widgets;

use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use ArberMustafa\FilamentGoogleCharts\Widgets\GoogleChartWidget;

class AgentStatusPivotTable extends GoogleChartWidget
{
    protected static ?string $heading = 'Agent-wise Ticket Status Summary';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 30;

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

        // Get all agents (users who can be assigned tickets)
        $agentsQuery = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['agent']);
        })->orderBy('name');

        // Apply user-based filtering if needed
        if ($currentUser && $currentUser->isAgent()) {
            // If current user is an agent, show only their data
            $agentsQuery->where('id', $currentUser->id);
        }

        $agents = $agentsQuery->get();

        // Get all ticket statuses
        $statuses = TicketStatus::cases();

        // Build the header row
        $header = [
            ['label' => 'Agent', 'type' => 'string'],
        ];

        foreach ($statuses as $status) {
            $header[] = ['label' => $status->getLabel(), 'type' => 'number'];
        }

        // Add total column
        $header[] = ['label' => 'Total', 'type' => 'number'];

        $data = [$header];

        // Get ticket counts for each agent and status combination
        $rawCounts = Ticket::selectRaw('assignee_id, status, COUNT(*) as count')
            ->whereNotNull('assignee_id')
            ->groupBy('assignee_id', 'status')
            ->get();

        // Organize counts by agent and status
        $ticketCounts = [];
        foreach ($rawCounts as $record) {
            $ticketCounts[$record->assignee_id][$record->status->value] = $record->count;
        }

        // Build data rows
        foreach ($agents as $agent) {
            $row = [$agent->name];
            $total = 0;

            $agentCounts = $ticketCounts[$agent->id] ?? [];

            foreach ($statuses as $status) {
                $count = $agentCounts[$status->value] ?? 0;
                $row[] = $count;
                $total += $count;
            }

            // Add total
            $row[] = $total;
            $data[] = $row;
        }

        // Add summary row with totals for each status
        if (count($agents) > 1) {
            $summaryRow = ['<strong>Total</strong>'];
            $grandTotal = 0;

            foreach ($statuses as $status) {
                $statusTotal = 0;
                foreach ($ticketCounts as $agentCounts) {
                    $statusTotal += $agentCounts[$status->value] ?? 0;
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
        return static::$heading ?? 'Agent Status Pivot Table';
    }
}
