<?php

namespace App\Http\Controllers;

use App\Actions\UpsertEmulatorSaveState;
use App\Models\EmulatorSaveState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmulatorSaveStateController extends Controller
{
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
            'slot' => ['required', 'integer', 'min:1', 'max:'.UpsertEmulatorSaveState::MAX_SLOTS],
            'label' => ['nullable', 'string', 'max:80'],
            'state' => ['required', 'file', 'max:102400'],
        ]);

        $contents = $request->file('state')->get();

        $save = app(UpsertEmulatorSaveState::class)->execute(
            $request->user(),
            $validated['console'],
            $validated['game_id'],
            $validated['emulator'],
            (int) $validated['slot'],
            $validated['label'] ?? null,
            $contents,
        );

        return response()->json(['data' => $this->serializeSave($save)], 201);
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
        // Avoid strict-type mismatches across DB drivers (e.g. bigint IDs coming back as strings).
        $requestUserId = (string) $request->user()->id;
        $ownerUserId = (string) $saveState->user_id;

        if ($ownerUserId !== $requestUserId) {
            logger()->warning('EmulatorSaveState unauthorized access attempt.', [
                'save_state_id' => $saveState->id,
                'owner_user_id' => $ownerUserId,
                'request_user_id' => $requestUserId,
                'path' => $request->path(),
            ]);

            abort(403);
        }
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
