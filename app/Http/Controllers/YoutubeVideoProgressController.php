<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\YoutubeVideoProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YoutubeVideoProgressController extends Controller
{
    public function index(Request $request, Game $game): JsonResponse
    {
        $rows = YoutubeVideoProgress::query()
            ->where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->get(['youtube_id', 'position_seconds', 'updated_at']);

        $data = [];
        foreach ($rows as $row) {
            $data[$row->youtube_id] = [
                'position_seconds' => (int) $row->position_seconds,
                'updated_at' => $row->updated_at?->toISOString(),
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function upsert(Request $request, Game $game): JsonResponse
    {
        $validated = $request->validate([
            'youtube_id' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{11}$/'],
            'position_seconds' => ['required', 'integer', 'min:0', 'max:86400'],
        ]);

        // Ignore near-start positions so we don't resume at 0–2s noise
        $position = (int) $validated['position_seconds'];
        if ($position < 3) {
            YoutubeVideoProgress::query()
                ->where('user_id', $request->user()->id)
                ->where('game_id', $game->id)
                ->where('youtube_id', $validated['youtube_id'])
                ->delete();

            return response()->json(['data' => null]);
        }

        $row = YoutubeVideoProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'game_id' => $game->id,
                'youtube_id' => $validated['youtube_id'],
            ],
            [
                'position_seconds' => $position,
            ],
        );

        return response()->json([
            'data' => [
                'youtube_id' => $row->youtube_id,
                'position_seconds' => (int) $row->position_seconds,
                'updated_at' => $row->updated_at?->toISOString(),
            ],
        ]);
    }
}
