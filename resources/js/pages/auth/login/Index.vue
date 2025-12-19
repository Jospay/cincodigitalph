<template>
    <div
        class="min-h-screen flex items-center justify-center bg-linear-to-br from-brand-blue/10 to-white"
    >
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
            <!-- Logo / Title -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-brand-blue">Admin Panel</h1>
                <p class="text-sm text-gray-500 mt-1">Sign in to continue</p>
            </div>

            <!-- Form -->
            <form class="space-y-5">
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Email address</label
                    >
                    <input
                        type="email"
                        placeholder="admin@email.com"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent"
                    />
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Password</label
                    >
                    <input
                        type="password"
                        placeholder="••••••••"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-brand-blue)] focus:border-transparent"
                    />
                </div>

                <!-- Remember / Forgot -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        <input
                            type="checkbox"
                            class="rounded border-gray-300 text-brand-blue focus:ring-brand-blue"
                        />
                        Remember me
                    </label>
                    <!-- <a
                        href="#"
                        class="text-[var(--color-brand-blue)] hover:underline"
                        >Forgot password?</a
                    > -->
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full bg-brand-blue hover:opacity-90 text-white font-semibold py-3 rounded-xl transition"
                >
                    Sign In
                </button>
            </form>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-400 mt-8">
                © 2025 Admin System. All rights reserved.
            </p>
        </div>
    </div>
</template>
