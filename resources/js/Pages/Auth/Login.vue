<script setup lang="ts">
import AppLogo from "@/Components/AppLogo.vue";
import SiteFooter from "@/Components/SiteFooter.vue";
import ThemeToggle from "@/Components/ThemeToggle.vue";
import type { DevAccount } from "@/types/auth";
import { roleLabel } from "@/types/auth";
import {
    clearRememberLogin,
    loadRememberLogin,
    saveRememberLogin,
} from "@/utils/rememberLogin";
import { Link, useForm } from "@inertiajs/vue3";
import { Eye, EyeOff } from "@lucide/vue";
import { onMounted, ref, watch } from "vue";

defineProps<{
    showDevAccounts?: boolean;
}>();

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const showPassword = ref(false);

const devAccounts: DevAccount[] = [
    {
        email: "learner@app-english.test",
        password: "password",
        role: "learner",
    },
    {
        email: "admin@app-english.test",
        password: "password",
        role: "administrator",
    },
];

onMounted(() => {
    const saved = loadRememberLogin();

    if (saved === null) {
        return;
    }

    form.email = saved.email;
    form.password = saved.password;
    form.remember = true;
});

watch(
    () => form.remember,
    (enabled) => {
        if (!enabled) {
            clearRememberLogin();
        }
    },
);

function submit(): void {
    form.post("/login", {
        onSuccess: () => {
            if (form.remember) {
                saveRememberLogin(form.email, form.password);
            } else {
                clearRememberLogin();
            }
        },
        onFinish: () => {
            if (!form.remember) {
                form.reset("password");
            }
        },
    });
}

function togglePasswordVisibility(): void {
    showPassword.value = !showPassword.value;
}

function fillCredentials(account: DevAccount): void {
    form.email = account.email;
    form.password = account.password;
    form.clearErrors();
}
</script>

<template>
    <div class="surface-page flex min-h-screen flex-col">
        <div class="relative flex flex-1 items-center justify-center p-4 pb-8">
        <div class="absolute right-4 top-4">
            <ThemeToggle />
        </div>

        <div class="w-full max-w-md">
            <div class="surface-card p-8 shadow-lg">
                <div class="mb-8 text-center">
                    <div class="mb-4 flex justify-center">
                        <AppLogo
                            :clickable="false"
                            size="lg"
                        />
                    </div>
                    <h1 class="text-2xl font-bold text-heading">
                        Iniciar sesión
                    </h1>
                    <p class="mt-2 text-muted">
                        Practica inglés para desarrolladores
                    </p>
                </div>

                <form
                    class="space-y-5"
                    @submit.prevent="submit"
                >
                    <div>
                        <label
                            for="email"
                            class="text-label mb-1 block"
                        >
                            Correo
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            class="input-field"
                            :class="{ 'border-red-500 dark:border-red-500': form.errors.email }"
                            @input="form.clearErrors('email')"
                        />
                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="password"
                            class="text-label mb-1 block"
                        >
                            Contraseña
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                class="input-field pr-11"
                                :class="{ 'border-red-500 dark:border-red-500': form.errors.password }"
                                @input="form.clearErrors('password')"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-muted hover:text-body"
                                :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                @click="togglePasswordVisibility"
                            >
                                <EyeOff v-if="showPassword" class="h-5 w-5" />
                                <Eye v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <label class="flex items-start gap-2 text-sm text-body">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                        />
                        <span>
                            Recordarme
                            <span class="mt-0.5 block text-xs text-muted">
                                Guarda correo y contraseña en este navegador por 30 días. La sesión también se mantendrá activa durante ese periodo.
                            </span>
                        </span>
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 font-medium text-white transition-colors hover:bg-blue-700 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
                    >
                        {{ form.processing ? "Entrando..." : "Entrar" }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-muted">
                    ¿No tienes cuenta?
                    <Link
                        href="/register"
                        class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        Regístrate
                    </Link>
                </p>

                <div
                    v-if="showDevAccounts"
                    class="mt-6 border-t border-gray-100 pt-6 dark:border-gray-800"
                >
                    <p class="mb-3 text-center text-xs font-medium uppercase tracking-wide text-amber-600 dark:text-amber-400">
                        Modo desarrollo
                    </p>
                    <div class="space-y-2">
                        <button
                            v-for="account in devAccounts"
                            :key="account.email"
                            type="button"
                            class="w-full rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-left transition-colors hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-800/60 dark:hover:border-blue-600 dark:hover:bg-blue-950/40"
                            @click="fillCredentials(account)"
                        >
                            <p class="text-sm font-medium text-heading">
                                {{ account.email }}
                            </p>
                            <p class="text-sm text-muted">
                                password
                            </p>
                            <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                                {{ roleLabel(account.role) }}
                            </p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <SiteFooter />
    </div>
</template>
