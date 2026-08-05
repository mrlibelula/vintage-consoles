<?php

namespace App\Services\CheatImport;

use InvalidArgumentException;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;

/**
 * Turns cheat source material into clean Markdown for the play-page Cheats panel.
 *
 * Two modes:
 * - format  — pasted text the admin already curated: reshape into perfect MD only
 * - extract — uploaded docs (pdf/docx/…): keep cheats + important supporting context
 */
class CheatMarkdownGenerator
{
    public const MODE_FORMAT = 'format';

    public const MODE_EXTRACT = 'extract';

    public function generate(
        string $sourceText,
        string $gameTitle,
        string $consoleName,
        string $mode = self::MODE_EXTRACT,
    ): string {
        if (trim($sourceText) === '') {
            throw new RuntimeException('Nothing to process — the source is empty.');
        }

        if (! in_array($mode, [self::MODE_FORMAT, self::MODE_EXTRACT], true)) {
            throw new InvalidArgumentException("Unknown cheat markdown mode: {$mode}");
        }

        $apiKey = config('openai.api_key');
        if (empty($apiKey)) {
            throw new RuntimeException('OpenAI API key is not configured (OPENAI_API_KEY).');
        }

        $response = OpenAI::chat()->create([
            'model' => config('openai.model', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt($mode)],
                ['role' => 'user', 'content' => $this->userPrompt($sourceText, $gameTitle, $consoleName, $mode)],
            ],
        ]);

        $content = trim((string) ($response->choices[0]->message->content ?? ''));

        if ($content === '') {
            throw new RuntimeException('The AI did not return any content. Please try again.');
        }

        // Play UI already shows the game title — drop a redundant leading H1 if the model adds one.
        return $this->stripLeadingTitle($this->stripCodeFences($content));
    }

    private function systemPrompt(string $mode): string
    {
        return $mode === self::MODE_FORMAT
            ? $this->formatSystemPrompt()
            : $this->extractSystemPrompt();
    }

    /**
     * Pasted text: admin already chose what to keep. Only reshape into clean Markdown
     * that reads well in the play right-panel Cheats accordion (prose-sm).
     */
    private function formatSystemPrompt(): string
    {
        return <<<'PROMPT'
You will be given pasted text about a retro video game that an admin already selected as cheat-sheet
material. Your job is NOT to analyze, filter, or extract — keep the substance of what they pasted.

Your task: transform that text into clean, skimmable Markdown that looks good in a narrow right-hand
panel (small prose typography). Reorganize and format only; do not invent new cheats, and do not drop
meaningful tips/codes/secrets the paste already contains. You may drop pure chat fluff / meta asides
(e.g. "let me know which sequel you meant") that are not game content.

Output rules:
- Output valid Markdown only — no commentary, no code fences, no meta-explanation of what you did.
- Do NOT include a level-1 heading or the game title — the UI already shows the game name. Start
  directly with content (level-2 headings and/or bullets).
- Use short level-2 headings to group related items when helpful.
- Prefer bullet lists for most content.
- Use a Markdown table only when it clearly improves scanability (e.g. many rows of Code | Effect).
  If bullets are clearer, do not use a table.
- Preserve exact codes, button sequences, phone numbers, and inputs verbatim — do not paraphrase the
  actual code text.
- Keep it compact and readable in a side panel — short lines, no giant walls of prose.
PROMPT;
    }

    /**
     * Uploaded documents (manuals, PDFs, etc.): unknown shape — extract cheats + related context.
     */
    private function extractSystemPrompt(): string
    {
        return <<<'PROMPT'
You will be given a source document about a retro video game. The document's shape is unknown ahead of
time — it could be a dedicated cheat sheet, a full instruction/user manual, a walkthrough, a forum post, or
anything in between. It may mix cheat codes, unlockables, and secrets together with completely unrelated
content such as story text, control diagrams, box art descriptions, or legal boilerplate.

Your task: read the whole document, then extract the cheat codes, button-input sequences, unlockables,
secrets, AND all important supporting data around them that a player needs to use those cheats successfully.
Keep related context such as: when/where to enter a code, menu/version notes, prerequisites, passwords,
phone numbers used as skips, effects/rewards, warnings, and short tip lines that directly support a cheat
or secret. Discard unrelated manual content (story, full controls diagrams, legal text, long walkthrough
prose) that is not about cheats/unlockables/secrets.

Output rules:
- Output valid Markdown only — no commentary, no code fences, no meta-explanation of what you did.
- Do NOT include a level-1 heading or the game title — the UI already shows the game name. Start
  directly with content (level-2 headings and/or bullets).
- Group the extracted content under short level-2 headings only for groups that actually have content
  (e.g. "Cheat Codes", "Unlockables", "Secrets", "Tips"). Omit empty groups entirely.
- Prefer bullet lists. Use a small Markdown table only when it clearly improves scanability
  (e.g. many Code | Effect rows); otherwise stick to bullets.
- Preserve exact codes, button sequences, and inputs verbatim — do not paraphrase or "clean up" the actual
  code text (e.g. keep "Up, Up, Down, Down, Left, Right, Left, Right, B, A, Start" exactly as written).
- Keep the whole thing short and skimmable — no long prose, no restating the whole manual.
- If the document contains no cheat codes, unlockables, or secrets at all, ignore all the formatting rules
  above and output exactly this sentence and nothing else: "No cheat codes found in the provided document."
PROMPT;
    }

    private function userPrompt(string $sourceText, string $gameTitle, string $consoleName, string $mode): string
    {
        $label = $mode === self::MODE_FORMAT ? 'Pasted text' : 'Source document';

        return "Game: {$gameTitle} ({$consoleName})\n\n{$label}:\n---\n{$sourceText}\n---";
    }

    private function stripCodeFences(string $content): string
    {
        $trimmed = trim($content);

        if (str_starts_with($trimmed, '```')) {
            $trimmed = (string) preg_replace('/^```[a-zA-Z]*\n/', '', $trimmed);
            $trimmed = (string) preg_replace('/\n```$/', '', $trimmed);
        }

        return trim($trimmed);
    }

    private function stripLeadingTitle(string $markdown): string
    {
        return trim((string) preg_replace('/^\s*#\s+[^\n]+\n*/', '', $markdown, 1));
    }
}
