@extends('layouts.app')

@section('title', 'Restablecer Contraseña')

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
      <h2>¿Olvidaste tu contraseña?</h2>

      @if (session('status'))
        <div class="alert alert-success">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
          class="@error('email') input-error @enderror">

        @error('email')
          <p class="error-text">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-purple mt-3">Enviar enlace de restablecimiento</button>
      </form>

      <p class="text-center mt-4">
        ¿Ya lo recordaste?
        <a href="{{ route('login') }}" class="link-purple">Inicia sesión</a>
      </p>
    </div>

    <!-- Imagen QR al lado -->
    <div class="login-image">
      <img src="{{ asset('img/recuperar.JPG') }}" alt="recuperar" />
    </div>
  </div>
</div>
@endsection
