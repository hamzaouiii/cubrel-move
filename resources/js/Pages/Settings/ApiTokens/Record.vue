<script setup>
import { getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { useClipboard } from "@/Composables/useClipboard";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { copy } = useClipboard();
const appSettings = usePage().props.appSettings;

const props = defineProps({
    token: Object,
    plaintextToken: String,
});

const crumbs = [
    { label: t("settings.label"), href: "/settings" },
    { label: t("settings.items.api_tokens"), href: "/settings/api-tokens" },
    { label: t("globals.api_tokens.labels.show_page_title") },
];
</script>

<template>
    <Head>
        <title>{{ $t("globals.api_tokens.labels.show_page_title") }}</title>
    </Head>

    <div
        class="settings"
        :style="{
            '--primary-color': appSettings.primary_color,
            '--module-color': appSettings.primary_color,
            '--danger-color': appSettings.danger_color,
        }"
    >
        <div class="settings__module__header">
            <SettingsBreadcrumb :crumbs="crumbs" />
        </div>

        <div class="settings__module__edit">
            <div class="settings__module__edit__form">
                <div class="settings__module__edit__element">
                    <label>{{
                        $t("globals.api_tokens.labels.name_label")
                    }}</label>
                    <span>{{ token.name }}</span>
                </div>

                <div class="settings__module__edit__element">
                    <label>{{
                        $t("globals.api_tokens.labels.user_label")
                    }}</label>
                    <span
                        >{{ token.owner_name }} ({{ token.owner_email }})</span
                    >
                </div>

                <div class="settings__module__edit__element">
                    <label>{{
                        $t("globals.api_tokens.labels.abilities_column")
                    }}</label>
                    <span>{{ token.abilities.join(", ") }}</span>
                </div>

                <div class="api-tokens__new-token">
                    <span class="api-tokens__new-token__title">{{
                        $t("globals.api_tokens.labels.new_token_title")
                    }}</span>
                    <p>{{ $t("globals.api_tokens.hints.new_token") }}</p>
                    <div class="api-tokens__new-token__value">
                        <code>{{ plaintextToken }}</code>
                        <button
                            type="button"
                            class="api-tokens__new-token__copy"
                            @click="copy(plaintextToken)"
                        >
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="settings__actions">
                    <button
                        type="button"
                        class="settings__actions__save"
                        @click="router.visit('/settings/api-tokens')"
                    >
                        {{ $t("globals.api_tokens.buttons.done_btn") }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
