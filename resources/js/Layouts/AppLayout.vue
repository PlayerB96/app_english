<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import type { PageProps } from "@/types/auth";

const page = usePage<{ auth: PageProps["auth"] }>();

const user = computed(() => page.props.auth.user);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header
            v-if="user"
            class="bg-white border-b border-gray-200 px-4 py-3 md:px-6"
        >
            <div
                class="max-w-5xl mx-auto flex items-center justify-between gap-4"
            >
                <Link
                    href="/dashboard"
                    class="text-lg font-bold text-gray-900"
                >
                    Caja Rápida
                </Link>
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <span>{{ user.name }}</span>
                    <span
                        class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 capitalize"
                    >
                        {{ user.role }}
                    </span>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="text-red-600 hover:text-red-700 font-medium"
                    >
                        Salir
                    </Link>
                </div>
            </div>
        </header>

        <main class="p-4 md:p-6">
            <slot />
        </main>
    </div>
</template>
