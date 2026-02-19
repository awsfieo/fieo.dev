<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appraisal Form - {{ $record->employee->name }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @page { margin: 15px; }
        body { 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
            font-family: 'Inter', sans-serif;
        }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
{{-- GLOBAL: Set text to 10px and tighter line height --}}
<body class="bg-gray-50 p-6 text-[10px] text-gray-800 leading-snug">

    {{-- HEADER SECTION --}}
    <div class="mb-4">
        <h1 class="text-base font-bold text-gray-900 mb-2 uppercase">{{ $record->employee->name }}</h1>
        
        {{-- COMPACT GRID: Reduced padding (p-3) and gaps --}}
        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 grid grid-cols-6 gap-y-2 gap-x-2">
            
            {{-- Row 1 --}}
            <div>
                <span class="block text-gray-700 font-bold uppercase tracking-wider text-[9px]">Appraisal Ref No</span>
                <span class="font-bold text-gray-800">{{ $record->application_no }}</span>
            </div>
            <div>
                <span class="block text-gray-700 font-bold uppercase tracking-wider text-[9px]">Employee Code</span>
                <span class="font-bold text-gray-800">{{ $record->employee->employee_code ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-gray-700 font-bold uppercase tracking-wider text-[9px]">Appraisal Year</span>
                <span class="font-bold text-gray-800">{{ $record->appraisal_year }}</span>
            </div>

            {{-- Row 2 --}}
            <div>
                <span class="block text-gray-700 font-bold uppercase tracking-wider text-[9px]">Designation</span>
                {{-- Uses Appraisal Snapshot --}}
                <span class="font-bold text-gray-800">{{ $record->designation->designation ?? '-' }}</span>
            </div>
            <div class="col-span-2">
                <span class="block text-gray-700 font-bold uppercase tracking-wider text-[9px]">Department</span>
                {{-- Uses Appraisal Snapshot --}}
                <span class="font-bold text-gray-800">{{ $record->department->department ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- APPRAISAL FORM CARD --}}
    <div class="bg-white border-2 border-dark-600 rounded-xl overflow-hidden shadow-sm">
        
        {{-- Card Header: Reduced padding --}}
        <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h2 class="text-[11px] font-bold text-gray-900 uppercase tracking-wide">Appraisal Form</h2>
            <p class="text-gray-400 text-[9px]">To be filled by the Employee</p>
        </div>

        {{-- Content: Reduced spacing between questions (space-y-4) --}}
        <div class="p-4 space-y-4">

            {{-- Question 1 --}}
            <div class="no-break">
                <h3 class="text-dark-700 font-bold text-[11px] mb-1">1. Define your job profile</h3>
                <div class="text-gray-700 text-justify">
                    {!! $record->appraisal_form_data['job_profile'] ?? 'Not provided.' !!}
                </div>
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Question 2 --}}
            <div class="no-break">
                <h3 class="text-dark-700 font-bold text-[11px] mb-1">2. How satisfied are you with your job profile?</h3>
                <div>
                    @php
                        $satisfaction = $record->appraisal_form_data['job_satisfaction'] ?? 'Not Provided';
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border bg-gray-100 text-gray-700 border-gray-200">
                        {{ $satisfaction }}
                    </span>
                </div>
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Question 3 --}}
            <div class="no-break">
                <h3 class="text-dark-700 font-bold text-[11px] mb-1">3. Outline what you value most about your job profile and/or changes, if any, that could help better utilise your potential within the Federation</h3>
                <div class="text-gray-700 text-justify">
                    {!! $record->appraisal_form_data['job_enrichment'] ?? 'Not provided.' !!}
                </div>
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Question 4 --}}
            <div class="no-break">
                <h3 class="text-dark-700 font-bold text-[11px] mb-1">4. What were your achievements during the review period and how did you achieve them?</h3>
                <div class="text-gray-700 text-justify">
                    {!! $record->appraisal_form_data['achievements'] ?? 'Not provided.' !!}
                </div>
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Question 5 --}}
            <div class="no-break">
                <h3 class="text-dark-700 font-bold text-[11px] mb-1">5. What areas of your individual performance could have been better during the review period? What support is required to improve the performance?</h3>
                <div class="text-gray-700 text-justify">
                    {!! $record->appraisal_form_data['performance_gaps'] ?? 'Not provided.' !!}
                </div>
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Question 6 --}}
            <div class="no-break">
                <h3 class="text-dark-700 font-bold text-[11px] mb-1">6. What are your medium to long-term career goals? How can the Federation help you to achieve them?</h3>
                <div class="text-gray-700 text-justify">
                    {!! $record->appraisal_form_data['career_goals'] ?? 'Not provided.' !!}
                </div>
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Question 7 --}}
            <div class="no-break">
                <h3 class="text-dark-700 font-bold text-[11px] mb-1">7. Outline specific training and mentoring programs which would improve your performance and make you more relevant and valuable to the Federation.</h3>
                <div class="text-gray-700 text-justify">
                    {!! $record->appraisal_form_data['training_needs'] ?? 'Not provided.' !!}
                </div>
            </div>

        </div>
    </div>

    <div class="mt-2 text-center text-[9px] text-gray-400">
        Generated on {{ date('d M Y, h:i A') }}
    </div>

</body>
</html>