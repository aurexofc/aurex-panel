<?php

return [
    'sign_in' => 'Iniciar sesión',
    'go_to_login' => 'Ir al inicio de sesión',
    'failed' => 'No se encontró ninguna cuenta con esas credenciales.',

    'forgot_password' => [
        'label' => '¿Olvidaste tu contraseña?',
        'label_help' => 'Introduce el correo electrónico de tu cuenta para recibir instrucciones sobre cómo restablecer tu contraseña.',
        'button' => 'Recuperar cuenta',
    ],

    'reset_password' => [
        'button' => 'Restablecer e iniciar sesión',
    ],

    'two_factor' => [
        'label' => 'Código de doble factor',
        'label_help' => 'Esta cuenta requiere una segunda capa de autenticación para continuar. Introduce el código generado por tu dispositivo para completar el inicio de sesión.',
        'checkpoint_failed' => 'El código de autenticación de doble factor no es válido.',
    ],

    'throttle' => 'Demasiados intentos de inicio de sesión. Inténtalo de nuevo en :seconds segundos.',
    'password_requirements' => 'La contraseña debe tener al menos 8 caracteres y debe ser única para este sitio.',
    '2fa_must_be_enabled' => 'El administrador ha requerido que la autenticación de doble factor esté activada en tu cuenta para poder usar el panel.',
];
