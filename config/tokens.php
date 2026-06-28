<?php

return [

    /** Saldo inicial al registrar una cuenta nueva. */
    'initial_balance' => (int) env('TOKENS_INITIAL_BALANCE', 100),

    /** Coste para omitir el bloqueo de un subnivel. */
    'skip_lockout_cost' => (int) env('TOKENS_SKIP_LOCKOUT_COST', 10),

    /** Máximo de reinicios por módulo (por modo: speaking / quiz). */
    'max_tier_resets' => (int) env('TOKENS_MAX_TIER_RESETS', 2),

    /** Coste de poder al reiniciar un módulo completado. */
    'tier_reset_cost' => (int) env('TOKENS_TIER_RESET_COST', 30),

    /** @deprecated Usar tier_reset_cost */
    'tier_reset_rewards' => [
        'basico' => (int) env('TOKENS_TIER_RESET_BASICO', 50),
        'intermedio' => (int) env('TOKENS_TIER_RESET_INTERMEDIO', 80),
        'avanzado' => (int) env('TOKENS_TIER_RESET_AVANZADO', 100),
    ],

    /** Tokens otorgados al completar un subnivel (Práctica o Tracks). */
    'sublevel_complete_reward' => (int) env('TOKENS_SUBLEVEL_COMPLETE_REWARD', 10),

    /** Coste único para desbloquear el Mundo. */
    'world_unlock_cost' => (int) env('TOKENS_WORLD_UNLOCK_COST', 300),

];
