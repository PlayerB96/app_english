<script setup lang="ts">
import PowerIcon from "@/Components/PowerIcon.vue";
import PowerWalletModal from "@/Components/PowerWalletModal.vue";
import { powerBalanceLabel } from "@/utils/powerLabels";
import type { PageProps } from "@/types/auth";
import { ShoppingBag } from "@lucide/vue";
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps<{
    tokens: number;
}>();

const page = usePage<PageProps>();

const walletOpen = ref(false);

const powerShop = computed(() => page.props.game.power_shop);

function openWallet(): void {
    walletOpen.value = true;
}

function closeWallet(): void {
    walletOpen.value = false;
}
</script>

<template>
    <div class="header-power-vault group">
        <div class="header-power-vault-track">
            <span
                class="header-power-badge"
                :title="`${powerBalanceLabel(tokens)} disponible`"
            >
                <PowerIcon size-class="h-3.5 w-3.5" />
                <span>{{ tokens }}</span>
                <span class="capitalize">poder</span>
            </span>

            <button
                type="button"
                class="header-power-vault-secret"
                title="Comprar poder · Wallet"
                aria-label="Comprar poder · Wallet"
                @click="openWallet"
            >
                <ShoppingBag class="header-shop-icon" />
            </button>
        </div>

        <PowerWalletModal
            :open="walletOpen"
            :tokens="tokens"
            :shop="powerShop"
            @close="closeWallet"
        />
    </div>
</template>
