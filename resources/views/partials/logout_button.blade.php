{{-- Botón de cierre de sesión --}}
<form method="POST" action="{{ route('logout') }}" class="d-inline">
    @csrf
    <button type="submit" class="actions__button" title="Cerrar Sesión">
        <i class="fas fa-sign-out-alt"></i>
    </button>
</form>
