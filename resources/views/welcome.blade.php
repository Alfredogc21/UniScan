<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>UniScan - Control de Asistencia Inteligente</title>
    <link rel="icon" href="{{ asset('img/uniscan_logo.png') }}" type="image/png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary-color: #7b1fa2;
            --primary-light: #9c27b0;
            --secondary-color: #ff9800;
            --dark-color: #333;
            --light-color: #f8f9fa;
            --text-color: #444;
            --gradient-purple: linear-gradient(135deg, #7b1fa2, #9c27b0);
            --gradient-orange: linear-gradient(135deg, #ff9800, #ff5722);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom, #e7d6fa, #f6ecff);
            color: var(--text-color);
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        .content-panel {
            flex: 1;
            background-color: white;
            padding: 4rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .image-panel {
            flex: 1;
            background: url("{{ asset('img/img_onda.JPEG') }}") no-repeat center center;
            background-size: cover;
            position: relative;
        }

        .decorative-element {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.15;
            z-index: 0;
            animation: float 12s ease-in-out infinite alternate;
        }

        .purple-orb {
            width: 300px;
            height: 300px;
            top: -50px;
            left: -50px;
            background: radial-gradient(circle, var(--primary-light), transparent 70%);
        }

        .blue-orb {
            width: 400px;
            height: 400px;
            bottom: -80px;
            right: -80px;
            background: radial-gradient(circle, #4fc3f7, transparent 70%);
            animation-delay: 2s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(10px, -15px) rotate(3deg); }
            50% { transform: translate(-5px, 10px) rotate(-3deg); }
            75% { transform: translate(5px, 5px) rotate(2deg); }
        }

        .logo-container {
            margin-bottom: 2rem;
            text-align: center;
            z-index: 2;
        }

        .logo-container img {
            width: 220px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .logo-container img:hover {
            transform: scale(1.05) rotate(-5deg);
        }

        .title-container {
            text-align: center;
            margin-bottom: 2.5rem;
            z-index: 2;
        }

        .title-container h1 {
            font-size: 3rem;
            font-weight: 700;
            background: var(--gradient-purple);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .title-container h2 {
            font-size: 1.8rem;
            font-weight: 500;
            color: var(--primary-color);
        }

        .description {
            max-width: 600px;
            text-align: center;
            margin-bottom: 3rem;
            font-size: 1.2rem;
            z-index: 2;
        }

        .btn-group {
            display: flex;
            gap: 20px;
            margin-bottom: 2rem;
            z-index: 2;
        }

        .btn {
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 30px;
            border: none;
            text-transform: uppercase;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: var(--gradient-purple);
            color: white;
        }

        .btn-secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-primary:hover {
            box-shadow: 0 10px 25px rgba(123, 31, 162, 0.3);
        }

        .btn-secondary:hover {
            background: var(--primary-color);
            color: white;
        }

        .features-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            max-width: 800px;
            margin-top: 2rem;
            z-index: 2;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            width: 250px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .feature-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .image-buttons {
            position: absolute;
            top: 30px;
            right: 30px;
            display: flex;
            gap: 15px;
            z-index: 3;
        }

        .mobile-bottom-btn {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
            width: 90%;
            max-width: 350px;
            z-index: 10;
            scroll-behavior: unset !important; /* evitar desplazamiento */
        }

        .mobile-top-pico {
            display: none;
        }

        .top-wave-desktop,
        .bottom-wave-desktop {
            display: block;
            position: absolute;
            width: 100%;
            height: 120px;
            left: 0;
            z-index: 0;
        }

        .top-wave-desktop {
            top: 0;
        }

        .bottom-wave-desktop {
            bottom: 0;
            transform: rotate(180deg);
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }

            .image-panel,
            .features-container,
            .image-buttons {
                display: none !important;
            }

            .mobile-top-pico {
                display: block;
                width: 100%;
                height: 120px;
                overflow: hidden;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 0;
            }

            .mobile-top-pico svg {
                width: 100%;
                height: 100%;
                display: block;
            }

            .content-panel {
                padding: 3rem 1.5rem;
                background-color: #fefeff;
                z-index: 1;
            }

            .btn-group {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
            }

            .features-container {
                flex-direction: column;
                align-items: center;
            }

            .feature-card {
                width: 100%;
                max-width: 350px;
            }

            .top-wave-desktop,
            .bottom-wave-desktop {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Panel de contenido -->
        <div class="content-panel">
            <!-- Pico superior para móvil -->
            <div class="mobile-top-pico">
                <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                    <path d="M0,0 C150,80 350,0 500,90 L500,0 L0,0 Z" style="stroke: none; fill: #d1aef2;"></path>
                </svg>
            </div>

            <!-- Picos decorativos para escritorio -->
            <div class="top-wave-desktop">
                <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                    <path d="M0,0 C150,80 350,0 500,90 L500,0 L0,0 Z" style="stroke: none; fill: #e4c8f8;"></path>
                </svg>
            </div>

            <div class="decorative-element purple-orb"></div>
            <div class="decorative-element blue-orb"></div>

            <!-- Logo -->
            <div class="logo-container animate__animated animate__fadeIn">
                <img src="{{ asset('img/uniscan_logo.png') }}" alt="UniScan Logo" class="img-fluid">
            </div>

            <!-- Títulos -->
            <div class="title-container animate__animated animate__fadeIn animate__delay-1s">
                <h1>UniScan</h1>
                <h2>Control de Asistencia Inteligente</h2>
            </div>

            <!-- Descripción -->
            <div class="description animate__animated animate__fadeIn animate__delay-2s">
                <p>Esta es una app para el control de asistencia mediante códigos QR</p>
            </div>

            <!-- Características -->
            <div class="features-container animate__animated animate__fadeIn animate__delay-4s">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-qrcode"></i></div>
                    <h3 class="feature-title">Registro Rápido</h3>
                    <p>Escanea códigos QR para registrar asistencia en segundos</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h3 class="feature-title">Reportes</h3>
                    <p>Genera reportes detallados de asistencia</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                    <h3 class="feature-title">Accesible</h3>
                    <p>Funciona en cualquier dispositivo móvil</p>
                </div>
            </div>

            <!-- Botón móvil -->
            <a href="{{ route('register') }}" class="btn btn-primary mobile-bottom-btn d-md-none">
                Comenzar <i class="fas fa-arrow-right ms-2"></i>
            </a>

            <!-- Pico inferior para escritorio -->
            <div class="bottom-wave-desktop">
                <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                    <path d="M0,0 C150,80 350,0 500,90 L500,0 L0,0 Z" style="stroke: none; fill: #e4c8f8;"></path>
                </svg>
            </div>
        </div>

        <!-- Panel de imagen (oculto en móviles) -->
        <div class="image-panel">
            <div class="image-buttons">
                <a href="{{ route('login') }}" class="btn btn-primary">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="btn btn-secondary">Registrarse</a>
            </div>
        </div>
    </div>
</body>
</html>
