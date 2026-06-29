<script setup lang="ts">
import LegalLayout from "@/Layouts/LegalLayout.vue";
import type {
    ComplaintDocumentType,
    ComplaintItemType,
    ComplaintType,
    LegalConfig,
} from "@/types/legal";
import { Head, useForm } from "@inertiajs/vue3";

defineProps<{
    legal: LegalConfig;
}>();

const form = useForm({
    consumer_name: "",
    document_type: "dni" as ComplaintDocumentType,
    document_number: "",
    address: "",
    email: "",
    phone: "",
    item_type: "servicio" as ComplaintItemType,
    amount: "" as string | number,
    complaint_type: "reclamo" as ComplaintType,
    description: "",
    order_reference: "",
});

function submit(): void {
    form.post("/legal/reclamaciones", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.document_type = "dni";
            form.item_type = "servicio";
            form.complaint_type = "reclamo";
        },
    });
}
</script>

<template>
    <Head title="Libro de reclamaciones" />

    <LegalLayout
        title="Libro de reclamaciones virtual"
        :description="`Conforme al Código de Protección y Defensa del Consumidor. ${legal.business.name} — RUC ${legal.business.ruc}.`"
    >
        <div class="legal-complaint-intro">
            <p>
                <strong>Reclamo:</strong> disconformidad vinculada a un producto o servicio, con solicitud de solución.
            </p>
            <p>
                <strong>Queja:</strong> malestar o descontento respecto a la atención, sin pedir necesariamente una compensación.
            </p>
            <p class="text-sm text-muted">
                Te responderemos en un plazo máximo de {{ legal.complaint_response_days }} días calendario
                al correo que indiques.
            </p>
        </div>

        <form
            class="legal-complaint-form"
            @submit.prevent="submit"
        >
            <div class="legal-form-grid">
                <div class="legal-form-field legal-form-field--full">
                    <label
                        for="consumer_name"
                        class="text-label"
                    >
                        Nombre completo del consumidor
                    </label>
                    <input
                        id="consumer_name"
                        v-model="form.consumer_name"
                        type="text"
                        required
                        class="input-field"
                        :class="{ 'border-red-500 dark:border-red-500': form.errors.consumer_name }"
                    />
                    <p
                        v-if="form.errors.consumer_name"
                        class="legal-form-error"
                    >
                        {{ form.errors.consumer_name }}
                    </p>
                </div>

                <div class="legal-form-field">
                    <label
                        for="document_type"
                        class="text-label"
                    >
                        Tipo de documento
                    </label>
                    <select
                        id="document_type"
                        v-model="form.document_type"
                        class="input-field"
                    >
                        <option value="dni">
                            DNI
                        </option>
                        <option value="ce">
                            Carné de extranjería
                        </option>
                        <option value="pasaporte">
                            Pasaporte
                        </option>
                        <option value="ruc">
                            RUC
                        </option>
                    </select>
                </div>

                <div class="legal-form-field">
                    <label
                        for="document_number"
                        class="text-label"
                    >
                        Número de documento
                    </label>
                    <input
                        id="document_number"
                        v-model="form.document_number"
                        type="text"
                        required
                        class="input-field"
                        :class="{ 'border-red-500 dark:border-red-500': form.errors.document_number }"
                    />
                    <p
                        v-if="form.errors.document_number"
                        class="legal-form-error"
                    >
                        {{ form.errors.document_number }}
                    </p>
                </div>

                <div class="legal-form-field legal-form-field--full">
                    <label
                        for="address"
                        class="text-label"
                    >
                        Domicilio
                    </label>
                    <input
                        id="address"
                        v-model="form.address"
                        type="text"
                        required
                        class="input-field"
                        :class="{ 'border-red-500 dark:border-red-500': form.errors.address }"
                    />
                    <p
                        v-if="form.errors.address"
                        class="legal-form-error"
                    >
                        {{ form.errors.address }}
                    </p>
                </div>

                <div class="legal-form-field">
                    <label
                        for="email"
                        class="text-label"
                    >
                        Correo electrónico
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="input-field"
                        :class="{ 'border-red-500 dark:border-red-500': form.errors.email }"
                    />
                    <p
                        v-if="form.errors.email"
                        class="legal-form-error"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="legal-form-field">
                    <label
                        for="phone"
                        class="text-label"
                    >
                        Teléfono (opcional)
                    </label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="input-field"
                    />
                </div>

                <div class="legal-form-field">
                    <label
                        for="item_type"
                        class="text-label"
                    >
                        Bien contratado
                    </label>
                    <select
                        id="item_type"
                        v-model="form.item_type"
                        class="input-field"
                    >
                        <option value="servicio">
                            Servicio
                        </option>
                        <option value="producto">
                            Producto
                        </option>
                    </select>
                </div>

                <div class="legal-form-field">
                    <label
                        for="complaint_type"
                        class="text-label"
                    >
                        Tipo de solicitud
                    </label>
                    <select
                        id="complaint_type"
                        v-model="form.complaint_type"
                        class="input-field"
                    >
                        <option value="reclamo">
                            Reclamo
                        </option>
                        <option value="queja">
                            Queja
                        </option>
                    </select>
                </div>

                <div class="legal-form-field">
                    <label
                        for="amount"
                        class="text-label"
                    >
                        Monto reclamado (S/) — opcional
                    </label>
                    <input
                        id="amount"
                        v-model="form.amount"
                        type="number"
                        min="0"
                        step="0.01"
                        class="input-field"
                    />
                </div>

                <div class="legal-form-field">
                    <label
                        for="order_reference"
                        class="text-label"
                    >
                        Pedido o referencia — opcional
                    </label>
                    <input
                        id="order_reference"
                        v-model="form.order_reference"
                        type="text"
                        class="input-field"
                        placeholder="Ej. compra de 300 poder"
                    />
                </div>

                <div class="legal-form-field legal-form-field--full">
                    <label
                        for="description"
                        class="text-label"
                    >
                        Detalle del reclamo o queja
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        required
                        rows="6"
                        class="input-field resize-y"
                        :class="{ 'border-red-500 dark:border-red-500': form.errors.description }"
                    />
                    <p
                        v-if="form.errors.description"
                        class="legal-form-error"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>
            </div>

            <p class="legal-form-note">
                Al enviar, declaras que la información es veraz. {{ legal.business.name }} tratará tus datos
                conforme a la <a href="/legal/privacidad">política de privacidad</a>.
            </p>

            <button
                type="submit"
                :disabled="form.processing"
                class="legal-form-submit"
            >
                {{ form.processing ? "Registrando..." : "Registrar en el libro de reclamaciones" }}
            </button>
        </form>
    </LegalLayout>
</template>
