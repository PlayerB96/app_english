<script setup lang="ts">
import LegalLayout from "@/Layouts/LegalLayout.vue";
import type { LegalConfig } from "@/types/legal";
import { Head } from "@inertiajs/vue3";

defineProps<{
    legal: LegalConfig;
}>();
</script>

<template>
    <Head title="Datos del proveedor" />

    <LegalLayout
        title="Datos del proveedor de servicios"
        description="Información exigida para comercio electrónico y protección al consumidor en el Perú."
    >
        <section>
            <h2>Identificación del titular</h2>
            <dl class="legal-dl">
                <div>
                    <dt>Razón social / nombre comercial</dt>
                    <dd>{{ legal.business.name }}</dd>
                </div>
                <div>
                    <dt>RUC</dt>
                    <dd>{{ legal.business.ruc }}</dd>
                </div>
                <div>
                    <dt>Domicilio fiscal</dt>
                    <dd>{{ legal.business.address }}</dd>
                </div>
                <div>
                    <dt>Correo electrónico</dt>
                    <dd>
                        <a :href="`mailto:${legal.business.email}`">{{ legal.business.email }}</a>
                    </dd>
                </div>
                <div v-if="legal.business.phone">
                    <dt>Teléfono</dt>
                    <dd>{{ legal.business.phone }}</dd>
                </div>
            </dl>
        </section>

        <section>
            <h2>Actividad</h2>
            <p>
                Prestación de servicios educativos digitales de idioma inglés orientados a profesionales
                de tecnología, incluyendo práctica interactiva, contenido gamificado y venta de créditos
                virtuales internos («poder») para funciones premium de la plataforma.
            </p>
        </section>

        <section>
            <h2>Medios de pago</h2>
            <p>
                Las compras de créditos de poder pueden realizarse mediante transferencias a los números
                de Yape o Plin publicados dentro de la aplicación. Los montos se expresan en soles (PEN)
                e incluyen la información del paquete antes de confirmar.
            </p>
        </section>

        <section>
            <h2>Atención al consumidor</h2>
            <ul>
                <li>
                    <strong>Libro de reclamaciones virtual:</strong>
                    <a href="/legal/reclamaciones">/legal/reclamaciones</a>
                </li>
                <li>
                    <strong>Plazo de respuesta:</strong>
                    {{ legal.complaint_response_days }} días calendario desde el registro del reclamo o queja.
                </li>
                <li>
                    <strong>Horario de atención por correo:</strong>
                    lunes a viernes, 9:00 a 18:00 (hora de Lima), salvo feriados.
                </li>
            </ul>
        </section>

        <section>
            <h2>Documentos legales</h2>
            <ul>
                <li><a href="/legal/terminos">Términos y condiciones de uso</a></li>
                <li><a href="/legal/privacidad">Política de privacidad</a></li>
                <li><a href="/legal/devoluciones">Política de devoluciones y reembolsos</a></li>
            </ul>
        </section>
    </LegalLayout>
</template>
