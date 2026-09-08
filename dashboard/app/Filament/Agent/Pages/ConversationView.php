<?php

namespace App\Filament\Agent\Pages;

use App\Filament\Agent\Concerns\HasAgentAccess;
use App\Models\ChatConversation;
use App\Services\LiveChatService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ConversationView extends Page
{
    use HasAgentAccess;

    protected static ?string $slug = 'conversations/{conversation}';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Conversation';

    protected static string|\BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChatBubbleLeftRight;

    protected string $view = 'filament.agent.pages.conversation-view';

    public int $conversationId;

    public string $message = '';

    public function mount(int|string $conversation): void
    {
        $this->conversationId = (int) $conversation;

        $this->getConversation();
    }

    public function getTitle(): string
    {
        return 'Conversation #' . $this->conversationId;
    }

    public function getSubheading(): ?string
    {
        $conversation = $this->getConversation();

        $visitor = $conversation->visitor?->visitor_uuid
            ? 'Visitor ' . substr($conversation->visitor->visitor_uuid, 0, 8)
            : 'Unknown Visitor';

        return "{$visitor} · {$conversation->website?->name} · {$conversation->status}";
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function sendReply(): void
    {
        $message = trim($this->message);

        if ($message === '') {
            return;
        }

        try {
            app(LiveChatService::class)->sendAgentMessage(
                $this->getConversation(),
                auth()->user(),
                $message
            );

            $this->message = '';

            Notification::make()
                ->title('Reply sent.')
                ->success()
                ->send();
        } catch (
            AuthorizationException |
            InvalidArgumentException $exception
        ) {
            Notification::make()
                ->title('Unable to send reply.')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function closeConversation(): void
    {
        try {
            app(LiveChatService::class)
                ->closeConversationAsAgent(
                    $this->getConversation(),
                    auth()->user()
                );

            Notification::make()
                ->title('Conversation closed.')
                ->success()
                ->send();

            $this->redirect(MyActiveChats::getUrl(panel: 'agent'));
        } catch (
            AuthorizationException |
            InvalidArgumentException $exception
        ) {
            Notification::make()
                ->title('Unable to close conversation.')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getMessages(): Collection
    {
        return $this->getConversation()
            ->messages()
            ->oldest('created_at')
            ->oldest('id')
            ->get();
    }

    public function getConversation(): ChatConversation
    {
        return ChatConversation::query()
            ->with(['visitor', 'website'])
            ->whereKey($this->conversationId)
            ->where('assigned_agent_id', auth()->id())
            ->whereHas(
                'website',
                fn ($query) => $query->where(
                    'company_id',
                    $this->agentCompanyId()
                )
            )
            ->whereIn('status', ['live_active', 'closed'])
            ->firstOrFail();
    }
}
