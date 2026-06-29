<script setup lang="ts">
import AppLogo from "@/Components/AppLogo.vue";
import ThemeToggle from "@/Components/ThemeToggle.vue";
import SiteFooter from "@/Components/SiteFooter.vue";
import WelcomeShowcaseVideo from "@/Components/WelcomeShowcaseVideo.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowRight,
    BookOpen,
    CheckCircle2,
    Code2,
    Lock,
    Mic,
    Sparkles,
    Target,
    Trophy,
    Zap,
} from "@lucide/vue";
import { computed } from "vue";

const props = defineProps<{
    canonicalUrl: string;
}>();

const metaDescription =
    "Aprende inglés para developers con práctica gratis por voz, vocabulario técnico y preparación para entrevistas. Inglés rápido para programadores: speaking, tracks y niveles desbloqueables.";

const features = [
    {
        icon: Mic,
        title: "Speaking con validación de voz",
        description:
            "Practica pronunciación en inglés técnico. Graba tu respuesta, valida el audio y compara la traducción para programadores que quieren hablar con confianza.",
    },
    {
        icon: BookOpen,
        title: "Tracks de vocabulario dev",
        description:
            "Palabras y oraciones cortas en inglés con opciones múltiples. Ideal para reforzar inglés para entrevistas y el día a día en equipos de software.",
    },
    {
        icon: Trophy,
        title: "15 subniveles tipo videojuego",
        description:
            "Básico, intermedio y avanzado con fases que se desbloquean al aprobar. Aprende inglés rápido con progreso visible y motivación constante.",
    },
    {
        icon: Target,
        title: "Enfocado en entrevistas técnicas",
        description:
            "Escenarios reales: pull requests, deployments, bugs y arquitectura. Inglés para developers que necesitan rendir en entrevistas en inglés.",
    },
];

const steps = [
    {
        step: "01",
        title: "Elige tu modo",
        text: "Speaking para hablar o Tracks para vocabulario con opciones. Ambos con niveles progresivos.",
    },
    {
        step: "02",
        title: "Practica en inglés",
        text: "Responde por voz o elige la traducción correcta. Contenido pensado para programadores.",
    },
    {
        step: "03",
        title: "Desbloquea niveles",
        text: "Aprueba cada fase y avanza de básico a avanzado. Tu progreso se guarda mientras practicas.",
    },
];

const showcases = [
    {
        id: "speaking",
        eyebrow: "Práctica · Speaking",
        title: "Habla inglés técnico con feedback inmediato",
        description:
            "Graba tu respuesta, valida la pronunciación y compara la traducción. Ideal para reuniones, stand-ups y entrevistas técnicas.",
        bullets: [
            "Reconocimiento de voz en inglés",
            "Frases de contexto dev real",
            "Progreso por subnivel",
        ],
        src: "/videos/welcome/video1.mp4",
        reverse: false,
    },
    {
        id: "tracks",
        eyebrow: "Tracks · Vocabulario",
        title: "Repasa vocabulario dev en sesiones cortas",
        description:
            "Elige la traducción correcta entre tres opciones. Refuerza términos de PR, deploy, bugs y arquitectura sin salir del flujo de trabajo.",
        bullets: [
            "Opciones múltiples por pregunta",
            "Contenido técnico actualizado",
            "Ideal entre commits",
        ],
        src: "/videos/welcome/video2.mp4",
        reverse: true,
    },
    {
        id: "levels",
        eyebrow: "Mapa de niveles",
        title: "Desbloquea fases como en un videojuego",
        description:
            "Cada subnivel aprobado abre el siguiente. Si fallas, espera el cooldown o usa poder para saltar y seguir practicando.",
        bullets: [
            "15 subniveles progresivos",
            "Bloqueos con temporizador",
            "Power-ups con poder",
        ],
        src: "/videos/welcome/video3.mp4",
        reverse: false,
    },
    {
        id: "dashboard",
        eyebrow: "Tu progreso",
        title: "Mide avance, racha y precisión",
        description:
            "Dashboard con métricas de sesiones, aciertos y evolución. Ves en un vistazo cuánto has practicado y qué sigue.",
        bullets: [
            "KPIs de práctica diaria",
            "Historial por modo",
            "Motivación visible",
        ],
        src: "/videos/welcome/video4.mp4",
        reverse: true,
    },
];

const faqs = [
    {
        question: "¿Es gratis practicar inglés en Dev English?",
        answer: "Sí. Puedes empezar a practicar inglés gratis con los niveles básicos. La plataforma está pensada para developers que quieren mejorar sin barreras de entrada.",
    },
    {
        question: "¿Sirve para entrevistas técnicas en inglés?",
        answer: "Sí. Incluimos vocabulario y frases de entrevistas técnicas, code reviews, deployments y trabajo en equipo — el inglés que realmente usas como programador.",
    },
    {
        question: "¿Cómo aprendo inglés rápido si soy programador?",
        answer: "Combinamos práctica activa (speaking) con repaso de vocabulario técnico en sesiones cortas. Los niveles desbloqueables te mantienen en un ritmo constante sin abrumarte.",
    },
    {
        question: "¿Necesito nivel avanzado para empezar?",
        answer: "No. Comienzas en el Módulo Básico con saludos y frases simples. Al aprobar cada subnivel desbloqueas el siguiente hasta llegar a contenido avanzado de arquitectura y DevOps.",
    },
];

const jsonLd = computed(() =>
    JSON.stringify({
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "WebSite",
                name: "Dev English",
                url: props.canonicalUrl,
                description: metaDescription,
                inLanguage: "es",
                potentialAction: {
                    "@type": "SearchAction",
                    target: `${props.canonicalUrl}/login`,
                    "query-input": "required name=search_term_string",
                },
            },
            {
                "@type": "SoftwareApplication",
                name: "Dev English",
                applicationCategory: "EducationalApplication",
                operatingSystem: "Web",
                offers: {
                    "@type": "Offer",
                    price: "0",
                    priceCurrency: "USD",
                },
                description: metaDescription,
                audience: {
                    "@type": "Audience",
                    audienceType: "Software developers",
                },
            },
            {
                "@type": "FAQPage",
                mainEntity: faqs.map((item) => ({
                    "@type": "Question",
                    name: item.question,
                    acceptedAnswer: {
                        "@type": "Answer",
                        text: item.answer,
                    },
                })),
            },
        ],
    }),
);
</script>

<template>
    <Head>
        <title>
            Dev English — Inglés para developers | Práctica gratis y entrevistas técnicas
        </title>
        <meta
            head-key="description"
            name="description"
            :content="metaDescription"
        />
        <meta
            head-key="keywords"
            name="keywords"
            content="inglés para developers, inglés para desarrolladores, aprender inglés rápido para programadores, inglés rápido, inglés para entrevistas, práctica inglés gratis, inglés técnico programación, speaking inglés developers, vocabulario dev inglés, entrevistas técnicas en inglés"
        />
        <meta
            head-key="robots"
            name="robots"
            content="index, follow"
        />
        <link
            head-key="canonical"
            rel="canonical"
            :href="canonicalUrl"
        />
        <meta
            head-key="og:type"
            property="og:type"
            content="website"
        />
        <meta
            head-key="og:title"
            property="og:title"
            content="Dev English — Inglés para developers y programadores"
        />
        <meta
            head-key="og:description"
            property="og:description"
            :content="metaDescription"
        />
        <meta
            head-key="og:url"
            property="og:url"
            :content="canonicalUrl"
        />
        <meta
            head-key="og:locale"
            property="og:locale"
            content="es_ES"
        />
        <meta
            head-key="og:site_name"
            property="og:site_name"
            content="Dev English"
        />
        <meta
            head-key="twitter:card"
            name="twitter:card"
            content="summary_large_image"
        />
        <meta
            head-key="twitter:title"
            name="twitter:title"
            content="Dev English — Inglés para developers"
        />
        <meta
            head-key="twitter:description"
            name="twitter:description"
            :content="metaDescription"
        />
        <component
            :is="'script'"
            head-key="json-ld"
            type="application/ld+json"
            v-html="jsonLd"
        />
    </Head>

    <div class="min-h-screen bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-100">
        <header class="surface-header sticky top-0 z-50">
            <div class="app-container-wide flex items-center justify-between gap-4 py-4 sm:py-5">
                <AppLogo size="lg" />
                <nav
                    class="hidden items-center gap-6 text-sm font-medium text-body md:flex"
                    aria-label="Navegación principal"
                >
                    <a
                        href="#funciones"
                        class="hover:text-blue-600 dark:hover:text-blue-400"
                    >Funciones</a>
                    <a
                        href="#como-funciona"
                        class="hover:text-blue-600 dark:hover:text-blue-400"
                    >Cómo funciona</a>
                    <a
                        href="#demos"
                        class="hover:text-blue-600 dark:hover:text-blue-400"
                    >Demos</a>
                    <a
                        href="#faq"
                        class="hover:text-blue-600 dark:hover:text-blue-400"
                    >FAQ</a>
                </nav>
                <div class="flex items-center gap-2">
                    <ThemeToggle />
                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                    >
                        Empezar gratis
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </header>

        <main>
            <section
                class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 py-20 text-white sm:py-24 lg:py-28"
                aria-labelledby="hero-heading"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-white/10 blur-3xl"
                    aria-hidden="true"
                />
                <div
                    class="pointer-events-none absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-indigo-400/20 blur-3xl"
                    aria-hidden="true"
                />

                <div class="relative app-container-wide">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium backdrop-blur-sm">
                        <Sparkles class="h-4 w-4" />
                        Práctica de inglés gratis para programadores
                    </div>

                    <h1
                        id="hero-heading"
                        class="max-w-4xl text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl xl:max-w-5xl"
                    >
                        Aprende inglés rápido para developers
                    </h1>

                    <p class="mt-6 max-w-3xl text-lg text-blue-100 sm:text-xl lg:max-w-4xl">
                        La plataforma de <strong class="text-white">inglés para developers</strong>
                        con speaking por voz, vocabulario técnico y preparación para
                        <strong class="text-white">entrevistas en inglés</strong>.
                        Practica gratis y desbloquea niveles como en un videojuego.
                    </p>

                    <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                        <Link
                            href="/login"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-3.5 text-base font-semibold text-blue-700 hover:bg-blue-50"
                        >
                            <Zap class="h-5 w-5" />
                            Practicar inglés gratis
                        </Link>
                        <a
                            href="#demos"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 px-6 py-3.5 text-base font-semibold text-white hover:bg-white/10"
                        >
                            Ver la plataforma
                        </a>
                    </div>

                    <ul class="mt-12 flex flex-wrap gap-x-8 gap-y-3 text-sm text-blue-100">
                        <li class="flex items-center gap-2">
                            <CheckCircle2 class="h-4 w-4 text-emerald-300" />
                            Inglés técnico para programadores
                        </li>
                        <li class="flex items-center gap-2">
                            <CheckCircle2 class="h-4 w-4 text-emerald-300" />
                            Speaking + vocabulario dev
                        </li>
                        <li class="flex items-center gap-2">
                            <CheckCircle2 class="h-4 w-4 text-emerald-300" />
                            15 niveles progresivos
                        </li>
                    </ul>
                </div>
            </section>

            <section
                id="funciones"
                class="app-container-wide py-16 sm:py-20 lg:py-24"
                aria-labelledby="features-heading"
            >
                <div class="mx-auto max-w-3xl text-center lg:max-w-4xl">
                    <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">
                        Inglés para programadores
                    </p>
                    <h2
                        id="features-heading"
                        class="mt-2 text-3xl font-bold text-heading sm:text-4xl"
                    >
                        Todo lo que necesitas para hablar inglés en tech
                    </h2>
                    <p class="mt-4 text-body">
                        Diseñado para quienes quieren
                        <strong>aprender inglés rápido</strong> sin perder tiempo en contenido genérico.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 xl:grid-cols-4 xl:gap-8">
                    <article
                        v-for="feature in features"
                        :key="feature.title"
                        class="surface-card-muted p-6 transition-shadow hover:shadow-md"
                    >
                        <div class="mb-4 inline-flex rounded-xl bg-blue-100 p-3 text-blue-600">
                            <component
                                :is="feature.icon"
                                class="h-6 w-6"
                            />
                        </div>
                        <h3 class="text-lg font-semibold text-heading">
                            {{ feature.title }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-body">
                            {{ feature.description }}
                        </p>
                    </article>
                </div>
            </section>

            <section
                id="demos"
                class="border-y border-gray-100 bg-gray-50 py-16 sm:py-20 lg:py-24 dark:border-gray-800 dark:bg-gray-900/40"
                aria-labelledby="demos-heading"
            >
                <div class="app-container-wide">
                    <div class="mx-auto max-w-3xl text-center">
                        <p class="text-sm font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">
                            Demos en vivo
                        </p>
                        <h2
                            id="demos-heading"
                            class="mt-2 text-3xl font-bold text-heading sm:text-4xl"
                        >
                            Así se ve la plataforma por dentro
                        </h2>
                        <p class="mt-4 text-body">
                            Capturas reales de la cuenta demo de aprendiz. Los videos se reproducen solos y no abren pantallas ni enlaces.
                        </p>
                    </div>

                    <div class="mt-16 space-y-20 lg:space-y-28">
                        <article
                            v-for="item in showcases"
                            :key="item.id"
                            class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16"
                            :class="item.reverse ? 'lg:[&>*:first-child]:order-2' : ''"
                        >
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400">
                                    {{ item.eyebrow }}
                                </p>
                                <h3 class="mt-3 text-2xl font-bold text-heading sm:text-3xl">
                                    {{ item.title }}
                                </h3>
                                <p class="mt-4 text-base leading-relaxed text-body">
                                    {{ item.description }}
                                </p>
                                <ul class="mt-6 space-y-2.5">
                                    <li
                                        v-for="bullet in item.bullets"
                                        :key="bullet"
                                        class="flex items-start gap-2.5 text-sm text-body"
                                    >
                                        <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                                        {{ bullet }}
                                    </li>
                                </ul>
                            </div>

                            <WelcomeShowcaseVideo
                                :src="item.src"
                                :label="item.title"
                            />
                        </article>
                    </div>
                </div>
            </section>

            <section
                class="border-y border-gray-100 bg-white py-16 sm:py-20 lg:py-24 dark:border-gray-800 dark:bg-gray-950"
                aria-labelledby="modes-heading"
            >
                <div class="app-container-wide">
                    <h2
                        id="modes-heading"
                        class="text-center text-3xl font-bold text-heading"
                    >
                        Dos modos para practicar inglés gratis
                    </h2>

                    <div class="mt-12 grid gap-8 lg:grid-cols-2 lg:gap-10 xl:gap-12">
                        <article class="surface-card p-8">
                            <div class="mb-4 flex items-center gap-3">
                                <div class="rounded-xl bg-blue-600 p-3 text-white">
                                    <Mic class="h-6 w-6" />
                                </div>
                                <h3 class="text-xl font-bold text-heading">
                                    Práctica · Speaking
                                </h3>
                            </div>
                            <p class="text-body">
                                Di frases en inglés en voz alta. El sistema valida tu audio,
                                transcribe lo que hablaste y compara la traducción.
                                Perfecto para <strong>inglés para entrevistas</strong> y reuniones técnicas.
                            </p>
                            <ul class="mt-6 space-y-2 text-sm text-body">
                                <li class="flex items-center gap-2">
                                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                                    Niveles básico, intermedio y avanzado
                                </li>
                                <li class="flex items-center gap-2">
                                    <Lock class="h-4 w-4 text-gray-400" />
                                    Desbloquea fases al aprobar cada nivel
                                </li>
                            </ul>
                        </article>

                        <article class="surface-card p-8">
                            <div class="mb-4 flex items-center gap-3">
                                <div class="rounded-xl bg-violet-600 p-3 text-white">
                                    <Code2 class="h-6 w-6" />
                                </div>
                                <h3 class="text-xl font-bold text-heading">
                                    Tracks · Vocabulario
                                </h3>
                            </div>
                            <p class="text-body">
                                Palabras y oraciones cortas en inglés con tres opciones de traducción.
                                Refuerza <strong>inglés técnico</strong> de forma rápida entre commits.
                            </p>
                            <ul class="mt-6 space-y-2 text-sm text-body">
                                <li class="flex items-center gap-2">
                                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                                    Vocabulario dev: PR, deploy, bugs, arquitectura
                                </li>
                                <li class="flex items-center gap-2">
                                    <Lock class="h-4 w-4 text-amber-500" />
                                    Error = bloqueo 24 h · acierto = siguiente nivel
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <section
                id="como-funciona"
                class="app-container-wide py-16 sm:py-20 lg:py-24"
                aria-labelledby="steps-heading"
            >
                <h2
                    id="steps-heading"
                    class="text-center text-3xl font-bold text-heading"
                >
                    Cómo aprender inglés rápido si eres developer
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-center text-gray-600 lg:max-w-3xl">
                    Tres pasos simples para pasar de entender código en inglés a hablarlo con seguridad.
                </p>

                <ol class="mt-14 grid gap-8 md:grid-cols-3 lg:gap-10">
                    <li
                        v-for="item in steps"
                        :key="item.step"
                        class="relative rounded-2xl border border-gray-100 p-6 dark:border-gray-800 dark:bg-gray-900/40"
                    >
                        <span class="text-4xl font-bold text-blue-100 dark:text-blue-900/80">{{ item.step }}</span>
                        <h3 class="mt-2 text-lg font-semibold text-heading">
                            {{ item.title }}
                        </h3>
                        <p class="mt-2 text-sm text-body">
                            {{ item.text }}
                        </p>
                    </li>
                </ol>
            </section>

            <section
                class="bg-blue-600 py-16 text-white sm:py-20 lg:py-24"
                aria-labelledby="cta-heading"
            >
                <div class="app-container-wide mx-auto max-w-4xl text-center xl:max-w-5xl">
                    <h2
                        id="cta-heading"
                        class="text-3xl font-bold sm:text-4xl"
                    >
                        Empieza tu práctica de inglés para developers hoy
                    </h2>
                    <p class="mt-4 text-lg text-blue-100">
                        Únete y accede a speaking, tracks y niveles desbloqueables.
                        <strong class="text-white">Práctica de inglés gratis</strong> para programadores que quieren avanzar rápido.
                    </p>
                    <Link
                        href="/login"
                        class="mt-8 inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-semibold text-blue-700 hover:bg-blue-50"
                    >
                        Crear cuenta / Iniciar sesión
                        <ArrowRight class="h-5 w-5" />
                    </Link>
                </div>
            </section>

            <section
                id="faq"
                class="py-16 sm:py-20 lg:py-24"
                aria-labelledby="faq-heading"
            >
                <div class="app-container-wide mx-auto max-w-4xl xl:max-w-5xl">
                <h2
                    id="faq-heading"
                    class="text-center text-3xl font-bold text-heading"
                >
                    Preguntas frecuentes
                </h2>

                <div class="mt-12 space-y-4">
                    <details
                        v-for="item in faqs"
                        :key="item.question"
                        class="group surface-card p-5 open:shadow-sm"
                    >
                        <summary class="cursor-pointer list-none font-semibold text-heading marker:content-none">
                            {{ item.question }}
                        </summary>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600">
                            {{ item.answer }}
                        </p>
                    </details>
                </div>
                </div>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
