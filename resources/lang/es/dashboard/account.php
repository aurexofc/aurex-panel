<?php

return [
    'email' => [
        'title' => 'Actualiza tu correo electrónico',
        'updated' => 'Tu dirección de correo electrónico se ha actualizado.',
    ],
    'password' => [
        'title' => 'Cambia tu contraseña',
        'requirements' => 'Tu nueva contraseña debe tener al menos 8 caracteres.',
        'updated' => 'Tu contraseña se ha actualizado.',
    ],
    'two_factor' => [
        'button' => 'Configurar autenticación de doble factor',
        'disabled' => 'La autenticación de doble factor se ha desactivado en tu cuenta. Ya no se te pedirá un token al iniciar sesión.',
        'enabled' => '¡La autenticación de doble factor se ha activado en tu cuenta! A partir de ahora, al iniciar sesión deberás proporcionar el código generado por tu dispositivo.',
        'invalid' => 'El token proporcionado no era válido.',
        'setup' => [
            'title' => 'Configurar autenticación de doble factor',
            'help' => '¿No puedes escanear el código? Introduce el código siguiente en tu aplicación:',
            'field' => 'Introduce el token',
        ],
        'disable' => [
            'title' => 'Desactivar autenticación de doble factor',
            'field' => 'Introduce el token',
        ],
    ],
];
