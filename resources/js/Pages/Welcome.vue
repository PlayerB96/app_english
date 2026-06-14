<script setup lang="ts">
import { BookOpen, Languages } from "@lucide/vue";

defineProps<{
    laravelVersion: string;
    phpVersion: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="mb-4 flex justify-center">
                    <div class="relative rounded-full bg-blue-50 p-4">
                        <Languages class="h-10 w-10 text-blue-600" />
                        <BookOpen
                            class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full bg-white p-0.5 text-blue-500"
                        />
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    Dev English
                </h1>
                <p class="text-gray-500 mb-6">
                    Aprende inglés para programadores con IA y práctica por voz
                </p>

                <div class="bg-blue-50 rounded-lg p-4 text-sm text-blue-700">
                    <p>Laravel {{ laravelVersion }} · PHP {{ phpVersion }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
