<script setup lang="ts">
import LegalLayout from "@/Layouts/LegalLayout.vue";
import type { LegalConfig } from "@/types/legal";
import { Head } from "@inertiajs/vue3";

defineProps<{
    legal: LegalConfig;
}>();
</script>

<template>
    <Head title="Devoluciones y reembolsos" />

    <LegalLayout
        title="Política de devoluciones y reembolsos"
        description="Condiciones aplicables a compras de créditos de poder en la plataforma."
    >
        <p class="legal-updated">
            Última actualización: junio de 2026
        </p>

        <section>
            <h2>1. Naturaleza del producto</h2>
            <p>
                Los paquetes de «poder» son créditos virtuales para desbloquear funciones dentro de
                {{ legal.business.name }}. Al acreditarse en tu cuenta, el servicio digital contratado
                se considera en proceso de entrega inmediata.
            </p>
        </section>

        <section>
            <h2>2. Antes de pagar</h2>
            <ul>
                <li>Revisa el monto, paquete y número de destino antes de confirmar el pago.</li>
                <li>Sube un comprobante legible que muestre fecha, monto y operación.</li>
                <li>La acreditación depende de validación manual y puede tomar hasta 24 horas hábiles.</li>
            </ul>
        </section>

        <section>
            <h2>3. Cuándo procede un reembolso</h2>
            <p>Podremos reembolsar el monto pagado cuando:</p>
            <ul>
                <li>Se cobró dos veces por el mismo pedido debidamente acreditado.</li>
                <li>Transcurrieron más de 48 horas hábiles sin acreditación y el pago fue verificado.</li>
                <li>Hubo error nuestro en el monto o paquete acreditado.</li>
                <li>La ley aplicable exija la devolución.</li>
            </ul>
        </section>

        <section>
            <h2>4. Cuándo no procede</h2>
            <ul>
                <li>El poder ya fue consumido total o parcialmente en la cuenta.</li>
                <li>El comprobante es inválido, alterado o no corresponde al pedido.</li>
                <li>El pago se realizó a un número distinto al indicado oficialmente en la Plataforma.</li>
                <li>Solicitudes por arrepentimiento una vez acreditados y usados los créditos.</li>
            </ul>
        </section>

        <section>
            <h2>5. Procedimiento</h2>
            <p>
                Escríbenos a
                <a :href="`mailto:${legal.business.email}`">{{ legal.business.email }}</a>
                o registra un <a href="/legal/reclamaciones">reclamo en el libro virtual</a> indicando
                número de operación, fecha, monto y correo de tu cuenta. Responderemos en un plazo máximo
                de {{ legal.complaint_response_days }} días calendario.
            </p>
            <p>
                Los reembolsos aprobados se realizarán por el mismo medio de pago cuando sea posible,
                dentro de 15 días hábiles desde la aprobación.
            </p>
        </section>

        <section>
            <h2>6. Comprobantes de pago</h2>
            <p>
                Emitimos boleta o factura electrónica cuando corresponda según la normativa tributaria
                vigente. Para solicitar tu comprobante, contáctanos con los datos de tu compra.
            </p>
        </section>
    </LegalLayout>
</template>
