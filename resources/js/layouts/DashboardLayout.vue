<script setup>
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";

const isSidebarOpen = ref(true);
const toggleSidebar = () => (isSidebarOpen.value = !isSidebarOpen.value);

// 1. Define our navigation data
const managementLinks = [
    { name: "Dashboard Management", href: "/admin", icon: "fa-gauge" },
    { name: "Users Management", href: "/admin/users", icon: "fa-user" },
    { name: "Earning Management", href: "/admin/earning", icon: "fa-coins" },
    {
        name: "Allocation Management",
        href: "/admin/allocation",
        icon: "fa-peso-sign",
    },
];

const landingLinks = [
    { name: "Banner", href: "/admin/banner" },
    { name: "About Us", href: "/admin/about" },
    { name: "Services", href: "/admin/services" },
    { name: "Technology", href: "/admin/technology" },
    { name: "Events", href: "/admin/events" },
    { name: "Careers", href: "/admin/careers" },
    { name: "New & Insights", href: "/admin/insights" },
    { name: "Contact Us", href: "/admin/contact" },
];
</script>

<template>
    <div class="h-screen flex flex-col overflow-hidden">
        <header
            class="bg-brand-dark-black w-full border-b-2 border-brand-border-black flex-none"
        >
            <div class="flex items-center gap-5">
                <i
                    @click="toggleSidebar"
                    class="fa-solid fa-bars text-white py-4 px-5 border-e-2 border-brand-border-black cursor-pointer hover:bg-gray-800 transition-colors"
                ></i>
                <img src="@/assets/footer logo.png" class="h-7" alt="Logo" />
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <aside
                :class="[
                    'bg-brand-dark-black border-e-2 border-brand-border-black transition-all duration-300 overflow-y-auto custom-scrollbar',
                    isSidebarOpen
                        ? 'w-[300px]'
                        : 'w-0 opacity-0 pointer-events-none',
                ]"
            >
                <div class="p-5 grid gap-1 text-white">
                    <Link
                        v-for="link in managementLinks"
                        :key="link.href"
                        :href="link.href"
                    >
                        <button
                            :class="[
                                'text-md flex gap-3 py-2 px-3 rounded-xl items-center w-full transition-colors',
                                $page.url === link.href
                                    ? 'text-white bg-brand-light-black'
                                    : 'text-brand-gray hover:text-white hover:bg-brand-light-black',
                            ]"
                        >
                            <i
                                :class="['fa-solid', link.icon, 'text-white']"
                            ></i>
                            {{ link.name }}
                        </button>
                    </Link>

                    <p class="text-brand-gray pt-3 font-semibold">
                        Landing Page
                    </p>

                    <div class="flex gap-3 mt-2">
                        <div class="w-0.5 h-auto bg-brand-gray"></div>
                        <div class="grid gap-2 w-full">
                            <Link
                                v-for="link in landingLinks"
                                :key="link.href"
                                :href="link.href"
                            >
                                <button
                                    :class="[
                                        'text-start transition-colors',
                                        $page.url === link.href
                                            ? 'text-white font-bold'
                                            : 'text-brand-gray hover:text-white',
                                    ]"
                                >
                                    {{ link.name }}
                                </button>
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex-1 bg-brand-light-black overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #333;
    border-radius: 10px;
}
</style>
