@props(['message'])
<div {{ $attributes->merge(['class' => 'mt-2 flex flex-col']) }}>
    @if (! empty($message['timestamp']))
    <div class="text-xs leading-none text-cod-gray-500 dark:text-cod-gray-600">
        {{ \Carbon\Carbon::parse($message['timestamp'])->isoFormat('MMM, Do YYYY HH:mm') }}
    </div>
    @endif
    <div class="flex items-start gap-x-1.5">
        <span @class([
            'mt-0.5 shrink-0',
            'text-sky-600 dark:text-sky-400' => ! empty($message['user_id']),
            'text-cod-gray-500 dark:text-cod-gray-500' => empty($message['user_id']),
        ])>
            <x-pixelarticon
                :name="! empty($message['user_id']) ? 'user' : 'computer'"
                :size="20"
            />
        </span>
        <span class="min-w-0 text-base leading-tight text-sky-600 dark:text-sky-400">
            {{ \App\Service\Tool::userName($message['user_id'] ?? null) }}:
            <span class="text-base leading-tight text-cod-gray-800 dark:text-cod-gray-300">
                {{ $message['message'] }}
            </span>
        </span>
    </div>
</div>
