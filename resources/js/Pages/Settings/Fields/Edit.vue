<script setup>
import axios from "axios";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, usePage, useForm, router } from "@inertiajs/vue3";
import {
    getCurrentInstance,
    toRef,
    watch,
    computed,
    ref,
    onMounted,
} from "vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useFieldRules } from "@/Composables/useFieldRules";

import ModuleSettingsHeader from "@/Pages/Components/Settings/ModuleSettingsHeader.vue";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import DropdownField from "@/Pages/Components/FiledTypes/SettingDropdownField.vue";
import DropdownSelector from "@/Pages/Components/Settings/Dropdowns/DropdownSelector.vue";
import CreateNewDropdownListModal from "@/Pages/Components/Settings/Dropdowns/CreateNewDropdownListModal.vue";
import EditDropdownListModal from "@/Pages/Components/Settings/Dropdowns/EditDropdownListModal.vue";

const { success, error, info, warning, clearAllAlerts } = useAlerts();

defineOptions({
    layout: [AppLayout, SettingsLayout],
});

const props = defineProps({
    module: Object,
    metadata: Object,
    item: Object,
    field_types: Array,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();
const appSettings = page.props.appSettings;

const form = useForm({
    name: props.metadata.name,
    type: props.metadata.type,
    label: t(props.metadata.label),
    readonly: !!props.metadata.readonly,
    hidden: !!props.metadata.hidden,
    required: !!props.metadata.required,
    searchable: !!props.metadata.searchable,
    filterable: !!props.metadata.filterable,
    sortable: !!props.metadata.sortable,
    default_value: props.metadata.default_value || "",
    min_length: props.metadata.min_length || "",
    max_length: props.metadata.max_length || "",
    regex: props.metadata.regex || "",
    dropdown_list: props.metadata.dropdown_list_id || "",
});

const {
    visibleMetadata,
    applyRules,
    isCheckbox,
    isDropDown,
    isReadonly,
    hasDropdownOptions,
} = useFieldRules(form, toRef(props, "metadata"));

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const selected_dropdown_list = ref(props.metadata.dropdown_list_id || null);
const initialDropdown = ref(props.metadata.dropdown_list_id || null);
const DropDownListOptions = ref([]);

const isDirty = computed(() => {
    return (
        form.isDirty || selected_dropdown_list.value !== initialDropdown.value
    );
});

const openCreateDialog = () => (showCreateDialog.value = true);
const closeCreateDialog = () => (showCreateDialog.value = false);
const openEditDialog = () => {
    if (!selected_dropdown_list.value) return;
    showEditDialog.value = true;
};
const closeEditDialog = () => (showEditDialog.value = false);

const fetchDrodownList = async () => {
    try {
        const { data } = await axios.get("/api/dropdown-lists", {});
        DropDownListOptions.value = data.list;
    } catch (error) {
        console.error(t("settings.dropdown_list_fetch_failed"), error);
    }
};

const assignList = (value) => {
    DropDownListOptions.value.push(value);
    selected_dropdown_list.value = value.id;
};

const getDropdonwItem = (id) => {
    return DropDownListOptions.value.find((e) => e.id === id);
};

onMounted(() => {
    fetchDrodownList();
});

// Apply rules whenever type changes
watch(
    () => form.type,
    (newType) => {
        applyRules(newType);
    },
);

const typesList = () => {
    return props.field_types.map((type) => ({
        value: type,
        label: t(`fields.types.${type}`),
    }));
};

const fieldsUrl = () => {
    const key = props.metadata?.name || "";
    const segments = page.url.split("/").filter(Boolean);
    if (segments.at(-1) === key) {
        segments.pop();
    }
    return "/" + segments.join("/");
};

const saveField = () => {
    info(t("settings.saving"));
    if (hasDropdownOptions(form.type)) {
        form.dropdown_list = selected_dropdown_list.value;
    }
    form.put(page.url, {
        preserveScroll: true,
        onSuccess: () => {
            clearAllAlerts();
            initialDropdown.value = selected_dropdown_list.value;
            success(t("fields.field_update_success"));
            router.visit(fieldsUrl());
        },
        onError: () => {
            clearAllAlerts();
            error(t("fields.field_update_error"));
        },
    });
};

const resetForm = () => {
    form.reset();
    selected_dropdown_list.value = initialDropdown.value;
    warning(t("fields.field_reset_success"));
};

const moduleColor = computed(() =>
    appSettings.use_individual_module_colors
        ? props.module.color
        : appSettings.primary_color,
);
</script>

<template>
    <Head>
        <title>
            {{ $t(metadata.label) }} - {{ module.label }} -
            {{ $t("fields.label") }} - {{ $t("settings.label") }} - Cubrel
        </title>
    </Head>

    <div
        class="settings"
        :style="
            ({ '--primary-color': appSettings.primary_color },
            { '--module-color': moduleColor })
        "
    >
        <ModuleSettingsHeader :setting-module="module" active-key="fields" />

        <div class="settings__module__edit">
            <form
                class="settings__module__edit__form"
                @submit.prevent="saveField"
            >
                <div
                    v-for="fieldName in visibleMetadata"
                    :key="fieldName"
                    class="settings__module__edit__element"
                >
                    <label class="settings__module__edit__element__label">
                        {{ $t("fields.metadata." + fieldName) }}
                    </label>

                    <div class="settings__module__edit__element__content">
                        <template v-if="isReadonly(fieldName, true)">
                            <input
                                type="text"
                                v-model="form[fieldName]"
                                disabled
                            />

                            <transition name="dropdown-fade">
                                <div
                                    v-if="
                                        fieldName === 'type' &&
                                        hasDropdownOptions(form.type)
                                    "
                                    class="dropdown-selector"
                                >
                                    <DropdownSelector
                                        v-model="selected_dropdown_list"
                                        :options="DropDownListOptions"
                                        @onOpenCreateDialog="openCreateDialog"
                                        @onOpenEditDialog="openEditDialog"
                                        :is-draft="form.type !== 'status'"
                                    />
                                </div>
                            </transition>
                        </template>

                        <template v-else-if="isCheckbox(fieldName)">
                            <Checkbox v-model="form[fieldName]" />
                            <span
                                v-if="form.errors[fieldName]"
                                class="settings__module__edit__element__error"
                            >
                                {{ form.errors[fieldName] }}
                            </span>
                        </template>

                        <template v-else-if="isDropDown(fieldName)">
                            <DropdownField
                                v-model="form[fieldName]"
                                :options="typesList()"
                            />
                            <span
                                v-if="form.errors[fieldName]"
                                class="settings__module__edit__element__error"
                            >
                                {{ form.errors[fieldName] }}
                            </span>
                        </template>

                        <template v-else>
                            <input
                                type="text"
                                v-model="form[fieldName]"
                                :class="{
                                    'settings__module__edit__element--error-field':
                                        form.errors[fieldName],
                                }"
                            />
                            <span
                                v-if="form.errors[fieldName]"
                                class="settings__module__edit__element__error"
                            >
                                {{ form.errors[fieldName] }}
                            </span>
                        </template>
                    </div>
                </div>

                <div class="settings__actions">
                    <button
                        type="button"
                        class="settings__actions__reset"
                        @click="resetForm"
                        :disabled="!isDirty"
                    >
                        {{ $t("settings.reset") }}
                    </button>

                    <button
                        type="submit"
                        class="settings__actions__save"
                        :disabled="!isDirty"
                    >
                        {{ $t("settings.save") }}
                    </button>
                </div>
            </form>
        </div>

        <CreateNewDropdownListModal
            @onCloseModal="closeCreateDialog"
            @listCreated="assignList"
            :is-status="form.type === 'status'"
            :module-slug="module.slug"
            :field-label="form.label"
            v-if="showCreateDialog"
        />

        <EditDropdownListModal
            :dropdown="getDropdonwItem(selected_dropdown_list)"
            :is-status="form.type === 'status'"
            @onCloseModal="closeEditDialog"
            v-if="showEditDialog"
        />
    </div>
</template>
