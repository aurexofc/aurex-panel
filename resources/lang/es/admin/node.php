<?php

return [
    'validation' => [
        'fqdn_not_resolvable' => 'El FQDN o la dirección IP proporcionada no se resuelve a una dirección IP válida.',
        'fqdn_required_for_ssl' => 'Se requiere un nombre de dominio completo que se resuelva a una IP pública para usar SSL en este nodo.',
    ],
    'notices' => [
        'allocations_added' => 'Las asignaciones se han añadido correctamente a este nodo.',
        'node_deleted' => 'El nodo se ha eliminado correctamente del panel.',
        'location_required' => 'Debes tener al menos una ubicación configurada antes de poder añadir un nodo a este panel.',
        'node_created' => 'Nodo creado correctamente. Puedes configurar automáticamente el demonio en esta máquina visitando la pestaña «Configuración». Antes de poder añadir servidores debes asignar al menos una dirección IP y un puerto.',
        'node_updated' => 'La información del nodo se ha actualizado. Si se cambió algún ajuste del demonio, deberás reiniciarlo para que los cambios surtan efecto.',
        'unallocated_deleted' => 'Se eliminaron todos los puertos sin asignar para <code>:ip</code>.',
    ],
];
