<?php

return [
    'daemon_connection_failed' => 'Hubo una excepción al intentar comunicarse con el demonio, resultando en un código de respuesta HTTP/:code. Esta excepción ha sido registrada.',
    'node' => [
        'servers_attached' => 'Un nodo no debe tener servidores vinculados para poder eliminarlo.',
        'daemon_off_config_updated' => 'La configuración del demonio se ha actualizado, sin embargo hubo un error al intentar actualizar automáticamente el archivo de configuración en el demonio. Deberás actualizar manualmente el archivo de configuración (config.yml) para que el demonio aplique estos cambios.',
    ],
    'allocations' => [
        'server_using' => 'Actualmente hay un servidor asignado a esta asignación. Una asignación solo puede eliminarse si no tiene ningún servidor asignado.',
        'too_many_ports' => 'No se admite añadir más de 1000 puertos en un solo rango a la vez.',
        'invalid_mapping' => 'El mapeo proporcionado para :port no era válido y no pudo procesarse.',
        'cidr_out_of_range' => 'La notación CIDR solo permite máscaras entre /25 y /32.',
        'port_out_of_range' => 'Los puertos de una asignación deben ser mayores de 1024 y menores o iguales a 65535.',
    ],
    'nest' => [
        'delete_has_servers' => 'No se puede eliminar del panel un Nest que tenga servidores activos vinculados.',
        'egg' => [
            'delete_has_servers' => 'No se puede eliminar del panel un Egg que tenga servidores activos vinculados.',
            'invalid_copy_id' => 'El Egg seleccionado para copiar un script no existe, o está copiando un script él mismo.',
            'must_be_child' => 'La directiva «Copiar ajustes de» para este Egg debe ser una opción hija del Nest seleccionado.',
            'has_children' => 'Este Egg es padre de uno o más Eggs. Elimina esos Eggs antes de eliminar este Egg.',
        ],
        'variables' => [
            'env_not_unique' => 'La variable de entorno :name debe ser única para este Egg.',
            'reserved_name' => 'La variable de entorno :name está protegida y no puede asignarse a una variable.',
            'bad_validation_rule' => 'La regla de validación «:rule» no es una regla válida para esta aplicación.',
        ],
        'importer' => [
            'json_error' => 'Hubo un error al intentar analizar el archivo JSON: :error.',
            'file_error' => 'El archivo JSON proporcionado no era válido.',
            'invalid_json_provided' => 'El archivo JSON proporcionado no tiene un formato reconocible.',
        ],
    ],
    'subusers' => [
        'editing_self' => 'No está permitido editar tu propia cuenta de subusuario.',
        'user_is_owner' => 'No puedes añadir al propietario del servidor como subusuario de este servidor.',
        'subuser_exists' => 'Ya hay un usuario con esa dirección de correo asignado como subusuario de este servidor.',
    ],
    'databases' => [
        'delete_has_databases' => 'No se puede eliminar un servidor de bases de datos que tenga bases de datos activas vinculadas.',
    ],
    'tasks' => [
        'chain_interval_too_long' => 'El intervalo máximo para una tarea encadenada es de 15 minutos.',
    ],
    'locations' => [
        'has_nodes' => 'No se puede eliminar una ubicación que tenga nodos activos vinculados.',
    ],
    'users' => [
        'node_revocation_failed' => 'No se pudieron revocar las claves en el <a href=":link">nodo #:node</a>. :error',
    ],
    'deployment' => [
        'no_viable_nodes' => 'No se encontró ningún nodo que cumpla los requisitos especificados para el despliegue automático.',
        'no_viable_allocations' => 'No se encontraron asignaciones que cumplan los requisitos para el despliegue automático.',
    ],
    'api' => [
        'resource_not_found' => 'El recurso solicitado no existe en este servidor.',
    ],
];
