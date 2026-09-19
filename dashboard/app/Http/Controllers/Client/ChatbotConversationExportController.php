<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\User;
use App\Models\Website;
use App\Support\BrowserTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatbotConversationExportController extends Controller
{
    public function download(Request $request): Response
    {
        $user = $this->authorizedOwner($request);
        $websiteId = $request->integer('website');

        if ($websiteId) {
            abort_unless(
                Website::query()
                    ->whereKey($websiteId)
                    ->where('company_id', $user->company_id)
                    ->exists(),
                404
            );
        }

        $conversations = $this->conversationQuery($user)
            ->when(
                $websiteId,
                fn (Builder $query) => $query->where('website_id', $websiteId)
            )
            ->latest('updated_at')
            ->get();

        return $this->pdfResponse(
            $conversations,
            'chatbot-conversations-' . now()->format('Y-m-d') . '.pdf'
        );
    }

    public function downloadConversation(
        Request $request,
        ChatConversation $conversation
    ): Response {
        $user = $this->authorizedOwner($request);

        $conversation = $this->conversationQuery($user)
            ->whereKey($conversation->id)
            ->firstOrFail();

        return $this->pdfResponse(
            collect([$conversation]),
            'chatbot-conversation-' . $conversation->id . '.pdf'
        );
    }

    protected function conversationQuery(User $user): Builder
    {
        return ChatConversation::query()
            ->with([
                'visitor',
                'website',
                'messages' => fn ($query) => $query
                    ->oldest('created_at')
                    ->oldest('id'),
            ])
            ->where('updated_at', '>=', now()->subDays(30))
            ->whereHas(
                'website',
                fn (Builder $query) => $query->where(
                    'company_id',
                    $user->company_id
                )
            );
    }

    protected function pdfResponse($conversations, string $filename): Response
    {
        return response($this->pdfContent($this->pdfLines($conversations)), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function pdfLines($conversations): array
    {
        $lines = [
            'Chatbot Conversation Transcript',
            'Generated: ' . BrowserTime::format(now()),
            '',
        ];

        foreach ($conversations as $conversation) {
            $visitor = $conversation->visitor?->visitor_uuid
                ? 'Visitor ' . substr($conversation->visitor->visitor_uuid, 0, 8)
                : 'Unknown Visitor';

            $lines = array_merge($lines, [
                'Conversation #' . $conversation->id,
                'Website: ' . ($conversation->website?->name ?? 'N/A'),
                'Visitor: ' . $visitor,
                'Status: ' . ucfirst((string) ($conversation->status ?? 'N/A')),
                'Mode: ' . ($conversation->mode ?: 'N/A'),
                'Started: ' . BrowserTime::format($conversation->started_at),
                'Ended: ' . BrowserTime::format($conversation->ended_at),
                'Last Activity: ' . BrowserTime::format($conversation->updated_at),
                'Summary: ' . ($conversation->summary ?: 'N/A'),
                '',
                'Messages',
                '--------',
            ]);

            if ($conversation->messages->isEmpty()) {
                $lines[] = 'No messages found.';
                $lines[] = '';
                $lines[] = str_repeat('-', 72);
                $lines[] = '';
                continue;
            }

            foreach ($conversation->messages as $message) {
                $text = trim((string) $message->message);

                if ($text === '' && $message->attachment) {
                    $text = 'Attachment';
                }

                if ($message->attachment) {
                    $text .= ($text === '' ? '' : ' ')
                        . '[Attachment: ' . ($message->attachment_type ?: 'file') . ']';
                }

                $lines[] = '[' . BrowserTime::format($message->created_at) . '] '
                    . $this->senderLabel($message->sender_type) . ':';

                foreach ($this->wrapLine($text ?: 'N/A') as $wrappedLine) {
                    $lines[] = '  ' . $wrappedLine;
                }

                $lines[] = '';
            }

            $lines[] = str_repeat('-', 72);
            $lines[] = '';
        }

        return $lines;
    }

    protected function pdfContent(array $lines): string
    {
        $pages = array_chunk($lines, 52);
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pageObjectNumbers = [];

        foreach ($pages as $pageLines) {
            $contentObjectNumber = count($objects) + 1;
            $pageObjectNumber = $contentObjectNumber + 1;
            $pageObjectNumbers[] = $pageObjectNumber;

            $stream = "BT\n/F1 10 Tf\n40 800 Td\n14 TL\n";

            foreach ($pageLines as $line) {
                $stream .= '(' . $this->pdfEscape($line) . ") Tj\nT*\n";
            }

            $stream .= "ET\n";

            $objects[] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents ' . $contentObjectNumber . ' 0 R >>';
        }

        $objects[1] = '<< /Type /Pages /Kids ['
            . collect($pageObjectNumbers)->map(fn ($number) => $number . ' 0 R')->implode(' ')
            . '] /Count ' . count($pageObjectNumbers) . ' >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($index = 1; $index <= count($objects); $index++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
        }

        return $pdf
            . "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n"
            . "startxref\n" . $xrefOffset . "\n%%EOF";
    }

    protected function wrapLine(string $line): array
    {
        return explode("\n", wordwrap($this->pdfText($line), 92, "\n", true));
    }

    protected function pdfText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        $text = preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';

        return trim(preg_replace('/\s+/', ' ', $text) ?? '');
    }

    protected function pdfEscape(string $text): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $this->pdfText($text)
        );
    }

    protected function senderLabel(?string $senderType): string
    {
        return match ($senderType) {
            'visitor', 'user' => 'Visitor',
            'bot', 'assistant', 'ai' => 'AI Assistant',
            'agent' => 'Agent',
            null, '' => '',
            default => ucfirst($senderType),
        };
    }

    protected function authorizedOwner(Request $request): User
    {
        $user = $request->user();

        abort_unless($user?->role === 'owner' && $user->company_id, 403);

        return $user;
    }
}
