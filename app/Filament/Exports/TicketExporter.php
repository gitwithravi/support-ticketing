<?php

namespace App\Filament\Exports;

use App\Models\Ticket;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TicketExporter extends Exporter
{
    protected static ?string $model = Ticket::class;

    public static function getColumns(): array
    {
        return [
            // Primary identifiers
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('ticket_id')
                ->label('Ticket ID'),

            // Core ticket information
            ExportColumn::make('subject')
                ->label('Subject'),
            ExportColumn::make('ticket_description')
                ->label('Description'),

            // Client/Requester information

            ExportColumn::make('requester.name')
                ->label('Client Name'),
            ExportColumn::make('requester.unique_id')
                ->label('Client Unique ID'),

            // Assignment information

            ExportColumn::make('assignee.name')
                ->label('Assignee Name'),

            // Location information

            ExportColumn::make('building.name')
                ->label('Building Name'),
            ExportColumn::make('room_no')
                ->label('Room Number'),

            // Category information

            ExportColumn::make('category.name')
                ->label('Category Name'),

            ExportColumn::make('subCategory.name')
                ->label('Sub Category Name'),

            // Ticket properties
            ExportColumn::make('priority')
                ->label('Priority')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),
            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),
            ExportColumn::make('type')
                ->label('Type')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),
            ExportColumn::make('maintenance_term')
                ->label('Maintenance Term')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),

            // Status tracking
            ExportColumn::make('user_status')
                ->label('User Status')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),
            ExportColumn::make('cat_supervisor_status')
                ->label('Category Supervisor Status')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),
            ExportColumn::make('build_supervisor_status')
                ->label('Building Supervisor Status')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),

            // Verification and duplication
            ExportColumn::make('verifiedBy.name')
                ->label('Verified By'),
            ExportColumn::make('verification_status')
                ->label('Verification Status')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),
            ExportColumn::make('verification_timestamp')
                ->label('Verification Timestamp'),
            ExportColumn::make('verification_remarks')
                ->label('Verification Remarks'),

            // Flags and dates

            ExportColumn::make('ticket_closing_date')
                ->label('Closing Date'),

            // Timestamps
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your ticket export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
