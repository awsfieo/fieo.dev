<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appraisal Report - {{ $record->employee->name }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @page { margin: 15px; }
        body { 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
            font-family: 'Inter', sans-serif;
        }
        .no-break { page-break-inside: avoid; }
        .page-break { page-break-before: always; }
        
        /* Custom Colors matching Infolist */
        .border-eval { border-color: #216611; }
        .bg-eval { background-color: #216611; color: white; }
        .text-eval { color: #216611; }
        
        .border-regional { border-color: #535752; }
        .bg-regional { background-color: #535752; color: white; }
        .text-regional { color: #535752; }
        
        .border-final { border-color: #663399; }
        .bg-final { background-color: #663399; color: white; }
        .text-final { color: #663399; }
    </style>
</head>
<body class="bg-gray-50 p-6 text-[10px] text-gray-800 leading-snug">

    {{-- ========================================== --}}
    {{-- HEADER SECTION --}}
    {{-- ========================================== --}}
    <div class="mb-4">
        <h1 class="text-base font-bold text-gray-900 mb-2 uppercase">{{ $record->employee->name }}</h1>
        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 grid grid-cols-6 gap-y-2 gap-x-2">
            <div>
                <span class="block text-gray-500 font-bold uppercase tracking-wider text-[9px]">Appraisal Ref No</span>
                <span class="font-bold text-gray-800">{{ $record->application_no }}</span>
            </div>
            <div>
                <span class="block text-gray-500 font-bold uppercase tracking-wider text-[9px]">Employee Code</span>
                <span class="font-bold text-gray-800">{{ $record->employee->employee_code ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-gray-500 font-bold uppercase tracking-wider text-[9px]">Appraisal Year</span>
                <span class="font-bold text-gray-800">{{ $record->appraisal_year }}</span>
            </div>
            <div>
                <span class="block text-gray-500 font-bold uppercase tracking-wider text-[9px]">Designation</span>
                <span class="font-bold text-gray-800">{{ $record->designation->designation ?? '-' }}</span>
            </div>
            <div class="col-span-2">
                <span class="block text-gray-500 font-bold uppercase tracking-wider text-[9px]">Department</span>
                <span class="font-bold text-gray-800">{{ $record->department->department ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- PART A: APPRAISAL FORM (Employee) --}}
    {{-- ========================================== --}}
    <div class="bg-white border-2 border-blue-600 rounded-xl overflow-hidden shadow-sm mb-6">
        <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h2 class="text-[11px] font-bold text-gray-900 uppercase tracking-wide">Part A: Appraisal Form</h2>
            <p class="text-gray-400 text-[9px]">To be filled by the Employee</p>
        </div>
        <div class="p-4 space-y-4">
            <div class="no-break">
                <h3 class="text-blue-700 font-bold text-[11px] mb-1">1. Define your job profile</h3>
                <div class="text-gray-700 text-justify">{!! $record->appraisal_form_data['job_profile'] ?? 'Not provided.' !!}</div>
                <hr class="border-gray-100 mt-2">
            </div>
            <div class="no-break">
                <h3 class="text-blue-700 font-bold text-[11px] mb-1">2. How satisfied are you with your job profile?</h3>
                <div>
                    @php $satisfaction = $record->appraisal_form_data['job_satisfaction'] ?? 'Not Provided'; @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border bg-gray-100 text-gray-700 border-gray-200">{{ $satisfaction }}</span>
                </div>
                <hr class="border-gray-100 mt-2">
            </div>
            <div class="no-break">
                <h3 class="text-blue-700 font-bold text-[11px] mb-1">3. Outline what you value most about your job profile and/or changes that could help better utilise your potential</h3>
                <div class="text-gray-700 text-justify">{!! $record->appraisal_form_data['job_enrichment'] ?? 'Not provided.' !!}</div>
                <hr class="border-gray-100 mt-2">
            </div>
            <div class="no-break">
                <h3 class="text-blue-700 font-bold text-[11px] mb-1">4. What were your achievements during the review period and how did you achieve them?</h3>
                <div class="text-gray-700 text-justify">{!! $record->appraisal_form_data['achievements'] ?? 'Not provided.' !!}</div>
                <hr class="border-gray-100 mt-2">
            </div>
            <div class="no-break">
                <h3 class="text-blue-700 font-bold text-[11px] mb-1">5. What areas of your individual performance could have been better during the review period?</h3>
                <div class="text-gray-700 text-justify">{!! $record->appraisal_form_data['performance_gaps'] ?? 'Not provided.' !!}</div>
                <hr class="border-gray-100 mt-2">
            </div>
            <div class="no-break">
                <h3 class="text-blue-700 font-bold text-[11px] mb-1">6. What are your medium to long-term career goals?</h3>
                <div class="text-gray-700 text-justify">{!! $record->appraisal_form_data['career_goals'] ?? 'Not provided.' !!}</div>
                <hr class="border-gray-100 mt-2">
            </div>
            <div class="no-break">
                <h3 class="text-blue-700 font-bold text-[11px] mb-1">7. Outline specific training and mentoring programs</h3>
                <div class="text-gray-700 text-justify">{!! $record->appraisal_form_data['training_needs'] ?? 'Not provided.' !!}</div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- PART B: EVALUATION FORM (Reporting Officer) --}}
    {{-- ========================================== --}}
    @if($record->evaluation_form_data)
    <div class="no-break bg-white border-2 border-eval rounded-xl overflow-hidden shadow-sm mb-6">
        <div class="px-4 py-2 border-b border-gray-200 bg-eval flex justify-between items-center">
            <h2 class="text-[11px] font-bold uppercase tracking-wide">Part B: Evaluation Form</h2>
            <p class="text-white text-[9px] opacity-80">To be filled by the Supervising Officer</p>
        </div>
        
        <div class="p-4 space-y-4">
            
            {{-- Q1: Agreement --}}
            <div class="no-break">
                <h3 class="text-eval font-bold text-[11px] mb-1">1. Do you agree with the information submitted by the employee in the Appraisal Form?</h3>
                @php 
                    $agree = $record->evaluation_form_data['agree_with_employee'] ?? '-'; 
                    $agreeColor = $agree === 'Yes' ? 'text-green-700 bg-green-50 border-green-200' : 'text-red-700 bg-red-50 border-red-200';
                @endphp
                <span class="inline-block border px-2 py-0.5 rounded text-[9px] font-bold {{ $agreeColor }}">{{ $agree }}</span>
                
                @if($agree === 'No')
                    <div class="mt-2 pl-2 border-l-2 border-red-300">
                        <h4 class="text-[9px] font-bold text-red-600 uppercase mb-1">Outline the Disagreements:</h4>
                        <div class="text-gray-700 text-justify">{!! $record->evaluation_form_data['disagreement'] ?? '-' !!}</div>
                    </div>
                @endif
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Q2: Comparison --}}
            <div class="no-break">
                <h3 class="text-eval font-bold text-[11px] mb-1">2. Draw a comparison of the employee's job competencies vis-a-vis others with the same job profile</h3>
                <div class="text-gray-700 text-justify">{!! $record->evaluation_form_data['competency_comparison'] ?? '-' !!}</div>
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Q3: Initiative --}}
            <div class="no-break">
                <h3 class="text-eval font-bold text-[11px] mb-1">3. Enumerate the employee's drive to take initiative and innovation</h3>
                <div class="text-gray-700 text-justify">{!! $record->evaluation_form_data['initiative'] ?? '-' !!}</div>
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Q4: Accomplishments --}}
            <div class="no-break">
                <h3 class="text-eval font-bold text-[11px] mb-1">4. Outline the employee's Outstanding accomplishments during the review period</h3>
                <div class="text-gray-700 text-justify">{!! $record->evaluation_form_data['accomplishments'] ?? '-' !!}</div>
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Q5: Ratings --}}
            @if(isset($record->evaluation_form_data['ratings']))
            <div class="no-break">
                <h3 class="text-eval font-bold text-[11px] mb-1">5. Employee Skills and Competency Score (1-10)</h3>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1 mb-2">
                    @php $sum = 0; $count = 0; @endphp
                    @foreach($record->evaluation_form_data['ratings'] as $key => $score)
                        @php 
                            if(is_numeric($score)) { $sum += $score; $count++; }
                        @endphp
                        <div class="flex justify-between items-center border-b border-gray-100 pb-1">
                            <span class="text-gray-600 capitalize text-[9px]">{{ str_replace('_', ' ', $key) }}</span>
                            <span class="font-bold text-gray-900 text-[10px]">{{ $score }}</span>
                        </div>
                    @endforeach
                </div>
                {{-- Calculated Average --}}
                <div class="bg-blue-50 border border-blue-100 p-2 rounded flex justify-between items-center">
                    <span class="text-blue-800 font-bold uppercase text-[9px]">Average Score</span>
                    <span class="text-blue-800 font-bold text-sm">{{ $count > 0 ? number_format($sum / $count, 2) : '0.00' }}</span>
                </div>
                <hr class="border-gray-100 mt-2">
            </div>
            @endif

            {{-- Q6: Overall Assessment --}}
            <div class="no-break">
                <h3 class="text-eval font-bold text-[11px] mb-1">6. Overall Assessment</h3>
                <div class="text-gray-700 text-justify">{!! $record->evaluation_form_data['overall_assessment'] ?? '-' !!}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- PART C: REGIONAL HEAD REVIEW --}}
    {{-- ========================================== --}}
    @if($record->regional_head_review_data && !empty($record->regional_head_review_data))
    <div class="no-break bg-white border-2 border-regional rounded-xl overflow-hidden shadow-sm mb-6">
        <div class="px-4 py-2 border-b border-gray-200 bg-regional flex justify-between items-center">
            <h2 class="text-[11px] font-bold uppercase tracking-wide">Part C: Regional Head Review</h2>
            <p class="text-white text-[9px] opacity-80">To be filled by the Regional Head</p>
        </div>
        <div class="p-4 space-y-4">
            
            {{-- Q1: Agreement --}}
            <div class="no-break">
                <h3 class="text-regional font-bold text-[11px] mb-1">1. Do you agree with the assessment made by the Chapter Head?</h3>
                @php $agreeReg = $record->regional_head_review_data['agree_with_chapter_head'] ?? '-'; @endphp
                <span class="inline-block border px-2 py-0.5 rounded text-[9px] font-bold bg-gray-50 text-gray-700">{{ $agreeReg }}</span>
                
                @if($agreeReg === 'No')
                    <div class="mt-2 pl-2 border-l-2 border-red-300">
                        <h4 class="text-[9px] font-bold text-red-600 uppercase mb-1">Outline the Disagreements:</h4>
                        <div class="text-gray-700 text-justify">{!! $record->regional_head_review_data['disagreement'] ?? '-' !!}</div>
                    </div>
                @endif
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Q2: Overall Assessment --}}
            <div class="no-break">
                <h3 class="text-regional font-bold text-[11px] mb-1">2. Overall assessment</h3>
                <div class="text-gray-700 text-justify">{!! $record->regional_head_review_data['comments'] ?? '-' !!}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- PART D: FINAL ASSESSMENT (DG & CEO) --}}
    {{-- ========================================== --}}
    @if($record->final_assessment_data)
    <div class="no-break bg-white border-2 border-final rounded-xl overflow-hidden shadow-sm">
        <div class="px-4 py-2 border-b border-gray-200 bg-final flex justify-between items-center">
            <h2 class="text-[11px] font-bold uppercase tracking-wide">Part D: Final Assessment (DG & CEO)</h2>
            <p class="text-white text-[9px] opacity-80">To be filled by the DG & CEO</p>
        </div>
        <div class="p-4 space-y-4">
            
            {{-- Q1: Agreement --}}
            <div class="no-break">
                <h3 class="text-final font-bold text-[11px] mb-1">1. Do you agree with the assessment?</h3>
                @php $agreeDG = $record->final_assessment_data['agree_with_evaluation'] ?? '-'; @endphp
                <span class="inline-block border px-2 py-0.5 rounded text-[9px] font-bold bg-gray-50 text-gray-700">{{ $agreeDG }}</span>
                
                @if($agreeDG === 'No')
                    <div class="mt-2 pl-2 border-l-2 border-red-300">
                        <h4 class="text-[9px] font-bold text-red-600 uppercase mb-1">Outline the Disagreements:</h4>
                        <div class="text-gray-700 text-justify">{!! $record->final_assessment_data['disagreement'] ?? '-' !!}</div>
                    </div>
                @endif
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Q2: Overall Assessment --}}
            <div class="no-break">
                <h3 class="text-final font-bold text-[11px] mb-1">2. Overall assessment</h3>
                <div class="text-gray-700 text-justify">{!! $record->final_assessment_data['comments'] ?? '-' !!}</div>
                <hr class="border-gray-100 mt-2">
            </div>

            {{-- Q3: Final Increment --}}
            <div class="bg-green-50 border border-green-200 p-3 rounded flex justify-between items-center">
                <div>
                    <h3 class="text-green-800 font-bold text-[11px] uppercase">3. Final Recommendation for Annual Increment</h3>
                </div>
                <div>
                    <span class="font-bold text-sm text-green-800 border-2 border-green-600 px-3 py-1 rounded bg-white">
                        {{ $record->final_increment ?? 'N/A' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Footer Timestamp --}}
    <div class="mt-4 text-center text-[9px] text-gray-400">