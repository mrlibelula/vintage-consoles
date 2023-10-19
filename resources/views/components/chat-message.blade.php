@props(['message'])
<div {{ $attributes->merge(['class' => 'flex flex-col mt-2']) }}>
    <div class="text-xs text-cod-gray-600 leading-none">
        {{ \Carbon\Carbon::create($message['timestamp'])->isoFormat('MMM, Do YYYY HH:mm') }}
    </div>
    <div class="flex gap-x-3">
        <span class="text-sky-500 text-base leading-tight">
            {{ \App\Service\Tool::userName($message['user_id']) }}:
            <span class="text-cod-gray-300 text-base leading-tight">
                {{ $message['message'] }}
            </span>
        </span>
    </div>
</div>