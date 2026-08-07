<script setup>
import { computed, getCurrentInstance, reactive, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import Selectbox from "@/Pages/Components/FiledTypes/Selectbox.vue";
import RecordSelectorDrawer from "@/Pages/Components/Modules/RecordSelectorDrawer.vue";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { error } = useAlerts();
const appSettings = usePage().props.appSettings;
const allModules = computed(() => usePage().props.modules);
const allLayouts = computed(() => usePage().props.layouts);

const props = defineProps({
    userFields: Array,
    apiModules: Array,
});

const VERBS = ["read", "write", "delete", "link"];

const crumbs = [
    { label: t("settings.label"), href: "/settings" },
    { label: t("settings.items.api_tokens"), href: "/settings/api-tokens" },
    { label: t("globals.api_tokens.labels.new_page_title") },
];

const userField = { name: "user_id", type: "record", related_module: "users" };

const userIcon = computed(
    () =>
        allModules.value.find((m) => m.slug === "users")?.icon ||
        "fa-solid fa-user",
);

const usersLinkingLayout = computed(
    () =>
        allLayouts.value.find((l) => l.module === "users")?.layouts
            ?.linkingPanel?.columns || null,
);

const userColor = computed(
    () => allModules.value.find((m) => m.slug === "users")?.color || null,
);

const userOverlayOpen = ref(false);

const form = useForm({
    user_id: "",
    user_id__label: "",
    name: "",
    full_access: false,
    abilities: [],
});

const onUserSelect = (record) => {
    form.user_id = record.id;
    form.user_id__label = record.name;
};

const grants = reactive(
    Object.fromEntries(
        props.apiModules.map((m) => [
            m.slug,
            { read: false, write: false, delete: false, link: false },
        ]),
    ),
);

const columnAll = (verb) => ({
    get: () =>
        props.apiModules
            .filter((m) => m.verbs.includes(verb))
            .every((m) => grants[m.slug][verb]),
    set: (checked) => {
        props.apiModules.forEach((m) => {
            if (m.verbs.includes(verb)) {
                grants[m.slug][verb] = checked;
            }
        });
    },
});
const readAll = computed(columnAll("read"));
const writeAll = computed(columnAll("write"));
const deleteAll = computed(columnAll("delete"));
const linkAll = computed(columnAll("link"));
const columnAllModels = { read: readAll, write: writeAll, delete: deleteAll, link: linkAll };

const columnPartial = (verb) => {
    const applicable = props.apiModules.filter((m) => m.verbs.includes(verb));
    const checkedCount = applicable.filter((m) => grants[m.slug][verb]).length;
    return checkedCount > 0 && checkedCount < applicable.length;
};

const submit = () => {
    form.abilities = props.apiModules.flatMap((m) =>
        m.verbs
            .filter((verb) => grants[m.slug][verb])
            .map((verb) => `${m.slug}:${verb}`),
    );

    form.post("/settings/api-tokens", {
        onError: (errors) =>
            Object.values(errors).forEach((message) => error(message)),
    });
};
</script>

<template>
    <Head>
        <title>{{ $t("globals.api_tokens.labels.new_page_title") }}</title>
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
            <form class="settings__module__edit__form" @submit.prevent="submit">
                <div class="settings__module__edit__element">
                    <label>{{
                        $t("globals.api_tokens.labels.name_label")
                    }}</label>
                    <FieldRenderer
                        v-model="form.name"
                        :field="{ name: 'name', type: 'text' }"
                        mode="edit"
                        :hasError="!!form.errors.name"
                    />
                </div>

                <div class="settings__module__edit__element">
                    <label>{{
                        $t("globals.api_tokens.labels.user_label")
                    }}</label>
                    <FieldRenderer
                        v-model="form.user_id"
                        :field="userField"
                        :related_label="form.user_id__label || null"
                        mode="edit"
                        :icon="userIcon"
                        :hasError="!!form.errors.user_id"
                        @open-link-overlay="userOverlayOpen = true"
                    />
                </div>

                <div class="settings__module__edit__element">
                    <label>{{
                        $t("globals.api_tokens.labels.full_access_label")
                    }}</label>
                    <Checkbox
                        v-model="form.full_access"
                        :color="appSettings.primary_color"
                    />
                </div>

                <table
                    v-if="!form.full_access"
                    class="list-layout__table api-tokens__grants"
                >
                    <thead>
                        <tr>
                            <th></th>
                            <th v-for="verb in VERBS" :key="verb">
                                <span>{{
                                    $t(`globals.api_tokens.labels.${verb}`)
                                }}</span>
                                <Selectbox
                                    v-model="columnAllModels[verb].value"
                                    :current-page-all="columnPartial(verb)"
                                    :color="appSettings.primary_color"
                                />
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in apiModules" :key="m.slug">
                            <td>{{ m.name }}</td>
                            <td v-for="verb in VERBS" :key="verb">
                                <Selectbox
                                    v-if="m.verbs.includes(verb)"
                                    v-model="grants[m.slug][verb]"
                                    :color="appSettings.primary_color"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="settings__actions">
                    <button
                        type="button"
                        class="settings__actions__reset"
                        @click="router.visit('/settings/api-tokens')"
                    >
                        {{ $t("globals.api_tokens.buttons.cancel_btn") }}
                    </button>
                    <button
                        type="submit"
                        class="settings__actions__save"
                        :disabled="
                            form.processing || !form.user_id || !form.name
                        "
                    >
                        {{ $t("globals.api_tokens.buttons.create_btn") }}
                    </button>
                </div>
            </form>

            <RecordSelectorDrawer
                :open="userOverlayOpen"
                search-endpoint="/relatedfield/search/users"
                related-module="users"
                :icon="userIcon"
                :accent-color="userColor"
                :layout="usersLinkingLayout"
                :selected-record="form.user_id"
                :fields="userFields"
                allow-create
                @select="onUserSelect"
                @close="userOverlayOpen = false"
            />
        </div>
    </div>
</template>
