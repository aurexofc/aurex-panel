<?php

/**
 * Contiene todas las cadenas de traducción para los diferentes eventos
 * del registro de actividad. Deben tener como clave el valor delante de
 * los dos puntos (:) en el nombre del evento. Si no hay dos puntos,
 * deben estar en el nivel superior.
 */
return [
    'auth' => [
        'fail' => 'Inicio de sesión fallido',
        'success' => 'Sesión iniciada',
        'password-reset' => 'Contraseña restablecida',
        'reset-password' => 'Restablecimiento de contraseña solicitado',
        'register' => 'Cuenta registrada',
        'checkpoint' => 'Autenticación de doble factor solicitada',
        'recovery-token' => 'Token de recuperación de doble factor usado',
        'token' => 'Desafío de doble factor resuelto',
        'ip-blocked' => 'Solicitud bloqueada desde una dirección IP no listada para :identifier',
        'sftp' => [
            'fail' => 'Inicio de sesión SFTP fallido',
        ],
    ],
    'user' => [
        'user' => [
            'create' => 'Creó un nuevo usuario :email',
        ],
        'account' => [
            'email-changed' => 'Cambió el correo de :old a :new',
            'password-changed' => 'Cambió la contraseña',
            'language-changed' => 'Cambió el idioma a :language',
        ],
        'api-key' => [
            'create' => 'Creó una nueva clave API :identifier',
            'delete' => 'Eliminó la clave API :identifier',
        ],
        'ssh-key' => [
            'create' => 'Añadió la clave SSH :fingerprint a la cuenta',
            'delete' => 'Eliminó la clave SSH :fingerprint de la cuenta',
        ],
        'two-factor' => [
            'create' => 'Activó la autenticación de doble factor',
            'delete' => 'Desactivó la autenticación de doble factor',
        ],
    ],
    'server' => [
        'reinstall' => 'Reinstaló el servidor',
        'console' => [
            'command' => 'Ejecutó «:command» en el servidor',
        ],
        'power' => [
            'start' => 'Inició el servidor',
            'stop' => 'Detuvo el servidor',
            'restart' => 'Reinició el servidor',
            'kill' => 'Terminó el proceso del servidor',
        ],
        'backup' => [
            'download' => 'Descargó la copia de seguridad :name',
            'delete' => 'Eliminó la copia de seguridad :name',
            'restore' => 'Restauró la copia de seguridad :name (archivos eliminados: :truncate)',
            'restore-complete' => 'Completó la restauración de la copia de seguridad :name',
            'restore-failed' => 'No se pudo completar la restauración de la copia de seguridad :name',
            'start' => 'Inició una nueva copia de seguridad :name',
            'complete' => 'Marcó la copia de seguridad :name como completada',
            'fail' => 'Marcó la copia de seguridad :name como fallida',
            'lock' => 'Bloqueó la copia de seguridad :name',
            'unlock' => 'Desbloqueó la copia de seguridad :name',
        ],
        'database' => [
            'create' => 'Creó una nueva base de datos :name',
            'rotate-password' => 'Contraseña rotada para la base de datos :name',
            'delete' => 'Eliminó la base de datos :name',
        ],
        'file' => [
            'compress_one' => 'Comprimió :directory:files.0',
            'compress_other' => 'Comprimió :count archivos en :directory',
            'read' => 'Vio el contenido de :file',
            'copy' => 'Creó una copia de :file',
            'create-directory' => 'Creó el directorio :directory:name',
            'decompress' => 'Descomprimió :files en :directory',
            'delete_one' => 'Eliminó :directory:files.0',
            'delete_other' => 'Eliminó :count archivos en :directory',
            'download' => 'Descargó :file',
            'pull' => 'Descargó un archivo remoto de :url a :directory',
            'rename_one' => 'Renombró :directory:files.0.from a :directory:files.0.to',
            'rename_other' => 'Renombró :count archivos en :directory',
            'write' => 'Escribió nuevo contenido en :file',
            'upload' => 'Inició una subida de archivos',
            'uploaded' => 'Subió :directory:file',
        ],
        'sftp' => [
            'denied' => 'Acceso SFTP bloqueado por permisos',
            'create_one' => 'Creó :files.0',
            'create_other' => 'Creó :count archivos nuevos',
            'write_one' => 'Modificó el contenido de :files.0',
            'write_other' => 'Modificó el contenido de :count archivos',
            'delete_one' => 'Eliminó :files.0',
            'delete_other' => 'Eliminó :count archivos',
            'create-directory_one' => 'Creó el directorio :files.0',
            'create-directory_other' => 'Creó :count directorios',
            'rename_one' => 'Renombró :files.0.from a :files.0.to',
            'rename_other' => 'Renombró o movió :count archivos',
        ],
        'allocation' => [
            'create' => 'Añadió :allocation al servidor',
            'notes' => 'Actualizó las notas de :allocation de «:old» a «:new»',
            'primary' => 'Estableció :allocation como asignación principal del servidor',
            'delete' => 'Eliminó la asignación :allocation',
        ],
        'schedule' => [
            'create' => 'Creó el horario :name',
            'update' => 'Actualizó el horario :name',
            'execute' => 'Ejecutó manualmente el horario :name',
            'delete' => 'Eliminó el horario :name',
        ],
        'task' => [
            'create' => 'Creó una nueva tarea «:action» para el horario :name',
            'update' => 'Actualizó la tarea «:action» para el horario :name',
            'delete' => 'Eliminó una tarea del horario :name',
        ],
        'settings' => [
            'rename' => 'Renombró el servidor de :old a :new',
            'description' => 'Cambió la descripción del servidor de :old a :new',
        ],
        'startup' => [
            'edit' => 'Cambió la variable :variable de «:old» a «:new»',
            'image' => 'Actualizó la imagen Docker del servidor de :old a :new',
        ],
        'subuser' => [
            'create' => 'Añadió a :email como subusuario',
            'update' => 'Actualizó los permisos de subusuario para :email',
            'delete' => 'Eliminó a :email como subusuario',
        ],
    ],
];
