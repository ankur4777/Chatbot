<?php

namespace App\Filament\Client\Pages;

use App\Filament\Client\Concerns\HasSelectedLiveChatWebsite;
use App\Filament\Client\Concerns\RequiresLiveChatAccess;
use App\Models\CannedReply;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class CannedReplies extends Page
{
    use HasSelectedLiveChatWebsite;
    use RequiresLiveChatAccess;

    protected static ?string $title = 'Canned Replies';

    protected static ?string $navigationLabel = 'Canned Replies';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\UnitEnum|null $navigationGroup = 'Live Chat';

    protected static ?int $navigationSort = 60;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedDocumentText;

    protected string $view = 'filament.client.pages.canned-replies';

    public static function canAccess(): bool
    {
        return false;
    }

    public ?int $editingId = null;

    public string $replyTitle = '';

    public string $replyMessage = '';

    public bool $is_active = true;

    public function getRepliesProperty(): Collection
    {
        return CannedReply::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('website_id', $this->selectedLiveChatWebsiteId())
            ->latest()
            ->get();
    }

    public function save(): void
    {
        $this->validate([
            'replyTitle' => ['required', 'string', 'max:255'],
            'replyMessage' => ['required', 'string', 'max:5000'],
            'is_active' => ['boolean'],
        ]);

        $data = [
            'title' => $this->replyTitle,
            'message' => $this->replyMessage,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            CannedReply::query()
                ->where('company_id', auth()->user()->company_id)
                ->where('website_id', $this->selectedLiveChatWebsiteId())
                ->whereKey($this->editingId)
                ->firstOrFail()
                ->update($data);
        } else {
            CannedReply::query()->create(
                $data + [
                'company_id' => auth()->user()->company_id,
                'website_id' => $this->selectedLiveChatWebsiteId(),
                ]
            );
        }

        $this->resetForm();

        session()->flash('status', 'Canned reply saved.');
    }

    public function edit(int $replyId): void
    {
        $reply = CannedReply::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('website_id', $this->selectedLiveChatWebsiteId())
            ->whereKey($replyId)
            ->firstOrFail();

        $this->editingId = $reply->id;
        $this->replyTitle = $reply->title;
        $this->replyMessage = $reply->message;
        $this->is_active = $reply->is_active;
    }

    public function delete(int $replyId): void
    {
        CannedReply::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('website_id', $this->selectedLiveChatWebsiteId())
            ->whereKey($replyId)
            ->delete();

        if ($this->editingId === $replyId) {
            $this->resetForm();
        }

        session()->flash('status', 'Canned reply deleted.');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->replyTitle = '';
        $this->replyMessage = '';
        $this->is_active = true;
    }
}
