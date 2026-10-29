<?php

return [
    // Exigir correo verificado para votar (evita votos con cuentas falsas)
    'require_verified_to_vote' => env('CATEDRA_REQUIRE_VERIFIED_TO_VOTE', true),

    // Exigir correo verificado para comentar y para registrar intenciones de apoyo
    'require_verified_to_comment' => env('CATEDRA_REQUIRE_VERIFIED_TO_COMMENT', true),
    'require_verified_to_pledge' => env('CATEDRA_REQUIRE_VERIFIED_TO_PLEDGE', true),

    // Peticiones por minuto permitidas a una misma IP en todo el sitio
    'rate_limit_per_minute' => env('CATEDRA_RATE_LIMIT_PER_MINUTE', 180),

    // Comentarios nuevos quedan pendientes de aprobación del admin
    'moderate_comments' => env('CATEDRA_MODERATE_COMMENTS', false),

    // Único administrador que puede borrar cuentas de usuario
    'superadmin' => env('CATEDRA_SUPERADMIN', 'webmaster@uninavarra.edu.co'),

    'contact' => [
        'phone' => '+57 318 071 5286',
        'whatsapp' => '573180715286',
        'email' => 'comunicacionesymarketing@uninavarra.edu.co',
        'address' => 'Calle 10 No. 6-41, Neiva, Huila',
        'site' => 'https://uninavarra.edu.co',
    ],
];
