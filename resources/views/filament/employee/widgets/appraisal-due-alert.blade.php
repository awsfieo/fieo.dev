<x-filament-widgets::widget>
    <x-filament::section>
        <table style="width:100%; border-collapse:collapse;">
            <tr style="vertical-align:top;">
                {{-- Col 1: Icon --}}
                <td style="width:44px; padding-right:14px;">
                    <x-filament::icon
                        icon="heroicon-o-exclamation-triangle"
                        style="width:28px;height:28px; color:#d97706;"
                    />
                </td>

                {{-- Col 2: Content --}}
                <td style="width:auto;">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <div style="font-size:14px; font-weight:600;">
                            Appraisal submission
                        </div>

                        {{-- DYNAMIC BADGE --}}
                        <x-filament::badge :color="$badgeColor" size="sm">
                            {{ $badgeLabel }}
                        </x-filament::badge>
                    </div>

                    <div style="margin-top:6px; font-size:13px; color:rgba(55,65,81,0.9);">
                        @if($dueRecord?->deadline_extension)
                            Submit the Appraisal Form before
                            <span style="font-weight:600; color:#b45309;">
                                {{ $dueRecord->deadline_extension_date?->format('d M Y') }}
                            </span>
                        @else
                            Submit the Appraisal Form before
                            <span style="font-weight:600;">
                                {{ $dueRecord?->appraisal_end_date?->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    @if($dueRecord?->deadline_extension)
                        <div style="margin-top:8px;">
                            <x-filament::badge color="success" size="sm">
                                Extension granted
                            </x-filament::badge>
                        </div>
                    @endif
                </td>

                {{-- Col 3: Button --}}
                <td style="width:240px; text-align:right; white-space:nowrap; padding-left:14px;">
                    <x-filament::button
                        tag="a"
                        :href="$actionUrl"
                        color="warning"
                        size="lg"
                    >
                        {{ $buttonLabel }}
                    </x-filament::button>
                </td>
            </tr>
        </table>
    </x-filament::section>
</x-filament-widgets::widget>