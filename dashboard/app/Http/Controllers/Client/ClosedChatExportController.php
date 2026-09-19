<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LiveChatSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClosedChatExportController extends Controller
{
    public function download(Request $request, LiveChatSession $session): Response
    {
        $user = $this->authorizedOwner($request);

        $session = $this->closedChatQuery($user)
            ->whereKey($session->id)
            ->firstOrFail();

        return $this->pdfResponse(
            collect([$session]),
            'closed-chat-' . $session->conversation_id . '.pdf'
        );
    }

    public function downloadAll(Request $request): Response
    {
        $user = $this->authorizedOwner($request);
        $websiteId = $request->integer('website');
        $agentId = $request->integer('agent');

        $sessions = $this->closedChatQuery($user)
            ->when($websiteId, fn (Builder $query) => $query->whereHas(
                'conversation.website',
                fn (Builder $websiteQuery) => $websiteQuery->whereKey($websiteId)
            ))
            ->when($agentId, fn (Builder $query) => $query->where('agent_id', $agentId))
            ->latest('ended_at')
            ->get();

        return $this->pdfResponse(
            $sessions,
            'closed-chats-' . now()->format('Y-m-d') . '.pdf'
        );
    }

    protected function closedChatQuery(User $user): Builder
    {
        return LiveChatSession::query()
            ->with([
                'agent',
                'conversation.visitor',
                'conversation.website',
                'conversation.messages' => fn ($query) => $query
                    ->oldest('created_at')
                    ->oldest('id'),
            ])
            ->whereNotNull('ended_at')
            ->where('ended_at', '>=', now()->subDays(30))
            ->whereHas(
                'conversation.website',
                fn (Builder $query) => $query->where('company_id', $user->company_id)
            );
    }

    protected function pdfResponse($sessions, string $filename): Response
    {
        return response($this->pdfContent($this->pdfLines($sessions)), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function pdfLines($sessions): array
    {
        $lines = [
            'Closed Chat Transcript',
            'Generated: ' . now()->format('d M Y, h:i A'),
            '',
        ];

        foreach ($sessions as $session) {
            $conversation = $session->conversation;
            $visitor = $conversation?->visitor?->visitor_uuid
                ? 'Visitor ' . substr($conversation->visitor->visitor_uuid, 0, 8)
                : 'Unknown Visitor';

            $lines = array_merge($lines, [
                'Conversation #' . ($conversation?->id ?? 'N/A'),
                'Website: ' . ($conversation?->website?->name ?? 'N/A'),
                'Visitor: ' . $visitor,
                'Agent: ' . ($session->agent?->name ?? 'Unassigned'),
                'Started: ' . ($session->started_at?->format('d M Y, h:i A') ?? 'N/A'),
                'Closed: ' . ($session->ended_at?->format('d M Y, h:i A') ?? 'N/A'),
                'Closed By: ' . ucfirst((string) ($session->ended_by ?? 'N/A')),
                'Duration: ' . $this->formatDuration($session),
                'Rating: ' . (
                    $session->rating_status === 'submitted' && $session->rating
                        ? $session->rating . '/5'
                        : 'Not Rated'
                ),
                'Feedback: ' . ($session->feedback ?: 'N/A'),
                'Agent Note: ' . ($session->note ?: 'N/A'),
                '',
                'Messages',
                '--------',
            ]);

            $messages = $session->conversation?->messages
                ? $session->conversation->messages->filter(
                    fn ($message) => ! $session->ended_at
                        || ! $message->created_at
                        || $message->created_at->lessThanOrEqualTo($session->ended_at)
                )
                : collect();

            if ($messages->isEmpty()) {
                $lines[] = 'No messages found.';
                $lines[] = '';
                $lines[] = str_repeat('-', 72);
                $lines[] = '';
                continue;
            }

            foreach ($messages as $message) {
                $text = trim((string) $message->message);

                if ($text === '' && $message->attachment) {
                    $text = 'Attachment';
                }

                if ($message->attachment) {
                    $text .= ($text === '' ? '' : ' ')
                        . '[Attachment: ' . ($message->attachment_type ?: 'file') . ']';
                }

                $lines[] = '[' . ($message->created_at?->format('d M Y, h:i A') ?? 'N/A') . '] '
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

        foreach ($pages as $pageIndex => $pageLines) {
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

    protected function formatDuration(LiveChatSession $session): string
    {
        if (! $session->started_at || ! $session->ended_at) {
            return '';
        }

        $seconds = $session->started_at->diffInSeconds($session->ended_at, true);
        $minutes = intdiv($seconds, 60);

        if ($seconds < 60) {
            return $seconds . ' sec';
        }

        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0
            ? $hours . ' hr ' . $remainingMinutes . ' min'
            : $hours . ' hr';
    }

    protected function authorizedOwner(Request $request): User
    {
        $user = $request->user();

        abort_unless($user?->role === 'owner' && $user->company_id, 403);

        return $user;
    }
}
