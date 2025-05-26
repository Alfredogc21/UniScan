@extends('layouts.app')

@section('title', 'Registrarse')

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
         <label for="name">Selecciona tu rol</label>
<select name="role_id" id="role_id" required
  class="styled-select @error('role_id') input-error @enderror">
  <option value=""> Elige un rol </option>
  <option value="1" {{ old('role_id') == 1 ? 'selected' : '' }}>Admin</option>
  <option value="2" {{ old('role_id') == 2 ? 'selected' : '' }}>Profesor</option>
  <option value="3" {{ old('role_id') == 3 ? 'selected' : '' }}>Estudiante</option>
</select>



@error('role_id')
  <p class="error-text">{{ $message }}</p>
@enderror

        <label for="name">Nombre</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
          class="@error('name') input-error @enderror">

        @error('name')
          <p class="error-text">{{ $message }}</p>
        @enderror

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required
          class="@error('email') input-error @enderror">

        @error('email')
          <p class="error-text">{{ $message }}</p>
        @enderror

        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required>

        @error('password')
          <p class="error-text">{{ $message }}</p>
        @enderror

        <label for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <button type="submit" class="btn-purple">Registrarse</button>
      </form>

      <p class="text-center mt-4">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="link-purple">Inicia sesión</a>
      </p>
    </div>

    <!-- Imagen onda a un lado -->
    <div class="login-image">
      <img src="{{ asset('img/qr.JPG') }}" alt="Decoración onda" />
    </div>
  </div>
</div>
@endsection
