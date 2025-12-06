<template>
    <div
        class="bg-[url('@/assets/bg.jpg')] bg-cover bg-center py-12 bg-no-repeat h-screen w-screen grid place-items-center"
    >
        <div class="mx-auto w-full max-w-[1320px]">
            <div class="flex justify-between">
                <div>
                    <img
                        src="@/assets/landing-cinco-logo.png"
                        class="mx-auto h-[120px] pt-8 pb-5"
                        alt=""
                    />
                </div>

                <div class="flex gap-5 items-center justify-center">
                    <p class="text-white text-xl">In Cooperation with:</p>
                    <img
                        src="@/assets/landing-bb88-logo.png"
                        class="h-[60px]"
                        alt=""
                    />
                </div>
            </div>

            <div class="text-[#DCDBE0] flex gap-5 mt-10 text-lg">
                <a href="/">HOME</a>
                <div>|</div>
                <a href="">MORE INFO</a>
            </div>

            <div class="mt-12 pb-3 relative overflow-hidden">
                <!-- Gradient overlay with left/right opacity -->
                <div
                    class="absolute inset-0"
                    style="
                        background: linear-gradient(
                            to right,
                            rgba(20, 124, 195, 0),
                            #147cc3 25%,
                            #bf38a6 75%,
                            rgba(191, 56, 166, 0)
                        );
                    "
                ></div>

                <h1
                    class="font-gaming text-center text-white text-7xl relative z-10"
                >
                    <i>REGISTRATION FORM</i>
                </h1>
            </div>

            <div
                class="mt-12 pb-3 relative pb-10 pt-8 px-10"
                style="position: relative"
            >
                <!-- Gradient overlay -->
                <div
                    style="
                        position: absolute;
                        inset: 0;
                        background: linear-gradient(
                            to right,
                            rgba(20, 124, 195, 0),
                            /* transparent left */ #147cc3 25%,
                            /* solid blue */ #bf38a6 75%,
                            /* solid pink */ rgba(191, 56, 166, 0)
                                /* transparent right */
                        );
                        z-index: 0;
                    "
                ></div>

                <!-- Content -->
                <div class="flex items-center gap-4 relative z-10">
                    <h1
                        class="font-gaming text-white text-3xl whitespace-nowrap"
                    >
                        <i>TEAM NAME:</i>
                    </h1>
                    <input
                        type="text"
                        placeholder="Team Name (must be unique)"
                        class="bg-[rgba(0,0,0,0.7)] text-white flex-1 p-2 rounded-md outline-none ring-2 ring-brand-blue"
                    />
                </div>

                <div class="relative z-10">
                    <h1
                        class="font-gaming text-white text-3xl whitespace-nowrap text-center pt-5 pb-6"
                    >
                        <i>ADDRESS</i>
                    </h1>

                    <div class="grid grid-cols-6 gap-4">
                        <div>
                            <label class="text-white">Country</label>
                            <input
                                type="text"
                                placeholder="Philippines"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 rounded-md outline-none ring-2 ring-[#bf38a6]"
                            />
                        </div>
                        <div>
                            <label class="text-white">Region</label>
                            <input
                                type="text"
                                placeholder="Philippines"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 rounded-md outline-none ring-2 ring-[#bf38a6]"
                            />
                        </div>
                        <div>
                            <label class="text-white">Province</label>
                            <input
                                type="text"
                                placeholder="Philippines"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 rounded-md outline-none ring-2 ring-[#bf38a6]"
                            />
                        </div>
                        <div>
                            <label class="text-white">City</label>
                            <input
                                type="text"
                                placeholder="Philippines"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 rounded-md outline-none ring-2 ring-[#bf38a6]"
                            />
                        </div>
                        <div>
                            <label class="text-white">Barangay</label>
                            <input
                                type="text"
                                placeholder="Philippines"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 rounded-md outline-none ring-2 ring-[#bf38a6]"
                            />
                        </div>
                        <div>
                            <label class="text-white">Postal Code</label>
                            <input
                                type="text"
                                placeholder="Philippines"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 rounded-md outline-none ring-2 ring-[#bf38a6]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@font-face {
    font-family: "GamingSporty";
    src: url("../../../fonts/GamingSporty.ttf") format("truetype");
    font-weight: normal;
    font-style: normal;
}

.font-gaming {
    font-family: "GamingSporty", sans-serif;
}
</style>
