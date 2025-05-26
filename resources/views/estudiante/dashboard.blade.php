<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>UniScan Estudiante</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary-color: #7b62d9;
            --primary-light: #a488f0;
            --primary-dark: #5a43b0;
            --text-dark: #4a357a;
            --text-light: #5e4a8a;
            --white: #ffffff;
            --gray-light: #f5f5f5;
            --gray-medium: #e0e0e0;
            --gray-dark: #757575;
            --gradient-purple: linear-gradient(135deg, #7b62d9, #a488f0);
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 15px 25px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --sidebar-width: 280px;
            --sidebar-width-collapsed: 80px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f5ff, #e9e1fa);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
        }

        /* Layout general */
        .app-container {
            display: flex;
            flex: 1;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* Menú lateral */
        nav.sidebar {
            background: var(--gradient-purple);
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            padding: 2.5rem 1.5rem;
            color: var(--white);
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            box-shadow: 5px 0 25px rgba(106, 84, 194, 0.3);
            border-radius: 0 25px 25px 0;
            font-weight: 500;
            position: relative;
            z-index: 2;
            transition: var(--transition);
            overflow: hidden;
        }

        nav.sidebar a {
            color: var(--white);
            text-decoration: none;
            padding: 0.8rem 1.2rem;
            border-radius: 12px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        nav.sidebar a:hover, 
        nav.sidebar a.active {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        /* Contenido principal */
        main.content {
            flex: 1;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px 0 0 25px;
            box-shadow: var(--shadow-lg);
            color: var(--text-dark);
            min-height: 100vh;
            max-width: 100%;
            overflow-y: auto;
        }

        /* Tarjetas */
        .card {
            background: var(--white);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Formularios */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--gray-medium);
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(123, 98, 217, 0.2);
        }

        /* Botones */
        .btn {
            display: inline-block;
            padding: 0.8rem 1.8rem;
            border-radius: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 1rem;
            text-align: center;
        }

        .btn-primary {
            background: var(--gradient-purple);
            color: white;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        /* Sección QR */
        .qr-section {
            text-align: center;
            padding: 2rem;
        }

        .qr-placeholder {
            width: 200px;
            height: 200px;
            margin: 0 auto 1.5rem;
            background: var(--gray-light);
            border: 2px dashed var(--gray-medium);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Mensajes de dispositivo */
        .device-message {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            display: none;
        }

        .mobile-message { background: #e3f2fd; color: #0d47a1; }
        .desktop-message { background: #fff8e1; color: #e65100; }

        /* Perfil de usuario */
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1.5rem;
            border: 3px solid var(--primary-light);
        }

        /* Configuración */
        .settings-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-medium);
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--gray-medium);
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background: var(--gradient-purple);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .app-container {
                flex-direction: column;
            }
            
            nav.sidebar {
                width: 100%;
                flex-direction: row;
                padding: 1rem;
                border-radius: 0;
                justify-content: space-around;
                flex-wrap: wrap;
            }
            
            nav.sidebar a {
                padding: 0.8rem;
                flex-direction: column;
                font-size: 0.8rem;
                text-align: center;
                min-width: 70px;
            }
            
            nav.sidebar a i {
                margin-bottom: 0.3rem;
                font-size: 1.2rem;
            }
            
            main.content {
                border-radius: 0;
                padding: 1.5rem;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-avatar {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            nav.sidebar a {
                padding: 0.6rem;
                min-width: 60px;
            }

            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Menú lateral -->
        <nav class="sidebar">
            <a href="#inicio" class="active">
                <i class="fas fa-home"></i>
                <span>Inicio</span>
            </a>
            
            <a href="#ausencia">
                <i class="fas fa-calendar-times"></i>
                <span>Ausencia</span>
            </a>
            
            <a href="#perfil">
                <i class="fas fa-user"></i>
                <span>Perfil</span>
            </a>
            
            <a href="#asistencia">
                <i class="fas fa-calendar-check"></i>
                <span>Asistencia</span>
            </a>
            
            <a href="#configuracion">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a>
        </nav>

        <main class="content">
            <!-- Sección Inicio -->
            <section id="inicio" class="content-section">
                <div class="card">
                    <h2>Bienvenido, <span id="nombreUsuario">Estudiante</span></h2>
                    <p>Gestiona tu asistencia académica de manera sencilla</p>
                </div>

                <div class="card">
                    <h3>Resumen de actividades</h3>
                    <p>Aquí puedes ver tus próximas clases y asistencias recientes</p>
                    <!-- Contenido adicional del inicio -->
                </div>
            </section>

            <!-- Sección Ausencia -->
            <section id="ausencia" class="content-section" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Reportar Ausencia</h2>
                    </div>
                    <div class="card-body">
                        <form id="ausenciaForm">
                            <div class="form-group">
                                <label for="fechaAusencia" class="form-label">Fecha de Ausencia</label>
                                <input type="date" id="fechaAusencia" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcionAusencia" class="form-label">Descripción</label>
                                <textarea id="descripcionAusencia" class="form-control" rows="4" required placeholder="Explica el motivo de tu ausencia"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="evidenciaAusencia" class="form-label">Subir Evidencia (Opcional)</label>
                                <input type="file" id="evidenciaAusencia" class="form-control">
                                <small>Puedes subir un justificante médico u otro documento.</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">Enviar Reporte</button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Sección Perfil -->
            <section id="perfil" class="content-section" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Mi Perfil</h2>
                    </div>
                    <div class="card-body">
                        <div class="profile-header">
                            <img src="https://ui-avatars.com/api/?name=Estudiante&background=7b62d9&color=fff&size=100" alt="Foto de perfil" class="profile-avatar" id="profileAvatar">
                            <div>
                                <h2 id="profileName">Estudiante Ejemplo</h2>
                                <p id="profileEmail">estudiante@ejemplo.com</p>
                            </div>
                        </div>
                        
                        <form id="profileForm">
                            <div class="form-group">
                                <label for="cambiarFoto" class="form-label">Cambiar Foto de Perfil</label>
                                <input type="file" id="cambiarFoto" class="form-control" accept="image/*">
                            </div>
                            
                            <div class="form-group">
                                <label for="nombreCompleto" class="form-label">Nombre Completo</label>
                                <input type="text" id="nombreCompleto" class="form-control" value="Estudiante Ejemplo">
                            </div>
                            
                            <div class="form-group">
                                <label for="correoElectronico" class="form-label">Correo Electrónico</label>
                                <input type="email" id="correoElectronico" class="form-control" value="estudiante@ejemplo.com">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Sección Asistencia -->
            <section id="asistencia" class="content-section" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Registro de Asistencia</h2>
                    </div>
                    <div class="card-body">
                        <p>Escanea el código QR proporcionado por tu profesor para registrar tu asistencia.</p>
                        
                        <div class="qr-section">
                            <div class="qr-placeholder">
                                <i class="fas fa-qrcode" style="font-size: 3rem; color: var(--gray-dark);"></i>
                            </div>
                            
                            <div class="device-message mobile-message">
                                <p>Por favor, permite el acceso a tu cámara para escanear el código QR</p>
                                <button class="btn btn-primary" id="startScanner">Iniciar Escáner</button>
                            </div>
                            
                            <div class="device-message desktop-message">
                                <p>Para registrar tu asistencia, abre esta página en tu dispositivo móvil</p>
                                <p>O utiliza la cámara de tu computadora si está disponible</p>
                            </div>
                            
                            <div id="scannerResult" style="display: none;">
                                <h3>¡Asistencia registrada!</h3>
                                <p id="attendanceDetails"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sección Configuración -->
            <section id="configuracion" class="content-section" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Configuración</h2>
                    </div>
                    <div class="card-body">
                        <div class="settings-option">
                            <div>
                                <h3>Modo Oscuro</h3>
                                <p>Activa el modo oscuro para una mejor experiencia nocturna</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="darkModeToggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="settings-option">
                            <div>
                                <h3>Notificaciones</h3>
                                <p>Recibe notificaciones sobre tus clases y actividades</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="notificationsToggle" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simular datos del usuario (en producción esto vendría del backend)
            const userData = {
                name: localStorage.getItem('userName') || "Estudiante",
                email: localStorage.getItem('userEmail') || "estudiante@ejemplo.com"
            };
            
            // Mostrar nombre de usuario en todas las secciones
            document.getElementById('nombreUsuario').textContent = userData.name;
            document.getElementById('profileName').textContent = userData.name;
            document.getElementById('profileEmail').textContent = userData.email;
            
            // Navegación entre secciones
            document.querySelectorAll('nav a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    
                    // Ocultar todas las secciones
                    document.querySelectorAll('.content-section').forEach(section => {
                        if (section) section.style.display = 'none';
                    });
                    
                    // Mostrar sección seleccionada
                    const targetSection = document.getElementById(targetId);
                    if (targetSection) {
                        targetSection.style.display = 'block';
                    }
                    
                    // Actualizar menú activo
                    document.querySelectorAll('nav a').forEach(navLink => {
                        navLink.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Acciones específicas por sección
                    if (targetId === 'asistencia') {
                        checkDeviceForQR();
                    }
                });
            });
            
            // Verificar dispositivo para el escáner QR
            function checkDeviceForQR() {
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                const mobileMsg = document.querySelector('.mobile-message');
                const desktopMsg = document.querySelector('.desktop-message');
                
                if (mobileMsg && desktopMsg) {
                    mobileMsg.style.display = isMobile ? 'block' : 'none';
                    desktopMsg.style.display = isMobile ? 'none' : 'block';
                }
            }
            
            // Simular escáner QR
            const startScannerBtn = document.getElementById('startScanner');
            if (startScannerBtn) {
                startScannerBtn.addEventListener('click', function() {
                    this.textContent = 'Escaneando...';
                    this.disabled = true;
                    
                    // Simular resultado después de 2 segundos
                    setTimeout(() => {
                        const mobileMsg = document.querySelector('.mobile-message');
                        if (mobileMsg) mobileMsg.style.display = 'none';
                        
                        const resultDiv = document.getElementById('scannerResult');
                        if (resultDiv) {
                            resultDiv.style.display = 'block';
                            
                            const now = new Date();
                            document.getElementById('attendanceDetails').textContent = 
                                `Registrado el ${now.toLocaleDateString()} a las ${now.toLocaleTimeString()}`;
                        }
                    }, 2000);
                });
            }
            
            // Manejar formulario de ausencia
            const ausenciaForm = document.getElementById('ausenciaForm');
            if (ausenciaForm) {
                ausenciaForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Ausencia reportada correctamente');
                    this.reset();
                    // Volver a inicio
                    document.querySelector('nav a[href="#inicio"]').click();
                });
            }
            
            // Manejar formulario de perfil
            const profileForm = document.getElementById('profileForm');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const newName = document.getElementById('nombreCompleto').value;
                    const newEmail = document.getElementById('correoElectronico').value;
                    
                    // Actualizar datos en todas las secciones
                    document.getElementById('nombreUsuario').textContent = newName;
                    document.getElementById('profileName').textContent = newName;
                    document.getElementById('profileEmail').textContent = newEmail;
                    
                    // Guardar en localStorage (simulando backend)
                    localStorage.setItem('userName', newName);
                    localStorage.setItem('userEmail', newEmail);
                    
                    alert('Perfil actualizado correctamente');
                });
            }
            
            // Configurar modo oscuro
            const darkModeToggle = document.getElementById('darkModeToggle');
            if (darkModeToggle) {
                const darkModeState = localStorage.getItem('darkMode') === 'true';
                
                if (darkModeState) {
                    document.body.classList.add('dark-mode');
                    darkModeToggle.checked = true;
                }
                
                darkModeToggle.addEventListener('change', function() {
                    document.body.classList.toggle('dark-mode');
                    localStorage.setItem('darkMode', this.checked);
                });
            }
            
            // Mostrar sección inicial
            document.getElementById('inicio').style.display = 'block';
        });
    </script>
</body>
</html>