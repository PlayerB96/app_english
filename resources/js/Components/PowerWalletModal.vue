<script setup lang="ts">
import PowerIcon from "@/Components/PowerIcon.vue";
import type { PowerShopConfig, PowerShopPackage } from "@/types/powerShop";
import { POWER_UNIT, powerBalanceLabel } from "@/utils/powerLabels";
import { router, usePage } from "@inertiajs/vue3";
import { Check, Copy, ImagePlus, Loader2, Upload, X } from "@lucide/vue";
import { computed, onBeforeUnmount, ref, watch } from "vue";
import type { PageProps } from "@/types/auth";

const props = defineProps<{
    open: boolean;
    tokens: number;
    shop: PowerShopConfig;
}>();

const emit = defineEmits<{
    close: [];
}>();

type PaymentMethod = "yape" | "plin";

const page = usePage<PageProps>();

const selectedPackage = ref<PowerShopPackage | null>(null);
const paymentMethod = ref<PaymentMethod>("yape");
const receiptFile = ref<File | null>(null);
const receiptPreview = ref<string | null>(null);
const submitting = ref(false);
const copied = ref(false);
const acceptedTerms = ref(false);
const redeemCode = ref("");
const redeeming = ref(false);

const canRedeem = computed(
    () => /^[A-Za-z0-9]{3}$/.test(redeemCode.value) && !redeeming.value && !submitting.value,
);

function sanitizeRedeemCode(value: string): string {
    return value.replace(/[^A-Za-z0-9]/g, "").slice(0, 3).toUpperCase();
}

const activePayment = computed(() =>
    paymentMethod.value === "yape" ? props.shop.yape : props.shop.plin,
);

const canSubmit = computed(
    () =>
        selectedPackage.value !== null
        && receiptFile.value !== null
        && acceptedTerms.value
        && !submitting.value,
);

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            resetForm();

            return;
        }

        selectedPackage.value = props.shop.packages[0] ?? null;
    },
);

watch(receiptFile, (file) => {
    if (receiptPreview.value) {
        URL.revokeObjectURL(receiptPreview.value);
        receiptPreview.value = null;
    }

    if (file) {
        receiptPreview.value = URL.createObjectURL(file);
    }
});

onBeforeUnmount(() => {
    if (receiptPreview.value) {
        URL.revokeObjectURL(receiptPreview.value);
    }

    window.removeEventListener("keydown", onKeydown);
});

function resetForm(): void {
    selectedPackage.value = null;
    paymentMethod.value = "yape";
    receiptFile.value = null;
    submitting.value = false;
    copied.value = false;
    acceptedTerms.value = false;
    redeemCode.value = "";
    redeeming.value = false;

    if (receiptPreview.value) {
        URL.revokeObjectURL(receiptPreview.value);
        receiptPreview.value = null;
    }
}

function closeModal(): void {
    if (submitting.value) {
        return;
    }

    emit("close");
}

function selectPackage(pack: PowerShopPackage): void {
    selectedPackage.value = pack;
}

function onReceiptChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    receiptFile.value = input.files?.[0] ?? null;
}

function clearReceipt(): void {
    receiptFile.value = null;
}

async function copyPhone(): Promise<void> {
    const phone = activePayment.value.phone.replace(/\s+/g, "");

    try {
        await navigator.clipboard.writeText(phone);
        copied.value = true;
        window.setTimeout(() => {
            copied.value = false;
        }, 1800);
    } catch {
        copied.value = false;
    }
}

function submitRedeem(): void {
    if (!canRedeem.value) {
        return;
    }

    redeeming.value = true;

    router.post(
        "/power-shop/redeem",
        { code: redeemCode.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                redeemCode.value = "";
            },
            onFinish: () => {
                redeeming.value = false;
            },
        },
    );
}

function submitPurchase(): void {
    if (!selectedPackage.value || !receiptFile.value || submitting.value || !acceptedTerms.value) {
        return;
    }

    submitting.value = true;

    const formData = new FormData();
    formData.append("power_amount", String(selectedPackage.value.power));
    formData.append("payment_method", paymentMethod.value);
    formData.append("receipt", receiptFile.value);

    router.post("/power-shop/purchases", formData, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            emit("close");
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
}

function handleBackdropClick(event: MouseEvent): void {
    if (event.target === event.currentTarget) {
        closeModal();
    }
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === "Escape") {
        closeModal();
    }
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            window.addEventListener("keydown", onKeydown);
        } else {
            window.removeEventListener("keydown", onKeydown);
        }
    },
);

const receiptError = computed(() => page.props.errors?.receipt ?? null);
const redeemError = computed(() => page.props.errors?.redeem_code ?? null);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-3 backdrop-blur-sm dark:bg-black/60"
            @click="handleBackdropClick"
        >
            <div
                class="flex w-full max-w-md flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900"
                role="dialog"
                aria-modal="true"
                aria-labelledby="power-wallet-title"
            >
                <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-muted">
                            Wallet
                        </p>
                        <h2
                            id="power-wallet-title"
                            class="text-base font-bold text-heading"
                        >
                            Comprar {{ POWER_UNIT }}
                        </h2>
                        <p class="mt-0.5 inline-flex items-center gap-1 text-xs text-muted">
                            <PowerIcon size-class="h-3 w-3" />
                            Saldo: <strong class="text-orange-700 dark:text-orange-300">{{ powerBalanceLabel(tokens) }}</strong>
                        </p>
                    </div>
                    <button
                        type="button"
                        class="btn-theme h-8 w-8 shrink-0"
                        aria-label="Cerrar"
                        @click="closeModal"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-3 px-4 py-3">
                    <div>
                        <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-muted">
                            Paquete
                        </p>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button
                                v-for="pack in shop.packages"
                                :key="pack.power"
                                type="button"
                                class="rounded-lg border px-1.5 py-1.5 text-center transition-colors"
                                :class="
                                    selectedPackage?.power === pack.power
                                        ? 'border-orange-400 bg-orange-50 dark:border-orange-500 dark:bg-orange-950/40'
                                        : 'border-gray-200 bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800/60 dark:hover:border-gray-600'
                                "
                                @click="selectPackage(pack)"
                            >
                                <span class="block text-xs font-bold tabular-nums text-orange-700 dark:text-orange-300">
                                    +{{ pack.power }}
                                </span>
                                <span class="block text-[10px] font-semibold text-heading">
                                    S/ {{ pack.soles }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="selectedPackage"
                        class="rounded-xl border border-gray-200 bg-gray-50/80 p-2.5 dark:border-gray-700 dark:bg-gray-800/50"
                    >
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                                Pago · QR
                            </p>
                            <div class="inline-flex rounded-md border border-gray-200 bg-white p-0.5 dark:border-gray-600 dark:bg-gray-900">
                                <button
                                    type="button"
                                    class="rounded px-2 py-0.5 text-[11px] font-semibold transition-colors"
                                    :class="
                                        paymentMethod === 'yape'
                                            ? 'bg-violet-600 text-white'
                                            : 'text-muted hover:text-heading'
                                    "
                                    @click="paymentMethod = 'yape'"
                                >
                                    Yape
                                </button>
                                <button
                                    type="button"
                                    class="rounded px-2 py-0.5 text-[11px] font-semibold transition-colors"
                                    :class="
                                        paymentMethod === 'plin'
                                            ? 'bg-sky-600 text-white'
                                            : 'text-muted hover:text-heading'
                                    "
                                    @click="paymentMethod = 'plin'"
                                >
                                    Plin
                                </button>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <div class="shrink-0 rounded-lg border border-gray-200 bg-white p-1.5 dark:border-gray-600 dark:bg-gray-950">
                                <img
                                    :src="activePayment.qr_image"
                                    :alt="`QR ${paymentMethod === 'yape' ? 'Yape' : 'Plin'}`"
                                    class="h-[4.75rem] w-[4.75rem] object-contain"
                                >
                                <p class="mt-0.5 text-center text-[9px] font-semibold text-muted">
                                    S/ {{ selectedPackage.soles }}
                                </p>
                            </div>

                            <div class="min-w-0 flex-1 space-y-1.5">
                                <p class="text-[11px] leading-snug text-body">
                                    Escanea o envía el monto exacto a
                                    <strong class="text-heading">{{ activePayment.holder }}</strong>.
                                </p>
                                <div class="flex items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-2 py-1.5 dark:border-gray-600 dark:bg-gray-900">
                                    <p class="truncate text-sm font-bold tabular-nums text-heading">
                                        {{ activePayment.phone }}
                                    </p>
                                    <button
                                        type="button"
                                        class="inline-flex shrink-0 items-center gap-0.5 rounded-md border border-gray-200 px-1.5 py-0.5 text-[10px] font-semibold text-body hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-800"
                                        @click="copyPhone"
                                    >
                                        <Check
                                            v-if="copied"
                                            class="h-3 w-3"
                                        />
                                        <Copy
                                            v-else
                                            class="h-3 w-3"
                                        />
                                        {{ copied ? "OK" : "Copiar" }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedPackage">
                        <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-muted">
                            Comprobante
                        </p>

                        <label
                            v-if="!receiptPreview"
                            class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-dashed border-gray-300 bg-white px-3 py-2 dark:border-gray-600 dark:bg-gray-900/60"
                        >
                            <ImagePlus class="h-5 w-5 shrink-0 text-muted" />
                            <span class="min-w-0">
                                <span class="block text-xs font-medium text-heading">
                                    Subir captura
                                </span>
                                <span class="block text-[10px] text-muted">
                                    JPG, PNG o WebP · máx. 5 MB
                                </span>
                            </span>
                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="sr-only"
                                @change="onReceiptChange"
                            >
                        </label>

                        <div
                            v-else
                            class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-700 dark:bg-gray-900/60"
                        >
                            <img
                                :src="receiptPreview"
                                alt="Vista previa del comprobante"
                                class="h-12 w-12 shrink-0 rounded-md border border-gray-200 object-cover dark:border-gray-700"
                            >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium text-heading">
                                    {{ receiptFile?.name }}
                                </p>
                                <button
                                    type="button"
                                    class="text-[10px] font-semibold text-red-600 dark:text-red-400"
                                    @click="clearReceipt"
                                >
                                    Quitar
                                </button>
                            </div>
                        </div>

                        <p
                            v-if="receiptError"
                            class="mt-1 text-[11px] text-red-600 dark:text-red-400"
                        >
                            {{ receiptError }}
                        </p>
                    </div>

                    <label
                        v-if="selectedPackage"
                        class="flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50/70 px-2.5 py-2 dark:border-gray-700 dark:bg-gray-800/40"
                    >
                        <input
                            v-model="acceptedTerms"
                            type="checkbox"
                            class="mt-0.5 h-3.5 w-3.5 shrink-0 rounded border-gray-300 text-orange-600 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-900"
                        >
                        <span class="text-[10px] leading-snug text-muted">
                            Acepto que el pago se valida manualmente en un plazo de 24 h.
                            El {{ POWER_UNIT }} se acredita tras verificar el comprobante.
                            No hay reembolsos por pagos duplicados o montos incorrectos.
                        </span>
                    </label>

                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/70 px-2.5 py-2.5 dark:border-gray-700 dark:bg-gray-800/40">
                        <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-muted">
                            Canjear código · +500 poder
                        </p>
                        <div class="flex items-center gap-1.5">
                            <input
                                v-model="redeemCode"
                                type="text"
                                autocapitalize="characters"
                                autocomplete="off"
                                spellcheck="false"
                                maxlength="3"
                                placeholder="_ _ _"
                                class="input-field h-8 w-[4.25rem] shrink-0 text-center text-sm font-bold uppercase tracking-widest"
                                :class="{ 'border-red-500 dark:border-red-500': redeemError }"
                                @input="redeemCode = sanitizeRedeemCode(redeemCode)"
                            >
                            <button
                                type="button"
                                class="inline-flex h-8 shrink-0 items-center rounded-lg bg-gray-900 px-2.5 text-[11px] font-semibold text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white"
                                :disabled="!canRedeem"
                                @click="submitRedeem"
                            >
                                <Loader2
                                    v-if="redeeming"
                                    class="h-3.5 w-3.5 animate-spin"
                                />
                                <span v-else>Canjear</span>
                            </button>
                        </div>
                        <p
                            v-if="redeemError"
                            class="mt-1 text-[11px] text-red-600 dark:text-red-400"
                        >
                            {{ redeemError }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                    <button
                        type="button"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold text-muted hover:text-heading"
                        :disabled="submitting"
                        @click="closeModal"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-orange-500 dark:hover:bg-orange-600"
                        :disabled="!canSubmit"
                        @click="submitPurchase"
                    >
                        <Loader2
                            v-if="submitting"
                            class="h-3.5 w-3.5 animate-spin"
                        />
                        <Upload
                            v-else
                            class="h-3.5 w-3.5"
                        />
                        {{ submitting ? "Enviando…" : "Enviar" }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
