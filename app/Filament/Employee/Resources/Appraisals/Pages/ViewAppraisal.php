<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use App\Services\AppraisalWorkflow;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\Appraisal;
use Filament\Actions\Action;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;


class ViewAppraisal extends ViewRecord
{
    protected static string $resource = AppraisalResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        // This bypasses any in-memory caching on the existing model instance
        $this->record = $this->getRecord()->fresh();
    }

    public function getTitle(): string | Htmlable
    {
        // Uses the relationship to get the real name
        return $this->getRecord()->employee->name ?? 'View Appraisal';
    }

    protected function getHeaderActions(): array
    {
        return [
            // 1. SUBMIT ACTION (For Employee - Draft Only)
            Actions\Action::make('submit')
                ->label('Submit Appraisal')
                ->color('warning')
                ->icon('heroicon-m-paper-airplane')
                ->visible(
                    fn($record) =>
                    $record->status === 'draft' &&
                        $record->employee_id === Auth::user()->employee?->id
                )
                ->requiresConfirmation()
                ->modalHeading('Submit Appraisal?')
                ->modalDescription('Only one appraisal form can be submitted in a year. After the Appraisal Form is submitted, it will be locked and cannot be edited or recalled back. 
                Therefore, ensure that all information provided is accurate and complete before proceeding.')
                ->action(function (AppraisalWorkflow $workflow) {
                    $workflow->submit($this->getRecord());
                    Notification::make()->title('Appraisal Submitted Successfully')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 2. EDIT ACTION (Draft Only)
            Actions\EditAction::make()
                ->visible(fn($record) => $record->status === 'draft'),

            // 3. ASSESS ACTION (Reporting Officer)
            Actions\Action::make('assess')
                ->label('Fill Assessment')
                ->color('warning')
                ->icon('heroicon-m-pencil-square')
                ->visible(
                    fn($record) =>
                    $record->status === 'submitted' &&
                        $record->pending_with === Auth::user()->employee?->employee_code
                )
                // Redirect to Edit page to fill Form B
                ->url(fn($record) => $this->getResource()::getUrl('edit', ['record' => $record]) . '?tab=evaluation-form'),

            // 4. REGIONAL REVIEW ACTION
            Actions\Action::make('regional_review')
                ->label('Regional Head Review')
                ->color('warning')
                ->icon('heroicon-m-pencil-square')
                ->visible(
                    fn($record) =>
                    $record->status === 'regional_head_review_pending' &&
                        $record->pending_with === Auth::user()->employee?->employee_code
                )
                ->url(fn($record) => $this->getResource()::getUrl('edit', ['record' => $record]) . '?tab=regional-head-review'),

            // 5. FINALIZE (DG)
            Actions\Action::make('finalize')
                ->label('Final Assessment')
                ->icon('heroicon-m-pencil-square')
                ->color('warning')
                ->visible(
                    fn($record) =>
                    $record->status === 'final_assessment_pending' &&
                        Auth::user()->hasRole('DG & CEO')
                )
                ->url(fn($record) => $this->getResource()::getUrl('edit', ['record' => $record]) . '?tab=final-assessment'),

            Actions\Action::make('download_self_appraisal')
                ->label('Download Form')
                ->icon('heroicon-m-document-arrow-down')
                ->color('dark')
                ->visible(fn($record) => in_array($record->status, ['submitted', 'regional_head_review_pending', 'final_assessment_pending', 'completed']) && $record->employee_id === Auth::user()->employee?->id)
                ->url(fn($record) => route('employee.appraisals.pdf', ['appraisal' => $record]), shouldOpenInNewTab: true),


        ];
    }
}
