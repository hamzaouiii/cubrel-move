<script setup>
import { computed, reactive, ref, getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import DropdownField from "../Components/FiledTypes/SettingDropdownField.vue";
import Switcher from "../Components/FiledTypes/Switcher.vue";
import Checkbox from "../Components/FiledTypes/Checkbox.vue";
import ColorPicker from "../Components/FiledTypes/ColorPicker.vue";

const { success, error, clearAllAlerts } = useAlerts();

defineOptions({
  layout: AppLayout,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const appSettings = usePage().props.appSettings;

const props = defineProps({
  tabs: { type: Object, default: () => ({}) },
  themeOptions: { type: Array, default: () => [] },
  systemDefaults: { type: Object, default: () => ({}) },
  userOverrides: { type: Object, default: () => ({}) },
  languageOptions: { type: Array, default: () => [] },
  dateFormatOptions: { type: Array, default: () => [] },
  datetimeFormatOptions: { type: Array, default: () => [] },
});

const currentTab = ref(Object.keys(props.tabs)[0] ?? "general");

const currentFields = computed(() =>
  Object.entries(props.tabs[currentTab.value]?.fields ?? {}).map(
    ([key, field]) => ({
      key,
      ...field,
    }),
  ),
);

const allKeys = Object.values(props.tabs).flatMap((tab) =>
  Object.keys(tab.fields),
);

// Which keys currently carry a personal override vs. inherit the System value.
const overridden = reactive(
  Object.fromEntries(
    allKeys.map((key) => [
      key,
      Object.prototype.hasOwnProperty.call(props.userOverrides, key),
    ]),
  ),
);

const form = useForm(
  Object.fromEntries(
    allKeys.map((key) => [
      key,
      Object.prototype.hasOwnProperty.call(props.userOverrides, key)
        ? props.userOverrides[key]
        : props.systemDefaults[key],
    ]),
  ),
);

const markOverridden = (key) => {
  overridden[key] = true;
};

const resetField = (key) => {
  form[key] = props.systemDefaults[key];
  overridden[key] = false;
};

const isDirty = computed(() => form.isDirty);

// Fields backed by an options list (dropdowns/switchers) store a raw value
// (e.g. "l, d.m.Y") — show the matching option's human label instead.
const optionsForType = (type) => {
  if (type === "date") return props.dateFormatOptions;
  if (type === "datetime") return props.datetimeFormatOptions;
  if (type === "lang_switcher") return props.languageOptions;
  if (type === "theme_switcher") return props.themeOptions;
  return null;
};

const systemDefaultLabel = (f) => {
  const value = props.systemDefaults[f.key];

  if (f.type === "bool") {
    const truthy = value === true || value === 1 || value === "1";
    return truthy ? t("fields.checkbox_yes") : t("fields.checkbox_no");
  }

  const options = optionsForType(f.type);
  const match = options?.find((o) => o.value === value);
  return match ? match.label : value;
};

const savePreferences = () => {
  clearAllAlerts();
  form
    .transform((data) => {
      const payload = {};
      allKeys.forEach((key) => {
        payload[key] = overridden[key] ? data[key] : null;
      });
      return payload;
    })
    .put("/preferences", {
      preserveScroll: true,
      onSuccess: () => {
        clearAllAlerts();
        success(t("preferences.update_success"));
      },
      onError: () => {
        clearAllAlerts();
        error(t("preferences.update_error"));
      },
    });
};
</script>

<template>
  <Head>
    <title>{{ $t("preferences.label") }} - Cubrel</title>
  </Head>

  <div
    class="settings"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--module-color': appSettings.primary_color,
    }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <h3 class="settings__header__title__text">
          {{ $t("preferences.label") }}
        </h3>
      </div>
    </div>

    <div class="settings__module__tabs">
      <button
        v-for="(tab, key) in tabs"
        :key="key"
        type="button"
        class="settings__module__tabs__item"
        :class="{ 'settings__module__tabs__item--active': currentTab === key }"
        @click="currentTab = key"
      >
        {{ $t(tab.label) }}
      </button>
    </div>

    <form @submit.prevent="savePreferences">
      <div v-if="currentFields.length === 0" class="settings__empty">
        <i class="fa-solid fa-bell"></i>
        <p>{{ $t("preferences.notifications_placeholder") }}</p>
      </div>

      <div v-else class="settings__system">
        <div class="settings__system__form">
          <div
            v-for="f in currentFields"
            :key="f.key"
            class="settings__system__form__field"
          >
            <div class="preferences__field-label">
              <label>{{ $t(f.label) }}</label>
              <span v-if="!overridden[f.key]" class="settings__optional-label">
                {{
                  $t("preferences.current_system_value", {
                    value: systemDefaultLabel(f),
                  })
                }}
              </span>
            </div>

            <div class="settings__system__form__field__content">
              <template v-if="f.type === 'bool'">
                <Checkbox
                  v-model="form[f.key]"
                  @update:model-value="markOverridden(f.key)"
                />
              </template>
              <template v-else-if="f.type === 'date'">
                <DropdownField
                  v-model="form[f.key]"
                  :options="dateFormatOptions"
                  @update:model-value="markOverridden(f.key)"
                />
              </template>
              <template v-else-if="f.type === 'datetime'">
                <DropdownField
                  v-model="form[f.key]"
                  :options="datetimeFormatOptions"
                  @update:model-value="markOverridden(f.key)"
                />
              </template>
              <template v-else-if="f.type === 'lang_switcher'">
                <Switcher
                  v-model="form[f.key]"
                  :options="languageOptions"
                  @update:model-value="markOverridden(f.key)"
                />
              </template>
              <template v-else-if="f.type === 'theme_switcher'">
                <Switcher
                  v-model="form[f.key]"
                  :options="themeOptions"
                  @update:model-value="markOverridden(f.key)"
                />
              </template>
              <template v-else-if="f.type === 'color'">
                <ColorPicker
                  v-model="form[f.key]"
                  @update:model-value="markOverridden(f.key)"
                />
              </template>
              <template v-else>
                <input
                  type="number"
                  v-model.number="form[f.key]"
                  @input="markOverridden(f.key)"
                />
              </template>

              <button
                v-if="overridden[f.key]"
                type="button"
                class="preferences__reset-btn"
                @click="resetField(f.key)"
              >
                {{ $t("preferences.use_system_default") }}
              </button>
            </div>
          </div>
        </div>

        <div class="settings__actions">
          <button
            type="submit"
            class="settings__actions__save"
            :disabled="!isDirty || form.processing"
          >
            {{ $t("preferences.save") }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<style scoped>
.preferences__subtitle {
  margin: -12px 24px 20px;
  color: #9ba3bc;
  font-size: 0.85rem;
}

.preferences__field-label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 0 0 450px;

  @media (max-width: 1250px) {
    flex: 0 0 250px;
    max-width: 100%;
  }

  @media (max-width: 950px) {
    flex: none;
  }

  label {
    flex: none !important;
  }

  .settings__optional-label {
    margin: 0;
  }
}

.settings__module__tabs__item {
  border: none;
  background: transparent;
  font: inherit;
  outline: none;
  appearance: none;
}

.settings__module__tabs__item--active {
  background: color-mix(in srgb, var(--module-color) 10%, transparent);
}

.preferences__reset-btn {
  margin-left: 10px;
  flex-shrink: 0;
  border: none;
  background: none;
  padding: 0;
  font-size: 12px;
  font-weight: 500;
  color: var(--primary-color);
  cursor: pointer;
  white-space: nowrap;

  &:hover {
    text-decoration: underline;
  }
}
</style>
