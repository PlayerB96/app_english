<script setup lang="ts">
import ThemeToggle from "@/Components/ThemeToggle.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { Languages, MailCheck } from "@lucide/vue";
import { computed } from "vue";

const props = defineProps<{
    status?: string | null;
}>();

const resendForm = useForm({});

const statusMessage = computed(() => {
    if (props.status === "verification-link-sent") {
        return "Te enviamos un nuevo enlace de verificación.";
    }

    if (props.status === "verified") {
        return "Tu correo fue verificado correctamente.";
    }

    return null;
});

function resend(): void {
    resendForm.post("/email/verification-notification");
}
</script>

<template>
    <div class="surface-page relative flex items-center justify-center p-4">
        <div class="absolute right-4 top-4">
            <ThemeToggle />
        </div>

        <div class="w-full max-w-md">
            <div class="surface-card p-8 shadow-lg">
                <div class="mb-8 text-center">
                    <div class="mb-4 flex justify-center">
                        <div class="rounded-full bg-blue-50 p-4 dark:bg-blue-950/60">
                            <MailCheck class="h-10 w-10 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold text-heading">
                        Verifica tu correo
                    </h1>
                    <p class="mt-2 text-muted">
                        Te enviamos un enlace de verificación. Revisa tu bandeja de entrada.
                    </p>
                </div>

                <p
                    v-if="statusMessage"
                    class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-950/40 dark:text-green-300"
                >
                    {{ statusMessage }}
                </p>

                <button
                    type="button"
                    :disabled="resendForm.processing"
                    class="w-full rounded-lg bg-blue-600 px-4 py-2.5 font-medium text-white transition-colors hover:bg-blue-700 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
                    @click="resend"
                >
                    {{ resendForm.processing ? "Enviando..." : "Reenviar correo de verificación" }}
                </button>

                <div class="mt-6 text-center text-sm">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="inline-flex items-center gap-1 text-muted hover:text-body"
                    >
                        <Languages class="h-4 w-4" />
                        Volver al inicio de sesión
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
