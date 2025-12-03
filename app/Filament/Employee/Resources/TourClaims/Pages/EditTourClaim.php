<?php

namespace App\Filament\Employee\Resources\TourClaims\Pages;

use App\Filament\Employee\Resources\TourClaims\TourClaimResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTourClaim extends EditRecord
{
    protected static string $resource = TourClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. CAPTURE DATA FIRST
        $mealsProvided = $data['meals_provided'] ?? 'no';
        $mealsDetails  = $data['meals_provided_details'] ?? [];

        // 2. Run existing Resource logic
        $data = $this->getResource()::mutateFormDataBeforeSave($data);

        // 3. Prepare Payload
        $payload = $data['payload_json'] ?? [];

        // 4. Inject captured data
        $payload['meals_provided'] = $mealsProvided;
        $payload['meals_details']  = $mealsDetails;

        // 5. Save payload
        $data['payload_json'] = $payload;

        // 6. Cleanup
        unset($data['meals_provided']);
        unset($data['meals_provided_details']);

        return $data;
    }

    // Ensure data loads correctly when you open the Edit page
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $payload = $data['payload_json'] ?? [];

        $data['meals_provided'] = $payload['meals_provided'] ?? 'no';
        $data['meals_provided_details'] = $payload['meals_details'] ?? [];

        return $data;
    }
}
