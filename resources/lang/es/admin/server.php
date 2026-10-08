<?php

return [
    'exceptions' => [
        'no_new_default_allocation' => 'Estás intentando eliminar la asignación predeterminada de este servidor pero no hay una asignación alternativa que usar.',
        'marked_as_failed' => 'Este servidor fue marcado como fallido en una instalación anterior. El estado actual no puede cambiarse en este estado.',
        'skipping_install_script' => 'Este servidor está configurado para omitir el script de instalación de su egg. La reinstalación no está disponible hasta que se desactive ese ajuste.',
        'bad_variable' => 'Hubo un error de validación con la variable :name.',
        'daemon_exception' => 'Hubo una excepción al intentar comunicarse con el demonio, resultando en un código de respuesta HTTP/:code. Esta excepción ha sido registrada. (id de solicitud: :request_id)',
        'default_allocation_not_found' => 'La asignación predeterminada solicitada no se encontró en las asignaciones de este servidor.',
    ],
    'alerts' => [
        'startup_changed' => 'La configuración de arranque de este servidor se ha actualizado. Si se cambió el nest o el egg de este servidor, se está realizando una reinstalación ahora.',
        'server_deleted' => 'El servidor se ha eliminado correctamente del sistema.',
        'server_created' => 'El servidor se creó correctamente en el panel. Dale al demonio unos minutos para instalar completamente este servidor.',
        'build_updated' => 'Los detalles de construcción de este servidor se han actualizado. Algunos cambios pueden requerir un reinicio para surtir efecto.',
        'suspension_toggled' => 'El estado de suspensión del servidor se ha cambiado a :status.',
        'rebuild_on_boot' => 'Este servidor ha sido marcado como que requiere una reconstrucción del contenedor Docker. Esto ocurrirá la próxima vez que se inicie el servidor.',
        'install_toggled' => 'El estado de instalación de este servidor ha cambiado.',
        'server_reinstalled' => 'Este servidor ha sido puesto en cola para una reinstalación que comienza ahora.',
        'details_updated' => 'Los detalles del servidor se han actualizado correctamente.',
        'docker_image_updated' => 'Se cambió correctamente la imagen Docker predeterminada a usar para este servidor. Se requiere un reinicio para aplicar este cambio.',
        'node_required' => 'Debes tener al menos un nodo configurado antes de poder añadir un servidor a este panel.',
        'transfer_nodes_required' => 'Debes tener al menos dos nodos configurados antes de poder transferir servidores.',
        'transfer_started' => 'La transferencia del servidor ha comenzado.',
        'transfer_not_viable' => 'El nodo seleccionado no tiene el espacio en disco o la memoria requeridos para alojar este servidor.',
    ],
];
