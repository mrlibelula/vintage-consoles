<?php

namespace App\Http\Controllers;

use App\Models\EmulatorSaveState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmulatorSaveStateController extends Controller
{
    private const MAX_SLOTS = 5;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate($this->contextRules());

        $saves = EmulatorSaveState::query()
            ->where('user_id', $request->user()->id)
            ->where('console', $validated['console'])
            ->where('game_id', $validated['game_id'])
            ->where('emulator', $validated['emulator'])
            ->orderBy('slot')
            ->get()
            ->map(fn (EmulatorSaveState $save) => $this->serializeSave($save));

        return response()->json(['data' => $saves]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            ...$this->contextRules(),
            'slot' => ['required', 'integer', 'min:1', 'max:'.self::MAX_SLOTS],
            'label' => ['nullable', 'string', 'max:80'],
            'state' => ['required', 'file', 'max:102400'],
        ]);

        $contents = $request->file('state')->get();
        $diskPath = $this->diskPath(
            $request->user()->id,
            $validated['console'],
            $validated['game_id'],
            $validated['emulator'],
            (int) $validated['slot'],
        );

        Storage::disk('savestates')->put($diskPath, $contents);

        $save = EmulatorSaveState::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'console' => $validated['console'],
                'game_id' => $validated['game_id'],
                'emulator' => $validated['emulator'],
                'slot' => $validated['slot'],
            ],
            [
                'label' => $validated['label'] ?? null,
                'disk_path' => $diskPath,
                'size_bytes' => strlen($contents),
                'checksum' => hash('sha256', $contents),
            ],
        );

        return response()->json(['data' => $this->serializeSave($save->fresh())], 201);
    }

    public function update(Request $request, EmulatorSaveState $saveState): JsonResponse
    {
        $this->authorizeSave($request, $saveState);

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:80'],
        ]);

        $saveState->update(['label' => $validated['label'] ?? null]);

        return response()->json(['data' => $this->serializeSave($saveState->fresh())]);
    }

    public function download(Request $request, EmulatorSaveState $saveState): StreamedResponse
    {
        $this->authorizeSave($request, $saveState);

        abort_unless(Storage::disk('savestates')->exists($saveState->disk_path), 404);

        $stream = Storage::disk('savestates')->readStream($saveState->disk_path);

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, "slot-{$saveState->slot}.state", ['Content-Type' => 'application/octet-stream']);
    }

    public function destroy(Request $request, EmulatorSaveState $saveState): JsonResponse
    {
        $this->authorizeSave($request, $saveState);

        Storage::disk('savestates')->delete($saveState->disk_path);
        $saveState->delete();

        return response()->json(['data' => ['deleted' => true]]);
    }

    private function contextRules(): array
    {
        return [
            'console' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
            'game_id' => ['required', 'string', 'max:128'],
            'emulator' => ['required', 'string', Rule::in(['emulatorjs', 'jsdos'])],
        ];
    }

    private function authorizeSave(Request $request, EmulatorSaveState $saveState): void
    {
        abort_unless($saveState->user_id === $request->user()->id, 403);
    }

    private function diskPath(int $userId, string $console, string $gameId, string $emulator, int $slot): string
    {
        $safeGameId = preg_replace('/[^A-Za-z0-9_-]/', '_', $gameId);

        return "{$userId}/{$console}/{$safeGameId}/{$emulator}/slot-{$slot}.state";
    }

    private function serializeSave(EmulatorSaveState $save): array
    {
        return [
            'id' => $save->id,
            'slot' => $save->slot,
            'label' => $save->label,
            'size_bytes' => $save->size_bytes,
            'checksum' => $save->checksum,
            'updated_at' => $save->updated_at?->toISOString(),
            'download_url' => route('player-data.save-states.download', $save),
            'delete_url' => route('player-data.save-states.destroy', $save),
        ];
    }
}
