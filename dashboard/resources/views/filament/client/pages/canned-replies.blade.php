<x-filament-panels::page>
    @php
        $replies = $this->replies;
    @endphp

    <div class="space-y-6">
        <style>
            .canned-reply-control {
                display: block;
                width: 100%;
                border: 1px solid rgb(209 213 219);
                border-radius: 0.625rem;
                background: transparent;
                color: rgb(17 24 39);
                box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
                outline: none;
                transition: border-color 150ms ease, box-shadow 150ms ease;
            }

            .canned-reply-control:focus {
                border-color: rgb(59 130 246);
                box-shadow: 0 0 0 1px rgb(59 130 246);
            }

            .dark .canned-reply-control {
                border-color: rgb(75 85 99);
                background: transparent;
                color: rgb(255 255 255);
            }

            .dark .canned-reply-control:focus {
                border-color: rgb(96 165 250);
                box-shadow: 0 0 0 1px rgb(96 165 250);
            }
        </style>

        @if (session('status'))
            <x-filament::section>
                <p class="text-sm text-success-600 dark:text-success-400">
                    {{ session('status') }}
                </p>
            </x-filament::section>
        @endif

        @php
            $websites = $this->liveChatWebsiteOptions();
            $selectedWebsiteId = $this->selectedLiveChatWebsiteId();
        @endphp

        <x-filament::section>
            <label
                for="live-chat-website-selector"
                class="mb-2 block text-sm font-medium text-gray-950 dark:text-white"
            >
                Website
            </label>

            <select
                id="live-chat-website-selector"
                wire:model.live="selectedLiveChatWebsite"
                style="color-scheme: dark;"
                class="block w-full max-w-md rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm outline-none transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
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
        </x-filament::section>

        <x-filament::section
            :heading="$editingId ? 'Edit Reply' : 'Create Reply'"
            description="Reusable messages agents can insert into live chat replies."
        >
            <form wire:submit="save">
                <div class="space-y-8">
                    <div style="margin-bottom: 24px;">
                        <label
                            for="reply-title"
                            class="mb-2 block text-sm font-medium text-gray-950 dark:text-white"
                        >
                            Title
                        </label>

                        <input
                            id="reply-title"
                            type="text"
                            wire:model="replyTitle"
                            required
                            placeholder="Enter reply title"
                            style="height: 42px; padding: 0 12px;"
                            class="canned-reply-control text-sm placeholder:text-gray-400 dark:placeholder:text-gray-500"
                        >

                        @error('replyTitle')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label
                            for="reply-message"
                            class="mb-2 block text-sm font-medium text-gray-950 dark:text-white"
                        >
                            Message
                        </label>

                        <textarea
                            id="reply-message"
                            wire:model="replyMessage"
                            required
                            rows="5"
                            placeholder="Type the canned reply message"
                            style="min-height: 120px; padding: 10px 12px; resize: vertical;"
                            class="canned-reply-control text-sm placeholder:text-gray-400 dark:placeholder:text-gray-500"
                        ></textarea>

                        @error('replyMessage')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-950 dark:text-white">
                            <input
                                type="checkbox"
                                wire:model="is_active"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900"
                            >
                            <span>Active</span>
                        </label>

                        @error('is_active')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-3" style="margin-top: 28px;">
                        <x-filament::button type="submit">
                            {{ $editingId ? 'Update Reply' : 'Create Reply' }}
                        </x-filament::button>

                        @if ($editingId)
                            <x-filament::button
                                type="button"
                                color="gray"
                                outlined
                                wire:click="resetForm"
                            >
                                Cancel
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </form>
        </x-filament::section>

        <x-filament::section
            heading="Saved Replies"
            description="Company-scoped replies available in the agent chat composer."
        >
            @if ($replies->isEmpty())
                <div class="rounded-lg border border-gray-200 px-6 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    No canned replies yet.
                </div>
            @else
                <style>
                    .canned-replies-table {
                        display: grid;
                        gap: 10px;
                    }

                    .canned-replies-header,
                    .canned-replies-row {
                        display: grid;
                        grid-template-columns: minmax(0, 1fr) 120px 170px;
                        gap: 20px;
                        align-items: center;
                    }

                    .canned-replies-row {
                        padding: 18px 22px;
                    }

                    .canned-reply-preview {
                        display: -webkit-box;
                        -webkit-box-orient: vertical;
                        -webkit-line-clamp: 2;
                        overflow: hidden;
                    }

                    @media (max-width: 767px) {
                        .canned-replies-header {
                            display: none;
                        }

                        .canned-replies-row {
                            grid-template-columns: 1fr;
                            gap: 14px;
                            align-items: start;
                        }

                        .canned-replies-actions {
                            justify-content: flex-start;
                        }
                    }
                </style>

                <div class="canned-replies-table">
                    <div class="canned-replies-header rounded-lg border border-gray-200 bg-gray-50 px-6 py-3 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:bg-white/5 dark:text-gray-200">
                        <div>Reply</div>
                        <div>Status</div>
                        <div class="text-right">Actions</div>
                    </div>

                    @foreach ($replies as $reply)
                        <div class="canned-replies-row rounded-xl border border-gray-200 bg-white shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-white/5">
                            <div class="min-w-0 text-left">
                                <div class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                    <span class="mr-2 text-sm font-semibold" style="color: #d6a21a;">
                                        Title:
                                    </span>
                                    {{ $reply->title }}
                                </div>
                                <div class="canned-reply-preview mt-2 break-words text-sm leading-6 text-gray-600 dark:text-gray-300">
                                    <span class="mr-2 font-semibold" style="color: #d6a21a;">
                                        Message:
                                    </span>
                                    {{ $reply->message }}
                                </div>
                            </div>

                            <div class="flex items-center">
                                <x-filament::badge :color="$reply->is_active ? 'success' : 'gray'">
                                    {{ $reply->is_active ? 'Active' : 'Inactive' }}
                                </x-filament::badge>
                            </div>

                            <div class="canned-replies-actions flex items-center justify-end gap-2">
                                <x-filament::button
                                    type="button"
                                    color="gray"
                                    size="sm"
                                    wire:click="edit({{ $reply->id }})"
                                >
                                    Edit
                                </x-filament::button>

                                <x-filament::button
                                    type="button"
                                    color="danger"
                                    size="sm"
                                    outlined
                                    wire:click="delete({{ $reply->id }})"
                                >
                                    Delete
                                </x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
