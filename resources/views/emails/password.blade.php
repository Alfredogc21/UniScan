@component('mail::message')
# Solicitud de restablecimiento de contraseña

Hola {{ $user->name ?? 'usuario' }},

Hemos recibido una solicitud para restablecer tu contraseña. Si no hiciste esta solicitud, puedes ignorar este correo.

Haz clic en el siguiente botón para restablecer tu contraseña:

@component('mail::button', ['url' => $url])
Restablecer contraseña
@endcomponent

Este enlace caduca en 60 minutos.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
