<?php

return [

    /** Coste único para desbloquear el Mundo. */
    'unlock_cost' => (int) env('TOKENS_WORLD_UNLOCK_COST', 300),

    /** Horas de bloqueo tras fallar una pregunta del mundo (Tracks/Práctica usan 24). */
    'lockout_hours' => (int) env('WORLD_LOCKOUT_HOURS', 2),

    /** Mundos del supernivel. */
    'worlds' => [
        [
            'tier' => 'basico',
            'emoji' => '🐧',
            'name' => 'Linux Kingdom',
            'subtitle' => 'The Broken System',
            'description' => 'Domina la terminal, permisos y procesos mientras practicas inglés técnico en un reino roto.',
            'status' => 'available',
            'zones' => [
                [
                    'slug' => 'welcome-village',
                    'emoji' => '🪵',
                    'name' => 'Welcome Village',
                    'level_range' => '1-3',
                    'curriculum' => ['Terminal básica', 'Navegación de archivos', 'Estructura Linux'],
                    'commands' => ['ls', 'cd', 'pwd', 'mkdir', 'touch'],
                    'english' => ['file', 'folder', 'open', 'create', 'path'],
                    'gameplay' => 'NPCs te piden encontrar objetos perdidos: "Find my lost file."',
                ],
                [
                    'slug' => 'directory-forest',
                    'emoji' => '🌲',
                    'name' => 'Directory Forest',
                    'level_range' => '4-7',
                    'curriculum' => ['Rutas absolutas / relativas', 'Manipulación de archivos'],
                    'commands' => ['cp', 'mv', 'rm', 'find'],
                    'english' => ['move', 'copy', 'delete', 'search', 'directory'],
                    'gameplay' => 'El bosque cambia según tus comandos.',
                ],
                [
                    'slug' => 'permission-mountains',
                    'emoji' => '🪨',
                    'name' => 'Permission Mountains',
                    'level_range' => '8-12',
                    'curriculum' => ['Permisos', 'Usuarios', 'Seguridad'],
                    'commands' => ['chmod', 'chown', 'sudo'],
                    'english' => ['permission', 'owner', 'group', 'access', 'denied'],
                    'gameplay' => 'Puertas bloqueadas con "Access denied". Debes desbloquearlas.',
                ],
                [
                    'slug' => 'process-mines',
                    'emoji' => '⚙️',
                    'name' => 'Process Mines',
                    'level_range' => '13-17',
                    'curriculum' => ['Procesos', 'CPU', 'Memoria'],
                    'commands' => ['ps', 'top', 'kill'],
                    'english' => ['process', 'running', 'memory', 'crash', 'usage'],
                    'gameplay' => 'Máquinas mineras colapsadas: repara o mata procesos.',
                ],
            ],
            'boss' => [
                'emoji' => '🧑‍💼',
                'title' => 'System Administrator Interview',
                'description' => 'Entrevista técnica simulada — no es un jefe monstruo, es una entrevista real.',
            ],
        ],
        [
            'tier' => 'intermedio',
            'emoji' => '🐳',
            'name' => 'Docker Planet',
            'subtitle' => 'Próximamente',
            'description' => 'Contenedores, imágenes y despliegues en inglés técnico.',
            'status' => 'coming_soon',
            'zones' => [],
        ],
        [
            'tier' => 'avanzado',
            'emoji' => '🧱',
            'name' => 'Git Castle',
            'subtitle' => 'Próximamente',
            'description' => 'Ramas, merges y flujos de equipo en un castillo de control de versiones.',
            'status' => 'coming_soon',
            'zones' => [],
        ],
    ],

    /**
     * Desafíos del Mundo 1 — Linux Kingdom (17 niveles + boss).
     *
     * @var list<array<string, mixed>>
     */
    'levels' => [
        // 🪵 Welcome Village (1-3)
        [
            'id' => 1, 'tier' => 'basico', 'zone' => 'welcome-village', 'phase' => 1,
            'title' => 'Find my lost file',
            'type' => 'quest',
            'scenario' => 'Un aldeano perdió un archivo en el sistema. Debes usar la terminal para localizarlo.',
            'objective' => 'Practicar ls, pwd y vocabulario: file, folder, path.',
            'gameplay' => 'NPC: "Find my lost file."',
            'duration_minutes' => 8,
        ],
        [
            'id' => 2, 'tier' => 'basico', 'zone' => 'welcome-village', 'phase' => 2,
            'title' => 'Open the right folder',
            'type' => 'quest',
            'scenario' => 'Navega entre carpetas para abrir la ruta correcta del inventario del pueblo.',
            'objective' => 'Usar cd y explicar en inglés cómo abrir un folder.',
            'gameplay' => 'NPC: "Open the config folder, please."',
            'duration_minutes' => 8,
        ],
        [
            'id' => 3, 'tier' => 'basico', 'zone' => 'welcome-village', 'phase' => 3,
            'title' => 'Create the village path',
            'type' => 'command_lab',
            'scenario' => 'Crea la estructura de directorios y archivos que el elder necesita para el festival.',
            'objective' => 'mkdir, touch y frases: create, path, open.',
            'gameplay' => 'Construye la ruta del festival sin romper la estructura Linux.',
            'duration_minutes' => 10,
        ],

        // 🌲 Directory Forest (4-7)
        [
            'id' => 4, 'tier' => 'basico', 'zone' => 'directory-forest', 'phase' => 1,
            'title' => 'Copy the ancient map',
            'type' => 'command_lab',
            'scenario' => 'Duplica un mapa del bosque antes de que la niebla borre los senderos.',
            'objective' => 'cp y vocabulario: copy, directory.',
            'gameplay' => 'El bosque reacciona cuando copias archivos al lugar correcto.',
            'duration_minutes' => 10,
        ],
        [
            'id' => 5, 'tier' => 'basico', 'zone' => 'directory-forest', 'phase' => 2,
            'title' => 'Move through the trees',
            'type' => 'command_lab',
            'scenario' => 'Reorganiza archivos entre rutas absolutas y relativas sin perderte.',
            'objective' => 'mv y frases: move, path, search.',
            'gameplay' => 'Los árboles se reordenan según tus movimientos.',
            'duration_minutes' => 10,
        ],
        [
            'id' => 6, 'tier' => 'basico', 'zone' => 'directory-forest', 'phase' => 3,
            'title' => 'Clean the dead branches',
            'type' => 'command_lab',
            'scenario' => 'Elimina archivos corruptos que bloquean el sendero principal.',
            'objective' => 'rm y vocabulario: delete, file.',
            'gameplay' => 'Limpia el bosque antes de que crezca la maleza digital.',
            'duration_minutes' => 10,
        ],
        [
            'id' => 7, 'tier' => 'basico', 'zone' => 'directory-forest', 'phase' => 4,
            'title' => 'Search the hidden trail',
            'type' => 'puzzle',
            'scenario' => 'Encuentra un archivo oculto en subdirectorios profundos del bosque.',
            'objective' => 'find y frases: search, directory, open.',
            'gameplay' => 'El clima del bosque cambia cuando acercas la búsqueda.',
            'duration_minutes' => 12,
        ],

        // 🪨 Permission Mountains (8-12)
        [
            'id' => 8, 'tier' => 'basico', 'zone' => 'permission-mountains', 'phase' => 1,
            'title' => 'Read the stone signs',
            'type' => 'puzzle',
            'scenario' => 'Interpreta permisos de lectura en tablones de la montaña.',
            'objective' => 'chmod básico y vocabulario: permission, access.',
            'gameplay' => 'Puertas muestran "Permission denied" hasta que ajustas los bits.',
            'duration_minutes' => 12,
        ],
        [
            'id' => 9, 'tier' => 'basico', 'zone' => 'permission-mountains', 'phase' => 2,
            'title' => 'Who owns the gate?',
            'type' => 'puzzle',
            'scenario' => 'Un guardián pregunta quién es el owner del archivo del templo.',
            'objective' => 'chown y frases: owner, group.',
            'gameplay' => 'Debes demostrar ownership antes de cruzar.',
            'duration_minutes' => 12,
        ],
        [
            'id' => 10, 'tier' => 'basico', 'zone' => 'permission-mountains', 'phase' => 3,
            'title' => 'Group access ritual',
            'type' => 'command_lab',
            'scenario' => 'Configura permisos de grupo para que tu party cruce juntos.',
            'objective' => 'chmod/chown grupal y vocabulario: group, access.',
            'gameplay' => 'Solo el grupo correcto puede abrir la puerta de roca.',
            'duration_minutes' => 12,
        ],
        [
            'id' => 11, 'tier' => 'basico', 'zone' => 'permission-mountains', 'phase' => 4,
            'title' => 'Access denied',
            'type' => 'puzzle',
            'scenario' => 'Una puerta bloqueada repite "Access denied" hasta que resuelves el puzzle de permisos.',
            'objective' => 'Combinar permisos y explicar denied en inglés.',
            'gameplay' => 'Cada intento fallido muestra el mensaje clásico del sistema.',
            'duration_minutes' => 14,
        ],
        [
            'id' => 12, 'tier' => 'basico', 'zone' => 'permission-mountains', 'phase' => 5,
            'title' => 'The sudo key',
            'type' => 'command_lab',
            'scenario' => 'Obtén privilegios elevados para reparar el mecanismo de la cima.',
            'objective' => 'sudo y frases de seguridad en inglés.',
            'gameplay' => 'Usa sudo con responsabilidad para desbloquear la cumbre.',
            'duration_minutes' => 14,
        ],

        // ⚙️ Process Mines (13-17)
        [
            'id' => 13, 'tier' => 'basico', 'zone' => 'process-mines', 'phase' => 1,
            'title' => 'Sleeping miners',
            'type' => 'command_lab',
            'scenario' => 'Lista procesos dormidos que bloquean las vías del mineral.',
            'objective' => 'ps y vocabulario: process, running.',
            'gameplay' => 'Identifica qué procesos están running vs idle.',
            'duration_minutes' => 12,
        ],
        [
            'id' => 14, 'tier' => 'basico', 'zone' => 'process-mines', 'phase' => 2,
            'title' => 'CPU overload',
            'type' => 'puzzle',
            'scenario' => 'La CPU al 100% detiene las máquinas extractoras.',
            'objective' => 'top y frases: usage, crash.',
            'gameplay' => 'Monitorea usage hasta encontrar al culpable.',
            'duration_minutes' => 14,
        ],
        [
            'id' => 15, 'tier' => 'basico', 'zone' => 'process-mines', 'phase' => 3,
            'title' => 'Memory leak',
            'type' => 'puzzle',
            'scenario' => 'Una fuga de memoria derrumba el nivel inferior de la mina.',
            'objective' => 'Diagnosticar memory y explicar el problema en inglés.',
            'gameplay' => 'Repara la mina antes del crash total.',
            'duration_minutes' => 14,
        ],
        [
            'id' => 16, 'tier' => 'basico', 'zone' => 'process-mines', 'phase' => 4,
            'title' => 'Kill the zombie process',
            'type' => 'command_lab',
            'scenario' => 'Un proceso zombie impide reiniciar la maquinaria principal.',
            'objective' => 'kill y vocabulario: process, running.',
            'gameplay' => 'Elimina el proceso correcto sin tumbar el servidor.',
            'duration_minutes' => 14,
        ],
        [
            'id' => 17, 'tier' => 'basico', 'zone' => 'process-mines', 'phase' => 5,
            'title' => 'Restart the core drill',
            'type' => 'command_lab',
            'scenario' => 'Restaura operaciones en la mina tras estabilizar todos los procesos.',
            'objective' => 'Combinar ps, top y kill en un escenario final de la zona.',
            'gameplay' => 'La máquina vuelve a life cuando el sistema queda estable.',
            'duration_minutes' => 15,
        ],

        // 🧑‍💼 Final Boss
        [
            'id' => 18, 'tier' => 'basico', 'zone' => 'final-boss', 'phase' => 1,
            'title' => 'System Administrator Interview',
            'type' => 'boss_interview',
            'is_boss' => true,
            'scenario' => 'Entrevista técnica real simulada con un sysadmin senior. No hay monstruo — hay preguntas.',
            'objective' => 'Demostrar dominio de Linux y comunicación clara en inglés bajo presión.',
            'gameplay' => 'Responde escenarios de terminal, permisos y procesos como en una entrevista real.',
            'duration_minutes' => 25,
        ],
    ],

];
