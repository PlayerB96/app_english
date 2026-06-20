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
