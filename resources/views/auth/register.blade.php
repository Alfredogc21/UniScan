@extends('layouts.app')

@section('title', 'Registrarse')

@push('styles')
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
@endpush

@section('content')
<div class="login-wrapper">
  <!-- Círculos decorativos (mismos que en login) -->
  <div class="circle circle1"></div>
  <div class="circle circle2"></div>
  <div class="circle circle3"></div>
  <div class="circle circle4"></div>
  <div class="circle circle5"></div>

  <div class="login-container">
    <!-- Pico morado recto arriba -->
    <div class="top-peak"></div>

    <div class="login-form">
      <h2>Registrarse</h2>

      <form method="POST" action="{{ route('register') }}">
        @csrf
        <!-- La asignación de roles ahora será responsabilidad del administrador -->
        <input type="hidden" name="role_id" value="3">
        <!-- El valor 3 corresponde al rol de Estudiante, que es el predeterminado para nuevos usuarios -->

        <div class="form-field">
          <label for="name">Nombre</label>
          <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
            class="@error('name') input-error @enderror" autocomplete="name">
          @error('name')
            <p class="error-text">{{ $message }}</p>
          @enderror
        </div>

        <div class="form-field">
          <label for="email">Correo electrónico</label>
          <input id="email" type="email" name="email" value="{{ old('email') }}" required
            class="@error('email') input-error @enderror" autocomplete="email">
          @error('email')
            <p class="error-text">{{ $message }}</p>
          @enderror
        </div>

        <div class="form-field">
          <label for="password">Contraseña</label>
          <input id="password" type="password" name="password" required
            class="@error('password') input-error @enderror" autocomplete="new-password">
          @error('password')
            <p class="error-text">{{ $message }}</p>
          @enderror
        </div>

        <div class="form-field">
          <label for="password_confirmation">Confirmar contraseña</label>
          <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn-purple">Registrarse</button>
      </form>

      <p class="text-center mt-4">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="link-purple">Inicia sesión</a>
      </p>
    </div>

    <!-- Imagen onda a un lado -->
    <div class="login-image">
      <img src="{{ asset('img/qr.jpg') }}" alt="Decoración onda" loading="lazy" />
    </div>
  </div>
</div>
@endsection
