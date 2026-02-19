<?php

namespace App\Jobs;

use App\Models\Appraisal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ArchiveAppraisalsPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $appraisalIds;
    public $userToNotify;

    /**
     * Create a new job instance.
     *
     * @param array $appraisalIds Array of IDs to process
     * @param mixed $userToNotify The user model to notify upon completion
     */
    public function __construct(array $appraisalIds, $userToNotify)
    {
        $this->appraisalIds = $appraisalIds;
        $this->userToNotify = $userToNotify;
    }

    public function handle()
    {
        $count = 0;
        $errors = 0;

        // 1. Fetch records with the snapshot relationships
        $records = Appraisal::whereIn('id', $this->appraisalIds)
            ->with(['employee', 'designation', 'department']) // Load snapshot data
            ->get();

        foreach ($records as $record) {
            try {
                // 2. Prepare Directory Structure
                // Structure: storage/app/appraisals/2026/Marketing/
                $year = $record->appraisal_year;
                $deptName = $record->department->department ?? 'General';
                $cleanDept = Str::slug($deptName);

                // 3. Prepare Filename
                // LOGIC: Title Case (Ankit-Dewlekar)
                // Step A: Slug with spaces allowed to clean special chars
                $slugName = Str::slug($record->employee->name, ' ');
                // Step B: Capitalize Words
                $titleName = Str::title($slugName);
                // Step C: Replace spaces with hyphens
                $cleanName = str_replace(' ', '-', $titleName);
                $empCode = $record->employee->employee_code ?? 'NA';
                $filename = "Appraisal-{$year}-{$cleanName}-{$empCode}.pdf";

                // 4. Define Full Path
                // $relativePath = "appraisals/{$year}/{$cleanDept}/{$filename}"; // If you want to include department in the path
                $relativePath = "appraisals-pdf/{$year}/{$filename}";
                $absolutePath = storage_path('app/' . $relativePath);

                // Ensure directory exists
                $directory = dirname($absolutePath);
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                // 5. Generate and Save PDF
                Pdf::view('pdf.full-appraisal-report', ['record' => $record])
                    ->format('a4')
                    ->save($absolutePath);

                $count++;
            } catch (\Exception $e) {
                $errors++;
                Log::error("Appraisal Archiving Failed for ID {$record->id}: " . $e->getMessage());
            }
        }

        // 6. Send Notification to the Admin/HOD who started the job
        if ($this->userToNotify) {
            Notification::make()
                ->title('Archiving Completed')
                ->body("Processed {$count} files successfully. " . ($errors > 0 ? "({$errors} failed)" : ""))
                ->success()
                ->sendToDatabase($this->userToNotify);
        }
    }
}
