@php
    $employees = collect($employees)->filter()->unique()->sort()->values();
@endphp

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
    <div class="text-sm text-gray-600 dark:text-gray-300">
        Total: <span class="font-medium text-gray-900 dark:text-white">{{ $employees->count() }}</span>
    </div>
</div>

<div style="max-height:360px; overflow:auto; padding-right:4px;">
    @forelse($employees as $name)
        <div style="display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:10px;"
             class="hover:bg-gray-50 dark:hover:bg-gray-800"
        >
            <div style="display:flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:999px;"
                 class="bg-gray-100 dark:bg-gray-800"
            >
                <x-filament::icon
                    icon="heroicon-m-user"
                    style="width:16px;height:16px;"
                    class="text-gray-500"
                />
            </div>

            <div class="text-sm font-medium text-gray-950 dark:text-white">
                {{ $name }}
            </div>
        </div>
    @empty
        <div class="text-sm text-gray-500 dark:text-gray-400" style="padding:12px;">
            No employees found.
        </div>
    @endforelse
</div>
