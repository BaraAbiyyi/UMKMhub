@props(['steps' => [], 'current' => 0])

<ol class="flex items-center gap-2 text-sm">
    @foreach ($steps as $index => $step)
        @php $isDone = $index < $current; $isActive = $index === $current; @endphp
        <li class="flex items-center gap-2 {{ $isActive ? 'text-brand' : 'text-ink-muted' }}">
            <span class="flex h-7 w-7 items-center justify-center rounded-full border text-xs font-bold
                {{ $isDone ? 'border-ok bg-ok text-white' : ($isActive ? 'border-brand bg-brand text-white' : 'border-mist bg-white') }}">
                @if ($isDone)
                    <x-icon name="check" class="h-4 w-4" />
                @else
                    {{ $index + 1 }}
                @endif
            </span>
            <span class="hidden font-medium sm:inline {{ $isActive ? 'text-ink' : '' }}">{{ $step }}</span>
        </li>
        @if (! $loop->last)
            <li class="h-px w-6 bg-mist sm:w-10" aria-hidden="true"></li>
        @endif
    @endforeach
</ol>
