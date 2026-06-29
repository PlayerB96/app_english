<script setup lang="ts">
import LegalLayout from "@/Layouts/LegalLayout.vue";
import type { LegalConfig } from "@/types/legal";
import { Head } from "@inertiajs/vue3";

defineProps<{
    legal: LegalConfig;
}>();
</script>

<template>
    <Head title="Términos y condiciones" />

    <LegalLayout
        title="Términos y condiciones de uso"
        description="Condiciones que rigen el acceso y uso de la plataforma de aprendizaje de inglés para desarrolladores."
    >
        <p class="legal-updated">
            Última actualización: junio de 2026
        </p>

        <section>
            <h2>1. Identificación del proveedor</h2>
            <p>
                La plataforma <strong>{{ legal.business.name }}</strong> (en adelante, «la Plataforma»)
                es operada por el titular identificado con RUC <strong>{{ legal.business.ruc }}</strong>,
                con domicilio en {{ legal.business.address }}, y correo de contacto
                <a :href="`mailto:${legal.business.email}`">{{ legal.business.email }}</a>.
            </p>
        </section>

        <section>
            <h2>2. Aceptación</h2>
            <p>
                Al registrarte, iniciar sesión o utilizar cualquier funcionalidad de la Plataforma,
                aceptas estos Términos y Condiciones, nuestra Política de Privacidad y las políticas
                complementarias publicadas en el sitio. Si no estás de acuerdo, no debes usar el servicio.
            </p>
        </section>

        <section>
            <h2>3. Descripción del servicio</h2>
            <p>
                La Plataforma ofrece contenido educativo de inglés orientado a programadores, incluyendo
                práctica por voz, quizzes, tracks de vocabulario, niveles desbloqueables y funciones
                asistidas por inteligencia artificial. Parte del contenido es gratuito; otras funciones
                requieren «poder» (créditos virtuales internos).
            </p>
        </section>

        <section>
            <h2>4. Registro y cuenta</h2>
            <ul>
                <li>Debes proporcionar información veraz y mantenerla actualizada.</li>
                <li>Eres responsable de la confidencialidad de tus credenciales.</li>
                <li>Debes tener al menos 18 años o contar con autorización de un tutor legal.</li>
                <li>Nos reservamos el derecho de suspender cuentas por uso indebido o fraude.</li>
            </ul>
        </section>

        <section>
            <h2>5. Créditos de poder y compras</h2>
            <p>
                El «poder» es una unidad virtual sin valor monetario fuera de la Plataforma. Las compras
                se realizan mediante métodos de pago indicados en la app (por ejemplo, Yape o Plin) y
                quedan sujetas a validación manual del comprobante enviado.
            </p>
            <ul>
                <li>Los precios se muestran en soles peruanos (PEN) antes de confirmar la compra.</li>
                <li>La acreditación del poder puede demorar tras la verificación del pago.</li>
                <li>Los créditos no son transferibles entre usuarios salvo que la Plataforma lo indique.</li>
            </ul>
        </section>

        <section>
            <h2>6. Uso permitido</h2>
            <p>Te comprometes a no:</p>
            <ul>
                <li>Intentar acceder a áreas restringidas o vulnerar la seguridad del sistema.</li>
                <li>Usar bots, scraping o automatización no autorizada.</li>
                <li>Reproducir o revender el contenido sin autorización escrita.</li>
                <li>Enviar comprobantes de pago falsos o suplantar identidad.</li>
            </ul>
        </section>

        <section>
            <h2>7. Propiedad intelectual</h2>
            <p>
                El software, diseño, textos, marcas y contenidos de la Plataforma pertenecen a
                {{ legal.business.name }} o a sus licenciantes. Se te concede una licencia limitada,
                personal, no exclusiva y revocable para uso educativo conforme a estos términos.
            </p>
        </section>

        <section>
            <h2>8. Limitación de responsabilidad</h2>
            <p>
                La Plataforma se ofrece «tal cual». No garantizamos resultados específicos de aprendizaje.
                En la medida permitida por la ley peruana, no seremos responsables por daños indirectos
                derivados del uso del servicio, interrupciones o errores de terceros (incluidos proveedores de IA o voz).
            </p>
        </section>

        <section>
            <h2>9. Modificaciones</h2>
            <p>
                Podemos actualizar estos términos publicando la versión vigente en esta página.
                El uso continuado tras los cambios implica aceptación de la nueva versión.
            </p>
        </section>

        <section>
            <h2>10. Ley aplicable y jurisdicción</h2>
            <p>
                Estos términos se rigen por las leyes de la República del Perú. Cualquier controversia
                se someterá a los tribunales de Lima, sin perjuicio de los derechos irrenunciables del consumidor.
            </p>
        </section>
    </LegalLayout>
</template>
