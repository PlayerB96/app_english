<script setup lang="ts">
import LegalLayout from "@/Layouts/LegalLayout.vue";
import type { LegalConfig } from "@/types/legal";
import { Head } from "@inertiajs/vue3";

defineProps<{
    legal: LegalConfig;
}>();
</script>

<template>
    <Head title="Política de privacidad" />

    <LegalLayout
        title="Política de privacidad"
        description="Cómo recopilamos, usamos y protegemos tus datos personales conforme a la Ley N.° 29733 y su reglamento."
    >
        <p class="legal-updated">
            Última actualización: junio de 2026
        </p>

        <section>
            <h2>1. Responsable del tratamiento</h2>
            <p>
                <strong>{{ legal.business.name }}</strong>, RUC {{ legal.business.ruc }},
                domicilio {{ legal.business.address }}, correo
                <a :href="`mailto:${legal.business.email}`">{{ legal.business.email }}</a>.
            </p>
        </section>

        <section>
            <h2>2. Datos que recopilamos</h2>
            <ul>
                <li><strong>Registro:</strong> nombre, correo electrónico y contraseña (almacenada cifrada).</li>
                <li><strong>Uso de la plataforma:</strong> progreso de niveles, respuestas, métricas de práctica y tokens de juego.</li>
                <li><strong>Compras:</strong> datos de la solicitud de compra y comprobante de pago (imagen) que subes voluntariamente.</li>
                <li><strong>Técnicos:</strong> dirección IP, navegador, cookies de sesión y registros de seguridad.</li>
                <li><strong>Voz (si la activas):</strong> audio procesado en tu navegador o por APIs compatibles para transcripción.</li>
            </ul>
        </section>

        <section>
            <h2>3. Finalidades</h2>
            <ul>
                <li>Prestación del servicio educativo y gestión de tu cuenta.</li>
                <li>Seguimiento de progreso y personalización de la experiencia.</li>
                <li>Validación de pagos y atención de consultas o reclamos.</li>
                <li>Seguridad, prevención de fraude y cumplimiento legal.</li>
                <li>Comunicaciones operativas (verificación de correo, avisos del servicio).</li>
            </ul>
        </section>

        <section>
            <h2>4. Base legal</h2>
            <p>
                Tratamos tus datos con base en la ejecución del contrato de servicios, tu consentimiento
                (cuando corresponda), el interés legítimo en mejorar y asegurar la plataforma, y el
                cumplimiento de obligaciones legales (tributarias, de consumo y protección de datos).
            </p>
        </section>

        <section>
            <h2>5. Encargados y transferencias</h2>
            <p>
                Podemos compartir datos con proveedores que nos ayudan a operar el servicio (hosting,
                correo, APIs de inteligencia artificial), bajo contratos que exigen confidencialidad y
                seguridad. Si algún proveedor procesa datos fuera del Perú, adoptaremos medidas razonables
                de protección conforme a la normativa vigente.
            </p>
        </section>

        <section>
            <h2>6. Conservación</h2>
            <p>
                Conservamos los datos mientras mantengas tu cuenta activa y el tiempo necesario para
                obligaciones legales, defensa de reclamos y respaldo de transacciones comerciales.
                Los comprobantes de pago se conservan el plazo necesario para conciliación y auditoría.
            </p>
        </section>

        <section>
            <h2>7. Tus derechos</h2>
            <p>
                Puedes ejercer los derechos de acceso, rectificación, cancelación, oposición, información,
                revocación del consentimiento y portabilidad escribiendo a
                <a :href="`mailto:${legal.business.email}`">{{ legal.business.email }}</a>.
                También puedes presentar una reclamación ante la Autoridad Nacional de Protección de Datos Personales.
            </p>
        </section>

        <section>
            <h2>8. Seguridad</h2>
            <p>
                Aplicamos medidas técnicas y organizativas razonables (cifrado de contraseñas, control de
                acceso, HTTPS). Ningún sistema es 100 % infalible; te recomendamos usar contraseñas fuertes
                y no compartir tu cuenta.
            </p>
        </section>

        <section>
            <h2>9. Menores de edad</h2>
            <p>
                El servicio no está dirigido a menores de 14 años sin consentimiento de padre o tutor.
                Si detectamos datos de menores recopilados indebidamente, procederemos a eliminarlos.
            </p>
        </section>

        <section>
            <h2>10. Cambios</h2>
            <p>
                Publicaremos las actualizaciones en esta página. Los cambios relevantes podrán notificarse
                por correo o dentro de la aplicación.
            </p>
        </section>
    </LegalLayout>
</template>
