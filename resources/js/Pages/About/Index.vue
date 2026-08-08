<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";

defineOptions({
    layout: AppLayout,
});

defineProps({
    version: String,
});

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head>
        <title>{{ $t("globals.about.title") }} - Cubrel</title>
    </Head>

    <div class="settings">
        <div class="settings__header">
            <div class="settings__header__title">
                <h3 class="settings__header__title__text">
                    {{ $t("globals.about.title") }}
                </h3>
            </div>
        </div>

        <div class="about-page">
            <div class="about-page__card">
                <img
                    class="about-page__logo"
                    src="/img/logo/default-monochrome.svg"
                    alt="Cubrel"
                />

                <div class="about-page__version">
                    {{ $t("globals.about.version_label") }} {{ version }}
                </div>

                <div class="about-page__links">
                    <a
                        href="https://docs.cubrel.com"
                        target="_blank"
                        rel="noopener"
                    >
                        <i class="fa-solid fa-book"></i>
                        {{ $t("globals.about.documentation") }}
                    </a>
                    <a href="https://cubrel.com" target="_blank" rel="noopener">
                        <i class="fa-solid fa-globe"></i>
                        {{ $t("globals.about.website") }}
                    </a>
                </div>

                <div class="about-page__copyright">
                    {{ $t("globals.about.copyright", { year: currentYear }) }}
                </div>
            </div>
        </div>
    </div>
</template>
