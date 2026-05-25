<script setup lang="ts">
defineProps<{
    laravelVersion: string;
    phpVersion: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="text-5xl mb-4">🛒</div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    Caja Rápida
                </h1>
                <p class="text-gray-500 mb-6">
                    Sistema de punto de venta móvil
                </p>

                <div class="bg-blue-50 rounded-lg p-4 text-sm text-blue-700">
                    <p>Laravel {{ laravelVersion }} · PHP {{ phpVersion }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
