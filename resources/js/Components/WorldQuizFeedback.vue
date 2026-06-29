<script setup lang="ts">
import PowerChip from "@/Components/PowerChip.vue";
import { useLockoutCountdown } from "@/composables/useLockoutCountdown";
import type { WorldQuizFeedback } from "@/types/world";
import { formatLockoutGameTimer } from "@/utils/formatLockout";
import { CheckCircle2, Lock } from "@lucide/vue";
import { computed } from "vue";

const props = defineProps<{
    feedback: WorldQuizFeedback;
    skipCost: number;
    tokens: number;
    skipping?: boolean;
}>();

const emit = defineEmits<{
    continue: [];
    skip: [];
}>();

const hasLockout = computed(
    () => !props.feedback.is_correct && Boolean(props.feedback.locked_until),
);

const { tick } = useLockoutCountdown(hasLockout);

const lockoutTimer = computed(() => {
    void tick.value;

    if (!props.feedback.locked_until) {
        return null;
    }

    return formatLockoutGameTimer(props.feedback.locked_until);
});

const canSkip = computed(
    () => hasLockout.value && props.tokens >= props.skipCost,
);

const skipDisabled = computed(
    () => props.skipping || !canSkip.value,
);
</script>

<template>
    <div class="space-y-4">
        <div
            class="rounded-2xl border p-5 shadow-sm"
            :class="
                feedback.is_correct
                    ? 'border-emerald-100 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40'
                    : 'border-red-100 bg-red-50 dark:border-red-900 dark:bg-red-950/40'
            "
        >
            <div class="mb-2 flex items-center gap-2">
                <CheckCircle2
                    v-if="feedback.is_correct"
                    class="h-5 w-5 text-emerald-600"
                />
                <Lock
                    v-else
                    class="h-5 w-5 text-red-600"
                />
                <h2 class="font-semibold text-heading">
                    {{
                        feedback.level_completed
                            ? "¡Desafío completado!"
                            : feedback.is_correct
                              ? "¡Pregunta correcta!"
                              : "Desafío bloqueado"
                    }}
                </h2>
            </div>
            <p class="text-sm text-body">
                {{ feedback.message }}
            </p>
            <p
                v-if="feedback.is_correct && !feedback.level_completed"
                class="mt-2 text-sm font-medium text-emerald-700 dark:text-emerald-300"
            >
                Progreso del desafío: {{ feedback.questions_correct }}/{{ feedback.questions_total }}
            </p>
            <p
                v-if="!feedback.is_correct && feedback.locked_until"
                class="mt-2 text-sm font-medium text-red-700 dark:text-red-300"
            >
                <template v-if="lockoutTimer">
                    Podrás reintentar en {{ lockoutTimer }}.
                </template>
                <template v-else>
                    Podrás reintentar después de las
                    {{ new Date(feedback.locked_until).toLocaleString("es-ES") }}.
                </template>
            </p>
        </div>

        <button
            v-if="hasLockout"
            type="button"
            class="flex w-full flex-col items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900 transition-colors hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200 dark:hover:bg-amber-950/60"
            :disabled="skipDisabled"
            @click="emit('skip')"
        >
            <span>{{ skipping ? "Desbloqueando…" : "Desbloquear ahora" }}</span>
            <PowerChip
                :amount="skipCost"
                sign="−"
                size="md"
            />
            <span
                v-if="!canSkip && !skipping"
                class="text-xs font-normal text-amber-800/80 dark:text-amber-300/80"
            >
                Necesitas al menos {{ skipCost }} poder
            </span>
        </button>

        <button
            type="button"
            class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
            @click="emit('continue')"
        >
            {{ feedback.is_correct && !feedback.level_completed ? "Siguiente pregunta" : "Volver al mapa" }}
        </button>
    </div>
</template>
