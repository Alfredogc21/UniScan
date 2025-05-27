<?php

// Este es un script simple para probar manualmente la funcionalidad del middleware CheckUserActive

echo "Script para probar la funcionalidad del middleware CheckUserActive\n\n";
echo "Para ejecutar este script, usa el siguiente comando desde la terminal:\n";
echo "php test-user-active.php\n\n";
echo "Instrucciones para verificar la funcionalidad:\n\n";
echo "1. Asegúrese de que tiene usuarios en la base de datos con diferentes valores de estado_id\n";
echo "2. Intente iniciar sesión con un usuario que tenga estado_id = 0\n";
echo "   - Debería ver un mensaje de error: 'Tu cuenta ha sido desactivada. Contacta al administrador.'\n";
echo "3. Intente iniciar sesión con un usuario que tenga estado_id = 1\n";
echo "   - Debería poder acceder normalmente\n";
echo "4. Si inicia sesión con un usuario activo y luego un administrador cambia su estado_id a 0\n";
echo "   - Al navegar a cualquier página protegida, debería ser redirigido a la página de inicio de sesión\n";
echo "   - Debería ver el mensaje: 'Tu cuenta ha sido desactivada. Contacta al administrador.'\n\n";
echo "Comprobaciones adicionales para administradores:\n";
echo "1. Verifique que puede cambiar el estado de un usuario desde el panel de administración\n";
echo "2. Compruebe que los badges de estado (activo/inactivo) se muestran correctamente en la lista de usuarios\n";
echo "3. Verifique que los cambios de estado tienen efecto inmediato en la capacidad de inicio de sesión del usuario\n";
