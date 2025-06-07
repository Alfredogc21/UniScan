<!DOCTYPE html>
<html lang="es">
<head>
@push('styles')
  <link rel="icon" href="{{ asset('img/uniscan_logo.png') }}" type="image/png" />
<style>
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #fce1ff, #e0c7f7, #ffffff);
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1.5rem;
  overflow-x: hidden;
  box-sizing: border-box;
}

.login-wrapper {
  position: relative;
  width: 100%;
  max-width: 1100px;
  min-height: 80vh;
  background: white;
  border-radius: 30px;
  box-shadow: 0 15px 50px rgba(101, 64, 255, 0.25);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  gap: 3rem;
}

/* Pico morado recto arriba */
.top-peak {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 120px;
  background: linear-gradient(90deg, #7f6fcb, #ab94ff);
  clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
  border-top-left-radius: 30px;
  border-top-right-radius: 30px;
  z-index: 1;
}

/* Círculos decorativos grandes y pequeños */
.circle {
  position: absolute;
  border-radius: 50%;
  opacity: 0.15;
  z-index: 0;
}
.circle1 {
  width: 220px;
  height: 220px;
  background: #a084f9;
  top: 40px;
  left: -100px;
}
.circle2 {
  width: 180px;
  height: 180px;
  background: #c3afff;
  bottom: 90px;
  right: -90px;
}
.circle3 {
  width: 120px;
  height: 120px;
  background: #b998ff;
  top: 200px;
  right: 40px;
}
.circle4 {
  width: 140px;
  height: 140px;
  background: #d6bcff;
  bottom: 40px;
  left: 50px;
}
.circle5 {
  width: 90px;
  height: 90px;
  background: #af96ff;
  top: 140px;
  left: 250px;
}

/* Contenedor del formulario e imagen */
.login-container {
  display: flex;
  width: 100%;
  max-width: 1000px;
  height: 100%;
  align-items: center;
  justify-content: space-between;
  position: relative;
  padding-top: 120px; /* espacio para el pico */
  padding-left: 1rem;
  padding-right: 1rem;
  z-index: 2;
  gap: 2rem;
  box-sizing: border-box;
}

/* Formulario */

.styled-select {
  width: 100%;
  padding: 14px 15px;
  border-radius: 12px;
  border: 1.5px solid #c7b9f7;
  font-size: 1rem;
  color: #4a3c85;
  background: white;
  appearance: none; /* elimina estilo por defecto */
  background-image: url('data:image/svg+xml;utf8,<svg fill="none" stroke="%237f6fcb" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M6 9l6 6 6-6"/></svg>');
  background-repeat: no-repeat;
  background-position: right 15px center;
  background-size: 1em;
  cursor: pointer;
  transition: border-color 0.3s ease;
}

.styled-select:focus {
  outline: none;
  border-color: #7f6fcb;
  box-shadow: 0 0 8px #7f6fcbaa;
}
.login-form {
  flex: 1;
  background: white;
  padding: 2.5rem 2.5rem 3.5rem;
  border-radius: 24px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  justify-content: center;
  color: #4a3c85;
  min-width: 280px;
  max-width: 500px;
  width: 100%;
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.login-form h2 {
  font-weight: 700;
  font-size: 2.5rem;
  margin-bottom: 2rem;
  text-align: center;
}

label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: block;
  font-size: 1rem;
  color: #574b9b;
  user-select: none; /* Evitar selección de texto para mejor interacción táctil */
}

input {
  width: 100%;
  padding: 14px 15px;
  margin-bottom: 1.5rem;
  border-radius: 12px;
  border: 1.5px solid #c7b9f7;
  font-size: 1rem;
  transition: border-color 0.3s ease;
  box-sizing: border-box;
}
input:focus {
  outline: none;
  border-color: #7f6fcb;
  box-shadow: 0 0 8px #7f6fcbaa;
}

.input-error {
  border-color: #f87171 !important;
  border-width: 2px; /* Hacer más visible el error para accesibilidad */
}

.error-text {
  color: #e53e3e;
  margin-top: 0.25rem;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.form-field {
  margin-bottom: 1rem;
}

.form-field label {
  margin-bottom: 0.4rem;
}

.form-field input {
  margin-bottom: 0.25rem;
}
.forgot-password-container {
  text-align: right;
  margin-top: 0.2rem; /* margen pequeño para que no quede tan separado */
  margin-bottom: 1.5rem; /* para separar del botón */
}

.forgot-password-container .link-purple {
  font-size: 0.9rem;
  font-weight: 600;
  display: inline-block;
  padding: 0.25rem 0; /* Para hacer el área de toque más grande en móviles */
}



/* Botón centrado */
.btn-purple {
  background: linear-gradient(90deg, #7f6fcb, #ab94ff);
  border: none;
  padding: 14px;
  border-radius: 30px;
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  cursor: pointer;
  transition: background 0.4s ease;
  margin: 20px auto 0; /* margen arriba 20px, centrado horizontal */
  box-shadow: 0 4px 10px rgba(127,111,203,0.4);
  display: block;
  width: 200px;
  text-align: center;
}
.btn-purple:hover {
  background: linear-gradient(90deg, #5e4bb7, #7e6fc8);
}
.btn-purple:active {
  background: linear-gradient(90deg, #534498, #6f60b9); /* Oscurecer un poco más al hacer clic para feedback visual */
}

.link-purple {
  display: inline-block;
  margin-top: 1.5rem;
  text-align: center;
  color: #6c43cd;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.3s;
  padding: 0.25rem 0.1rem; /* Hace el área de toque más grande */
}
.link-purple:hover {
  color: rgb(158, 89, 190);
  text-decoration: underline;
}
.link-purple:active {
  transform: translateY(1px);
}
 
/* Imagen onda */
.login-image {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  max-width: 450px;
  height: auto;
  overflow: hidden;
}
.login-image img {
  width: 100%;
  height: auto;
  border-radius: 30px;
  object-fit: cover;
  box-shadow: 0 10px 30px rgba(127, 111, 203, 0.3);
}

/* Alertas */
.alert-success {
  background-color: #d1f7d6;
  color: #2e7d32;
  padding: 10px 15px;
  border-radius: 10px;
  margin-bottom: 1rem;
  font-weight: 600;
  text-align: center;
}

/* Responsive */
@media screen and (orientation: landscape) and (max-height: 600px) {
  body {
    padding: 0.5rem;
  }
  
  .login-wrapper {
    margin: 0;
    padding: 0.5rem;
    min-height: auto;
    max-height: none;
  }
  
  .login-container {
    padding-top: 80px;
  }
  
  .top-peak {
    height: 80px;
  }
  
  .login-form h2 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
  }
  
  input, .btn-purple {
    padding: 8px 10px;
  }
  
  .form-field {
    margin-bottom: 0.5rem;
  }
}

@media (max-width: 1024px) {
  .login-wrapper {
    flex-direction: column;
    padding: 1rem;
    height: auto;
    border-radius: 20px;
    min-height: auto;
    margin: 1rem;
  }

  .login-container {
    flex-direction: column;
    padding-top: 120px;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
  }

  .login-form {
    width: 100%;
    max-width: 100%;
    padding: 1.5rem 1.5rem 2.5rem;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    border-radius: 20px;
  }

  .login-image {
    display: none;
  }

  .btn-purple {
    width: 100%;
    padding: 12px;
    font-size: 1rem;
    border-radius: 25px;
    margin-top: 1rem;
  }

  input,
  .styled-select {
    font-size: 1rem;
    padding: 12px 14px;
    margin-bottom: 1.2rem;
  }

  h2 {
    font-size: 2rem;
    margin-bottom: 1.5rem;
  }
}

/* Mejoras adicionales para móviles pequeños */
@media (max-width: 480px) {
  body {
    padding: 1rem;
    background-attachment: fixed; /* Evitar problemas con fondo al hacer scroll */
  }
  
  .login-wrapper {
    padding: 0.5rem;
    margin: 0.5rem;
    border-radius: 15px;
    min-height: auto;
    max-height: none;
  }
  
  .top-peak {
    height: 100px;
  }
  
  .login-container {
    padding-top: 100px;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }
  
  .login-form {
    padding: 1rem 1rem 2rem;
  }
  
  .login-form h2 {
    font-size: 1.75rem;
    margin-bottom: 1.25rem;
  }
  
  label {
    font-size: 0.95rem;
    margin-bottom: 0.3rem;
  }
  
  input {
    padding: 10px 12px;
    border-radius: 10px;
    margin-bottom: 0.5rem;
    font-size: 16px; /* Evita zoom automático en iOS */
  }
  
  .form-field {
    margin-bottom: 0.75rem;
  }
  
  .forgot-password-container {
    margin-bottom: 0.75rem;
  }
  
  .forgot-password-container .link-purple {
    font-size: 0.85rem;
    padding: 0.5rem 0;
  }
  
  .btn-purple {
    padding: 10px;
    font-size: 0.95rem;
    margin-top: 1rem;
    height: auto;
    min-height: 44px; /* Asegura área de toque adecuada */
  }
  
  .text-center.mt-4 {
    margin-top: 1rem !important;
    font-size: 0.9rem;
  }
  
  /* Mejor manejo del foco en inputs para móviles */
  input:focus {
    box-shadow: 0 0 5px #7f6fcbaa;
  }
  
  /* Ajustar círculos decorativos para mejor visualización en móviles */
  .circle {
    opacity: 0.1; /* Reducir opacidad en móviles */
  }
  
  /* Mejorar espaciado de errores */
  .error-text {
    margin-top: 0.1rem;
    font-size: 0.8rem;
  }
}
</style>
@endpush

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'UniScan')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    /* Gradiente fondo morado pastel */
   /* Estilo general */
body {
  background: white;
  font-family: 'Poppins', sans-serif;
  color: #3c2a74;
  margin: 0;
}

@keyframes bgMove {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.welcome-container {
  background: linear-gradient(-45deg, #fce1ff, #e6d4ff, #f7f0ff, #ffffff);
  background-size: 400% 400%;
  animation: bgMove 15s ease infinite;
  
}

/* Botones escritorio */
.top-buttons-desktop {
  position: absolute;
  top: 20px;
  right: 40px;
  z-index: 10;
  display: flex;
  gap: 1rem;
}
.top-buttons-desktop a {
  padding: 10px 20px;
  background-color: #e5dfff;
  border-radius: 25px;
  color: #4a3c85;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}
.top-buttons-desktop a:hover {
  background-color: #bfa9f3;
  color: white;
}

/* Contenedor principal */
.welcome-content {
  display: flex;
  height: 100%;
}

/* Izquierda */
.welcome-left {
  flex: 1;
  padding: 5vh 4vw;
  display: flex;
  flex-direction: column;
  justify-content: center;
  text-align: center;
}
.welcome-left h1 {
  font-size: 2.5rem;
  font-weight: 600;
}
.welcome-left h2 {
  font-size: 3rem;
  font-weight: 800;
}
.text-uni {
  color: #00c7ae;
}
.text-scan {
  color: #5e4bb7;
}
.logo-img {
  width: 140px;
  margin: 1.5rem auto;
}
.welcome-left p {
  font-size: 1.1rem;
  color: #3c2a74;
  line-height: 1.6;
}

/* Derecha */
.welcome-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #a74fe7, #ff69d4);
}
.onda-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Botón móvil */
.mobile-button {
  display: none;
  margin-top: 2rem;
}
.btn-purple {
  background-color: #7f6fcb;
  color: white;
  border: none;
  padding: 12px 28px;
  border-radius: 30px;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.3s ease;
}
.btn-purple:hover {
  background-color: #5e4bb7;
}

/* Responsive */
@media (max-width: 768px) {
  .welcome-content {
    flex-direction: column;
  }
  .welcome-left {
    padding: 3vh 2rem;
  }
  .welcome-right {
    height: 40%;
  }
  .top-buttons-desktop {
    display: none;
  }
  .mobile-button {
    display: block;
  }
}
.circle {
  position: absolute;
  border-radius: 50%;
  opacity: 0.15;
  z-index: 0;
}
.circle1 {
  width: 160px;
  height: 160px;
  background: linear-gradient(135deg, #d3c9f7, #b6a8f7);
  top: 10vh;
  left: 8vw;
}
.circle2 {
  width: 100px;
  height: 100px;
  background: linear-gradient(135deg, #b7a5f1, #a292e8);
  bottom: 12vh;
  left: 10vw;
}
.circle3 {
  width: 140px;
  height: 140px;
  background: linear-gradient(135deg, #a292e8, #9882e2);
  top: 30vh;
  right: 12vw;
}
.circle4 {
  width: 90px;
  height: 90px;
  background: linear-gradient(135deg, #9882e2, #7f6fcb);
  bottom: 10vh;
  right: 18vw;
}
  </style>
  

  @stack('styles')
</head>
<body>

  @yield('content')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
