<x-mail::message>
# Hello {{ $user->name }},

Your account has been created successfully for NEW ACCOUNT SETUP ON IBEDCPAY - FORM 74 AUTOMATION.

**Login Email:** {{ $user->email }} 
**Region:** {{ $user->region }}  
**Business Hub:** {{ $user->business_hub }}  
**Service Centre:** {{ $user->sc }} 
**Role:** {{ $user->authority }}  
**Password:** {{ $plainPassword }}

Please login and change your password after first login.




<!-- <x-mail::button :url="''">
Button Text
</x-mail::button> -->

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>


