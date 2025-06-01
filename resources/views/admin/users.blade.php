@extends('layouts.admin')

@section('title', 'Gestión de Usuarios - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@endsection

@section('content')
<div class="dashboard">
    <!-- Sidebar -->
    <aside class="dashboard__sidebar">
        <div class="sidebar__header">
            <div class="sidebar__logo">
                <img src="{{ asset('img/uniscan_logo.png') }}" alt="UniScan Logo" class="sidebar__logo-img">
                <span class="sidebar__logo-text">UniScan</span>
            </div>
            <button class="sidebar__toggle" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <nav class="sidebar__nav">
            <ul class="nav__list">
                <li class="nav__item">
                    <a href="{{ route('admin.dashboard') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-home"></i></span>
                        <span class="nav__link-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.users') }}" class="nav__link nav__link--active">
                        <span class="nav__link-icon"><i class="fas fa-users"></i></span>
                        <span class="nav__link-text">Usuarios</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.materias') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-book"></i></span>
                        <span class="nav__link-text">Materias</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.asistencias') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="nav__link-text">Asistencias</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.reports') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-chart-pie"></i></span>
                        <span class="nav__link-text">Reportes</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.settings') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-cog"></i></span>
                        <span class="nav__link-text">Configuración</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar__footer">
            <a href="{{ route('admin.profile') }}" class="user-info" style="color: white;">
                <div class="user-info__avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info__details">
                    <div class="user-info__name" style="color: white;">{{ Auth::user()->name }}</div>
                    <div class="user-info__role" style="color: rgba(255, 255, 255, 0.8);">Administrador</div>
                </div>
            </a>
        </div>
    </aside>

    <!-- Contenido principal -->
    <main class="dashboard__content">
        <header class="content__header">
            <button class="actions__button mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>

            <h1 class="header__title">Gestión de Usuarios</h1>

            <div class="header__search">
                <span class="search__icon"><i class="fas fa-search"></i></span>
                <input type="text" id="userSearchInput" class="search__input" placeholder="Buscar usuario...">
            </div>

            <div class="header__actions">
                @include('partials.logout_button')
            </div>
        </header>

        <div class="content__main">
            <!-- Mensajes de notificación -->
            @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
            @endif

            <!-- Tabla de usuarios -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Usuarios del Sistema</h2>
                    <button id="btnAddUser" class="section__action"><i class="fas fa-plus"></i> Nuevo Usuario</button>
                </div>
                <div class="section__content">
                    <table class="data-table" id="usersTable">
                        <thead class="data-table__head">
                            <tr>
                                <th class="data-table__header">ID</th>
                                <th class="data-table__header">Nombre</th>
                                <th class="data-table__header">Email</th>
                                <th class="data-table__header">Rol</th>
                                <th class="data-table__header">Estado</th>
                                <th class="data-table__header">Fecha Registro</th>
                                <th class="data-table__header">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="data-table__body"> @foreach($users as $user)
                            <tr>
                                <td class="data-table__cell">{{ $user->id }}</td>
                                <td class="data-table__cell">{{ $user->name }}</td>
                                <td class="data-table__cell">{{ $user->email }}</td>
                                <td class="data-table__cell">
                                    @if($user->role_id == 1)
                                    <span class="role-badge role-badge--admin">Administrador</span>
                                    @elseif($user->role_id == 2)
                                    <span class="role-badge role-badge--profesor">Profesor</span>
                                    @else
                                    <span class="role-badge role-badge--estudiante">Estudiante</span>
                                    @endif
                                </td>
                                <td class="data-table__cell">
                                    @if($user->estado_id == 1)
                                    <span class="estado-badge estado-badge--activo">Activo</span>
                                    @else
                                    <span class="estado-badge estado-badge--inactivo">Inactivo</span>
                                    @endif
                                </td>
                                <td class="data-table__cell">{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="data-table__cell">
                                    <div class="actions-cell">
                                        <button class="data-table__action btn-edit-user" data-user-id="{{ $user->id }}" title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="data-table__action btn-delete-user" title="Eliminar usuario">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal para editar usuario -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Usuario</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="editUserForm" class="user-form">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" id="editUserId" name="user_id">

            <div class="form-group">
                <label for="editName">Nombre</label>
                <input type="text" id="editName" name="name" required>
            </div>

            <div class="form-group">
                <label for="editEmail">Email</label>
                <input type="email" id="editEmail" name="email" required>
            </div>
            <div class="form-group">
                <label for="editRole">Rol</label>
                <select id="editRole" name="role_id" required>
                    <option value="1">Administrador</option>
                    <option value="2">Profesor</option>
                    <option value="3">Estudiante</option>
                </select>
            </div>

            <div class="form-group">
                <label for="editEstado">Estado</label>
                <select id="editEstado" name="estado_id" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="editPassword">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                <input type="password" id="editPassword" name="password">
            </div>

            <div class="btn-container">
                <button type="button" class="btn-cancel">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para agregar usuario -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Agregar Nuevo Usuario</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addUserForm" action="{{ route('admin.users.store') }}" method="POST" class="user-form">
            @csrf

            <div class="form-group">
                <label for="addName">Nombre</label>
                <input type="text" id="addName" name="name" required>
            </div>

            <div class="form-group">
                <label for="addEmail">Email</label>
                <input type="email" id="addEmail" name="email" required>
            </div>
            <div class="form-group">
                <label for="addRole">Rol</label>
                <select id="addRole" name="role_id" required>
                    <option value="1">Administrador</option>
                    <option value="2">Profesor</option>
                    <option value="3">Estudiante</option>
                </select>
            </div>

            <div class="form-group">
                <label for="addEstado">Estado</label>
                <select id="addEstado" name="estado_id" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="addPassword">Contraseña</label>
                <input type="password" id="addPassword" name="password" required>
            </div>

            <div class="form-group">
                <label for="addPasswordConfirmation">Confirmar Contraseña</label>
                <input type="password" id="addPasswordConfirmation" name="password_confirmation" required>
            </div>

            <div class="btn-container">
                <button type="button" class="btn-cancel">Cancelar</button>
                <button type="submit" class="btn-save">Agregar Usuario</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/admin/dashboard.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // La gestión del sidebar ahora está en dashboard.js

        // Búsqueda de usuarios
        const searchInput = document.getElementById('userSearchInput');
        const tableRows = document.querySelectorAll('#usersTable tbody tr');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                tableRows.forEach(row => {
                    const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const rol = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                    const estado = row.querySelector('td:nth-child(5)').textContent.toLowerCase();

                    if (name.includes(searchTerm) || email.includes(searchTerm) ||
                        rol.includes(searchTerm) || estado.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Mostrar/Ocultar modales
        const btnAddUser = document.getElementById('btnAddUser');
        const editButtons = document.querySelectorAll('.btn-edit-user');
        const modalCloseButtons = document.querySelectorAll('.modal-close');
        const cancelButtons = document.querySelectorAll('.btn-cancel');
        const addUserModal = document.getElementById('addUserModal');
        const editUserModal = document.getElementById('editUserModal');

        // Abrir modal para agregar usuario
        if (btnAddUser && addUserModal) {
            btnAddUser.addEventListener('click', function() {
                addUserModal.style.display = 'flex';
            });
        } // Abrir modal para editar usuario
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id'); // Hacemos petición AJAX para obtener los datos del usuario
                fetch(`{{ url('/admin/users/') }}/${userId}/edit`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }

                        document.getElementById('editUserId').value = data.id;
                        document.getElementById('editName').value = data.name;
                        document.getElementById('editEmail').value = data.email;
                        document.getElementById('editRole').value = data.role_id;
                        document.getElementById('editEstado').value = data.estado_id;

                        editUserModal.style.display = 'flex';
                    })
                    .catch(error => {
                        console.error('Error al cargar datos del usuario:', error);
                        alert('Error al cargar datos del usuario: ' + error.message);
                    });
            });
        });

        // Cerrar modales
        modalCloseButtons.forEach(button => {
            button.addEventListener('click', function() {
                addUserModal.style.display = 'none';
                editUserModal.style.display = 'none';
            });
        });

        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                addUserModal.style.display = 'none';
                editUserModal.style.display = 'none';
            });
        });

        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(event) {
            if (event.target === addUserModal) {
                addUserModal.style.display = 'none';
            }

            if (event.target === editUserModal) {
                editUserModal.style.display = 'none';
            }
        }); // Manejar envío de formulario para editar usuario
        const editUserForm = document.getElementById('editUserForm');

        if (editUserForm) {
            editUserForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const userId = document.getElementById('editUserId').value;
                const formData = new FormData(this);

                // Agregar el método PUT para la actualización
                formData.append('_method', 'PUT');

                fetch(`{{ url('/admin/users/') }}/${userId}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Mostrar mensaje de éxito y recargar página
                            window.location.reload();
                        } else {
                            // Mostrar mensaje de error
                            console.error('Error al actualizar usuario:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error en la petición:', error);
                    });
            });
        }

        // Confirmación para eliminar usuario
        const deleteForms = document.querySelectorAll('.delete-form');

        deleteForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (confirm('¿Está seguro de que desea eliminar este usuario? Esta acción no se puede deshacer.')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endsection