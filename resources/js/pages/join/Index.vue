<template>
    <div
        class="bg-[url('@/assets/bg.jpg')] bg-cover bg-center py-12 bg-no-repeat h-screen w-screen grid place-items-center"
    >
        <div class="mx-auto w-full max-w-[1320px]">
            <div class="text-[#DCDBE0] flex gap-5 text-lg">
                <a href="/">HOME</a>
                <div>|</div>
                <a href="">MORE INFO</a>
            </div>
            <img
                src="@/assets/landing-cinco-logo.png"
                class="mx-auto w-[400px] pt-8 pb-5"
                alt=""
            />

            <div class="flex gap-5 items-center justify-center">
                <p class="text-white text-xl">In Cooperation with:</p>
                <img
                    src="@/assets/landing-bb88-logo.png"
                    class="h-[60px]"
                    alt=""
                />
            </div>

            <h1 class="font-gaming text-center text-white text-7xl pt-12">
                <i>ENTER THE BATTLEZONE</i>
            </h1>

            <img src="@/assets/moba1.jpg" class="rounded-3xl mt-7" alt="" />

            <div class="flex justify-center pt-14 w-full">
                <a href="/register">
                    <img
                        src="@/assets/reg-button1.png"
                        class="w-[500px]"
                        alt=""
                    />
                </a>
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
