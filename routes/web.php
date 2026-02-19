<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;
use App\Models\Appraisal;
use Illuminate\Support\Str;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user && $user->hasRole('Employee')) {
            // If request came from Inertia (XHR), force full page navigation
            if (request()->header('X-Inertia')) {
                return Inertia::location('/employee');
            }

            // Normal browser request
            return redirect('/employee');
        }

        return Inertia::render('dashboard');
    })->name('dashboard');
});


Route::middleware(['auth', 'verified'])->group(function () {

    // IMPORTANT: Put this BEFORE any other /settings routes
    Route::get('/settings/{any?}', function () {
        $user = Auth::user();

        if ($user && $user->hasRole('Employee')) {
            // Inertia request: force full page load to Filament
            if (request()->header('X-Inertia')) {
                return Inertia::location('/employee');
            }

            return redirect('/employee');
        }

        // If not employee, send them to the normal settings landing (adjust as needed)
        // Option 1: redirect to your profile settings page
        return redirect('/settings/profile');
    })
        ->where('any', '.*')
        ->name('settings.catchall');
});

Route::middleware(['auth'])->get('/employee/appraisals/{appraisal}/pdf', function (Appraisal $appraisal) {

    $appraisal = $appraisal->fresh([
        'employee', 
        'designation', 
        'department'
    ]);

    // 1. Clean the name (remove special chars) but keep spaces for now
    $cleanName = Str::slug($appraisal->employee->name, ' '); // "ankit dewlekar"

    // 2. Capitalize each word
    $titleName = Str::title($cleanName); // "Ankit Dewlekar"

    // 3. Replace spaces with hyphens
    $finalName = str_replace(' ', '-', $titleName); // "Ankit-Dewlekar"

    // Final Result: Appraisal-Ankit-Dewlekar-2026.pdf
    $fileName = 'Appraisal-' . $finalName . '-' . $appraisal->appraisal_year . '.pdf';

    return Pdf::view('pdf.employee-appraisal', ['record' => $appraisal])
        ->format('a4')
        ->name($fileName)
        ->download();
})->name('employee.appraisals.pdf');


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
