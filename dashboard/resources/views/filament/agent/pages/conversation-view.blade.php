<x-filament-panels::page>
    @php
        $conversation = $this->getConversation();
        $messages = $this->getMessages();
        $isLive = $conversation->status === 'live_active';
    @endphp

    <div class="grid gap-4 lg:grid-cols-[260px_minmax(0,1fr)]">
        <aside class="rounded-lg border border-gray-200 bg-white p-4 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="font-semibold text-gray-950 dark:text-white">
                Visitor {{ substr($conversation->visitor?->visitor_uuid ?? 'unknown', 0, 8) }}
            </div>

            <dl class="mt-4 space-y-3 text-gray-600 dark:text-gray-300">
                <div>
                    <dt class="text-xs uppercase text-gray-400">Website</dt>
                    <dd>{{ $conversation->website?->name ?? 'Unknown' }}</dd>
                </div>

                <div>
                    <dt class="text-xs uppercase text-gray-400">Status</dt>
                    <dd>{{ str_replace('_', ' ', ucfirst($conversation->status)) }}</dd>
                </div>

                <div>
                    <dt class="text-xs uppercase text-gray-400">Started</dt>
                    <dd>{{ $conversation->started_at ? \App\Support\BrowserTime::format($conversation->started_at, 'd M Y, h:i A') : 'N/A' }}</dd>
                </div>
            </dl>

            <nav class="mt-6 space-y-1">
                <a class="block rounded-md px-3 py-2 text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800" href="{{ \App\Filament\Agent\Pages\AgentDashboard::getUrl(panel: 'agent') }}">Dashboard</a>
                <a class="block rounded-md px-3 py-2 text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800" href="{{ \App\Filament\Agent\Pages\WaitingChats::getUrl(panel: 'agent') }}">Waiting Chats</a>
                <a class="block rounded-md px-3 py-2 text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800" href="{{ \App\Filament\Agent\Pages\MyActiveChats::getUrl(panel: 'agent') }}">My Chats</a>
            </nav>
        </aside>

        <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                <div>
                    <div class="font-semibold text-gray-950 dark:text-white">Conversation #{{ $conversation->id }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Full AI, visitor, agent, and system timeline
                    </div>
                </div>

                @if ($isLive)
                    <button
                        type="button"
                        wire:click="closeConversation"
                        wire:confirm="Close this conversation?"
                        class="rounded-md bg-danger-600 px-3 py-2 text-sm font-semibold text-white hover:bg-danger-500"
                    >
                        Close Conversation
                    </button>
                @endif
            </div>

            <div class="max-h-[520px] space-y-3 overflow-y-auto px-4 py-4">
                @forelse ($messages as $message)
                    @php
                        $senderType = $message->sender_type ?? 'system';
                        $alignRight = $senderType === 'agent';
                        $label = match ($senderType) {
                            'visitor', 'user' => 'Visitor',
                            'bot', 'assistant', 'ai' => 'Bot',
                            'agent' => 'Agent',
                            'system' => 'System',
                            default => ucfirst($senderType),
                        };
                    @endphp

                    <div class="flex {{ $alignRight ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[78%] rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 {{ $alignRight ? 'bg-primary-50 dark:bg-primary-950' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <div class="mb-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $label }}</span>
                                <span>{{ $message->created_at ? \App\Support\BrowserTime::format($message->created_at, 'h:i A') : 'N/A' }}</span>
                            </div>
                            <div class="whitespace-pre-wrap text-gray-950 dark:text-white">{{ $message->message }}</div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No messages yet.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                @if ($isLive)
                    <form wire:submit="sendReply" class="space-y-3">
                        <textarea
                            wire:model="message"
                            rows="3"
                            maxlength="5000"
                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                            placeholder="Type your reply..."
                        ></textarea>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500"
                            >
                                Send Reply
                            </button>
                        </div>
                    </form>
                @else
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        This conversation is closed.
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-filament-panels::page>
