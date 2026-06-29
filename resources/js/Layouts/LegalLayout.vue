<script setup lang="ts">
import AppFlash from "@/Components/AppFlash.vue";
import AppLogo from "@/Components/AppLogo.vue";
import SiteFooter from "@/Components/SiteFooter.vue";
import ThemeToggle from "@/Components/ThemeToggle.vue";
import { Link } from "@inertiajs/vue3";

defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="surface-page flex min-h-screen flex-col">
        <header class="legal-layout-header">
            <div class="app-container-wide legal-layout-header__inner">
                <AppLogo class="legal-layout-brand" />

                <div class="flex items-center gap-3">
                    <Link
                        href="/login"
                        class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        Iniciar sesión
                    </Link>
                    <ThemeToggle />
                </div>
            </div>
        </header>

        <main class="flex-1 py-8 md:py-12">
            <div class="app-container-wide mx-auto max-w-4xl">
                <AppFlash />

                <header class="mb-8">
                    <h1 class="text-3xl font-bold text-heading md:text-4xl">
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="mt-3 text-body"
                    >
                        {{ description }}
                    </p>
                </header>

                <div class="legal-prose surface-card p-6 md:p-8">
                    <slot />
                </div>
            </div>
        </main>

        <SiteFooter />
    </div>
</template>
