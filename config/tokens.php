<?php

return [

    /** Saldo inicial al registrar una cuenta nueva. */
    'initial_balance' => (int) env('TOKENS_INITIAL_BALANCE', 100),

    /** Coste para omitir el bloqueo de un subnivel. */
    'skip_lockout_cost' => (int) env('TOKENS_SKIP_LOCKOUT_COST', 10),

];
