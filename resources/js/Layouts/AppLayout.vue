<script setup lang="ts">
import AppFlash from "@/Components/AppFlash.vue";
import AppIcon from "@/Components/AppIcon.vue";
import AppLogo from "@/Components/AppLogo.vue";
import HeaderPowerVault from "@/Components/HeaderPowerVault.vue";
import MobileSideNav from "@/Components/MobileSideNav.vue";
import PowerIcon from "@/Components/PowerIcon.vue";
import SiteFooter from "@/Components/SiteFooter.vue";
import ThemeToggle from "@/Components/ThemeToggle.vue";
import { useAutoHideHeader } from "@/composables/useAutoHideHeader";
import { useAppStore } from "@/Stores/useAppStore";
import { Link, usePage } from "@inertiajs/vue3";
import {
    BarChart3,
    BookOpen,
    Globe,
    LayoutDashboard,
    LogOut,
    Menu,
    Mic,
    Users,
} from "@lucide/vue";
import { computed, onMounted, ref, watch } from "vue";
import type { PageProps } from "@/types/auth";
import { roleLabel } from "@/types/auth";

const page = usePage<{ auth: PageProps["auth"]; url: string }>();
const appStore = useAppStore();

const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === "administrator");

const mainContainerClass = computed(() =>
    page.url.startsWith("/world") ? "app-container-game" : "app-container",
);

const topBarRef = ref<HTMLElement | null>(null);
const desktopNavRef = ref<HTMLElement | null>(null);

const { isVisible: isTopBarVisible, topBarHeight, show, updateTopBarHeight } =
    useAutoHideHeader(topBarRef, {
        forceVisible: () => appStore.isNavOpen || isAdmin.value,
    });

const desktopNavHeight = ref(56);

const WORLD_HREF = "/world";

interface NavItem {
    label: string;
    href: string;
    icon: typeof LayoutDashboard;
}

const learnerNav: NavItem[] = [
    { label: "Dashboard", href: "/dashboard", icon: LayoutDashboard },
    { label: "Práctica", href: "/practice", icon: Mic },
    { label: "Tracks", href: "/tracks", icon: BookOpen },
    { label: "Mundos", href: WORLD_HREF, icon: Globe },
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

const desktopRegularNavItems = computed(() => {
    if (isAdmin.value) {
        return visibleNavItems.value;
    }

    return visibleNavItems.value.filter((item) => item.href !== WORLD_HREF);
});

const worldNavItem = computed(() => {
    if (isAdmin.value) {
        return null;
    }

    return visibleNavItems.value.find((item) => item.href === WORLD_HREF) ?? null;
});

const homeHref = computed(() =>
    isAdmin.value ? "/admin" : "/dashboard",
);

function measureNavHeights(): void {
    desktopNavHeight.value = desktopNavRef.value?.offsetHeight ?? 0;
    updateTopBarHeight();
}

const headerOffset = computed(() => {
    if (!user.value) {
        return 0;
    }

    const top = isTopBarVisible.value ? topBarHeight.value : 0;

    return top + desktopNavHeight.value;
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
        if (!isAdmin.value) {
            show();
        }

        closeNav();
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
    <div class="surface-page flex min-h-screen flex-col">
        <header
            v-if="user"
            class="app-header"
        >
            <div
                class="app-header-top overflow-hidden"
                :class="{
                    'pointer-events-none app-header-top-collapsed': !isTopBarVisible,
                }"
                :style="{
                    maxHeight: isTopBarVisible ? `${topBarHeight}px` : '0px',
                    minHeight: '0px',
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
                            :aria-expanded="appStore.isNavOpen"
                            @click="appStore.toggleNav()"
                        >
                            <Menu class="h-4 w-4" />
                        </button>

                        <AppIcon
                            :href="homeHref"
                            size="sm"
                            class="header-brand md:hidden"
                        />
                        <AppLogo
                            :href="homeHref"
                            size="lg"
                            class="header-brand hidden md:inline-flex"
                        />
                    </div>

                    <div class="header-actions">
                        <div
                            class="header-utilities"
                            :class="{ 'hidden md:flex': isAdmin }"
                        >
                            <HeaderPowerVault
                                v-if="!isAdmin"
                                :tokens="user.tokens ?? 0"
                            />

                            <span
                                v-if="!isAdmin"
                                class="header-utilities-divider hidden md:block"
                                aria-hidden="true"
                            />

                            <span class="hidden md:inline-flex">
                                <ThemeToggle />
                            </span>
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
                        v-for="item in desktopRegularNavItems"
                        :key="item.href"
                        :href="item.href"
                        class="nav-link"
                        :class="isActive(item.href) ? 'nav-link-active' : 'nav-link-idle'"
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.label }}
                    </Link>

                    <Link
                        v-if="worldNavItem"
                        :href="worldNavItem.href"
                        class="nav-link app-header-nav-world"
                        :class="isActive(worldNavItem.href) ? 'nav-link-active' : 'nav-link-idle'"
                    >
                        <PowerIcon
                            size-class="h-4 w-4"
                            animated
                        />
                        {{ worldNavItem.label }}
                    </Link>
                </div>
            </nav>
        </header>

        <MobileSideNav
            v-if="user"
            :user="user"
            :is-admin="isAdmin"
            :nav-items="visibleNavItems"
            :home-href="homeHref"
        />

        <main
            class="flex-1 pb-4 md:pb-6 lg:pb-8"
            :style="{ paddingTop: mainPaddingTop }"
        >
            <div
                class="space-y-6"
                :class="mainContainerClass"
            >
                <AppFlash />
                <slot />
            </div>
        </main>

        <SiteFooter />
    </div>
</template>
