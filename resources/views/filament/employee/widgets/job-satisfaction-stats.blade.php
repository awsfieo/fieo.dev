<x-filament-widgets::widget>
    <x-filament-actions::modals />

    <div class="js-stats-grid"
         style="display:grid; gap:14px; grid-template-columns: repeat(1, minmax(0, 1fr));">

        @foreach($this->getStats() as $label => $count)

            @php($meta = $this->getMeta($label))

            <div
                wire:click="mountAction('viewEmployees', { category: '{{ $label }}' })"
                class="relative rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                style="
                    padding:16px 18px;
                    cursor:pointer;
                    transition: transform .12s ease, box-shadow .12s ease;
                "
                onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 10px 26px rgba(0,0,0,.07)';"
                onmouseout="this.style.transform=''; this.style.boxShadow='';"
            >

                <div style="display:flex; align-items:center; justify-content:space-between; gap:18px;">

                    {{-- LEFT SECTION --}}
                    <div style="display:flex; align-items:center; gap:12px; min-width:0;">

                        <div style="
                            width:38px; height:38px;
                            border-radius:12px;
                            display:flex; align-items:center; justify-content:center;
                            background: {{ $meta['tint'] }};
                            flex:0 0 auto;
                        ">
                            <x-filament::icon
                                :icon="$meta['icon']"
                                style="width:100px;height:100px;color:{{ $meta['accent'] }};"
                            />
                        </div>

                        <div>
                            <div style="
                                margin-bottom:8px;
                                margin-left:20px;
                                font-size:16px;
                                color: rgba(107,114,128,.9);
                            "
                            class="dark:text-gray-400">
                                Employee Satisfaction
                            </div>
                            <div style="
                                font-size:20px;
                                margin-left:20px;
                                font-weight:600;
                                color: rgba(17,24,39,.85);
                            "
                            class="dark:text-white/90">
                                {{ $label }}
                            </div>

                            {{-- <div style="
                                margin-top:4px;
                                font-size:12px;
                                color: rgba(107,114,128,.9);
                            "
                            class="dark:text-gray-400">
                                Click to view list
                            </div> --}}
                        </div>
                    </div>

                    {{-- RIGHT SECTION --}}
                    <div style="display:flex; align-items:center; gap:12px;">

                        {{-- Count --}}
                        <div style="
                            font-size:34px;
                            font-weight:700;
                            letter-spacing:-0.6px;
                            line-height:1;
                            text-align:right;
                            color: rgba(17,24,39,.95);
                        "
                        class="dark:text-white">
                            {{ $count }}
                        </div>

                        {{-- Chevron --}}
                        <x-filament::icon
                            icon="heroicon-m-chevron-right"
                            style="width:18px;height:18px; opacity:.55;"
                            class="text-gray-400"
                        />

                    </div>

                </div>

            </div>

        @endforeach
    </div>

    <style>
        @media (min-width: 768px) {
            .js-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        }
        @media (min-width: 1280px) {
            .js-stats-grid { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
        }
    </style>
</x-filament-widgets::widget>
