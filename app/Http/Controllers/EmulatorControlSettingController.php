<?php

namespace App\Http\Controllers;

use App\Models\EmulatorControlSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmulatorControlSettingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $profile = $validated['profile'] ?? 'default';

        $setting = EmulatorControlSetting::query()
            ->where('user_id', $request->user()->id)
            ->where('console', $validated['console'])
            ->where('game_id', $validated['game_id'])
            ->where('emulator', $validated['emulator'])
            ->where('profile', $profile)
            ->first();

        return response()->json([
            'data' => $setting ? $this->serializeSetting($setting) : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            ...$this->rules(),
            'settings' => ['required', 'array'],
        ]);
        $profile = $validated['profile'] ?? 'default';
        $settings = $validated['settings'];

        $setting = EmulatorControlSetting::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'console' => $validated['console'],
                'game_id' => $validated['game_id'],
                'emulator' => $validated['emulator'],
                'profile' => $profile,
            ],
            [
                'settings' => $settings,
                'checksum' => hash('sha256', json_encode($settings)),
            ],
        );

        return response()->json(['data' => $this->serializeSetting($setting->fresh())]);
    }

    private function rules(): array
    {
        return [
            'console' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
            'game_id' => ['required', 'string', 'max:128'],
            'emulator' => ['required', 'string', Rule::in(['emulatorjs', 'jsdos'])],
            'profile' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }

    private function serializeSetting(EmulatorControlSetting $setting): array
    {
        return [
            'id' => $setting->id,
            'profile' => $setting->profile,
            'settings' => $setting->settings,
            'checksum' => $setting->checksum,
            'updated_at' => $setting->updated_at?->toISOString(),
        ];
    }
}
