<?php

return [
    'notices' => [
        'created' => 'Un nuevo nest, :name, se ha creado correctamente.',
        'deleted' => 'El nest solicitado se ha eliminado correctamente del panel.',
        'updated' => 'Las opciones de configuración del nest se han actualizado correctamente.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'Este Egg y sus variables asociadas se han importado correctamente.',
            'updated_via_import' => 'Este Egg se ha actualizado usando el archivo proporcionado.',
            'deleted' => 'El egg solicitado se ha eliminado correctamente del panel.',
            'updated' => 'La configuración del Egg se ha actualizado correctamente.',
            'script_updated' => 'El script de instalación del Egg se ha actualizado y se ejecutará cuando se instalen servidores.',
            'egg_created' => 'Un nuevo egg se ha creado correctamente. Deberás reiniciar los demonios en ejecución para aplicar este nuevo egg.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'La variable «:variable» se ha eliminado y ya no estará disponible para los servidores una vez reconstruidos.',
            'variable_updated' => 'La variable «:variable» se ha actualizado. Deberás reconstruir los servidores que usen esta variable para aplicar los cambios.',
            'variable_created' => 'La nueva variable se ha creado correctamente y se ha asignado a este egg.',
        ],
    ],
];
