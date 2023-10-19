<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class Chat extends Component
{
    public int $console_id;
    public array $game;
    public array $messages = [];

    protected $listeners = ['updateChatMessages'];

    public function updateChatMessages(array $messages)
    {
        $this->messages = $messages;
    }

    public function loadMessages()
    {
        $disk = 'data';
        $file_name = $this->chatFilePath();
        if (Storage::disk($disk)->exists($file_name)) {
            $this->messages = json_decode(Storage::disk($disk)->get($file_name), true) ?? [];
            $this->messages = Tool::sortBy($this->messages, 'timestamp');
        } else {
            // create new empty json chat file
            Storage::disk($disk)->put($file_name, '[]');
        }
    }

    public function chatFilePath(): string
    {
        return 'chat/' . $this->console_id . '.' . $this->game['id'] . '.json';
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
