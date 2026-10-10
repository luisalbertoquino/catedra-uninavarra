<?php

return [
    // Exigir correo verificado para votar (evita votos con cuentas falsas)
    'require_verified_to_vote' => env('CATEDRA_REQUIRE_VERIFIED_TO_VOTE', true),

    // Exigir correo verificado para comentar
    'require_verified_to_comment' => env('CATEDRA_REQUIRE_VERIFIED_TO_COMMENT', false),

    // Comentarios nuevos quedan pendientes de aprobación del admin
    'moderate_comments' => env('CATEDRA_MODERATE_COMMENTS', false),

    'contact' => [
        'phone' => '+57 318 071 5286',
        'whatsapp' => '573180715286',
        'email' => 'comunicacionesymarketing@uninavarra.edu.co',
        'address' => 'Calle 10 No. 6-41, Neiva, Huila',
        'site' => 'https://uninavarra.edu.co',
    ],
];
