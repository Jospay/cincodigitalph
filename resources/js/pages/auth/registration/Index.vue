<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useAddress } from "@/composables/useAddress";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";

// --- CONSTANTS ---
const SHIRT_PRICE = 500;
const INITIAL_PLAYER_COUNT = 5;
const BASE_REGISTRATION_FEE = INITIAL_PLAYER_COUNT * SHIRT_PRICE;
const LOCAL_STORAGE_KEY = "teamRegistrationDraft"; // Key for Local Storage

// --- ADDRESS COMPOSABLE ---
// The address refs are still bound to the template, but their values are NOT restored from LS.
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
const players = ref([]);
const additionalShirtCount = ref(0);
const availShirtDetails = ref([]);

// --- UI & Submission State ---
const isSubmitting = ref(false);
const submitMessage = ref("");
const submitError = ref(null);
const validationErrors = ref({});

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

const totalPayment = computed(() => {
    const additionalShirtCost = additionalShirtCount.value * SHIRT_PRICE;
    return BASE_REGISTRATION_FEE + additionalShirtCost;
});

const isNcr = computed(
    () =>
        selectedRegion.value &&
        selectedRegion.value.includes("National Capital Region")
);

// --- LOCAL STORAGE FUNCTIONS ---

/**
 * Loads data from Local Storage and initializes the form state (EXCLUDING ADDRESS).
 */
const loadFormState = () => {
    try {
        const storedData = localStorage.getItem(LOCAL_STORAGE_KEY);
        if (storedData) {
            const data = JSON.parse(storedData);

            // Basic Team Info:
            teamName.value = data.teamName || "";
            postalCode.value = data.postalCode || "";

            // Players/Details
            players.value =
                data.players ||
                Array.from({ length: INITIAL_PLAYER_COUNT }, () => ({
                    fullName: "",
                    email: "",
                    mobileNumber: "",
                    accountType: "Player",
                }));
            additionalShirtCount.value = data.additionalShirtCount || 0;
            availShirtDetails.value = data.availShirtDetails || [];
        } else {
            // Initialize default players if no data is found
            players.value = Array.from(
                { length: INITIAL_PLAYER_COUNT },
                () => ({
                    fullName: "",
                    email: "",
                    mobileNumber: "",
                    accountType: "Player",
                })
            );
        }
    } catch (e) {
        console.error("Error loading state from localStorage:", e);
        // Fallback to default state initialization
        players.value = Array.from({ length: INITIAL_PLAYER_COUNT }, () => ({
            fullName: "",
            email: "",
            mobileNumber: "",
            accountType: "Player",
        }));
    }
};

/**
 * Saves the current form state to Local Storage (EXCLUDING ADDRESS).
 */
const saveFormState = () => {
    const dataToSave = {
        teamName: teamName.value,
        postalCode: postalCode.value,
        // --- ADDRESS FIELDS ARE REMOVED HERE ---
        // selectedRegion: selectedRegion.value,
        // selectedProvince: selectedProvince.value,
        // selectedCity: selectedCity.value,
        // selectedBarangay: selectedBarangay.value,
        players: players.value,
        additionalShirtCount: additionalShirtCount.value,
        availShirtDetails: availShirtDetails.value,
    };
    try {
        localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(dataToSave));
    } catch (e) {
        console.error("Error saving state to localStorage:", e);
    }
};

/**
 * Clears the saved draft from Local Storage.
 */
const clearFormState = () => {
    try {
        localStorage.removeItem(LOCAL_STORAGE_KEY);
    } catch (e) {
        console.error("Error clearing state from localStorage:", e);
    }
};

// --- LIFE CYCLE HOOKS ---
onMounted(() => {
    // 1. Load data from Local Storage when the component mounts
    loadFormState();
});

// 2. Watch variables and save to Local Storage on change
watch(
    [
        teamName,
        postalCode,
        // --- ADDRESS FIELDS ARE REMOVED FROM THE WATCHER ---
        // selectedRegion,
        // selectedProvince,
        // selectedCity,
        // selectedBarangay,
        players,
        additionalShirtCount,
        availShirtDetails,
    ],
    saveFormState,
    { deep: true } // Use deep watch for arrays and objects (players, details)
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

const getError = (fieldName) => {
    return validationErrors.value[fieldName]
        ? validationErrors.value[fieldName][0]
        : null;
};

// Function to handle form submission
const registerTeam = async () => {
    isSubmitting.value = true;
    submitMessage.value = "";
    submitError.value = null;
    validationErrors.value = {}; // Clear previous errors

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
            // The currently selected values are taken here, which will be empty on refresh
            // unless the user manually selected them.
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

        // *** CRITICAL STEP: CLEAR LOCAL STORAGE ON SUCCESS ***
        clearFormState();

        // --- Payment Redirection ---
        const checkoutUrl = response.data.checkout_url;

        if (checkoutUrl) {
            submitMessage.value =
                "Registration successful! Redirecting to PayMongo for payment...";

            window.location.href = checkoutUrl;
        } else {
            submitMessage.value =
                "Registration successful, but payment URL was not received.";
        }
    } catch (error) {
        if (error.response && error.response.status === 422) {
            // Handle validation errors from Laravel
            validationErrors.value = error.response.data.errors;

            submitError.value = "Please check the form for highlighted errors.";
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
        <div class="mx-auto w-full max-w-[1320px] px-5">
            <div class="flex flex-col md:flex-row justify-between">
                <div>
                    <img
                        src="@/assets/landing-cinco-logo.png"
                        class="mx-auto sm:h-[70px] h-auto sm:w-auto w-full pb-5"
                        alt=""
                    />
                </div>

                <div
                    class="flex gap-5 flex-col sm:flex-row items-center justify-between"
                >
                    <p class="text-white text-lg">In Cooperation with:</p>
                    <img
                        src="@/assets/landing-bb88-logo.png"
                        class="sm:h-[50px] h-auto sm:w-auto w-full"
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
                    class="font-gaming text-center md:pt-0 pt-2 text-white lg:text-7xl md:text-5xl text-3xl relative z-10"
                >
                    <i>REGISTRATION FORM</i>
                </h1>
            </div>
        </div>

        <div
            class="mt-12 relative mx-auto w-full max-w-[1500px] pt-8 pb-8 sm:px-10"
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

            <form
                @submit.prevent="registerTeam"
                class="mx-auto w-full max-w-[1320px] px-5"
            >
                <div class="sm:flex grid items-center gap-4 relative z-10">
                    <h1
                        class="font-gaming text-white mx-auto sm:text-3xl text-2xl"
                    >
                        <i>TEAM NAME:</i>
                    </h1>
                    <div class="flex-1">
                        <input
                            type="text"
                            placeholder="Team Name (must be unique)"
                            v-model="teamName"
                            required
                            class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 rounded-md outline-none ring-2 ring-brand-blue"
                            :class="{
                                'ring-red-500': getError('team.team_name'),
                            }"
                        />
                        <p
                            v-if="getError('team.team_name')"
                            class="text-red-500 text-xs pt-1"
                        >
                            {{ getError("team.team_name") }}
                        </p>
                    </div>
                </div>

                <div class="relative z-10">
                    <h1
                        class="font-gaming text-white sm:text-3xl text-2xl text-center py-5"
                    >
                        <i>ADDRESS</i>
                    </h1>

                    <div class="grid grid-cols-12 gap-4">
                        <div
                            class="xl:col-span-2 md:col-span-4 sm:col-span-6 col-span-12"
                        >
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

                        <div
                            class="xl:col-span-2 md:col-span-4 sm:col-span-6 col-span-12"
                        >
                            <label class="text-white">Region</label>
                            <select
                                v-model="selectedRegion"
                                required
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                name="region"
                                :class="{
                                    'ring-red-500': getError('team.region'),
                                }"
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
                            <p
                                v-if="getError('team.region')"
                                class="text-red-500 text-xs pt-1"
                            >
                                {{ getError("team.region") }}
                            </p>
                        </div>

                        <div
                            class="xl:col-span-2 md:col-span-4 sm:col-span-6 col-span-12"
                        >
                            <label class="text-white">Province</label>
                            <select
                                v-model="selectedProvince"
                                :disabled="isNcr || !selectedRegion"
                                :required="!isNcr"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6] disabled:opacity-40"
                                name="province"
                                :class="{
                                    'ring-red-500': getError('team.province'),
                                }"
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
                            <p
                                v-if="getError('team.province')"
                                class="text-red-500 text-xs pt-1"
                            >
                                {{ getError("team.province") }}
                            </p>
                        </div>

                        <div
                            class="xl:col-span-2 md:col-span-4 sm:col-span-6 col-span-12"
                        >
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
                                :class="{
                                    'ring-red-500': getError('team.city'),
                                }"
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
                            <p
                                v-if="getError('team.city')"
                                class="text-red-500 text-xs pt-1"
                            >
                                {{ getError("team.city") }}
                            </p>
                        </div>

                        <div
                            class="xl:col-span-2 md:col-span-4 sm:col-span-6 col-span-12"
                        >
                            <label class="text-white">Barangay</label>
                            <select
                                v-model="selectedBarangay"
                                :disabled="!selectedCity"
                                required
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full px-2 py-[11px] mt-1 rounded-md outline-none ring-2 ring-[#bf38a6] disabled:opacity-40"
                                name="barangay"
                                :class="{
                                    'ring-red-500': getError('team.barangay'),
                                }"
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
                            <p
                                v-if="getError('team.barangay')"
                                class="text-red-500 text-xs pt-1"
                            >
                                {{ getError("team.barangay") }}
                            </p>
                        </div>

                        <div
                            class="xl:col-span-2 md:col-span-4 sm:col-span-6 col-span-12"
                        >
                            <label class="text-white">Postal Code</label>
                            <input
                                type="text"
                                placeholder="Postal Code"
                                v-model="postalCode"
                                class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                name="postal"
                                :class="{
                                    'ring-red-500':
                                        getError('team.postal_code'),
                                }"
                            />
                            <p
                                v-if="getError('team.postal_code')"
                                class="text-red-500 text-xs pt-1"
                            >
                                {{ getError("team.postal_code") }}
                            </p>
                        </div>
                    </div>

                    <h1
                        class="font-gaming text-white sm:text-3xl text-2xl text-center py-5"
                    >
                        <i>TEAM MEMBER'S</i>
                    </h1>

                    <div class="grid grid-cols-12 gap-4">
                        <template v-for="(p, index) in players" :key="index">
                            <div
                                class="md:col-span-4 sm:col-span-6 col-span-12"
                            >
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
                                    :class="{
                                        'ring-red-500': getError(
                                            `details.${index}.fullName`
                                        ),
                                    }"
                                />
                                <p
                                    v-if="getError(`details.${index}.fullName`)"
                                    class="text-red-500 text-xs pt-1"
                                >
                                    {{ getError(`details.${index}.fullName`) }}
                                </p>
                            </div>

                            <div
                                class="md:col-span-4 sm:col-span-6 col-span-12"
                            >
                                <label class="text-white">E-mail</label>
                                <input
                                    type="email"
                                    v-model="p.email"
                                    :name="'playerEmail' + index"
                                    placeholder="E-mail"
                                    required
                                    class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                    :class="{
                                        'ring-red-500': getError(
                                            `details.${index}.email`
                                        ),
                                    }"
                                />
                                <p
                                    v-if="getError(`details.${index}.email`)"
                                    class="text-red-500 text-xs pt-1"
                                >
                                    {{ getError(`details.${index}.email`) }}
                                </p>
                            </div>

                            <div
                                class="md:col-span-4 sm:col-span-6 col-span-12"
                            >
                                <label class="text-white">Mobile Number</label>
                                <input
                                    type="number"
                                    v-model="p.mobileNumber"
                                    :name="'playerNumber' + index"
                                    placeholder="Mobile Number"
                                    required
                                    class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                    :class="{
                                        'ring-red-500': getError(
                                            `details.${index}.mobileNumber`
                                        ),
                                    }"
                                />
                                <p
                                    v-if="
                                        getError(
                                            `details.${index}.mobileNumber`
                                        )
                                    "
                                    class="text-red-500 text-xs pt-1"
                                >
                                    {{
                                        getError(
                                            `details.${index}.mobileNumber`
                                        )
                                    }}
                                </p>
                            </div>
                        </template>
                    </div>

                    <template v-if="additionalShirtCount > 0">
                        <h1
                            class="font-gaming text-white sm:text-3xl text-2xl text-center py-5"
                        >
                            <i>Availing Shirt Details</i>
                        </h1>

                        <div class="grid grid-cols-12 gap-4">
                            <template
                                v-for="(s, index) in availShirts"
                                :key="index"
                            >
                                <div
                                    class="md:col-span-4 sm:col-span-6 col-span-12"
                                >
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
                                        :class="{
                                            'ring-red-500': getError(
                                                `details.${
                                                    INITIAL_PLAYER_COUNT + index
                                                }.fullName`
                                            ),
                                        }"
                                    />
                                    <p
                                        v-if="
                                            getError(
                                                `details.${
                                                    INITIAL_PLAYER_COUNT + index
                                                }.fullName`
                                            )
                                        "
                                        class="text-red-500 text-xs pt-1"
                                    >
                                        {{
                                            getError(
                                                `details.${
                                                    INITIAL_PLAYER_COUNT + index
                                                }.fullName`
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="md:col-span-4 sm:col-span-6 col-span-12"
                                >
                                    <label class="text-white">E-mail</label>
                                    <input
                                        type="email"
                                        v-model="s.email"
                                        :name="'availEmail' + index"
                                        placeholder="E-mail"
                                        required
                                        class="bg-[rgba(0,0,0,0.7)] text-white w-full p-2 mt-1 rounded-md outline-none ring-2 ring-[#bf38a6]"
                                        :class="{
                                            'ring-red-500': getError(
                                                `details.${
                                                    INITIAL_PLAYER_COUNT + index
                                                }.email`
                                            ),
                                        }"
                                    />
                                    <p
                                        v-if="
                                            getError(
                                                `details.${
                                                    INITIAL_PLAYER_COUNT + index
                                                }.email`
                                            )
                                        "
                                        class="text-red-500 text-xs pt-1"
                                    >
                                        {{
                                            getError(
                                                `details.${
                                                    INITIAL_PLAYER_COUNT + index
                                                }.email`
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="md:col-span-4 sm:col-span-6 col-span-12"
                                >
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
                                        :class="{
                                            'ring-red-500': getError(
                                                `details.${
                                                    INITIAL_PLAYER_COUNT + index
                                                }.mobileNumber`
                                            ),
                                        }"
                                    />
                                    <p
                                        v-if="
                                            getError(
                                                `details.${
                                                    INITIAL_PLAYER_COUNT + index
                                                }.mobileNumber`
                                            )
                                        "
                                        class="text-red-500 text-xs pt-1"
                                    >
                                        {{
                                            getError(
                                                `details.${
                                                    INITIAL_PLAYER_COUNT + index
                                                }.mobileNumber`
                                            )
                                        }}
                                    </p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div
                        class="xl:flex grid xl:justify-between justify-center items-center mt-4"
                    >
                        <div>
                            <h1
                                class="font-gaming text-white sm:text-3xl text-2xl py-5"
                            >
                                <i>PAYMENT:</i>
                            </h1>
                        </div>

                        <div class="">
                            <div class="grid grid-cols-6 gap-4">
                                <div class="sm:col-span-2 col-span-6">
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

                                <div class="sm:col-span-2 col-span-6">
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

                                <div class="sm:col-span-2 col-span-6">
                                    <button
                                        type="submit"
                                        :disabled="isSubmitting"
                                        class="sm:py-[3px] py-[5px] sm:mt-[23px] mt-4 w-full text-2xl bg-green-register text-white rounded-md border-2 border-white disabled:opacity-50"
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

                            <p
                                class="text-[#d8d4d4] xl:text-start text-center text-sm pt-2"
                            >
                                Note: Every add on shirt is worth ₱ 500.00 (Any
                                add on shirt does not mean they can play)
                                <span class="hidden sm:inline">
                                    <br />
                                </span>
                                (The 5 listed player's automatically has Shirt
                                upon Registration, costing
                                {{
                                    BASE_REGISTRATION_FEE.toLocaleString(
                                        "en-PH",
                                        {
                                            minimumFractionDigits: 2,
                                        }
                                    )
                                }})
                            </p>
                        </div>
                    </div>
                </div>
            </form>
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
