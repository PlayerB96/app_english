<?php

namespace App\Console\Commands;

use App\Services\ProgressBackfillService;
use Illuminate\Console\Command;

class BackfillProgressCommand extends Command
{
    protected $signature = 'progress:backfill
                            {--user= : ID de usuario concreto}
                            {--force : Regenerar aunque ya existan respuestas}';

    protected $description = 'Migra progreso de subniveles (user_level_progress) a sesiones y métricas del dashboard';

    public function handle(ProgressBackfillService $backfill): int
    {
        $userId = $this->option('user') !== null
            ? (int) $this->option('user')
            : null;

        $force = (bool) $this->option('force');

        if ($force) {
            $this->warn('Modo --force: puede duplicar sesiones si ya ejecutaste el backfill antes.');
        }

        $stats = $backfill->backfill($userId, $force);

        $this->info(sprintf(
            'Backfill listo: %d sesiones, %d respuestas, %d snapshots (%d usuarios omitidos).',
            $stats['sessions'],
            $stats['answers'],
            $stats['snapshots'],
            $stats['skipped_users'],
        ));

        return self::SUCCESS;
    }
}
