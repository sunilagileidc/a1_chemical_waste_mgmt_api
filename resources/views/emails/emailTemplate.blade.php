@component('mail::message')
<div style="padding:20px; border: 2px solid #759898;
    border-radius: 10px;">
{!! $template_body !!}

@if($actionUrl)
<a href="{{$actionUrl}}">link</a>
@endif

{{-- Action Button --}}
@isset($actionText)
<?php
$level = 'danger';
switch ($level) {
    case 'success':
    case 'error':
        $color = $level;
        break;
    default:
        $color = 'primary';
}
?>

    {{ $actionText }}

@endisset
<div style="margin-top:40px">
{!! $template_signature !!}
</div>
</div>

