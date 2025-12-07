<script setup>
import { ref, computed } from "vue";
import { useAddress } from "@/composables/useAddress";

const SHIRT_PRICE = 500;
const INITIAL_PLAYER_COUNT = 5;
const BASE_REGISTRATION_FEE = INITIAL_PLAYER_COUNT * SHIRT_PRICE; // 5 players * 500

const {
    regions,
    provinces,
    cities,
    barangays,
    selectedRegion,
    selectedProvince,
    selectedCity,
    selectedBarangay,
    isLoadingRegions,
    isLoadingProvinces,
    isLoadingCities,
    isLoadingBarangays,
} = useAddress();

const players = Array.from({ length: INITIAL_PLAYER_COUNT });

// Use a reactive reference for the number of additional shirts
const additionalShirtCount = ref(0);

// Use a computed property to create the array for the v-for loop
const availShirts = computed(() =>
    Array.from({ length: additionalShirtCount.value })
);

// Computed property for the total payment
const totalPayment = computed(() => {
    const additionalShirtCost = additionalShirtCount.value * SHIRT_PRICE;
    return BASE_REGISTRATION_FEE + additionalShirtCost;
});

// Function to increment the additional shirt count
const incrementShirt = () => {
    additionalShirtCount.value++;
};

// Function to decrement the additional shirt count, preventing it from going below 0
const decrementShirt = () => {
    if (additionalShirtCount.value > 0) {
        additionalShirtCount.value--;
    }
};

const isNcr = computed(() => false);
</script>

<template>
    <div
        class="bg-[url('@/assets/bg.jpg')] bg-cover bg-center py-12 bg-no-repeat min-h-screen w-full grid place-items-center"
    >
        <div class="mx-auto w-full max-w-[1320px]">
            <div class="flex justify-between">
                <div>
                    <img
                        src="@/assets/landing-cinco-logo.png"
                        class="mx-auto h-[70px] pb-5"
                        alt=""
                    />
                </div>

                <div class="flex gap-5 items-center justify-center">
                    <p class="text-white text-lg">In Cooperation with:</p>
                    <img
                        src="@/assets/landing-bb88-logo.png"
                        class="h-[50px]"
                        alt=""
                    />
                </div>
            </div>

            <div class="text-[#DCDBE0] flex gap-5 mt-7 text-lg">
                <a href="/">HOME</a>
                <div>|</div>
                <a href="">MORE INFO</a>
            </div>
        </div>

        <div class="mt-4 pb-3 relative mx-auto w-full max-w-[1500px] px-5">
            <div
                class="absolute inset-0 opacity-80"
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
            <div class="mx-auto w-full max-w-[1320px]">
                <h1
                    class="font-gaming text-center text-white text-7xl relative z-10"
                >
                    <i>REGISTRATION FORM</i>
                </h1>
            </div>
        </div>

        <div
            class="mt-12 relative mx-auto w-full max-w-[1500px] pt-8 pb-8 px-10"
        >
            <div
                class="opacity-80"
                style="
                    position: absolute;
                    inset: 0;
                    background: linear-gradient(
                        to right,
                        rgba(20, 124, 195, 0),
                        #147cc3 25%,
                        #bf38a6 75%,
                        rgba(191, 56, 166, 0)
                    );
                    z-index: 0;
                "
            ></div>
            <div class="mx-auto w-full max-w-[1320px]">
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
                        class="font-gaming text-white text-3xl whitespace-nowrap text-center py-5"
                    >
                        <i>ADDRESS</i>
                    </h1>

                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-2">
                            <label class="text-white">Country</label>
                            <div class="relative">
                                <img
                                    src="@/assets/ph.jpg"
                                    class="absolute ps-3 pt-1 top-1/2 -translate-y-1/2 left-0"
                                    alt=""
                                />
                                <input
                                    type="text"
                                    value="Philippines"
                                    class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 text-center mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                    readonly
                                />
                            </div>
                        </div>

                        <div class="col-span-2">
                            <label class="text-white">Region</label>
                            <select
                                v-model="selectedRegion"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                name="region"
                            >
                                <option value="">Select Region</option>
                                <option
                                    v-for="r in regions"
                                    :key="r.code"
                                    :value="r.name"
                                >
                                    {{ r.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="text-white">Province</label>
                            <select
                                v-model="selectedProvince"
                                :disabled="isNcr || !selectedRegion"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6] disabled:opacity-40"
                                name="province"
                            >
                                <option value="">Select Province</option>
                                <option
                                    v-for="p in provinces"
                                    :key="p.code"
                                    :value="p.name"
                                >
                                    {{ p.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="text-white">City</label>
                            <select
                                v-model="selectedCity"
                                :disabled="
                                    (!isNcr && !selectedProvince) ||
                                    cities.length === 0
                                "
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6] disabled:opacity-40"
                                name="city"
                            >
                                <option value="">Select City</option>
                                <option
                                    v-for="c in cities"
                                    :key="c.code"
                                    :value="c.name"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="text-white">Barangay</label>
                            <select
                                v-model="selectedBarangay"
                                :disabled="!selectedCity"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6] disabled:opacity-40"
                                name="barangay"
                            >
                                <option value="">Select Barangay</option>
                                <option
                                    v-for="b in barangays"
                                    :key="b.code"
                                    :value="b.name"
                                >
                                    {{ b.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="text-white">Postal Code</label>
                            <input
                                type="text"
                                placeholder="Postal Code"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                name="postal"
                            />
                        </div>
                    </div>

                    <h1
                        class="font-gaming text-white text-3xl whitespace-nowrap text-center py-5"
                    >
                        <i>TEAM MEMBER'S</i>
                    </h1>

                    <div class="grid grid-cols-12 gap-4">
                        <template v-for="(p, index) in players" :key="index">
                            <div class="col-span-4">
                                <label class="text-white"
                                    >(Player {{ index + 1 }}) Full Name</label
                                >
                                <input
                                    type="text"
                                    :name="'playerName' + index"
                                    placeholder="Full Name"
                                    class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                />
                            </div>

                            <div class="col-span-4">
                                <label class="text-white">E-mail</label>
                                <input
                                    type="email"
                                    :name="'playerEmail' + index"
                                    placeholder="E-mail"
                                    class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                />
                            </div>

                            <div class="col-span-4">
                                <label class="text-white">Mobile Number</label>
                                <input
                                    type="number"
                                    :name="'playerNumber' + index"
                                    placeholder="Mobile Number"
                                    class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                />
                            </div>
                        </template>
                    </div>

                    <template v-if="additionalShirtCount > 0">
                        <h1
                            class="font-gaming text-white text-3xl whitespace-nowrap text-center py-5"
                        >
                            <i>Availing Shirt Details</i>
                        </h1>

                        <div class="grid grid-cols-12 gap-4">
                            <template
                                v-for="(p, index) in availShirts"
                                :key="index"
                            >
                                <div class="col-span-4">
                                    <label class="text-white"
                                        >(Shirt {{ index + 1 }}) Full
                                        Name</label
                                    >
                                    <input
                                        type="text"
                                        :name="'availName' + index"
                                        placeholder="Full Name"
                                        class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                    />
                                </div>

                                <div class="col-span-4">
                                    <label class="text-white">E-mail</label>
                                    <input
                                        type="email"
                                        :name="'availEmail' + index"
                                        placeholder="E-mail"
                                        class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                    />
                                </div>

                                <div class="col-span-4">
                                    <label class="text-white"
                                        >Mobile Number</label
                                    >
                                    <input
                                        type="number"
                                        :name="'availNumber' + index"
                                        placeholder="Mobile Number"
                                        class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                    />
                                </div>
                            </template>
                        </div>
                    </template>
                    <div class="flex justify-between items-center mt-4">
                        <div>
                            <h1
                                class="font-gaming text-white text-3xl whitespace-nowrap py-5"
                            >
                                <i>PAYMENT:</i>
                            </h1>
                        </div>

                        <div class="w-[50%]">
                            <div class="grid grid-cols-6 gap-4">
                                <div class="col-span-2">
                                    <label for="" class="text-white text-[14px]"
                                        >Add on Shirt:</label
                                    >

                                    <div class="relative">
                                        <div
                                            class="flex gap-2 absolute pe-2 pt-1 top-1/2 -translate-y-1/2 right-0"
                                        >
                                            <button
                                                @click="decrementShirt"
                                                :disabled="
                                                    additionalShirtCount === 0
                                                "
                                                class="bg-brand-blue text-white px-3 py-0 font-bold text-2xl disabled:opacity-40 disabled:cursor-not-allowed"
                                            >
                                                -
                                            </button>
                                            <button
                                                @click="incrementShirt"
                                                class="bg-brand-blue text-white px-2 py-0 font-bold text-2xl"
                                            >
                                                +
                                            </button>
                                        </div>

                                        <input
                                            type="text"
                                            :value="additionalShirtCount"
                                            readonly
                                            class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-brand-blue"
                                        />
                                    </div>
                                </div>

                                <div class="col-span-2">
                                    <label for="" class="text-white text-[14px]"
                                        >Total</label
                                    >
                                    <input
                                        type="text"
                                        :value="`₱ ${totalPayment.toLocaleString(
                                            'en-PH',
                                            {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2,
                                            }
                                        )}`"
                                        class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-green-register"
                                        readonly
                                    />
                                </div>

                                <div class="col-span-2">
                                    <button
                                        class="py-[3px] mt-[23px] px-10 text-2xl bg-green-register text-white rounded-md border-2 border-white"
                                    >
                                        Pay Now
                                    </button>
                                </div>
                            </div>

                            <p class="text-[#d8d4d4] text-sm pt-2">
                                Note: Every add on shirt is worth ₱ 500.00 (Any
                                add on shirt does not mean they can play) <br />
                                (The 5 listed player's automatically has Shirt
                                upon Registration, costing **₱
                                {{
                                    BASE_REGISTRATION_FEE.toLocaleString(
                                        "en-PH",
                                        {
                                            minimumFractionDigits: 2,
                                        }
                                    )
                                }}**)
                            </p>
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
