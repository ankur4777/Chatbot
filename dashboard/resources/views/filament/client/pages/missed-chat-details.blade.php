<div style="display: grid; gap: 14px;">
    <div>
        <strong>Visitor</strong>
        <div>{{ $lead->name ?: 'Unknown' }}</div>
    </div>

    <div>
        <strong>Contact</strong>
        <div>{{ $lead->email ?: 'N/A' }}{{ $lead->phone ? ' | ' . $lead->phone : '' }}</div>
    </div>

    <div>
        <strong>Website</strong>
        <div>{{ $lead->website?->name ?? 'Unknown website' }}</div>
    </div>

    <div>
        <strong>Message</strong>
        <div style="white-space: pre-wrap;">{{ $lead->notes ?: 'No message' }}</div>
    </div>

    <div>
        <strong>Assigned Agent</strong>
        <div>{{ $lead->assignedAgent?->name ?? 'Unassigned' }}</div>
    </div>

    <div>
        <strong>Follow-up Status</strong>
        <div>{{ $lead->followupStatusLabel() }}</div>
    </div>

    <div>
        <strong>Agent Note</strong>
        <div style="white-space: pre-wrap;">{{ $lead->agent_note ?: 'No note added' }}</div>
    </div>

    <div>
        <strong>Assigned At</strong>
        <div>
            {{ $lead->assigned_at ? \App\Support\BrowserTime::format($lead->assigned_at, 'd M Y, h:i A') : 'N/A' }}
        </div>
    </div>

    <div>
        <strong>Last Contacted</strong>
        <div>
            {{ $lead->last_contacted_at ? \App\Support\BrowserTime::format($lead->last_contacted_at, 'd M Y, h:i A') : 'N/A' }}
        </div>
    </div>

    <div>
        <strong>Next Follow-up</strong>
        <div>
            {{ $lead->next_followup_at ? \App\Support\BrowserTime::format($lead->next_followup_at, 'd M Y, h:i A') : 'N/A' }}
        </div>
    </div>

    <div>
        <strong>Resolved At</strong>
        <div>
            {{ $lead->resolved_at ? \App\Support\BrowserTime::format($lead->resolved_at, 'd M Y, h:i A') : 'N/A' }}
        </div>
    </div>

    <div>
        <strong>Last Updated</strong>
        <div>
            {{ $lead->updated_at ? \App\Support\BrowserTime::format($lead->updated_at, 'd M Y, h:i A') : 'N/A' }}
        </div>
    </div>
</div>
