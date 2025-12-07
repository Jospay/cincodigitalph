<script setup>
import { ref, computed } from "vue";
import { useAddress } from "@/composables/useAddress";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";

// --- INERTIA FLASH DATA ---
const page = usePage();
const flashStatus = computed(() => page.props.flash?.status);
const flashError = computed(() => page.props.flash?.error);

// --- CONSTANTS ---
const SHIRT_PRICE = 500;
const INITIAL_PLAYER_COUNT = 5;
const BASE_REGISTRATION_FEE = INITIAL_PLAYER_COUNT * SHIRT_PRICE;

// --- ADDRESS COMPOSABLE ---
const {
    regions,
    provinces,
    cities,
    barangays,
    selectedRegion,
    selectedProvince,
    selectedCity,
    selectedBarangay,
} = useAddress();

// --- FORM STATE VARIABLES (v-model) ---
const teamName = ref("");
const postalCode = ref("");
const players = ref(
    Array.from({ length: INITIAL_PLAYER_COUNT }, () => ({
        fullName: "",
        email: "",
        mobileNumber: "",
        accountType: "Player",
    }))
);

// Additional Shirts State
const additionalShirtCount = ref(0);
const availShirtDetails = ref([]);

// --- UI & Submission State ---
const isSubmitting = ref(false);
const submitMessage = ref("");
const submitError = ref(null);

// --- COMPUTED PROPERTIES ---
const availShirts = computed(() =>
    Array.from({ length: additionalShirtCount.value }, (_, index) => {
        if (!availShirtDetails.value[index]) {
            availShirtDetails.value[index] = {
                fullName: "",
                email: "",
                mobileNumber: "",
                accountType: "Shirt",
            };
        }
        return availShirtDetails.value[index];
    })
);

// Computed property for the total payment
const totalPayment = computed(() => {
    const additionalShirtCost = additionalShirtCount.value * SHIRT_PRICE;
    return BASE_REGISTRATION_FEE + additionalShirtCost;
});

// Checks if the selected region is NCR (Region IV - assumed, adjust if different)
const isNcr = computed(() =>
    selectedRegion.value.includes("National Capital Region")
);

// --- METHODS ---
const incrementShirt = () => {
    additionalShirtCount.value++;
};

const decrementShirt = () => {
    if (additionalShirtCount.value > 0) {
        additionalShirtCount.value--;
        availShirtDetails.value.pop();
    }
};

// Function to handle form submission
const registerTeam = async () => {
    isSubmitting.value = true;
    submitMessage.value = "";
    submitError.value = null;

    // 1. Combine all detail users (Players and Shirts)
    const allDetailUsers = [...players.value, ...availShirtDetails.value];

    // 2. Sanitize the details array to ensure mobileNumber is a string
    const sanitizedDetailUsers = allDetailUsers.map((detail) => ({
        ...detail,
        mobileNumber: String(detail.mobileNumber),
    }));

    // 3. Aggregate all data into the required payload structure
    const payload = {
        team: {
            team_name: teamName.value,
            total_payment: totalPayment.value,
            additional_shirt_count: additionalShirtCount.value,
            country: "Philippines",
            region: selectedRegion.value,
            province: selectedProvince.value,
            city: selectedCity.value,
            barangay: selectedBarangay.value,
            postal_code: postalCode.value,
        },
        details: sanitizedDetailUsers, // Use the sanitized array
    };

    // 4. Send data to Laravel backend
    try {
        const response = await axios.post("/api/register", payload);

        // --- Payment Redirection ---
        const checkoutUrl = response.data.checkout_url;

        if (checkoutUrl) {
            submitMessage.value =
                "Registration successful! Redirecting to PayMongo for payment...";

            // CRITICAL FIX: Force the browser to navigate to the external PayMongo URL
            window.location.href = checkoutUrl;
        } else {
            submitMessage.value =
                "Registration successful, but payment URL was not received.";
        }
    } catch (error) {
        if (error.response && error.response.status === 422) {
            // Handle validation errors from Laravel
            const validationErrors = error.response.data.errors;
            let errorMsg = "Validation failed:\n";
            for (const key in validationErrors) {
                errorMsg += `- ${validationErrors[key].join(", ")}\n`;
            }
            submitError.value = errorMsg;
        } else if (error.response && error.response.data.message) {
            // Handle internal server or PayMongo API errors
            submitError.value = error.response.data.message;
        } else {
            submitError.value = `An unknown error occurred: ${error.message}`;
        }
        console.error("Submission Error:", error);
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div
        class="bg-[url('@/assets/bg.jpg')] bg-cover bg-center py-12 bg-no-repeat min-h-screen w-full grid place-items-center"
    >
        <div
            class="mt-12 relative mx-auto w-full max-w-[1500px] pt-8 pb-8 px-10"
        >
            <form
                @submit.prevent="registerTeam"
                class="mx-auto w-full max-w-[1320px]"
            >
                <div class="flex items-center gap-4 relative z-10">
                    <h1
                        class="font-gaming text-white text-3xl whitespace-nowrap"
                    >
                        <i>TEAM NAME:</i>
                    </h1>
                    <input
                        type="text"
                        placeholder="Team Name (must be unique)"
                        v-model="teamName"
                        required
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
                                required
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                name="region"
                            >
                                <option value="" disabled>Select Region</option>
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
                                :required="!isNcr"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6] disabled:opacity-40"
                                name="province"
                            >
                                <option value="" disabled>
                                    Select Province
                                </option>
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
                                required
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6] disabled:opacity-40"
                                name="city"
                            >
                                <option value="" disabled>Select City</option>
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
                                required
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6] disabled:opacity-40"
                                name="barangay"
                            >
                                <option value="" disabled>
                                    Select Barangay
                                </option>
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
                                v-model="postalCode"
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
                                    v-model="p.fullName"
                                    :name="'playerName' + index"
                                    placeholder="Full Name"
                                    required
                                    class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                />
                            </div>

                            <div class="col-span-4">
                                <label class="text-white">E-mail</label>
                                <input
                                    type="email"
                                    v-model="p.email"
                                    :name="'playerEmail' + index"
                                    placeholder="E-mail"
                                    required
                                    class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                />
                            </div>

                            <div class="col-span-4">
                                <label class="text-white">Mobile Number</label>
                                <input
                                    type="number"
                                    v-model="p.mobileNumber"
                                    :name="'playerNumber' + index"
                                    placeholder="Mobile Number"
                                    required
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
                                v-for="(s, index) in availShirts"
                                :key="index"
                            >
                                <div class="col-span-4">
                                    <label class="text-white"
                                        >(Shirt {{ index + 1 }}) Full
                                        Name</label
                                    >
                                    <input
                                        type="text"
                                        v-model="s.fullName"
                                        :name="'availName' + index"
                                        placeholder="Full Name"
                                        required
                                        class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                    />
                                </div>

                                <div class="col-span-4">
                                    <label class="text-white">E-mail</label>
                                    <input
                                        type="email"
                                        v-model="s.email"
                                        :name="'availEmail' + index"
                                        placeholder="E-mail"
                                        required
                                        class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                    />
                                </div>

                                <div class="col-span-4">
                                    <label class="text-white"
                                        >Mobile Number</label
                                    >
                                    <input
                                        type="number"
                                        v-model="s.mobileNumber"
                                        :name="'availNumber' + index"
                                        placeholder="Mobile Number"
                                        required
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
                                                type="button"
                                                @click="decrementShirt"
                                                :disabled="
                                                    additionalShirtCount === 0
                                                "
                                                class="bg-brand-blue text-white px-3 py-0 font-bold text-2xl disabled:opacity-40 disabled:cursor-not-allowed"
                                            >
                                                -
                                            </button>
                                            <button
                                                type="button"
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
                                        type="submit"
                                        :disabled="isSubmitting"
                                        class="py-[3px] mt-[23px] px-10 text-2xl bg-green-register text-white rounded-md border-2 border-white disabled:opacity-50"
                                    >
                                        {{
                                            isSubmitting
                                                ? "Submitting..."
                                                : "Pay Now"
                                        }}
                                    </button>
                                </div>
                            </div>

                            <p
                                v-if="submitError"
                                class="text-red-500 text-sm pt-2 whitespace-pre-line"
                            >
                                {{ submitError }}
                            </p>
                            <p
                                v-else-if="submitMessage"
                                class="text-green-500 text-sm pt-2"
                            >
                                {{ submitMessage }}
                            </p>

                            <p class="text-[#d8d4d4] text-sm pt-2">
                                Note: Base Fee for 5 players is ₱
                                {{
                                    BASE_REGISTRATION_FEE.toLocaleString(
                                        "en-PH",
                                        {
                                            minimumFractionDigits: 2,
                                        }
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
