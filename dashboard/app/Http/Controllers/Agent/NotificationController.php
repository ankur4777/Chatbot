<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $agent = $request->user();
        $filter = (string) $request->query('filter', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = AgentNotification::query()
            ->with(['website', 'conversation.visitor', 'missedChat.visitor'])
            ->where('agent_id', $agent->id)
            ->where('company_id', $agent->company_id)
            ->latest();

        $this->applyFilter($query, $filter);

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('website', fn ($websiteQuery) => $websiteQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('conversation.visitor', fn ($visitorQuery) => $visitorQuery
                        ->where('visitor_uuid', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        return view('agent.notifications.index', [
            'notifications' => $query->paginate(15)->withQueryString(),
            'activeFilter' => $filter,
            'search' => $search,
            'unreadCount' => AgentNotification::query()
                ->where('agent_id', $agent->id)
                ->unread()
                ->count(),
        ]);
    }

    public function latest(Request $request): JsonResponse
    {
        $agent = $request->user();
        $notifications = AgentNotification::query()
            ->where('agent_id', $agent->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (AgentNotification $notification) => $this->payload($notification));

        return response()->json([
            'unread_count' => AgentNotification::query()->where('agent_id', $agent->id)->unread()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $agent = $request->user();

        AgentNotification::query()
            ->where('agent_id', $agent->id)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'All notifications marked as read.',
                'unread_count' => 0,
            ]);
        }

        return back()->with('status', 'All notifications marked as read.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:mark_read,delete'],
            'notifications' => ['array'],
            'notifications.*' => ['integer'],
        ]);

        $query = AgentNotification::query()
            ->where('agent_id', $request->user()->id)
            ->whereIn('id', $data['notifications'] ?? []);

        if ($data['action'] === 'delete') {
            $query->delete();
            return back()->with('status', 'Selected notifications deleted.');
        }

        $query->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('status', 'Selected notifications marked as read.');
    }

    public function destroy(Request $request, AgentNotification $notification): RedirectResponse
    {
        abort_unless($notification->agent_id === $request->user()->id, 403);
        $notification->delete();

        return back()->with('status', 'Notification deleted.');
    }

    public function clearRead(Request $request): RedirectResponse
    {
        AgentNotification::query()
            ->where('agent_id', $request->user()->id)
            ->where('is_read', true)
            ->delete();

        return back()->with('status', 'Read notifications cleared.');
    }

    protected function applyFilter($query, string $filter): void
    {
        match ($filter) {
            'unread' => $query->unread(),
            'visitor_messages' => $query->whereIn('type', [
                AgentNotification::TYPE_NEW_VISITOR_MESSAGE,
                AgentNotification::TYPE_VISITOR_REPLIED,
            ]),
            'follow_up_reminders' => $query->where('type', AgentNotification::TYPE_FOLLOW_UP_REMINDER),
            'system' => $query->whereIn('type', [AgentNotification::TYPE_SYSTEM, AgentNotification::TYPE_CONVERSATION_CLOSED]),
            'today' => $query->whereDate('created_at', today()),
            'this_week' => $query->where('created_at', '>=', now()->startOfWeek()),
            default => null,
        };
    }

    protected function payload(AgentNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'is_read' => $notification->is_read,
            'created_at' => optional($notification->created_at)->diffForHumans(),
            'action_url' => $notification->data['action_url'] ?? route('agent.notifications'),
        ];
    }

    protected function done(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
