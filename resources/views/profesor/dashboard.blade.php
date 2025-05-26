<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Profesor</title>
    <link rel="icon" href="{{ asset('img/uniscan_logo.png') }}" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <style>
        :root {
            --primary-color: #7b68ee;
            --primary-dark: #5a46b7;
            --primary-light: #c2b9ff;
            --primary-extra-light: #f0eeff;
            --text-dark: #2b1d5f;
            --text-light: #ffffff;
            --success-color: #28a745;
            --error-color: #dc3545;
            --warning-color: #ffc107;
            --shadow: 0 4px 20px rgba(123, 104, 238, 0.15);
            --border-radius: 16px;
            --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f3ff 0%, #e5e1ff 100%);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            transition: var(--transition);
            font-size: 18px;
        }

        /* Header Styles */
        header {
            background: var(--primary-color);
            color: var(--text-light);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 100;
            animation: fadeInDown 0.8s ease;
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        header p {
            font-style: italic;
            opacity: 0.9;
            font-size: 1.2rem;
            margin-top: 0.5rem;
        }

        /* Main Layout */
        .container-wrapper {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Navigation Styles */
        nav {
            background: var(--primary-color);
            width: 320px;
            min-width: 320px;
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            z-index: 90;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            animation: slideInLeft 0.8s ease;
        }

        nav ul {
            list-style: none;
            padding: 2rem 0;
            margin: 0;
            flex: 1;
            overflow-y: auto;
        }

        nav li {
            margin: 0.5rem 0;
        }

        nav a {
            display: flex;
            align-items: center;
            padding: 1.2rem 2rem;
            text-decoration: none;
            color: var(--text-light);
            font-weight: 500;
            font-size: 1.2rem;
            transition: var(--transition);
            border-left: 4px solid transparent;
        }

        nav a i {
            margin-right: 1rem;
            font-size: 1.4rem;
            width: 30px;
            text-align: center;
        }

        nav a:hover, nav a.active {
            background: rgba(255,255,255,0.1);
            border-left-color: var(--text-light);
            transform: translateX(5px);
        }

        /* Main Content Styles */
        main {
            flex: 1;
            padding: 3rem;
            overflow-y: auto;
            background: var(--primary-extra-light);
            transition: var(--transition);
            animation: fadeIn 1s ease;
        }

        section {
            max-width: 900px;
            margin: 0 auto;
            animation: fadeInUp 0.8s ease;
        }

        h2 {
            color: var(--primary-dark);
            font-size: 2.2rem;
            margin-bottom: 2rem;
            padding-bottom: 0.8rem;
            border-bottom: 3px solid var(--primary-light);
        }

        /* Form Styles */
        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2.5rem;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(123, 104, 238, 0.2);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 1.1rem;
        }

        input, select, textarea {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid var(--primary-light);
            border-radius: 10px;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(123, 104, 238, 0.2);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        button, .btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.2rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            box-shadow: 0 6px 15px rgba(123, 104, 238, 0.3);
        }

        button:hover, .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(123, 104, 238, 0.4);
        }

        button i, .btn i {
            margin-right: 0.8rem;
            font-size: 1.3rem;
        }

        /* QR Code Styles */
        .qr-container {
            margin-top: 2.5rem;
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            display: inline-block;
            transition: var(--transition);
        }

        .qr-container:hover {
            transform: scale(1.03);
        }

        /* Message Styles */
        .message {
            padding: 1rem 1.2rem;
            border-radius: 10px;
            margin-top: 1.5rem;
            font-weight: 500;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }

        .message i {
            margin-right: 0.8rem;
            font-size: 1.3rem;
        }

        .message.success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .message.error {
            background: rgba(220, 53, 69, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }

        /* Profile Picture Styles */
        .profile-pic-container {
            position: relative;
            width: 160px;
            height: 160px;
            margin: 1.5rem 0;
        }

        .profile-pic {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid var(--primary-light);
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(90, 70, 183, 0.3);
        }

        .profile-pic:hover {
            transform: scale(1.05);
        }

        .upload-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--primary-dark);
            color: white;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
            transition: var(--transition);
            font-size: 1.2rem;
        }

        .upload-btn:hover {
            background: var(--primary-color);
            transform: scale(1.15);
        }

        input[type="file"] {
            display: none;
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background: #121212;
            color: #e0e0e0;
        }

        body.dark-mode main {
            background: #1e1e1e;
        }

        body.dark-mode .card {
            background: #2d2d2d;
            color: #f0f0f0;
        }

        body.dark-mode input,
        body.dark-mode textarea,
        body.dark-mode select {
            background: #3a3a3a;
            border-color: #555;
            color: #f0f0f0;
        }

        body.dark-mode label {
            color: #c2b9ff;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive Styles */
        @media (max-width: 1200px) {
            body {
                font-size: 16px;
            }
            
            nav {
                width: 280px;
            }
            
            main {
                padding: 2rem;
            }
        }

        @media (max-width: 992px) {
            nav {
                position: fixed;
                top: 0;
                left: -320px;
                bottom: 0;
                z-index: 1000;
                box-shadow: 2px 0 15px rgba(0,0,0,0.2);
            }

            nav.active {
                left: 0;
            }

            main {
                margin-left: 0;
                padding: 2rem;
            }

            .menu-toggle {
                display: block;
                position: fixed;
                top: 1.5rem;
                left: 1.5rem;
                background: var(--primary-dark);
                color: white;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1100;
                cursor: pointer;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                font-size: 1.5rem;
                transition: var(--transition);
            }
            
            .menu-toggle:hover {
                transform: scale(1.1);
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 1.5rem;
            }

            header h1 {
                font-size: 2rem;
            }

            header p {
                font-size: 1rem;
            }

            .card {
                padding: 2rem;
            }
            
            h2 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 576px) {
            main {
                padding: 1.5rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            h2 {
                font-size: 1.6rem;
            }
            
            button, .btn {
                padding: 0.8rem 1.8rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </div>
    <h1>Bienvenido profesor {{ $user->nombre ?? 'Profesor' }}</h1>
    <p><em>Sembrar mentes es sembrar el futuro</em></p>
</header>

<div class="container-wrapper">
    <nav id="sideNav">
        <ul>
            <li><a href="#" class="active" data-section="inicio"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="#" data-section="materias"><i class="fas fa-book"></i> Materias</a></li>
            <li><a href="#" data-section="perfil"><i class="fas fa-user"></i> Perfil</a></li>
            <li><a href="#" data-section="reportes"><i class="fas fa-file-alt"></i> Reportes</a></li>
            <li><a href="#" data-section="configuracion"><i class="fas fa-cog"></i> Configuración</a></li>
        </ul>
    </nav>

    <main>
        <!-- Sección Inicio -->
        <section id="inicio">
            <h2>¡Bienvenido a tu espacio docente!</h2>
            <div class="card">
                <p>Desde aquí puedes llevar un control eficiente de la asistencia de tus estudiantes:</p>
                <ul style="margin-top: 1.5rem; padding-left: 2rem; font-size: 1.1rem;">
                    <li style="margin-bottom: 0.8rem;">Crear y administrar tus materias</li>
                    <li style="margin-bottom: 0.8rem;">Registrar y consultar asistencias mediante códigos QR</li>
                    <li style="margin-bottom: 0.8rem;">Generar reportes de asistencia por materia</li>
                </ul>
            </div>
        </section>

        <!-- Sección Materias -->
        <section id="materias" style="display:none;">
            <h2>Agregar Nueva Materia</h2>
            <form id="formMateria" class="card">
                @csrf
                <div class="form-group">
                    <label for="nombreMateria">Nombre de la materia</label>
                    <input type="text" id="nombreMateria" name="nombreMateria" required placeholder="Ej: Matemáticas Avanzadas">
                </div>
                
                <div class="form-group">
                    <label for="aula">Aula</label>
                    <input type="text" id="aula" name="aula" required placeholder="Ej: Aula 203">
                </div>
                
                <div class="form-group">
                    <label for="horaIngreso">Hora de inicio</label>
                    <input type="time" id="horaIngreso" name="horaIngreso" required>
                </div>
                
                <div class="form-group">
                    <label for="horaSalida">Hora de fin</label>
                    <input type="time" id="horaSalida" name="horaSalida" required>
                </div>
                
                <div class="form-group">
                    <label for="curso">Curso</label>
                    <input type="text" id="curso" name="curso" required placeholder="Ej: 3er Año - Grupo A">
                </div>
                
                <button type="submit">
                    <i class="fas fa-save"></i> Guardar materia
                </button>
                
                <div id="message" class="message" style="display:none;"></div>
            </form>
            
            <div id="qrSection" style="display:none;">
                <h3>Código QR para asistencia</h3>
                <div class="card">
                    <div id="qrCodeContainer" class="qr-container"></div>
                    <button id="downloadQR" class="btn" style="margin-top: 1.5rem;">
                        <i class="fas fa-download"></i> Descargar QR
                    </button>
                </div>
            </div>
        </section>

        <!-- Sección Perfil -->
        <section id="perfil" style="display:none;">
            <h2>Mi Perfil</h2>
            <form id="formPerfil" class="card">
                @csrf
                <div class="profile-pic-container">
                    <img id="profilePicture" src="https://ui-avatars.com/api/?name={{ urlencode($user->nombre ?? 'Profesor') }}&background=7c6bd6&color=fff" 
                         alt="Foto de perfil" class="profile-pic">
                    <label for="fotoPerfil" class="upload-btn">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="fotoPerfil" name="fotoPerfil" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" value="{{ $user->nombre ?? 'Profesor' }}" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ $user->email ?? 'profesor@ejemplo.com' }}" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Nueva contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Dejar en blanco para no cambiar">
                </div>
                
                <button type="submit">
                    <i class="fas fa-save"></i> Actualizar perfil
                </button>
                
                <div id="profileMessage" class="message" style="display:none;"></div>
            </form>
        </section>

        <!-- Sección Reportes -->
        <section id="reportes" style="display:none;">
            <h2>Generar Reportes</h2>
            <div class="card">
                <p>Selecciona el tipo de reporte que deseas generar:</p>
                
                <div style="display: flex; gap: 1.5rem; margin-top: 2rem; flex-wrap: wrap;">
                    <button id="btnExcel" class="btn">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </button>
                    <button id="btnPDF" class="btn">
                        <i class="fas fa-file-pdf"></i> Generar PDF
                    </button>
                </div>
            </div>
        </section>

        <!-- Sección Configuración -->
        <section id="configuracion" style="display:none;">
            <h2>Configuración</h2>
            <form id="formConfig" class="card">
                <div class="form-group" style="display: flex; align-items: center;">
                    <input type="checkbox" id="darkModeToggle" style="width: 25px; height: 25px; margin-right: 1rem;">
                    <label for="darkModeToggle" style="margin-bottom: 0; font-size: 1.1rem;">Modo oscuro</label>
                </div>
            </form>
        </section>
    </main>
</div>

<script>
    // Menú toggle para móviles
    const menuToggle = document.getElementById('menuToggle');
    const sideNav = document.getElementById('sideNav');
    
    // Función para cerrar el menú (solo móviles)
    const closeMenu = () => {
        if (window.innerWidth < 992) {
            sideNav.classList.remove('active');
            menuToggle.classList.remove('active');
        }
    };
    
    // Toggle del menú
    menuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        sideNav.classList.toggle('active');
        menuToggle.classList.toggle('active');
    });

    // Navegación entre secciones
    document.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Cerrar menú solo en móviles
            closeMenu();
            
            // Actualizar enlace activo
            document.querySelectorAll('nav a').forEach(a => a.classList.remove('active'));
            link.classList.add('active');
            
            // Ocultar todas las secciones
            document.querySelectorAll('main section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Mostrar sección correspondiente
            const sectionId = link.getAttribute('data-section');
            const activeSection = document.getElementById(sectionId);
            if (activeSection) {
                activeSection.style.display = 'block';
                activeSection.style.animation = 'fadeInUp 0.6s ease';
            }
            
            // Desplazamiento suave al inicio
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });

    // Cerrar menú al hacer clic fuera (solo móviles)
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 992 && 
            !sideNav.contains(e.target) && 
            !menuToggle.contains(e.target) &&
            sideNav.classList.contains('active')) {
            closeMenu();
        }
    });

    // Evitar que el clic en el menú se propague
    sideNav.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Mostrar sección inicial al cargar
    document.addEventListener('DOMContentLoaded', () => {
        const activeLink = document.querySelector('nav a.active');
        if (activeLink) {
            const sectionId = activeLink.getAttribute('data-section');
            document.getElementById(sectionId).style.display = 'block';
        }
    });

    // Envío del formulario de materias - Versión mejorada
    document.getElementById('formMateria').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const form = e.target;
        const message = document.getElementById('message');
        message.style.display = 'flex';
        message.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando materia...';
        message.className = 'message';
        
        try {
            const formData = new FormData(form);
            const response = await fetch('/materias', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error al guardar la materia');
            }
            
            const data = await response.json();
            
            // Limpiar contenedor QR antes de generar uno nuevo
            document.getElementById('qrCodeContainer').innerHTML = '';
            
            // Generar QR con los datos de la materia
            new QRCode(document.getElementById('qrCodeContainer'), {
                text: JSON.stringify({
                    token_qr: data.materia.token_qr,
                    materia_id: data.materia.id,
                    nombre: data.materia.nombre,
                    aula: data.materia.aula
                }),
                width: 200,
                height: 200,
                colorDark: "#5a46b7",
                colorLight: "#ffffff",
            });
            
            // Mostrar sección QR con animación
            document.getElementById('qrSection').style.display = 'block';
            document.getElementById('qrSection').style.animation = 'fadeInUp 0.6s ease';
            
            // Mensaje de éxito
            message.innerHTML = '<i class="fas fa-check-circle"></i> Materia guardada con éxito!';
            message.className = 'message success';
            
        } catch (error) {
            message.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error: ${error.message}`;
            message.className = 'message error';
            console.error('Error:', error);
        }
    });

    // Botón para descargar QR
    document.getElementById('downloadQR').addEventListener('click', () => {
        const canvas = document.querySelector('#qrCodeContainer canvas');
        if (canvas) {
            const link = document.createElement('a');
            const nombreMateria = document.getElementById('nombreMateria').value || 'asistencia';
            link.download = `qr-${nombreMateria.toLowerCase().replace(/\s+/g, '-')}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    });

    // Envío del formulario de perfil
    document.getElementById('formPerfil').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const message = document.getElementById('profileMessage');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        message.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando perfil...';
        message.style.display = 'flex';
        message.className = 'message';
        
        try {
            const formData = new FormData(form);
            const response = await fetch('/perfil', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Error al actualizar el perfil');
            }
            
            message.innerHTML = '<i class="fas fa-check-circle"></i> Perfil actualizado con éxito!';
            message.className = 'message success';
            
            // Actualizar nombre en el header si cambió
            const nombreInput = document.getElementById('nombre');
            if (nombreInput && nombreInput.value) {
                document.querySelector('header h1').textContent = `Bienvenido profesor ${nombreInput.value}`;
            }
            
        } catch (error) {
            message.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error: ${error.message}`;
            message.className = 'message error';
            console.error('Error:', error);
        }
    });

    // Actualizar vista previa de foto de perfil
    document.getElementById('fotoPerfil').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                document.getElementById('profilePicture').src = event.target.result;
                
                // Mostrar mensaje de éxito
                const message = document.getElementById('profileMessage');
                message.innerHTML = '<i class="fas fa-check-circle"></i> Foto actualizada (recuerda guardar los cambios)';
                message.style.display = 'flex';
                message.className = 'message success';
                
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => {
                        message.style.display = 'none';
                        message.style.opacity = '1';
                    }, 500);
                }, 3000);
            };
            reader.readAsDataURL(file);
        }
    });

    // Modo oscuro
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    // Cargar preferencia
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        darkModeToggle.checked = true;
    }
    
    // Cambiar modo
    darkModeToggle.addEventListener('change', () => {
        if (darkModeToggle.checked) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'disabled');
        }
    });

    // Placeholder para botones de reportes
    document.getElementById('btnExcel').addEventListener('click', () => {
        alert('Función de exportar a Excel estará disponible pronto');
    });
    
    document.getElementById('btnPDF').addEventListener('click', () => {
        alert('Función de generar PDF estará disponible pronto');
    });
</script>

</body>
</html>