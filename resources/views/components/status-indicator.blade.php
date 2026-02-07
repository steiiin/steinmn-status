@props(['state' => 'green'])

@php
    $stateClass = match ($state) {
        'yellow' => 'status-indicator--yellow',
        'red' => 'status-indicator--red',
        default => 'status-indicator--green',
    };
@endphp

<span {{ $attributes->merge(['class' => "status-indicator {$stateClass}"]) }} aria-hidden="true">
    <span class="status-indicator__pulse"></span>
    <span class="status-indicator__core"></span>
</span>
