<script setup lang="ts">
import PowerIcon from "@/Components/PowerIcon.vue";
import ThemeToggle from "@/Components/ThemeToggle.vue";
import { useAppStore } from "@/Stores/useAppStore";
import type { PageProps } from "@/types/auth";
import { roleLabel } from "@/types/auth";
import { powerBalanceLabel } from "@/utils/powerLabels";
import { Link, usePage } from "@inertiajs/vue3";
import type { Component } from "vue";
import { LogOut, X } from "@lucide/vue";
import { computed, onMounted, onUnmounted, watch } from "vue";

export interface MobileNavItem {
    label: string;
    href: string;
    icon: Component;
}

const WORLD_HREF = "/world";

const props = defineProps<{
    user: NonNullable<PageProps["auth"]["user"]>;
    isAdmin: boolean;
    navItems: MobileNavItem[];
    homeHref: string;
}>();

const page = usePage<{ url: string }>();
const appStore = useAppStore();

const regularNavItems = computed(() =>
    props.navItems.filter((item) => item.href !== WORLD_HREF),
);

const worldNavItem = computed(() =>
    props.navItems.find((item) => item.href === WORLD_HREF) ?? null,
);

function isActive(href: string): boolean {
    if (href === "/admin") {
        return page.url === "/admin";
    }

    return page.url === href || page.url.startsWith(`${href}/`);
}

function closeNav(): void {
    appStore.closeNav();
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === "Escape" && appStore.isNavOpen) {
        closeNav();
    }
}

watch(
    () => page.url,
    () => {
        closeNav();
    },
);

watch(
    () => appStore.isNavOpen,
    (open) => {
        document.body.style.overflow = open ? "hidden" : "";
    },
);

onMounted(() => {
    window.addEventListener("keydown", onKeydown);
});

onUnmounted(() => {
    window.removeEventListener("keydown", onKeydown);
    document.body.style.overflow = "";
});
</script>

<template>
    <Teleport to="body">
        <Transition name="mobile-sidenav-backdrop">
            <button
                v-if="appStore.isNavOpen"
                type="button"
                class="mobile-sidenav-backdrop md:hidden"
                aria-label="Cerrar menú"
                @click="closeNav"
            />
        </Transition>

        <Transition name="mobile-sidenav-panel">
            <aside
                v-if="appStore.isNavOpen"
                class="mobile-sidenav md:hidden"
                role="dialog"
                aria-modal="true"
                aria-label="Menú de navegación"
            >
                <div class="mobile-sidenav-header">
                    <div class="min-w-0 flex-1">
                        <Link
                            :href="homeHref"
                            class="mobile-sidenav-brand"
                            @click="closeNav"
                        >
                            Dev English
                        </Link>
                        <p class="mt-2 truncate text-sm font-medium text-heading">
                            {{ user.name }}
                        </p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="badge-role">
                                {{ roleLabel(user.role) }}
                            </span>
                            <span
                                v-if="!isAdmin && user.tokens !== undefined"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-orange-700 dark:text-orange-300"
                            >
                                <PowerIcon size-class="h-3 w-3" />
                                {{ powerBalanceLabel(user.tokens) }}
                            </span>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="btn-theme h-9 w-9 shrink-0"
                        aria-label="Cerrar menú"
                        @click="closeNav"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <nav
                    class="mobile-sidenav-nav"
                    aria-label="Navegación principal"
                >
                    <Link
                        v-for="item in regularNavItems"
                        :key="item.href"
                        :href="item.href"
                        class="mobile-sidenav-link"
                        :class="isActive(item.href) ? 'mobile-sidenav-link-active' : 'mobile-sidenav-link-idle'"
                        @click="closeNav"
                    >
                        <component
                            :is="item.icon"
                            class="h-5 w-5 shrink-0"
                        />
                        <span>{{ item.label }}</span>
                    </Link>

                    <div
                        v-if="worldNavItem"
                        class="mobile-sidenav-supreme"
                    >
                        <Link
                            :href="worldNavItem.href"
                            class="mobile-sidenav-link"
                            :class="isActive(worldNavItem.href) ? 'mobile-sidenav-link-active' : 'mobile-sidenav-link-idle'"
                            @click="closeNav"
                        >
                            <PowerIcon
                                size-class="h-5 w-5"
                                animated
                            />
                            <span>{{ worldNavItem.label }}</span>
                        </Link>
                    </div>
                </nav>

                <div class="mobile-sidenav-footer">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-medium text-muted">Tema</span>
                        <ThemeToggle />
                    </div>

                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="mobile-sidenav-logout"
                        @click="closeNav"
                    >
                        <LogOut class="h-4 w-4 shrink-0" />
                        Salir
                    </Link>
                </div>
            </aside>
        </Transition>
    </Teleport>
</template>
