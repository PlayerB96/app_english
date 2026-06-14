<script setup lang="ts">
import type { DevAccount } from "@/types/auth";
import { useForm } from "@inertiajs/vue3";
import { Eye, EyeOff, Languages } from "@lucide/vue";
import { ref } from "vue";

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

function submit(): void {
    form.post("/login", {
        onFinish: () => form.reset("password"),
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
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="text-center mb-8">
                    <div class="mb-4 flex justify-center">
                        <div class="rounded-full bg-blue-50 p-4">
                            <Languages class="h-10 w-10 text-blue-600" />
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Iniciar sesión
                    </h1>
                    <p class="text-gray-500 mt-2">
                        Practica inglés para desarrolladores
                    </p>
                </div>

                <form class="space-y-5" @submit.prevent="submit">
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Correo
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700 mb-1"
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
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-11 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                                :class="{ 'border-red-500': form.errors.password }"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700"
                                :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                @click="togglePasswordVisibility"
                            >
                                <EyeOff v-if="showPassword" class="h-5 w-5" />
                                <Eye v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        Recordarme
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-white font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors"
                    >
                        {{ form.processing ? "Entrando..." : "Entrar" }}
                    </button>
                </form>

                <div
                    v-if="showDevAccounts"
                    class="mt-6 border-t border-gray-100 pt-6"
                >
                    <p class="mb-3 text-center text-xs font-medium uppercase tracking-wide text-amber-600">
                        Modo desarrollo
                    </p>
                    <div class="space-y-2">
                        <button
                            v-for="account in devAccounts"
                            :key="account.email"
                            type="button"
                            class="w-full rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-left transition-colors hover:border-blue-300 hover:bg-blue-50"
                            @click="fillCredentials(account)"
                        >
                            <p class="text-sm font-medium text-gray-900">
                                {{ account.email }}
                            </p>
                            <p class="text-sm text-gray-500">
                                password
                            </p>
                            <p class="mt-1 text-xs capitalize text-blue-600">
                                {{ account.role }}
                            </p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
