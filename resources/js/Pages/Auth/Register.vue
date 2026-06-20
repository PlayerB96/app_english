<script setup lang="ts">
import ThemeToggle from "@/Components/ThemeToggle.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { Eye, EyeOff, Languages } from "@lucide/vue";
import { computed, ref } from "vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const showPassword = ref(false);

const passwordMismatch = computed(
    () =>
        form.password_confirmation !== ""
        && form.password !== form.password_confirmation,
);

const passwordConfirmationError = computed(
    () => form.errors.password_confirmation
        ?? (passwordMismatch.value ? "Las contraseñas no coinciden." : null),
);

function passwordsMatch(): boolean {
    return form.password === form.password_confirmation;
}

function submit(): void {
    form.clearErrors("password", "password_confirmation");

    if (!passwordsMatch()) {
        form.setError("password_confirmation", "Las contraseñas no coinciden.");

        return;
    }

    form.post("/register", {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
}

function togglePasswordVisibility(): void {
    showPassword.value = !showPassword.value;
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
                            <Languages class="h-10 w-10 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold text-heading">
                        Crear cuenta
                    </h1>
                    <p class="mt-2 text-muted">
                        Regístrate para empezar a practicar
                    </p>
                </div>

                <form
                    class="space-y-5"
                    @submit.prevent="submit"
                >
                    <div>
                        <label
                            for="name"
                            class="text-label mb-1 block"
                        >
                            Nombre
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            required
                            class="input-field"
                            :class="{ 'border-red-500 dark:border-red-500': form.errors.name }"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

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
                                autocomplete="new-password"
                                required
                                class="input-field pr-11"
                                :class="{ 'border-red-500 dark:border-red-500': form.errors.password }"
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

                    <div>
                        <label
                            for="password_confirmation"
                            class="text-label mb-1 block"
                        >
                            Confirmar contraseña
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="new-password"
                            required
                            class="input-field"
                            :class="{ 'border-red-500 dark:border-red-500': form.errors.password_confirmation || passwordMismatch }"
                        />
                        <p
                            v-if="passwordConfirmationError"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ passwordConfirmationError }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || passwordMismatch"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 font-medium text-white transition-colors hover:bg-blue-700 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
                    >
                        {{ form.processing ? "Creando cuenta..." : "Registrarme" }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-muted">
                    ¿Ya tienes cuenta?
                    <Link
                        href="/login"
                        class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        Inicia sesión
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
