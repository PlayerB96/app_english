<?php

return [

    /** Coste único para desbloquear el Mundo. */
    'unlock_cost' => (int) env('TOKENS_WORLD_UNLOCK_COST', 300),

    /** Mundos del supernivel (contenido distinto a Práctica / Tracks). */
    'worlds' => [
        [
            'tier' => 'basico',
            'name' => 'Mundo Daily Dev',
            'description' => 'Roleplay de standups, pair programming y mensajes de equipo.',
        ],
        [
            'tier' => 'intermedio',
            'name' => 'Mundo Tech Lead',
            'description' => 'Revisiones de código, estimaciones y conversaciones con stakeholders.',
        ],
        [
            'tier' => 'avanzado',
            'name' => 'Mundo Architect',
            'description' => 'Diseño de sistemas, entrevistas senior y presentaciones técnicas.',
        ],
    ],

    /**
     * 15 desafíos del Mundo (5 por mundo). Tipos distintos al speaking/quiz gratuito.
     *
     * @var list<array{
     *     id: int,
     *     tier: string,
     *     phase: int,
     *     title: string,
     *     type: string,
     *     scenario: string,
     *     objective: string,
     *     duration_minutes: int
     * }>
     */
    'levels' => [
        ['id' => 1, 'tier' => 'basico', 'phase' => 1, 'title' => 'Standup express', 'type' => 'roleplay', 'scenario' => 'Tu equipo espera tu update en la daily. Resume ayer, hoy y blockers en inglés.', 'objective' => 'Practicar un standup claro en menos de 90 segundos.', 'duration_minutes' => 5],
        ['id' => 2, 'tier' => 'basico', 'phase' => 2, 'title' => 'Slack al equipo', 'type' => 'writing', 'scenario' => 'Debes avisar en Slack que el deploy se retrasó 30 minutos.', 'objective' => 'Redactar un mensaje profesional, breve y sin alarmismo.', 'duration_minutes' => 5],
        ['id' => 3, 'tier' => 'basico', 'phase' => 3, 'title' => 'Pair invite', 'type' => 'dialogue', 'scenario' => 'Invita a un compañero a hacer pair programming en una tarea de bugfix.', 'objective' => 'Sonar natural al proponer colaboración en inglés.', 'duration_minutes' => 6],
        ['id' => 4, 'tier' => 'basico', 'phase' => 4, 'title' => 'Bug report', 'type' => 'writing', 'scenario' => 'Documenta un bug intermitente de login para el equipo de QA.', 'objective' => 'Estructurar steps, expected vs actual y severidad.', 'duration_minutes' => 8],
        ['id' => 5, 'tier' => 'basico', 'phase' => 5, 'title' => 'Onboarding buddy', 'type' => 'roleplay', 'scenario' => 'Un dev nuevo te pregunta cómo correr el proyecto localmente.', 'objective' => 'Explicar setup con paciencia y vocabulario dev.', 'duration_minutes' => 10],

        ['id' => 6, 'tier' => 'intermedio', 'phase' => 1, 'title' => 'Code review', 'type' => 'feedback', 'scenario' => 'Dejas comentarios constructivos en un PR con deuda técnica.', 'objective' => 'Dar feedback firme pero amable en inglés.', 'duration_minutes' => 10],
        ['id' => 7, 'tier' => 'intermedio', 'phase' => 2, 'title' => 'Sprint planning', 'type' => 'roleplay', 'scenario' => 'Defiendes por qué una historia necesita 8 puntos, no 3.', 'objective' => 'Argumentar estimaciones con claridad.', 'duration_minutes' => 12],
        ['id' => 8, 'tier' => 'intermedio', 'phase' => 3, 'title' => 'Incident update', 'type' => 'dialogue', 'scenario' => 'El PM pide status de un incidente en producción.', 'objective' => 'Comunicar impacto, causa probable y ETA.', 'duration_minutes' => 10],
        ['id' => 9, 'tier' => 'intermedio', 'phase' => 4, 'title' => 'Pushback polite', 'type' => 'dialogue', 'scenario' => 'Un stakeholder pide un feature fuera de scope esta semana.', 'objective' => 'Decir que no sin quemar la relación.', 'duration_minutes' => 12],
        ['id' => 10, 'tier' => 'intermedio', 'phase' => 5, 'title' => 'Demo day', 'type' => 'presentation', 'scenario' => 'Presentas en 3 minutos lo que entregó tu squad.', 'objective' => 'Pitch claro orientado a valor de negocio.', 'duration_minutes' => 15],

        ['id' => 11, 'tier' => 'avanzado', 'phase' => 1, 'title' => 'System design intro', 'type' => 'presentation', 'scenario' => 'Abres una sesión de diseño para un servicio de notificaciones.', 'objective' => 'Plantear requisitos, trade-offs y primer diagrama.', 'duration_minutes' => 15],
        ['id' => 12, 'tier' => 'avanzado', 'phase' => 2, 'title' => 'Senior interview', 'type' => 'roleplay', 'scenario' => 'Respondes “Tell me about a hard technical decision you made”.', 'objective' => 'Usar método STAR con vocabulario senior.', 'duration_minutes' => 15],
        ['id' => 13, 'tier' => 'avanzado', 'phase' => 3, 'title' => 'RFC review', 'type' => 'feedback', 'scenario' => 'Comentas un RFC de migración de monolito a microservicios.', 'objective' => 'Cuestionar supuestos con precisión técnica.', 'duration_minutes' => 18],
        ['id' => 14, 'tier' => 'avanzado', 'phase' => 4, 'title' => 'Exec briefing', 'type' => 'presentation', 'scenario' => 'Tienes 2 minutos ante dirección para explicar deuda técnica.', 'objective' => 'Traducir riesgo técnico a impacto de negocio.', 'duration_minutes' => 12],
        ['id' => 15, 'tier' => 'avanzado', 'phase' => 5, 'title' => 'Conference talk', 'type' => 'presentation', 'scenario' => 'Cierras una charla sobre observabilidad con Q&A del público.', 'objective' => 'Manejar preguntas difíciles con fluidez.', 'duration_minutes' => 20],
    ],

];
