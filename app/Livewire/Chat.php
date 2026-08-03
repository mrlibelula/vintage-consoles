<?php

namespace App\Livewire;

use App\Service\Tool;
use App\Support\BrowserLabel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Chat extends Component
{
    public int $console_id;
    public array $game;
    public array $messages = [];

    /** @var array<int, array{key: string, name: string, icon: string, user_id: int|null, last_seen: int}> */
    public array $online = [];

    protected $listeners = ['updateChatMessages'];

    private const PRESENCE_TTL_SECONDS = 45;

    public function updateChatMessages(array $messages)
    {
        $this->messages = $messages;
    }

    public function loadMessages()
    {
        $disk = 'data';
        $file_name = $this->chatFilePath();
        if (Storage::disk($disk)->exists($file_name)) {
            $decoded = json_decode(Storage::disk($disk)->get($file_name), true);
            $this->messages = is_array($decoded) ? $decoded : [];
            $this->messages = array_map(
                fn (array $message) => array_merge([
                    'user_id' => null,
                    'message' => '',
                    'timestamp' => null,
                ], $message),
                $this->messages
            );
            $this->messages = Tool::sortByDate($this->messages, 'timestamp');
        } else {
            Storage::disk($disk)->put($file_name, '[]');
        }

        $this->heartbeatPresence();
    }

    public function chatFilePath(): string
    {
        return 'chat/' . $this->console_id . '.' . $this->game['id'] . '.json';
    }

    public function presenceCacheKey(): string
    {
        return 'chat:presence.' . $this->console_id . '.' . $this->game['id'];
    }

    public function heartbeatPresence(): void
    {
        $key = $this->presenceCacheKey();
        $now = now()->timestamp;
        $ttl = self::PRESENCE_TTL_SECONDS;

        /** @var array<string, array{name: string, icon: string, user_id: int|null, last_seen: int}> $entries */
        $entries = Cache::get($key, []);
        if (! is_array($entries)) {
            $entries = [];
        }

        $entries = array_filter(
            $entries,
            fn ($entry) => is_array($entry) && ($now - (int) ($entry['last_seen'] ?? 0)) < $ttl
        );

        $presenceKey = auth()->check()
            ? 'user:' . auth()->id()
            : 'guest:' . session()->getId();

        $entries[$presenceKey] = [
            'name' => auth()->check() ? (string) auth()->user()->name : 'Guest',
            'icon' => auth()->check() ? 'user' : BrowserLabel::icon(request()->userAgent()),
            'user_id' => auth()->id(),
            'last_seen' => $now,
        ];

        Cache::put($key, $entries, $ttl + 15);

        $this->online = collect($entries)
            ->map(fn (array $entry, string $presenceKey) => [
                'key' => $presenceKey,
                'name' => (string) ($entry['name'] ?? 'Guest'),
                'icon' => (string) ($entry['icon'] ?? 'computer'),
                'user_id' => isset($entry['user_id']) && $entry['user_id'] !== null
                    ? (int) $entry['user_id']
                    : null,
                'last_seen' => (int) ($entry['last_seen'] ?? 0),
            ])
            ->sort(function (array $a, array $b) {
                $aAuth = $a['user_id'] !== null ? 0 : 1;
                $bAuth = $b['user_id'] !== null ? 0 : 1;
                if ($aAuth !== $bAuth) {
                    return $aAuth <=> $bAuth;
                }

                return strcasecmp($a['name'], $b['name']);
            })
            ->values()
            ->all();
    }

    public function refreshMessages()
    {
        $this->loadMessages();
    }

    public function mount()
    {
        $this->loadMessages();
    }

    public function render()
    {
        return view('livewire.chat');
    }
}
