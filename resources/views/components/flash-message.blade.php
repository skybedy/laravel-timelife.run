@php
    switch ($type) {
        case 'success':
            $bgColor = 'bg-amber-100';
            $borderColor = 'border-amber-400';
            $textColor = 'text-amber-800';
            $iconColor = 'text-amber-500';
            break;
        case 'error':
            $bgColor = 'bg-red-100';
            $borderColor = 'border-red-400';
            $textColor = 'text-red-700';
            $iconColor = 'text-red-500';
            break;
        case 'warning':
            $bgColor = 'bg-yellow-100';
            $borderColor = 'border-yellow-400';
            $textColor = 'text-yellow-700';
            $iconColor = 'text-yellow-500';
            break;
        case 'info':
            $bgColor = 'bg-blue-100';
            $borderColor = 'border-blue-400';
            $textColor = 'text-blue-700';
            $iconColor = 'text-blue-500';
            break;
        default:
            $bgColor = 'bg-gray-100';
            $borderColor = 'border-gray-400';
            $textColor = 'text-gray-700';
            $iconColor = 'text-gray-500';
    }
@endphp

<div class="{{ $bgColor }} border {{ $borderColor }} {{ $textColor }} border-x-0 px-4 py-3  relative mt-1 text-center" role="alert">
    <span class="block sm:inline">{!! $message !!}</span>
</div>
