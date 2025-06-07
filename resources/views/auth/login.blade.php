@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@push('styles')
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
@endpush

@section('content')
<div class="login-wrapper">
  <!-- Círculos decorativos -->
  <div class="circle circle1"></div>
  <div class="circle circle2"></div>
  <div class="circle circle3"></div>
  <div class="circle circle4"></div>
  <div class="circle circle5"></div>

  <div class="login-container">
    <!-- Pico morado recto arriba -->
    <div class="top-peak"></div>

    <div class="login-form">
      <h2>Iniciar Sesión</h2>

      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-field">
          <label for="email">Correo electrónico</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                 class="@error('email') input-error @enderror">
          @error('email')
            <p class="error-text">{{ $message }}</p>
          @enderror
        </div>

        <div class="form-field">
          <label for="password">Contraseña</label>
          <input type="password" id="password" name="password" required autocomplete="current-password"
                 class="@error('password') input-error @enderror">
          @error('password')
            <p class="error-text">{{ $message }}</p>
          @enderror
        </div>
        
        <div class="forgot-password-container">
          <a href="{{ route('password.request') }}" class="link-purple">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn-purple">Iniciar Sesión</button>
      </form>
  
      <p class="text-center mt-4">
        ¿No tienes cuenta?
        <a href="{{ route('register.form') }}" class="link-purple">Regístrate</a>
      </p>
    </div>

    <!-- Imagen onda a un lado -->
    <div class="login-image">
      <img src="{{ asset('img/qr.jpg') }}" alt="Decoración onda" loading="lazy" />
    </div>
  </div>
</div>
@endsection
