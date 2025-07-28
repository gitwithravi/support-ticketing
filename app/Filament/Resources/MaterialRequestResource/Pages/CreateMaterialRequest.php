<?php

namespace App\Filament\Resources\MaterialRequestResource\Pages;

use App\Filament\Resources\MaterialRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterialRequest extends CreateRecord
{
    protected static string $resource = MaterialRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
