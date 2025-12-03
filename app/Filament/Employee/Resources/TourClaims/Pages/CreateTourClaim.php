<?php

namespace App\Filament\Employee\Resources\TourClaims\Pages;

use App\Filament\Employee\Resources\TourClaims\TourClaimResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTourClaim extends CreateRecord
{
    protected static string $resource = TourClaimResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. CAPTURE DATA FIRST: Grab meals data before any other logic runs
        $mealsProvided = $data['meals_provided'] ?? 'no';
        $mealsDetails  = $data['meals_provided_details'] ?? [];

        // 2. Run your existing Resource logic (handles Advance, defaults, etc.)
        //    Even if this removes 'meals_provided' from $data, we saved it above.
        $data = $this->getResource()::mutateFormDataBeforeCreate($data);

        // 3. Prepare Payload
        $payload = $data['payload_json'] ?? [];

        // 4. Inject the captured meals data
        $payload['meals_provided'] = $mealsProvided;
        $payload['meals_details']  = $mealsDetails;

        // 5. Save payload back to data
        $data['payload_json'] = $payload;

        // 6. Cleanup to prevent "Column not found" SQL error
        unset($data['meals_provided']);
        unset($data['meals_provided_details']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->getResource()::mutateFormDataBeforeSave($data);
    }

    protected function afterCreate(): void
    {
        $this->recalculateTotals();
    }

    protected function afterSave(): void
    {
        // For edit page (if you ever use it)
        $this->recalculateTotals();
    }

    protected function recalculateTotals(): void
    {
        /** @var \App\Models\TourClaim $record */
        $record = $this->record;

        // 1. Totals from items
        $totalInr = (float) $record->items()->sum('amount_inr');
        $totalFx  = (float) $record->items()->sum('amount_forex');

        // 2. Advances (already split into INR / FOREX)
        $advInr = (float) $record->advance_inr;
        $advFx  = (float) $record->advance_forex;

        // 3. Net positions
        $netInr = $totalInr - $advInr;
        $netFx  = $totalFx  - $advFx;

        // 4. Split into reimbursement vs refund (always positive)
        $reimInr   = $netInr > 0 ? $netInr : 0;
        $refundInr = $netInr < 0 ? abs($netInr) : 0;

        $reimFx   = $netFx > 0 ? $netFx : 0;
        $refundFx = $netFx < 0 ? abs($netFx) : 0;

        $record->update([
            'total_expenses_inr'        => $totalInr,
            'total_expenses_forex'      => $totalFx,
            'amount_reimburse_inr'      => $reimInr,
            'amount_reimburse_forex'    => $reimFx,
            'amount_refund_inr'         => $refundInr,
            'amount_refund_forex'       => $refundFx,
        ]);
    }
}
