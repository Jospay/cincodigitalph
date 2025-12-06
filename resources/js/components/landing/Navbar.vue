<template>
    <nav
        id="navbar"
        class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[1320px] z-50 xl:mt-5 xl:rounded-lg transition-colors duration-300"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <img src="@/assets/nav logo.png" class="h-10" alt="" />
                </div>

                <!-- Desktop Nav Links -->
                <div class="hidden xl:flex items-center space-x-8">
                    <a
                        href="#"
                        class="relative hover:text-brand-blue font-medium text-white group"
                    >
                        <span class="inline-block relative">
                            Home
                            <span
                                class="absolute bottom-[-2px] left-0 w-0 h-[2px] bg-brand-blue transition-all duration-300 group-hover:w-full"
                            ></span>
                        </span>
                    </a>

                    <a
                        href="#aboutus"
                        class="relative hover:text-brand-blue font-medium text-white group"
                    >
                        <span class="inline-block relative">
                            About Us
                            <span
                                class="absolute bottom-[-2px] left-0 w-0 h-[2px] bg-brand-blue transition-all duration-300 group-hover:w-full"
                            ></span>
                        </span>
                    </a>

                    <a
                        href="#services"
                        class="relative hover:text-brand-blue font-medium text-white group"
                    >
                        <span class="inline-block relative">
                            Services
                            <span
                                class="absolute bottom-[-2px] left-0 w-0 h-[2px] bg-brand-blue transition-all duration-300 group-hover:w-full"
                            ></span>
                        </span>
                    </a>

                    <a
                        href="#technology"
                        class="relative hover:text-brand-blue font-medium text-white group"
                    >
                        <span class="inline-block relative">
                            Technology
                            <span
                                class="absolute bottom-[-2px] left-0 w-0 h-[2px] bg-brand-blue transition-all duration-300 group-hover:w-full"
                            ></span>
                        </span>
                    </a>

                    <a
                        href="#portfolio"
                        class="relative hover:text-brand-blue font-medium text-white group"
                    >
                        <span class="inline-block relative">
                            Portfolio
                            <span
                                class="absolute bottom-[-2px] left-0 w-0 h-[2px] bg-brand-blue transition-all duration-300 group-hover:w-full"
                            ></span>
                        </span>
                    </a>

                    <a
                        href="#careers"
                        class="relative hover:text-brand-blue font-medium text-white group"
                    >
                        <span class="inline-block relative">
                            Careers
                            <span
                                class="absolute bottom-[-2px] left-0 w-0 h-[2px] bg-brand-blue transition-all duration-300 group-hover:w-full"
                            ></span>
                        </span>
                    </a>

                    <a
                        href="#new"
                        class="relative hover:text-brand-blue font-medium text-white group"
                    >
                        <span class="inline-block relative">
                            New & Insights
                            <span
                                class="absolute bottom-[-2px] left-0 w-0 h-[2px] bg-brand-blue transition-all duration-300 group-hover:w-full"
                            ></span>
                        </span>
                    </a>

                    <a
                        href="#contact"
                        class="relative hover:text-brand-blue font-medium text-white group"
                    >
                        <span class="inline-block relative">
                            Contact Us
                            <span
                                class="absolute bottom-[-2px] left-0 w-0 h-[2px] bg-brand-blue transition-all duration-300 group-hover:w-full"
                            ></span>
                        </span>
                    </a>

                    <a href="/join">
                        <button
                            class="bg-brand-blue text-white px-5 py-1 rounded"
                        >
                            Join January 2026 Event
                        </button>
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="xl:hidden">
                    <button
                        id="menu-btn"
                        class="text-brand-blue hover:text-brand-blue focus:outline-none"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div
            id="mobile-menu"
            class="hidden xl:hidden bg-black border-t border-gray-200 py-3"
        >
            <a
                href="#home"
                class="mobile-link block px-4 py-2 text-brand-blue hover:text-gray-100 hover:bg-brand-green"
                >Home</a
            >
            <a
                href="#aboutus"
                class="mobile-link block px-4 py-2 text-brand-blue hover:bg-brand-green"
                >About</a
            >
            <a
                href="#services"
                class="mobile-link block px-4 py-2 text-brand-blue hover:bg-brand-green"
                >Services</a
            >
            <a
                href="#technology"
                class="mobile-link block px-4 py-2 text-brand-blue hover:bg-brand-green"
                >Technology</a
            >
            <a
                href="#portfolio"
                class="mobile-link block px-4 py-2 text-brand-blue hover:bg-brand-green"
                >Portfolio</a
            >
            <a
                href="#careers"
                class="mobile-link block px-4 py-2 text-brand-blue hover:bg-brand-green"
                >Careers</a
            >
            <a
                href="#new"
                class="mobile-link block px-4 py-2 text-brand-blue hover:bg-brand-green"
                >New & Insights</a
            >
            <a
                href="#contact"
                class="mobile-link block px-4 py-2 text-brand-blue hover:bg-brand-green"
                >Contact Us</a
            >

            <a href="/join">
                <button
                    class="bg-brand-blue text-white px-5 py-1 rounded ms-3 mt-3 mb-2"
                >
                    Join January 2026 Event
                </button>
            </a>
        </div>
    </nav>
</template>

<style scoped>
.mobile-link.active {
    background-color: #00b900 !important;
    color: white !important;
}
</style>
