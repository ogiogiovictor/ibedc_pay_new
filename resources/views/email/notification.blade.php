<x-mail::message>
# Introduction

 # Hello {{ $user_email }}

We are pleased to inform you that your payment has been received.

@php
$renderData = function($data, $level = 0) use (&$renderData) {
    echo '<ul>';
    foreach ($data as $key => $value) {
        echo '<li>';
        if (is_array($value) || is_object($value)) {
            echo str_repeat('&nbsp;', $level * 4) . ucfirst($key) . ':';
            $renderData($value, $level + 1);
        } else {
            echo str_repeat('&nbsp;', $level * 4) . ucfirst($key) . ': ' . htmlspecialchars($value);
        }
        echo '</li>';
    }
    echo '</ul>';
};

$renderData(json_decode(json_encode($payload), true));
@endphp

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
