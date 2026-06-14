<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import type { PageProps } from "@/types/auth";

const page = usePage<{ auth: PageProps["auth"] }>();

const user = computed(() => page.props.auth.user);
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    Panel de control
                </h1>
                <p class="text-gray-500 mb-6">
                    Tu progreso de inglés para desarrollo de software.
                </p>

                <div
                    v-if="user"
                    class="rounded-lg bg-blue-50 p-4 text-sm text-blue-800"
                >
                    <p>
                        Sesión activa como
                        <strong>{{ user.name }}</strong>
                        ({{ user.role }}).
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
