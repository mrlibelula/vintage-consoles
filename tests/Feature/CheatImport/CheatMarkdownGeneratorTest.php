<?php

use App\Services\CheatImport\CheatMarkdownGenerator;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

beforeEach(function () {
    config(['openai.api_key' => 'sk-test-fake-key']);
});

it('returns the ai markdown content on success without a leading game title', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                ['message' => ['content' => "# Contra\n\n## Cheat Codes\n- Up, Up, Down, Down, Left, Right, Left, Right, B, A, Start — 30 lives"]],
            ],
        ]),
    ]);

    $markdown = app(CheatMarkdownGenerator::class)->generate(
        'This is a full user manual with a cheat section buried inside it.',
        'Contra',
        'NES',
        CheatMarkdownGenerator::MODE_EXTRACT,
    );

    expect($markdown)->not->toContain('# Contra')
        ->and($markdown)->toStartWith('## Cheat Codes')
        ->and($markdown)->toContain('30 lives');
});

it('strips markdown code fences the model may add', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                ['message' => ['content' => "```markdown\n## Cheat Codes\n- Code A\n```"]],
            ],
        ]),
    ]);

    $markdown = app(CheatMarkdownGenerator::class)->generate(
        'source text',
        'Contra',
        'NES',
        CheatMarkdownGenerator::MODE_FORMAT,
    );

    expect($markdown)->toBe("## Cheat Codes\n- Code A");
});

it('sends the extract prompt when analyzing uploaded documents', function () {
    OpenAI::fake([
        CreateResponse::fake(['choices' => [['message' => ['content' => '# Contra']]]]),
    ]);

    config(['openai.model' => 'gpt-5-mini']);

    app(CheatMarkdownGenerator::class)->generate(
        'Manual text mentioning cheats somewhere.',
        'Contra',
        'NES',
        CheatMarkdownGenerator::MODE_EXTRACT,
    );

    OpenAI::chat()->assertSent(function (string $method, array $parameters) {
        $system = $parameters['messages'][0]['content'] ?? '';

        return $method === 'create'
            && $parameters['model'] === 'gpt-5-mini'
            && count($parameters['messages']) === 2
            && $parameters['messages'][0]['role'] === 'system'
            && str_contains($system, 'AND all important supporting data around them')
            && str_contains($parameters['messages'][1]['content'], 'Source document:')
            && str_contains($parameters['messages'][1]['content'], 'Contra');
    });
});

it('sends the format prompt when reshaping pasted text', function () {
    OpenAI::fake([
        CreateResponse::fake(['choices' => [['message' => ['content' => '# Contra']]]]),
    ]);

    app(CheatMarkdownGenerator::class)->generate(
        "Ctrl+Alt+X skips the quiz.\nAlways pay the cabbie.",
        'Contra',
        'NES',
        CheatMarkdownGenerator::MODE_FORMAT,
    );

    OpenAI::chat()->assertSent(function (string $method, array $parameters) {
        $system = $parameters['messages'][0]['content'] ?? '';

        return $method === 'create'
            && str_contains($system, 'NOT to analyze, filter, or extract')
            && str_contains($system, 'Do NOT include a level-1 heading')
            && str_contains($system, 'Prefer bullet lists')
            && str_contains($system, 'Use a Markdown table only when')
            && str_contains($parameters['messages'][1]['content'], 'Pasted text:')
            && ! str_contains($system, 'AND all important supporting data around them');
    });
});

it('throws when the source text is blank', function () {
    expect(fn () => app(CheatMarkdownGenerator::class)->generate('   ', 'Contra', 'NES'))
        ->toThrow(RuntimeException::class, 'Nothing to process');
});

it('throws when the openai api key is missing', function () {
    config(['openai.api_key' => null]);

    expect(fn () => app(CheatMarkdownGenerator::class)->generate('source', 'Contra', 'NES'))
        ->toThrow(RuntimeException::class, 'OpenAI API key is not configured');
});

it('throws when the model returns empty content', function () {
    OpenAI::fake([
        CreateResponse::fake(['choices' => [['message' => ['content' => '']]]]),
    ]);

    expect(fn () => app(CheatMarkdownGenerator::class)->generate('source', 'Contra', 'NES'))
        ->toThrow(RuntimeException::class, 'did not return any content');
});
