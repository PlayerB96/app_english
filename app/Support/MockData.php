<?php

namespace App\Support;

/**
 * Fixtures de prototipo (WS-012). Sin persistencia; alineados al esquema BD.
 */
final class MockData
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function learningTracks(): array
    {
        return [
            [
                'id' => 1,
                'slug' => 'dev-vocabulary',
                'name' => 'Dev Vocabulary',
                'description' => 'Términos técnicos en inglés para el día a día del desarrollo.',
                'difficulty' => 'beginner',
                'is_active' => true,
                'sort_order' => 1,
                'session_count' => 42,
            ],
            [
                'id' => 2,
                'slug' => 'technical-interviews',
                'name' => 'Technical Interviews',
                'description' => 'Respuestas claras para entrevistas técnicas en inglés.',
                'difficulty' => 'intermediate',
                'is_active' => true,
                'sort_order' => 2,
                'session_count' => 28,
            ],
            [
                'id' => 3,
                'slug' => 'documentation',
                'name' => 'Documentation',
                'description' => 'Redacción de READMEs, ADRs y comentarios profesionales.',
                'difficulty' => 'advanced',
                'is_active' => true,
                'sort_order' => 3,
                'session_count' => 15,
            ],
            [
                'id' => 4,
                'slug' => 'code-reviews',
                'name' => 'Code Reviews',
                'description' => 'Feedback constructivo en pull requests y revisiones de equipo.',
                'difficulty' => 'intermediate',
                'is_active' => false,
                'sort_order' => 4,
                'session_count' => 0,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function tiers(): array
    {
        return [
            [
                'slug' => 'basico',
                'name' => 'Nivel Básico',
                'description' => 'Saludos, palabras cotidianas y frases cortas.',
            ],
            [
                'slug' => 'intermedio',
                'name' => 'Nivel Intermedio',
                'description' => 'Conversaciones del día a día y vocabulario técnico ligero.',
            ],
            [
                'slug' => 'avanzado',
                'name' => 'Nivel Avanzado',
                'description' => 'Explicaciones complejas y contexto profesional.',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function speakingChallenges(): array
    {
        return [
            ['id' => 1, 'tier' => 'basico', 'phase' => 1, 'prompt' => 'Hello', 'expected_translation' => 'Hola', 'hint' => 'Saludo informal universal.'],
            ['id' => 2, 'tier' => 'basico', 'phase' => 2, 'prompt' => 'Good morning', 'expected_translation' => 'Buenos días', 'hint' => 'Saludo matutino.'],
            ['id' => 3, 'tier' => 'basico', 'phase' => 3, 'prompt' => 'How are you?', 'expected_translation' => '¿Cómo estás?', 'hint' => 'Pregunta sobre el estado de alguien.'],
            ['id' => 4, 'tier' => 'basico', 'phase' => 4, 'prompt' => 'Thank you very much', 'expected_translation' => 'Muchas gracias', 'hint' => 'Agradecimiento enfatizado.'],
            ['id' => 5, 'tier' => 'basico', 'phase' => 5, 'prompt' => 'See you tomorrow', 'expected_translation' => 'Nos vemos mañana', 'hint' => 'Despedida para el día siguiente.'],
            ['id' => 6, 'tier' => 'intermedio', 'phase' => 1, 'prompt' => 'I am working on a new feature', 'expected_translation' => 'Estoy trabajando en una nueva funcionalidad', 'hint' => 'Presente continuo en contexto laboral.'],
            ['id' => 7, 'tier' => 'intermedio', 'phase' => 2, 'prompt' => 'Can you review my pull request?', 'expected_translation' => '¿Puedes revisar mi pull request?', 'hint' => 'Petición educada en equipo de desarrollo.'],
            ['id' => 8, 'tier' => 'intermedio', 'phase' => 3, 'prompt' => 'The deployment failed last night', 'expected_translation' => 'El despliegue falló anoche', 'hint' => 'Reportar un incidente.'],
            ['id' => 9, 'tier' => 'intermedio', 'phase' => 4, 'prompt' => 'We need to fix this bug before the release', 'expected_translation' => 'Necesitamos arreglar este bug antes del lanzamiento', 'hint' => 'Urgencia técnica.'],
            ['id' => 10, 'tier' => 'intermedio', 'phase' => 5, 'prompt' => 'Let me schedule a meeting with the team', 'expected_translation' => 'Déjame agendar una reunión con el equipo', 'hint' => 'Coordinación de equipo.'],
            ['id' => 11, 'tier' => 'avanzado', 'phase' => 1, 'prompt' => 'The API latency increased after the last deployment', 'expected_translation' => 'La latencia de la API aumentó después del último despliegue', 'hint' => 'Diagnóstico de rendimiento.'],
            ['id' => 12, 'tier' => 'avanzado', 'phase' => 2, 'prompt' => 'We should refactor this module to improve maintainability', 'expected_translation' => 'Deberíamos refactorizar este módulo para mejorar la mantenibilidad', 'hint' => 'Propuesta técnica formal.'],
            ['id' => 13, 'tier' => 'avanzado', 'phase' => 3, 'prompt' => 'The root cause was a race condition in the cache layer', 'expected_translation' => 'La causa raíz fue una condición de carrera en la capa de caché', 'hint' => 'Postmortem técnico.'],
            ['id' => 14, 'tier' => 'avanzado', 'phase' => 4, 'prompt' => 'I recommend splitting the monolith into smaller services', 'expected_translation' => 'Recomiendo dividir el monolito en servicios más pequeños', 'hint' => 'Arquitectura de software.'],
            ['id' => 15, 'tier' => 'avanzado', 'phase' => 5, 'prompt' => 'Our observability stack helped us detect the issue early', 'expected_translation' => 'Nuestro stack de observabilidad nos ayudó a detectar el problema a tiempo', 'hint' => 'DevOps y monitoreo.'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function quizChallenges(): array
    {
        return [
            ['id' => 1, 'tier' => 'basico', 'phase' => 1, 'prompt' => 'Apple', 'options' => ['Manzana', 'Pera', 'Uva'], 'correct_index' => 0],
            ['id' => 2, 'tier' => 'basico', 'phase' => 2, 'prompt' => 'Book', 'options' => ['Libro', 'Mesa', 'Silla'], 'correct_index' => 0],
            ['id' => 3, 'tier' => 'basico', 'phase' => 3, 'prompt' => 'I need water', 'options' => ['Necesito agua', 'Tengo sed de café', 'Quiero pan'], 'correct_index' => 0],
            ['id' => 4, 'tier' => 'basico', 'phase' => 4, 'prompt' => 'Good night', 'options' => ['Buenas noches', 'Buenos días', 'Hasta luego'], 'correct_index' => 0],
            ['id' => 5, 'tier' => 'basico', 'phase' => 5, 'prompt' => 'My name is Alex', 'options' => ['Me llamo Alex', 'Vivo en Alex', 'Soy de noche'], 'correct_index' => 0],
            ['id' => 6, 'tier' => 'intermedio', 'phase' => 1, 'prompt' => 'Merge conflict', 'options' => ['Conflicto de fusión', 'Error de red', 'Código duplicado'], 'correct_index' => 0],
            ['id' => 7, 'tier' => 'intermedio', 'phase' => 2, 'prompt' => 'The server is down', 'options' => ['El servidor está caído', 'El servidor es rápido', 'El servidor es nuevo'], 'correct_index' => 0],
            ['id' => 8, 'tier' => 'intermedio', 'phase' => 3, 'prompt' => 'We rolled back the release', 'options' => ['Revertimos el lanzamiento', 'Aceleramos el lanzamiento', 'Cancelamos el equipo'], 'correct_index' => 0],
            ['id' => 9, 'tier' => 'intermedio', 'phase' => 4, 'prompt' => 'Write unit tests', 'options' => ['Escribir pruebas unitarias', 'Borrar la base de datos', 'Cerrar el proyecto'], 'correct_index' => 0],
            ['id' => 10, 'tier' => 'intermedio', 'phase' => 5, 'prompt' => 'The build passed successfully', 'options' => ['La compilación pasó exitosamente', 'La compilación falló ayer', 'La compilación es lenta'], 'correct_index' => 0],
            ['id' => 11, 'tier' => 'avanzado', 'phase' => 1, 'prompt' => 'Idempotent operation', 'options' => ['Operación idempotente', 'Operación aleatoria', 'Operación bloqueada'], 'correct_index' => 0],
            ['id' => 12, 'tier' => 'avanzado', 'phase' => 2, 'prompt' => 'Eventual consistency', 'options' => ['Consistencia eventual', 'Consistencia inmediata', 'Consistencia imposible'], 'correct_index' => 0],
            ['id' => 13, 'tier' => 'avanzado', 'phase' => 3, 'prompt' => 'Horizontal scaling', 'options' => ['Escalado horizontal', 'Escalado vertical único', 'Reducción de nodos'], 'correct_index' => 0],
            ['id' => 14, 'tier' => 'avanzado', 'phase' => 4, 'prompt' => 'Circuit breaker pattern', 'options' => ['Patrón circuit breaker', 'Patrón singleton', 'Patrón decorador'], 'correct_index' => 0],
            ['id' => 15, 'tier' => 'avanzado', 'phase' => 5, 'prompt' => 'Zero downtime deployment', 'options' => ['Despliegue sin tiempo de inactividad', 'Despliegue manual lento', 'Despliegue sin pruebas'], 'correct_index' => 0],
        ];
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public static function questionsByTrack(): array
    {
        return [
            '1' => [
                [
                    'id' => 101,
                    'practice_session_id' => null,
                    'learning_track_id' => 1,
                    'prompt' => 'Explain what a "pull request" is in your own words.',
                    'context' => 'Imagine you are onboarding a junior developer on your team.',
                    'difficulty' => 'beginner',
                    'source' => 'ai',
                    'sample_answer' => 'A pull request is a way to propose changes to a codebase and request a review before merging.',
                ],
                [
                    'id' => 102,
                    'practice_session_id' => null,
                    'learning_track_id' => 1,
                    'prompt' => 'How would you describe "refactoring" to a non-technical stakeholder?',
                    'context' => null,
                    'difficulty' => 'beginner',
                    'source' => 'ai',
                    'sample_answer' => 'Refactoring means improving the internal structure of code without changing what it does for users.',
                ],
            ],
            '2' => [
                [
                    'id' => 201,
                    'practice_session_id' => null,
                    'learning_track_id' => 2,
                    'prompt' => 'Walk me through how you would debug a production issue under time pressure.',
                    'context' => 'Interview scenario — keep it structured and concise.',
                    'difficulty' => 'intermediate',
                    'source' => 'ai',
                    'sample_answer' => 'I would reproduce the issue, check logs and metrics, isolate the failing component, and deploy a fix with monitoring.',
                ],
            ],
            '3' => [
                [
                    'id' => 301,
                    'practice_session_id' => null,
                    'learning_track_id' => 3,
                    'prompt' => 'Write a short README introduction for an open-source API client library.',
                    'context' => 'Tone: professional, welcoming, developer-focused.',
                    'difficulty' => 'advanced',
                    'source' => 'ai',
                    'sample_answer' => 'This library provides a typed client for our REST API with retries, pagination helpers, and clear error types.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function learnerDashboard(): array
    {
        return [
            'summary' => [
                'total_sessions' => 12,
                'avg_accuracy' => 78.5,
                'current_level' => 'intermediate',
                'streak_days' => 5,
                'last_practice_at' => '2026-06-13T18:30:00+00:00',
                'suggested_level' => 'intermediate',
            ],
            'chart_points' => [
                ['date' => '2026-06-08', 'accuracy' => 62],
                ['date' => '2026-06-09', 'accuracy' => 68],
                ['date' => '2026-06-10', 'accuracy' => 71],
                ['date' => '2026-06-11', 'accuracy' => 75],
                ['date' => '2026-06-12', 'accuracy' => 80],
                ['date' => '2026-06-13', 'accuracy' => 78],
            ],
            'recent_sessions' => [
                [
                    'id' => 11,
                    'track_name' => 'Dev Vocabulary',
                    'completed_at' => '2026-06-13T18:30:00+00:00',
                    'accuracy_pct' => 80,
                    'question_count' => 5,
                ],
                [
                    'id' => 10,
                    'track_name' => 'Technical Interviews',
                    'completed_at' => '2026-06-12T09:15:00+00:00',
                    'accuracy_pct' => 75,
                    'question_count' => 4,
                ],
                [
                    'id' => 9,
                    'track_name' => 'Documentation',
                    'completed_at' => '2026-06-11T20:00:00+00:00',
                    'accuracy_pct' => 72,
                    'question_count' => 3,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function adminDashboard(): array
    {
        return [
            'kpis' => [
                'total_learners' => 24,
                'completed_sessions' => 156,
                'active_sessions' => 3,
                'active_tracks' => 3,
                'avg_accuracy' => 74.2,
            ],
            'recent_learners' => array_slice(self::adminLearners(), 0, 5),
            'track_reports' => self::adminTrackReports(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function adminLearners(): array
    {
        return [
            [
                'id' => 2,
                'name' => 'Alex Developer',
                'email' => 'alex@example.com',
                'sessions_completed' => 14,
                'last_practice_at' => '2026-06-13T16:00:00+00:00',
                'level_estimated' => 'intermediate',
                'accuracy_pct' => 82.0,
            ],
            [
                'id' => 3,
                'name' => 'Sam Backend',
                'email' => 'sam@example.com',
                'sessions_completed' => 9,
                'last_practice_at' => '2026-06-12T11:30:00+00:00',
                'level_estimated' => 'beginner',
                'accuracy_pct' => 71.5,
            ],
            [
                'id' => 4,
                'name' => 'Jordan Frontend',
                'email' => 'jordan@example.com',
                'sessions_completed' => 18,
                'last_practice_at' => '2026-06-13T08:45:00+00:00',
                'level_estimated' => 'advanced',
                'accuracy_pct' => 88.0,
            ],
            [
                'id' => 5,
                'name' => 'Casey DevOps',
                'email' => 'casey@example.com',
                'sessions_completed' => 6,
                'last_practice_at' => '2026-06-10T19:20:00+00:00',
                'level_estimated' => 'intermediate',
                'accuracy_pct' => 76.0,
            ],
            [
                'id' => 6,
                'name' => 'Riley Fullstack',
                'email' => 'riley@example.com',
                'sessions_completed' => 11,
                'last_practice_at' => '2026-06-11T14:10:00+00:00',
                'level_estimated' => 'intermediate',
                'accuracy_pct' => 79.5,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function adminTracks(): array
    {
        return self::learningTracks();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function adminTrackReports(): array
    {
        return [
            [
                'track_id' => 1,
                'track_name' => 'Dev Vocabulary',
                'sessions_count' => 42,
                'avg_accuracy' => 76.8,
                'active_learners' => 18,
            ],
            [
                'track_id' => 2,
                'track_name' => 'Technical Interviews',
                'sessions_count' => 28,
                'avg_accuracy' => 71.2,
                'active_learners' => 12,
            ],
            [
                'track_id' => 3,
                'track_name' => 'Documentation',
                'sessions_count' => 15,
                'avg_accuracy' => 68.5,
                'active_learners' => 8,
            ],
        ];
    }
}
