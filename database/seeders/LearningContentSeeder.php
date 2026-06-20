<?php

namespace Database\Seeders;

use App\Models\LearningTrack;
use App\Models\Question;
use Illuminate\Database\Seeder;

class LearningContentSeeder extends Seeder
{
    public function run(): void
    {
        $speakingTrack = $this->upsertTrack(config('learning.tracks.speaking'));
        $quizTrack = $this->upsertTrack(config('learning.tracks.quiz'));

        Question::query()
            ->whereIn('learning_track_id', [$speakingTrack->id, $quizTrack->id])
            ->where('source', 'seed')
            ->delete();

        $this->seedSpeakingQuestions($speakingTrack);
        $this->seedQuizQuestions($quizTrack);
    }

    /**
     * @param  array{slug: string, name: string, description: string}  $definition
     */
    private function upsertTrack(array $definition): LearningTrack
    {
        return LearningTrack::updateOrCreate(
            ['slug' => $definition['slug']],
            [
                'name' => $definition['name'],
                'description' => $definition['description'],
                'difficulty' => 'beginner',
                'is_active' => true,
                'sort_order' => $definition['slug'] === config('learning.tracks.speaking.slug') ? 1 : 2,
            ],
        );
    }

    private function seedSpeakingQuestions(LearningTrack $track): void
    {
        /** @var list<array<string, mixed>> $items */
        $items = json_decode(
            file_get_contents(database_path('seeders/data/speaking-challenges.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($items as $item) {
            foreach ($this->speakingQuestionsForLevel($item) as $index => $question) {
                $this->upsertQuestion(
                    $track,
                    $item['tier'],
                    (int) $item['phase'],
                    $index + 1,
                    $question['prompt'],
                    [
                        'phase' => (int) $item['phase'],
                        'question_index' => $index + 1,
                        'expected_translation' => $question['expected_translation'],
                    ],
                    $question['hint'] ?? null,
                );
            }
        }
    }

    private function seedQuizQuestions(LearningTrack $track): void
    {
        /** @var list<array<string, mixed>> $items */
        $items = json_decode(
            file_get_contents(database_path('seeders/data/quiz-challenges.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($items as $item) {
            foreach ($this->quizQuestionsForLevel($item) as $index => $question) {
                $this->upsertQuestion(
                    $track,
                    $item['tier'],
                    (int) $item['phase'],
                    $index + 1,
                    $question['prompt'],
                    [
                        'phase' => (int) $item['phase'],
                        'question_index' => $index + 1,
                        'options' => $question['options'],
                        'correct_index' => $question['correct_index'],
                    ],
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @return list<array<string, mixed>>
     */
    private function speakingQuestionsForLevel(array $item): array
    {
        $key = $item['tier'].':'.($item['phase'] ?? 1);
        $extras = self::SPEAKING_EXTRAS[$key] ?? [];

        return array_values(array_merge([[
            'prompt' => $item['prompt'],
            'expected_translation' => $item['expected_translation'],
            'hint' => $item['hint'] ?? null,
        ]], $extras));
    }

    /**
     * @param  array<string, mixed>  $item
     * @return list<array<string, mixed>>
     */
    private function quizQuestionsForLevel(array $item): array
    {
        $key = $item['tier'].':'.($item['phase'] ?? 1);
        $extras = array_merge(
            self::QUIZ_EXTRAS[$key] ?? [],
            $this->quizPoolSupplement()[$key] ?? [],
        );

        return array_values(array_merge([[
            'prompt' => $item['prompt'],
            'options' => $item['options'],
            'correct_index' => $item['correct_index'],
        ]], $extras));
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function quizPoolSupplement(): array
    {
        static $pool = null;

        if ($pool !== null) {
            return $pool;
        }

        /** @var array<string, list<array<string, mixed>>> $pool */
        $pool = json_decode(
            file_get_contents(database_path('seeders/data/quiz-pool-supplement.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        return $pool;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function upsertQuestion(
        LearningTrack $track,
        string $tier,
        int $phase,
        int $questionIndex,
        string $prompt,
        array $metadata,
        ?string $context = null,
    ): void {
        $metadata['step_difficulty'] = match (true) {
            $questionIndex <= 1 => 'facil',
            $questionIndex === 2 => 'medio',
            default => 'dificil',
        };
        $metadata['sublevel_intensity'] = $phase;

        Question::query()->create([
            'learning_track_id' => $track->id,
            'prompt' => $prompt,
            'context' => $context,
            'difficulty' => $tier,
            'source' => 'seed',
            'metadata' => $metadata,
        ]);
    }

    /** @var array<string, list<array<string, mixed>>> */
    private const SPEAKING_EXTRAS = [
        'basico:1' => [
            ['prompt' => 'Hi', 'expected_translation' => 'Hola', 'hint' => 'Saludo informal corto.'],
            ['prompt' => 'Hey there', 'expected_translation' => 'Hola', 'hint' => 'Saludo casual amistoso.'],
        ],
        'basico:2' => [
            ['prompt' => 'Good afternoon', 'expected_translation' => 'Buenas tardes', 'hint' => 'Saludo de mediodía.'],
            ['prompt' => 'Good evening', 'expected_translation' => 'Buenas noches', 'hint' => 'Saludo al anochecer.'],
        ],
        'basico:3' => [
            ['prompt' => 'How is it going?', 'expected_translation' => '¿Cómo va?', 'hint' => 'Pregunta informal.'],
            ['prompt' => 'Are you okay?', 'expected_translation' => '¿Estás bien?', 'hint' => 'Mostrar interés.'],
        ],
        'basico:4' => [
            ['prompt' => 'Thanks a lot', 'expected_translation' => 'Muchas gracias', 'hint' => 'Agradecimiento informal.'],
            ['prompt' => 'I appreciate it', 'expected_translation' => 'Lo aprecio', 'hint' => 'Agradecimiento formal.'],
        ],
        'basico:5' => [
            ['prompt' => 'See you later', 'expected_translation' => 'Nos vemos luego', 'hint' => 'Despedida genérica.'],
            ['prompt' => 'See you soon', 'expected_translation' => 'Nos vemos pronto', 'hint' => 'Despedida cercana.'],
        ],
        'intermedio:1' => [
            ['prompt' => 'I am building a new module', 'expected_translation' => 'Estoy construyendo un módulo nuevo', 'hint' => 'Variante de trabajo en curso.'],
            ['prompt' => 'I am fixing a bug', 'expected_translation' => 'Estoy arreglando un bug', 'hint' => 'Tarea común de desarrollo.'],
        ],
        'intermedio:2' => [
            ['prompt' => 'Could you check my code?', 'expected_translation' => '¿Podrías revisar mi código?', 'hint' => 'Petición alternativa.'],
            ['prompt' => 'Please review this change', 'expected_translation' => 'Por favor revisa este cambio', 'hint' => 'Solicitud directa.'],
        ],
        'intermedio:3' => [
            ['prompt' => 'Production is down', 'expected_translation' => 'Producción está caída', 'hint' => 'Incidente crítico.'],
            ['prompt' => 'The release failed', 'expected_translation' => 'El lanzamiento falló', 'hint' => 'Reporte de fallo.'],
        ],
        'intermedio:4' => [
            ['prompt' => 'We must fix this before launch', 'expected_translation' => 'Debemos arreglar esto antes del lanzamiento', 'hint' => 'Urgencia previa al release.'],
            ['prompt' => 'This issue blocks the release', 'expected_translation' => 'Este problema bloquea el lanzamiento', 'hint' => 'Bloqueo de entrega.'],
        ],
        'intermedio:5' => [
            ['prompt' => 'Let us meet tomorrow', 'expected_translation' => 'Reunámonos mañana', 'hint' => 'Coordinación simple.'],
            ['prompt' => 'I will set up a call', 'expected_translation' => 'Organizaré una llamada', 'hint' => 'Agendar comunicación.'],
        ],
        'avanzado:1' => [
            ['prompt' => 'Response times increased significantly', 'expected_translation' => 'Los tiempos de respuesta aumentaron significativamente', 'hint' => 'Métrica de rendimiento.'],
            ['prompt' => 'Throughput dropped after deploy', 'expected_translation' => 'El throughput cayó después del despliegue', 'hint' => 'Impacto post-release.'],
        ],
        'avanzado:2' => [
            ['prompt' => 'This code needs refactoring', 'expected_translation' => 'Este código necesita refactorización', 'hint' => 'Deuda técnica.'],
            ['prompt' => 'We should improve code quality', 'expected_translation' => 'Deberíamos mejorar la calidad del código', 'hint' => 'Propuesta de mejora.'],
        ],
        'avanzado:3' => [
            ['prompt' => 'It was a concurrency bug', 'expected_translation' => 'Era un bug de concurrencia', 'hint' => 'Diagnóstico alternativo.'],
            ['prompt' => 'Cache invalidation caused the issue', 'expected_translation' => 'La invalidación de caché causó el problema', 'hint' => 'Causa en capa de datos.'],
        ],
        'avanzado:4' => [
            ['prompt' => 'Microservices could reduce coupling', 'expected_translation' => 'Los microservicios podrían reducir el acoplamiento', 'hint' => 'Beneficio arquitectónico.'],
            ['prompt' => 'We need better service boundaries', 'expected_translation' => 'Necesitamos mejores límites de servicio', 'hint' => 'Diseño de dominio.'],
        ],
        'avanzado:5' => [
            ['prompt' => 'Monitoring alerted us early', 'expected_translation' => 'El monitoreo nos alertó a tiempo', 'hint' => 'Detección temprana.'],
            ['prompt' => 'Metrics showed the anomaly', 'expected_translation' => 'Las métricas mostraron la anomalía', 'hint' => 'Observabilidad basada en datos.'],
        ],
    ];

    /** @var array<string, list<array<string, mixed>>> */
    private const QUIZ_EXTRAS = [
        'basico:1' => [
            ['prompt' => 'Orange', 'options' => ['Naranja', 'Manzana', 'Pera'], 'correct_index' => 0],
            ['prompt' => 'Banana', 'options' => ['Plátano', 'Uva', 'Limón'], 'correct_index' => 0],
        ],
        'basico:2' => [
            ['prompt' => 'Pen', 'options' => ['Bolígrafo', 'Mesa', 'Puerta'], 'correct_index' => 0],
            ['prompt' => 'Table', 'options' => ['Mesa', 'Libro', 'Ventana'], 'correct_index' => 0],
        ],
        'basico:3' => [
            ['prompt' => 'I am hungry', 'options' => ['Tengo hambre', 'Tengo frío', 'Estoy cansado'], 'correct_index' => 0],
            ['prompt' => 'I am tired', 'options' => ['Estoy cansado', 'Estoy feliz', 'Estoy listo'], 'correct_index' => 0],
        ],
        'basico:4' => [
            ['prompt' => 'See you', 'options' => ['Nos vemos', 'Buenas noches', 'Gracias'], 'correct_index' => 0],
            ['prompt' => 'Welcome', 'options' => ['Bienvenido', 'Adiós', 'Perdón'], 'correct_index' => 0],
        ],
        'basico:5' => [
            ['prompt' => 'Nice to meet you', 'options' => ['Mucho gusto', 'Hasta mañana', 'De nada'], 'correct_index' => 0],
            ['prompt' => 'What is your name?', 'options' => ['¿Cómo te llamas?', '¿Cómo estás?', '¿Dónde vives?'], 'correct_index' => 0],
        ],
        'intermedio:1' => [
            ['prompt' => 'Pull request', 'options' => ['Solicitud de cambios', 'Error de sintaxis', 'Servidor caído'], 'correct_index' => 0],
            ['prompt' => 'Code review', 'options' => ['Revisión de código', 'Despliegue manual', 'Copia de seguridad'], 'correct_index' => 0],
        ],
        'intermedio:2' => [
            ['prompt' => 'Outage', 'options' => ['Interrupción del servicio', 'Nueva funcionalidad', 'Documentación'], 'correct_index' => 0],
            ['prompt' => 'Downtime', 'options' => ['Tiempo de inactividad', 'Tiempo de desarrollo', 'Tiempo de prueba'], 'correct_index' => 0],
        ],
        'intermedio:3' => [
            ['prompt' => 'Hotfix', 'options' => ['Parche urgente', 'Nueva versión mayor', 'Refactor completo'], 'correct_index' => 0],
            ['prompt' => 'Rollback', 'options' => ['Revertir cambios', 'Acelerar release', 'Eliminar logs'], 'correct_index' => 0],
        ],
        'intermedio:4' => [
            ['prompt' => 'Integration test', 'options' => ['Prueba de integración', 'Comentario de código', 'Plan de marketing'], 'correct_index' => 0],
            ['prompt' => 'Test coverage', 'options' => ['Cobertura de pruebas', 'Carga del servidor', 'Diseño UI'], 'correct_index' => 0],
        ],
        'intermedio:5' => [
            ['prompt' => 'Pipeline', 'options' => ['Canal de CI/CD', 'Base de datos local', 'Interfaz gráfica'], 'correct_index' => 0],
            ['prompt' => 'Green build', 'options' => ['Compilación exitosa', 'Error crítico', 'Deploy fallido'], 'correct_index' => 0],
        ],
        'avanzado:1' => [
            ['prompt' => 'Latency', 'options' => ['Latencia', 'Seguridad', 'Interfaz'], 'correct_index' => 0],
            ['prompt' => 'Throughput', 'options' => ['Rendimiento', 'Permisos', 'Diseño'], 'correct_index' => 0],
        ],
        'avanzado:2' => [
            ['prompt' => 'Strong consistency', 'options' => ['Consistencia fuerte', 'Consistencia nula', 'Consistencia lenta'], 'correct_index' => 0],
            ['prompt' => 'CAP theorem', 'options' => ['Teorema CAP', 'Teorema de Pitágoras', 'Teorema de Moore'], 'correct_index' => 0],
        ],
        'avanzado:3' => [
            ['prompt' => 'Load balancer', 'options' => ['Balanceador de carga', 'Monitor de red', 'Editor de texto'], 'correct_index' => 0],
            ['prompt' => 'Auto scaling', 'options' => ['Escalado automático', 'Borrado automático', 'Copiado manual'], 'correct_index' => 0],
        ],
        'avanzado:4' => [
            ['prompt' => 'Retry with backoff', 'options' => ['Reintento con backoff', 'Fallo inmediato', 'Sin timeout'], 'correct_index' => 0],
            ['prompt' => 'Bulkhead pattern', 'options' => ['Patrón bulkhead', 'Patrón factory', 'Patrón observer'], 'correct_index' => 0],
        ],
        'avanzado:5' => [
            ['prompt' => 'Blue green deployment', 'options' => ['Despliegue blue-green', 'Despliegue manual', 'Despliegue sin pruebas'], 'correct_index' => 0],
            ['prompt' => 'Canary release', 'options' => ['Lanzamiento canary', 'Lanzamiento oculto', 'Lanzamiento revertido'], 'correct_index' => 0],
        ],
    ];
}
