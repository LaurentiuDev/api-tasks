@component('mail::message')

Hi {{$user->name}},

Please verify your link for password reset :

@component('mail::button',['url'=>'/resetPassword'.'/'.$user->hash])

Change password

@endcomponent



Thanks.<br>

{{config('app.name')}}

@endcomponent