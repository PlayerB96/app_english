<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { Eye, EyeOff } from "@lucide/vue";
import { ref } from "vue";

const form = useForm({
    username: "",
    password: "",
    remember: false,
});

const showPassword = ref(false);

function submit(): void {
    form.post("/login", {
        onFinish: () => form.reset("password"),
    });
}

function togglePasswordVisibility(): void {
    showPassword.value = !showPassword.value;
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="text-center mb-8">
                    <div class="text-5xl mb-4">🛒</div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Iniciar sesión
                    </h1>
                    <p class="text-gray-500 mt-2">
                        Accede al punto de venta
                    </p>
                </div>

                <form class="space-y-5" @submit.prevent="submit">
                    <div>
                        <label
                            for="username"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Usuario
                        </label>
                        <input
                            id="username"
                            v-model="form.username"
                            type="text"
                            autocomplete="username"
                            maxlength="20"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                            :class="{ 'border-red-500': form.errors.username }"
                        />
                        <p
                            v-if="form.errors.username"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.username }}
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
                                maxlength="15"
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
            </div>
        </div>
    </div>
</template>
