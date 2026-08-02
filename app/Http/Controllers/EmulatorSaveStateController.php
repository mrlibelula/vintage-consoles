<?php

namespace App\Http\Controllers;

use App\Actions\UpsertEmulatorSaveState;
use App\Models\EmulatorSaveState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmulatorSaveStateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate($this->contextRules());

        $saves = EmulatorSaveState::query()
            ->where('user_id', $request->user()->id)
            ->where('console', $validated['console'])
            ->where('game_slug', $validated['game_slug'])
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

        $uploaded = $request->file('state');
        abort_if($uploaded->getSize() === 0 || $uploaded->getSize() === false, 422, 'Save state file is empty.');

        // Prefer reading from the temp path (streamable) over loading via UploadedFile::get().
        $path = $uploaded->getRealPath();
        abort_unless(is_string($path) && is_readable($path), 422, 'Could not read uploaded save state.');

        $contents = file_get_contents($path);
        abort_if($contents === false || $contents === '', 422, 'Save state file is empty.');

        $save = app(UpsertEmulatorSaveState::class)->execute(
            $request->user(),
            $validated['console'],
            $validated['game_slug'],
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

        $disk     = Storage::disk('savestates');
        $path     = $saveState->disk_path;
        $size     = $disk->size($path);
        $stream   = $disk->readStream($path);
        $filename = "{$saveState->game_slug}-slot-{$saveState->slot}.state";

        // Content-Transfer-Encoding: binary and Cache-Control: no-transform prevent
        // PHP zlib output compression, mbstring output handlers, and reverse-proxy
        // gzip from corrupting the binary payload before arrayBuffer() on the client.
        // Content-Length lets the browser (and any proxy) detect truncation eagerly.
        return response()->streamDownload(function () use ($stream) {
            // Flush any output buffer started before this callback (e.g. from
            // output_buffering or zlib.output_compression in php.ini) so that
            // binary bytes are written directly to the SAPI output layer.
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $filename, [
            'Content-Type'              => 'application/octet-stream',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Length'            => $size,
            'Cache-Control'             => 'no-transform, no-store, private',
            'X-Content-Type-Options'    => 'nosniff',
        ]);
    }

    public function destroy(Request $request, EmulatorSaveState $saveState): JsonResponse
    {
        $this->authorizeSave($request, $saveState);

        $disk = Storage::disk('savestates');
        $disk->delete($saveState->disk_path);
        if ($saveState->backup_disk_path) {
            $disk->delete($saveState->backup_disk_path);
        } else {
            // Back-compat: if backup exists but DB was never updated, still delete it.
            $disk->delete("{$saveState->disk_path}.backup");
        }
        $saveState->delete();

        return response()->json(['data' => ['deleted' => true]]);
    }

    public function restoreBackup(Request $request, EmulatorSaveState $saveState): JsonResponse
    {
        $this->authorizeSave($request, $saveState);

        $disk = Storage::disk('savestates');
        $primaryPath = $saveState->disk_path;
        $backupPath = $saveState->backup_disk_path ?: "{$primaryPath}.backup";

        abort_unless($disk->exists($primaryPath), 404);
        abort_unless($disk->exists($backupPath), 404);

        $tmpPath = "{$primaryPath}.tmp-".bin2hex(random_bytes(6));

        // Swap primary and backup.
        $disk->move($primaryPath, $tmpPath);
        $disk->move($backupPath, $primaryPath);
        $disk->move($tmpPath, $backupPath);

        [$primaryChecksum, $primaryBytes] = $this->checksumAndSize($disk, $primaryPath);
        [$backupChecksum, $backupBytes] = $this->checksumAndSize($disk, $backupPath);

        $saveState->forceFill([
            'disk_path' => $primaryPath,
            'size_bytes' => $primaryBytes,
            'checksum' => $primaryChecksum,
            'backup_disk_path' => $backupPath,
            'backup_size_bytes' => $backupBytes,
            'backup_checksum' => $backupChecksum,
            'backup_updated_at' => Carbon::now(),
        ])->save();

        return response()->json(['data' => $this->serializeSave($saveState->fresh())]);
    }

    private function contextRules(): array
    {
        return [
            'console'   => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
            'game_slug' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }

    private function authorizeSave(Request $request, EmulatorSaveState $saveState): void
    {
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
        $backupPath = $save->backup_disk_path ?: ($save->disk_path ? "{$save->disk_path}.backup" : null);
        $hasBackup = $backupPath ? Storage::disk('savestates')->exists($backupPath) : false;

        return [
            'id'           => $save->id,
            'slot'         => $save->slot,
            'label'        => $save->label,
            'size_bytes'   => $save->size_bytes,
            'checksum'     => $save->checksum,
            'updated_at'   => $save->updated_at?->toISOString(),
            'has_backup'   => $hasBackup,
            'backup_updated_at' => $save->backup_updated_at?->toISOString(),
            'download_url' => route('player-data.save-states.download', $save),
            'delete_url'   => route('player-data.save-states.destroy', $save),
            'restore_backup_url' => $hasBackup ? route('player-data.save-states.restore-backup', $save) : null,
        ];
    }

    /**
     * @return array{string,int} [checksum, bytes]
     */
    private function checksumAndSize($disk, string $path): array
    {
        $stream = $disk->readStream($path);
        if (! is_resource($stream)) {
            $contents = (string) $disk->get($path);

            return [hash('sha256', $contents), strlen($contents)];
        }

        $hash = hash_init('sha256');
        $bytes = 0;

        while (! feof($stream)) {
            $chunk = fread($stream, 1024 * 1024);
            if ($chunk === false) {
                break;
            }

            $bytes += strlen($chunk);
            hash_update($hash, $chunk);
        }

        fclose($stream);

        return [hash_final($hash), $bytes];
    }
}
