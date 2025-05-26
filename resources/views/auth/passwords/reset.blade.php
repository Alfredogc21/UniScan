@extends('layouts.app')

@section('title', 'Nueva Contraseña')

@section('content')
<div class="login-wrapper">
  <!-- Círculos decorativos -->
  <div class="circle circle1"></div>
  <div class="circle circle2"></div>
  <div class="circle circle3"></div>
  <div class="circle circle4"></div>
  <div class="circle circle5"></div>

  <div class="login-container">
    <!-- Pico morado -->
    <div class="top-peak"></div>

    <div class="login-form">
      <h2>Restablece tu contraseña</h2>

      <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
          class="@error('email') input-error @enderror">

        @error('email')
          <p class="error-text">{{ $message }}</p>
        @enderror

        <label for="password">Nueva contraseña</label>
        <input id="password" type="password" name="password" required
          class="@error('password') input-error @enderror">

        @error('password')
          <p class="error-text">{{ $message }}</p>
        @enderror

        <label for="password_confirmation">Confirmar nueva contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <button type="submit" class="btn-purple mt-3">Guardar nueva contraseña</button>
      </form>

      <p class="text-center mt-4">
        ¿Recordaste tu contraseña?
        <a href="{{ route('login') }}" class="link-purple">Inicia sesión</a>
      </p>
    </div>

    <!-- Imagen QR al lado -->
    <div class="login-image">
      <img src="{{ asset('img/recuperar.JPG') }}" alt="Decoración onda" />
    </div>
  </div>
</div>
@endsection
