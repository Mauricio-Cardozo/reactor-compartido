<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Balance del reactor
    |--------------------------------------------------------------------------
    |
    | Todo lo que se puede ajustar del juego vive acá: nada de costos, tasas
    | ni tiempos hardcodeados en los servicios (RNF-07). Las cantidades que
    | no son enteras se escriben como string para que BCMath no las convierta
    | a float (RNF-08).
    |
    */

    'click' => [
        'power' => 1,
    ],

    'credits' => [
        // Fracción de la energía producida que se convierte en créditos.
        'rate' => '0.1',
    ],

    'limits' => [
        // Máximo de clicks por segundo y por jugador (RNF-03).
        'clicks_per_second' => 15,
        // Máximo de clicks que puede traer un solo lote del cliente.
        'clicks_per_request' => 15,
    ],

];
