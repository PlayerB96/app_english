<script setup lang="ts">
import AppFlash from "@/Components/AppFlash.vue";
import ThemeToggle from "@/Components/ThemeToggle.vue";
import { useAutoHideHeader } from "@/composables/useAutoHideHeader";
import { useAppStore } from "@/Stores/useAppStore";
import { Link, usePage } from "@inertiajs/vue3";
import {
    BarChart3,
    BookOpen,
    LayoutDashboard,
    LogOut,
    Menu,
    Mic,
    Users,
    X,
} from "@lucide/vue";
import { computed, onMounted, ref, watch } from "vue";
import type { PageProps } from "@/types/auth";
import { roleLabel } from "@/types/auth";

const page = usePage<{ auth: PageProps["auth"]; url: string }>();
const appStore = useAppStore();

const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === "administrator");

const topBarRef = ref<HTMLElement | null>(null);
const desktopNavRef = ref<HTMLElement | null>(null);
const mobileNavRef = ref<HTMLElement | null>(null);

const { isVisible: isTopBarVisible, topBarHeight, show, updateTopBarHeight } =
    useAutoHideHeader(topBarRef, {
        forceVisible: () => appStore.isNavOpen,
    });

const desktopNavHeight = ref(0);
const mobileNavHeight = ref(0);

interface NavItem {
    label: string;
    href: string;
    icon: typeof LayoutDashboard;
}

const learnerNav: NavItem[] = [
    { label: "Dashboard", href: "/dashboard", icon: LayoutDashboard },
    { label: "Práctica", href: "/practice", icon: Mic },
    { label: "Tracks", href: "/tracks", icon: BookOpen },
];

const adminNav: NavItem[] = [
    { label: "Panel", href: "/admin", icon: LayoutDashboard },
    { label: "Usuarios", href: "/admin/users", icon: Users },
    { label: "Tracks", href: "/admin/tracks", icon: BookOpen },
    { label: "Reportes", href: "/admin/reports", icon: BarChart3 },
];

const visibleNavItems = computed(() =>
    isAdmin.value ? adminNav : learnerNav,
);

const homeHref = computed(() =>
    isAdmin.value ? "/admin" : "/dashboard",
);

function measureNavHeights(): void {
    desktopNavHeight.value = desktopNavRef.value?.offsetHeight ?? 0;
    mobileNavHeight.value = appStore.isNavOpen
        ? (mobileNavRef.value?.offsetHeight ?? 0)
        : 0;
    updateTopBarHeight();
}

const headerOffset = computed(() => {
    if (!user.value) {
        return 0;
    }

    const top = isTopBarVisible.value ? topBarHeight.value : 0;
    const desktopNav = desktopNavHeight.value;
    const mobileNav = mobileNavHeight.value;

    return top + Math.max(desktopNav, mobileNav);
});

const mainPaddingTop = computed(() => {
    if (!user.value) {
        return "1rem";
    }

    return `calc(${headerOffset.value}px + 1rem)`;
});

function isActive(href: string): boolean {
    if (href === "/admin") {
        return page.url === "/admin";
    }

    return page.url === href || page.url.startsWith(`${href}/`);
}

function closeNav(): void {
    appStore.closeNav();
}

watch(
    () => page.url,
    () => {
        show();
        window.scrollTo({ top: 0 });
    },
);

watch(
    () => appStore.isNavOpen,
    () => {
        measureNavHeights();
    },
);

watch(isTopBarVisible, () => {
    measureNavHeights();
});

onMounted(() => {
    measureNavHeights();

    if (typeof ResizeObserver !== "undefined") {
        const observer = new ResizeObserver(() => measureNavHeights());

        if (desktopNavRef.value) {
            observer.observe(desktopNavRef.value);
        }

        if (topBarRef.value) {
            observer.observe(topBarRef.value);
        }
    }
});
</script>

<template>
    <div class="surface-page">
        <header
            v-if="user"
            class="app-header"
        >
            <div
                class="app-header-top overflow-hidden"
                :class="{ 'pointer-events-none': !isTopBarVisible }"
                :style="{
                    maxHeight: isTopBarVisible ? `${topBarHeight}px` : '0px',
                    minHeight: '0px',
                    opacity: isTopBarVisible ? 1 : 0,
                }"
            >
                <div
                    ref="topBarRef"
                    class="app-header-top-inner app-container !px-3 sm:!px-6"
                >
                    <div class="flex min-w-0 flex-1 items-center gap-1.5 sm:gap-3">
                        <button
                            type="button"
                            class="btn-theme h-8 w-8 md:hidden"
                            aria-label="Abrir menú"
                            @click="appStore.toggleNav()"
                        >
                            <Menu v-if="!appStore.isNavOpen" class="h-4 w-4" />
                            <X v-else class="h-4 w-4" />
                        </button>

                        <Link
                            :href="homeHref"
                            class="header-brand"
                        >
                            Dev English
                        </Link>
                    </div>

                    <div class="header-actions">
                        <div class="header-utilities">
                            <span
                                v-if="!isAdmin"
                                class="header-token-badge"
                                :title="`${user.tokens ?? 0} tokens disponibles`"
                            >
                                <span>{{ user.tokens ?? 0 }}</span>
                                <span class="hidden md:inline">Tokens</span>
                            </span>

                            <span
                                v-if="!isAdmin"
                                class="header-utilities-divider"
                                aria-hidden="true"
                            />

                            <ThemeToggle />
                        </div>

                        <span
                            class="hidden h-8 w-px bg-gray-200 dark:bg-gray-700 md:block"
                            aria-hidden="true"
                        />

                        <div class="header-user-block">
                            <span class="max-w-[10rem] truncate text-sm font-medium text-heading lg:max-w-[14rem]">
                                {{ user.name }}
                            </span>
                            <span class="text-xs text-muted">
                                {{ roleLabel(user.role) }}
                            </span>
                        </div>

                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="header-logout"
                            aria-label="Salir"
                            title="Salir"
                        >
                            <LogOut class="h-4 w-4 md:hidden" />
                            <span class="hidden md:inline">Salir</span>
                        </Link>
                    </div>
                </div>
            </div>

            <nav
                ref="desktopNavRef"
                class="app-header-nav hidden md:block"
                :class="isTopBarVisible ? 'border-t border-gray-100 dark:border-gray-800' : 'app-header-nav-flush'"
                aria-label="Navegación principal"
            >
                <div class="app-container flex gap-1 py-2">
                    <Link
                        v-for="item in visibleNavItems"
                        :key="item.href"
                        :href="item.href"
                        class="nav-link"
                        :class="isActive(item.href) ? 'nav-link-active' : 'nav-link-idle'"
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.label }}
                    </Link>
                </div>
            </nav>

            <nav
                v-if="appStore.isNavOpen"
                ref="mobileNavRef"
                class="app-header-nav border-t border-gray-100 dark:border-gray-800 md:hidden"
                aria-label="Navegación móvil"
            >
                <div class="header-mobile-user">
                    <p class="truncate text-sm font-medium text-heading">
                        {{ user.name }}
                    </p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <span class="badge-role">
                            {{ roleLabel(user.role) }}
                        </span>
                        <span
                            v-if="!isAdmin && user.tokens !== undefined"
                            class="text-xs font-semibold text-amber-700 dark:text-amber-300"
                        >
                            {{ user.tokens }} tokens
                        </span>
                    </div>
                </div>

                <div class="app-container flex flex-col gap-0.5 px-3 py-2 sm:px-6">
                    <Link
                        v-for="item in visibleNavItems"
                        :key="item.href"
                        :href="item.href"
                        class="nav-link nav-link-mobile"
                        :class="isActive(item.href) ? 'nav-link-active' : 'nav-link-idle'"
                        @click="closeNav"
                    >
                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                        {{ item.label }}
                    </Link>
                </div>
            </nav>
        </header>

        <main
            class="pb-4 md:pb-6 lg:pb-8"
            :style="{ paddingTop: mainPaddingTop }"
        >
            <div class="app-container space-y-6">
                <AppFlash />
                <slot />
            </div>
        </main>
    </div>
</template>
