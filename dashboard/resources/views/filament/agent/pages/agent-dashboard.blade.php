<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $waitingConversations = $this->getWaitingConversations();
        $myConversations = $this->getMyConversations();
    @endphp

    <div class="grid gap-4 lg:grid-cols-[220px_minmax(0,1fr)]">
        <aside class="rounded-lg border border-gray-200 bg-white p-3 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <nav class="space-y-1">
                <a class="block rounded-md px-3 py-2 font-medium text-gray-950 hover:bg-gray-50 dark:text-white dark:hover:bg-gray-800" href="{{ \App\Filament\Agent\Pages\AgentDashboard::getUrl(panel: 'agent') }}">Dashboard</a>
                <a class="block rounded-md px-3 py-2 text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800" href="{{ \App\Filament\Agent\Pages\WaitingChats::getUrl(panel: 'agent') }}">Waiting Chats</a>
                <a class="block rounded-md px-3 py-2 text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800" href="{{ \App\Filament\Agent\Pages\MyActiveChats::getUrl(panel: 'agent') }}">My Chats</a>
                <span class="block rounded-md px-3 py-2 text-gray-400 dark:text-gray-500">Closed Chats</span>
                <span class="block rounded-md px-3 py-2 text-gray-400 dark:text-gray-500">Availability</span>
            </nav>
        </aside>

        <div class="space-y-4">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Waiting Chats</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-950 dark:text-white">{{ $stats['waiting'] }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="text-sm text-gray-500 dark:text-gray-400">My Active Chats</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-950 dark:text-white">{{ $stats['active'] }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Closed Today</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-950 dark:text-white">{{ $stats['closed_today'] }}</div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-950 dark:border-gray-700 dark:text-white">
                        Waiting Chats
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($waitingConversations as $conversation)
                            <a class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800" href="{{ \App\Filament\Agent\Pages\WaitingChats::getUrl(panel: 'agent') }}">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-medium text-gray-950 dark:text-white">{{ $conversation->website?->name ?? 'Website' }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ optional($conversation->handoff_requested_at)->diffForHumans() ?? 'New' }}</span>
                                </div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    Visitor {{ substr($conversation->visitor?->visitor_uuid ?? 'unknown', 0, 8) }}
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">No waiting chats.</div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-950 dark:border-gray-700 dark:text-white">
                        My Chats
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($myConversations as $conversation)
                            <a class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800" href="{{ \App\Filament\Agent\Pages\ConversationView::getUrl(['conversation' => $conversation->id], panel: 'agent') }}">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-medium text-gray-950 dark:text-white">{{ $conversation->website?->name ?? 'Website' }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ optional($conversation->updated_at)->diffForHumans() }}</span>
                                </div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    Visitor {{ substr($conversation->visitor?->visitor_uuid ?? 'unknown', 0, 8) }}
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">No active chats assigned to you.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
