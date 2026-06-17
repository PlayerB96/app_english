<script setup lang="ts">
import AppFlash from "@/Components/AppFlash.vue";
import { useAppStore } from "@/Stores/useAppStore";
import { Link, usePage } from "@inertiajs/vue3";
import {
    BarChart3,
    BookOpen,
    LayoutDashboard,
    Menu,
    Mic,
    Users,
    X,
} from "@lucide/vue";
import { computed } from "vue";
import type { PageProps } from "@/types/auth";

const page = usePage<{ auth: PageProps["auth"]; url: string }>();
const appStore = useAppStore();

const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === "administrator");

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

function isActive(href: string): boolean {
    if (href === "/admin") {
        return page.url === "/admin";
    }

    return page.url === href || page.url.startsWith(`${href}/`);
}

function closeNav(): void {
    appStore.closeNav();
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header
            v-if="user"
            class="sticky top-0 z-20 border-b border-gray-200 bg-white"
        >
            <div
                class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3 md:px-6"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 md:hidden"
                        aria-label="Abrir menú"
                        @click="appStore.toggleNav()"
                    >
                        <Menu v-if="!appStore.isNavOpen" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </button>

                    <Link
                        :href="homeHref"
                        class="truncate text-lg font-bold text-gray-900"
                    >
                        Dev English
                    </Link>
                </div>

                <div class="flex shrink-0 items-center gap-2 text-sm text-gray-600 md:gap-3">
                    <span class="hidden max-w-[8rem] truncate md:inline">
                        {{ user.name }}
                    </span>
                    <span
                        class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium capitalize text-blue-700"
                    >
                        {{ user.role }}
                    </span>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="font-medium text-red-600 hover:text-red-700"
                    >
                        Salir
                    </Link>
                </div>
            </div>

            <nav
                class="hidden border-t border-gray-100 md:block"
                aria-label="Navegación principal"
            >
                <div class="mx-auto flex max-w-5xl gap-1 px-4 py-2 md:px-6">
                    <Link
                        v-for="item in visibleNavItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                        :class="
                            isActive(item.href)
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                        "
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.label }}
                    </Link>
                </div>
            </nav>

            <nav
                v-if="appStore.isNavOpen"
                class="border-t border-gray-100 md:hidden"
                aria-label="Navegación móvil"
            >
                <div class="mx-auto flex max-w-5xl flex-col gap-1 px-4 py-2">
                    <Link
                        v-for="item in visibleNavItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium"
                        :class="
                            isActive(item.href)
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-100'
                        "
                        @click="closeNav"
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        {{ item.label }}
                    </Link>
                </div>
            </nav>
        </header>

        <main class="p-4 md:p-6">
            <div class="mx-auto mb-4 max-w-5xl">
                <AppFlash />
            </div>
            <slot />
        </main>
    </div>
</template>
