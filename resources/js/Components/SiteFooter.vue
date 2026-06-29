<script setup lang="ts">
import AppLogo from "@/Components/AppLogo.vue";
import type { LegalConfig } from "@/types/legal";
import { Link, usePage } from "@inertiajs/vue3";
import { Mail } from "@lucide/vue";
import { computed } from "vue";

const page = usePage<{ legal: LegalConfig }>();

const legal = computed(() => page.props.legal);

const legalLinks = [
    { label: "Términos y condiciones", href: "/legal/terminos" },
    { label: "Política de privacidad", href: "/legal/privacidad" },
    { label: "Devoluciones y reembolsos", href: "/legal/devoluciones" },
    { label: "Datos del proveedor", href: "/legal/proveedor" },
    { label: "Libro de reclamaciones", href: "/legal/reclamaciones" },
];
</script>

<template>
    <footer class="site-footer">
        <div class="app-container-wide site-footer__inner">
            <div class="site-footer__brand">
                <AppLogo class="site-footer__brand-link" />
                <p class="site-footer__tagline">
                    Inglés para developers · Práctica con IA · Niveles desbloqueables
                </p>
                <dl class="site-footer__business">
                    <div>
                        <dt>RUC</dt>
                        <dd>{{ legal.business.ruc }}</dd>
                    </div>
                    <div>
                        <dt>Domicilio fiscal</dt>
                        <dd>{{ legal.business.address }}</dd>
                    </div>
                </dl>
            </div>

            <nav
                class="site-footer__nav"
                aria-label="Enlaces legales"
            >
                <p class="site-footer__nav-title">
                    Información legal
                </p>
                <ul class="site-footer__links">
                    <li
                        v-for="item in legalLinks"
                        :key="item.href"
                    >
                        <Link
                            :href="item.href"
                            class="site-footer__link"
                        >
                            {{ item.label }}
                        </Link>
                    </li>
                </ul>
            </nav>

            <div class="site-footer__contact">
                <p class="site-footer__nav-title">
                    Contacto
                </p>
                <a
                    :href="`mailto:${legal.business.email}`"
                    class="site-footer__contact-link"
                >
                    <Mail class="h-4 w-4 shrink-0" />
                    {{ legal.business.email }}
                </a>
                <p
                    v-if="legal.business.phone"
                    class="site-footer__phone"
                >
                    {{ legal.business.phone }}
                </p>
                <p class="site-footer__note">
                    Plazo de respuesta a reclamos: {{ legal.complaint_response_days }} días calendario.
                </p>
            </div>
        </div>

        <div class="site-footer__bottom">
            <div class="app-container-wide site-footer__bottom-inner">
                <p class="site-footer__copyright">
                    © {{ new Date().getFullYear() }} {{ legal.business.name }}. Todos los derechos reservados.
                </p>
                <p class="site-footer__disclaimer">
                    Los créditos de poder son unidades virtuales para uso dentro de la plataforma.
                </p>
            </div>
        </div>
    </footer>
</template>
