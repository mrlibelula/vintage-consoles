@props(['message'])
<div {{ $attributes->merge(['class' => 'flex flex-col mt-2']) }}>
    @if (! empty($message['timestamp']))
    <div class="text-xs text-cod-gray-600 leading-none">
        {{ \Carbon\Carbon::parse($message['timestamp'])->isoFormat('MMM, Do YYYY HH:mm') }}
    </div>
    @endif
    <div class="flex gap-x-3">
        <span class="text-sky-500 text-base leading-tight">
            {{ \App\Service\Tool::userName($message['user_id'] ?? null) }}:
            <span class="text-cod-gray-300 text-base leading-tight">
                {{ $message['message'] }}
            </span>
        </span>
    </div>
</div>