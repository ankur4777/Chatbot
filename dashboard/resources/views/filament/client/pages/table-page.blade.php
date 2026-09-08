<x-filament-panels::page>
    @if (method_exists($this, 'liveChatWebsiteOptions'))
        @php
            $websites = $this->liveChatWebsiteOptions();
            $selectedWebsiteId = $this->selectedLiveChatWebsiteId();
        @endphp

        <x-filament::section>
            <div style="display: flex; flex-wrap: wrap; gap: 18px; align-items: end;">
                <div style="display: grid; gap: 8px;">
                    <label
                        for="live-chat-website-selector"
                        class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                    >
                        Website
                    </label>

                    <select
                        id="live-chat-website-selector"
                        wire:model.live="selectedLiveChatWebsite"
                        style="appearance: auto; color-scheme: dark; background-color: #111827; border: 1px solid #374151; border-radius: 10px; color: #ffffff; display: block; font-size: 14px; font-weight: 700; height: 42px; min-width: 260px; outline: none; padding: 0 38px 0 12px;"
                    >
                        @foreach ($websites as $websiteId => $websiteName)
                            <option
                                value="{{ $websiteId }}"
                                style="background-color: #18181b; color: #ffffff;"
                                @selected((int) $selectedWebsiteId === (int) $websiteId)
                            >
                                {{ $websiteName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if (method_exists($this, 'liveChatAgentOptions'))
                    @php
                        $agents = $this->liveChatAgentOptions();
                    @endphp

                    <div style="display: grid; gap: 8px;">
                        <label
                            for="live-chat-agent-selector"
                            class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                        >
                            Agent
                        </label>

                        <select
                            id="live-chat-agent-selector"
                            wire:model.live="selectedLiveChatAgent"
                            style="appearance: auto; color-scheme: dark; background-color: #111827; border: 1px solid #374151; border-radius: 10px; color: #ffffff; display: block; font-size: 14px; font-weight: 700; height: 42px; min-width: 260px; outline: none; padding: 0 38px 0 12px;"
                        >
                            <option value="" style="background-color: #18181b; color: #ffffff;">
                                All Agents
                            </option>

                            @foreach ($agents as $agentId => $agentName)
                                <option
                                    value="{{ $agentId }}"
                                    style="background-color: #18181b; color: #ffffff;"
                                >
                                    {{ $agentName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </x-filament::section>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
