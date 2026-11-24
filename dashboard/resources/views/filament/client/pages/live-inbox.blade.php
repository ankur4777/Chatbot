<x-filament-panels::page>
    <div class="cw-inbox" wire:poll.15s>
        <section class="cw-inbox-header">
            <div>
                <h2>Live chat monitoring</h2>
                <p>Monitor your queue, agent availability and support performance.</p>
                <small>Refreshes automatically every 15 seconds while this tab is active.</small>
            </div>
            <div class="cw-inbox-select">
                <label for="live-inbox-website">Website</label>
                <select id="live-inbox-website" wire:model.live="selectedLiveChatWebsite" @disabled($websiteOptions->isEmpty())>
                    @forelse ($websiteOptions as $id => $name)
                        <option value="{{ $id }}" @selected((int) $websiteId === (int) $id)>{{ $name }}</option>
                    @empty
                        <option value="">No websites available</option>
                    @endforelse
                </select>
            </div>
        </section>

        @if ($websiteId)
            <div class="cw-inbox-grid">
                <a class="cw-inbox-stat" href="{{ \App\Filament\Client\Pages\WaitingChats::getUrl(['website' => $websiteId], panel: 'client') }}">
                    <span>Waiting chats</span><strong>{{ $metrics['waiting'] }}</strong><small>Visitors waiting for an agent →</small>
                </a>
                <a class="cw-inbox-stat" href="{{ \App\Filament\Client\Pages\ActiveChats::getUrl(['website' => $websiteId], panel: 'client') }}">
                    <span>Active chats</span><strong>{{ $metrics['active'] }}</strong><small>Ongoing support sessions →</small>
                </a>
                <div class="cw-inbox-stat"><span>Online agents</span><strong class="cw-inbox-green">{{ $metrics['online'] }}</strong><small>Marked online for live chat</small></div>
                <div class="cw-inbox-stat"><span>Offline agents</span><strong>{{ $metrics['offline'] }}</strong><small>{{ $metrics['away'] }} away · {{ $metrics['disabled'] }} disabled</small></div>
                <div class="cw-inbox-stat"><span>Average waiting time</span><strong>{{ \App\Services\LiveInboxMetrics::duration($metrics['average_wait']) }}</strong><small>Current queue · {{ $metrics['wait_samples'] }} timed chats</small></div>
                <div class="cw-inbox-stat"><span>Average first response</span><strong>{{ \App\Services\LiveInboxMetrics::duration($metrics['average_response']) }}</strong><small>After acceptance · last 7 days · {{ $metrics['response_samples'] }} replies</small></div>
            </div>

            <section class="cw-inbox-panel" aria-labelledby="cw-inbox-agents">
                <div class="cw-inbox-panel-head">
                    <div><h2 id="cw-inbox-agents">Agent workload</h2><p>Active chats by agent assigned to this website.</p></div>
                    <span>{{ $metrics['agents']->count() }} agents</span>
                </div>
                <div class="cw-inbox-table-wrap">
                    <table class="cw-inbox-table">
                        <thead><tr><th scope="col">Agent</th><th scope="col">Availability</th><th scope="col">Active chats</th><th scope="col">Workload</th></tr></thead>
                        <tbody>
                            @forelse ($metrics['agents'] as $agent)
                                <tr wire:key="inbox-agent-{{ $agent['id'] }}">
                                    <td><a href="{{ \App\Filament\Client\Pages\ActiveChats::getUrl(['website' => $websiteId, 'agent' => $agent['id']], panel: 'client') }}">{{ $agent['name'] }}</a></td>
                                    <td><span class="cw-inbox-status cw-inbox-{{ $agent['status'] }}">{{ ucfirst($agent['status']) }}</span></td>
                                    <td>{{ $agent['chats'] }}</td>
                                    <td><div class="cw-inbox-bar" role="img" aria-label="{{ $agent['chats'] }} of {{ $metrics['active'] }} active chats"><span style="width: {{ $metrics['active'] ? min(100, 100 * $agent['chats'] / $metrics['active']) : 0 }}%"></span></div></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="cw-inbox-empty">No agents assigned to this website.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($metrics['unassigned_active'] > 0)
                    <p class="cw-inbox-footnote">{{ $metrics['unassigned_active'] }} active chats are unassigned or assigned outside the current website agent roster.</p>
                @endif
            </section>
        @else
            <section class="cw-inbox-panel"><p class="cw-inbox-empty">Connect a website to view live chat metrics.</p></section>
        @endif
    </div>

    <div class="cw-inbox-panel-head">
        <div><h2>Recent live conversations</h2><p>Waiting and active chats with activity in the last 30 days.</p></div>
    </div>
    {{ $this->table }}
</x-filament-panels::page>
