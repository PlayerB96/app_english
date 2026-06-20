<?php

return [

    'tiers' => [
        [
            'slug' => 'basico',
            'name' => 'Módulo Básico',
            'description' => 'Saludos y frases cortas del día a día. La exigencia sube en cada etapa.',
        ],
        [
            'slug' => 'intermedio',
            'name' => 'Módulo Intermedio',
            'description' => 'Charlas técnicas y vocabulario dev. La exigencia sube en cada etapa.',
        ],
        [
            'slug' => 'avanzado',
            'name' => 'Módulo Avanzado',
            'description' => 'Inglés pro y arquitectura de software. La exigencia sube en cada etapa.',
        ],
    ],

    'tracks' => [
        'speaking' => [
            'slug' => 'practice-speaking',
            'name' => 'Práctica · Speaking',
            'description' => 'Speaking por voz con validación y traducción.',
        ],
        'quiz' => [
            'slug' => 'tracks-quiz',
            'name' => 'Tracks · Vocabulario',
            'description' => 'Vocabulario técnico con opción múltiple.',
        ],
    ],

    'questions_per_level' => 3,

    'questions_pool_min' => 9,

];
