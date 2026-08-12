<script setup>
import { computed, reactive, ref, getCurrentInstance, onUnmounted } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import DropdownField from "../Components/FiledTypes/SettingDropdownField.vue";
import Switcher from "../Components/FiledTypes/Switcher.vue";
import ThemeSwitcher from "../Components/FiledTypes/ThemeSwitcher.vue";
import Checkbox from "../Components/FiledTypes/Checkbox.vue";
import ColorPicker from "../Components/FiledTypes/ColorPicker.vue";
import AppTooltip from "../Components/Globals/AppTooltip.vue";
import IntegerField from "../Components/FiledTypes/IntegerField.vue";

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

const tabFromUrl = () => {
  const key = new URLSearchParams(window.location.search).get("tab");
  return key && props.tabs[key]
    ? key
    : (Object.keys(props.tabs)[0] ?? "general");
};

const currentTab = ref(tabFromUrl());

const selectTab = (key) => {
  currentTab.value = key;
  const url = new URL(window.location.href);
  url.searchParams.set("tab", key);
  window.history.pushState({}, "", url);
};

const onPopState = () => {
  currentTab.value = tabFromUrl();
};
window.addEventListener("popstate", onPopState);
onUnmounted(() => window.removeEventListener("popstate", onPopState));

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

const initialOverridden = { ...overridden };

const discardChanges = () => {
  form.reset();
  allKeys.forEach((key) => {
    overridden[key] = initialOverridden[key];
  });
};

const isNotificationsTab = computed(() => currentTab.value === "notifications");

const notificationTypeKeys = computed(() => {
  const fields = Object.keys(props.tabs.notifications?.fields ?? {});
  const types = [];
  fields.forEach((key) => {
    const type = key.startsWith("notify_email_")
      ? key.slice("notify_email_".length)
      : key.startsWith("notify_inapp_")
        ? key.slice("notify_inapp_".length)
        : null;
    if (type && !types.includes(type)) types.push(type);
  });
  return types;
});

const resetType = (type) => {
  resetField(`notify_email_${type}`);
  resetField(`notify_inapp_${type}`);
};

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

const notificationRowDefaultLabel = (type) => {
  const emailDefault = systemDefaultLabel({
    key: `notify_email_${type}`,
    type: "bool",
  });
  const inappDefault = systemDefaultLabel({
    key: `notify_inapp_${type}`,
    type: "bool",
  });

  return `${t("globals.preferences.notifications_email_column")}: ${emailDefault} · ${t("globals.preferences.notifications_inapp_column")}: ${inappDefault}`;
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
        success(t("globals.preferences.update_success"));
      },
      onError: () => {
        clearAllAlerts();
        error(t("globals.preferences.update_error"));
      },
    });
};

const isAppearanceTab = computed(() => currentTab.value === "appearance");

const onThemeToggle = (key) => {
  markOverridden(key);
  savePreferences();
};

const onThemeReset = (key) => {
  resetField(key);
  savePreferences();
};

const tooltip = reactive({
  show: false,
  text: "",
  top: 0,
  left: 0,
});

const onResetMouseEnter = (event) => {
  const rect = event.currentTarget.getBoundingClientRect();
  tooltip.text = t("globals.preferences.use_system_default");
  tooltip.top = rect.top + rect.height / 2;
  tooltip.left = rect.left - 10;
  tooltip.show = true;
};

const hideTooltip = () => {
  tooltip.show = false;
};
</script>

<template>
  <Head>
    <title>{{ $t("globals.preferences.label") }} - Cubrel</title>
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
          {{ $t("globals.preferences.label") }}
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
        @click="selectTab(key)"
      >
        {{ $t(tab.label) }}
      </button>
    </div>

    <form @submit.prevent="savePreferences">
      <div v-if="isNotificationsTab" class="settings__system">
        <div class="settings__notifications">
          <div class="settings__notifications__header">
            <span class="settings__notifications__header__label"> </span>
            <span class="settings__notifications__header__col">
              <i class="fa-solid fa-envelope"></i>
              {{ $t("globals.preferences.notifications_email_column") }}
            </span>
            <span class="settings__notifications__header__col">
              <i class="fa-solid fa-bell"></i>
              {{ $t("globals.preferences.notifications_inapp_column") }}
            </span>
            <span class="settings__notifications__header__reset"></span>
          </div>

          <div
            v-for="type in notificationTypeKeys"
            :key="type"
            class="settings__notifications__row"
          >
            <div class="settings__notifications__row__label">
              <label>{{
                $t(`globals.preferences.notification_types.${type}`)
              }}</label>
              <span class="settings__optional-label">
                {{
                  $t("globals.preferences.current_system_value", {
                    value: notificationRowDefaultLabel(type),
                  })
                }}
              </span>
            </div>

            <div class="settings__notifications__row__col">
              <Checkbox
                v-model="form[`notify_email_${type}`]"
                @update:model-value="markOverridden(`notify_email_${type}`)"
              />
            </div>

            <div class="settings__notifications__row__col">
              <Checkbox
                v-model="form[`notify_inapp_${type}`]"
                @update:model-value="markOverridden(`notify_inapp_${type}`)"
              />
            </div>

            <div class="settings__notifications__row__reset">
              <button
                v-if="
                  overridden[`notify_email_${type}`] ||
                  overridden[`notify_inapp_${type}`]
                "
                type="button"
                class="preferences__reset-btn"
                @click="resetType(type)"
                @mouseenter="onResetMouseEnter"
                @mouseleave="hideTooltip"
              >
                <i class="fa-solid fa-rotate-left"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="settings__actions">
          <button
            type="button"
            class="settings__actions__reset"
            @click="discardChanges"
            :disabled="!isDirty"
          >
            {{ $t("globals.preferences.reset") }}
          </button>

          <button
            type="submit"
            class="settings__actions__save"
            :disabled="!isDirty || form.processing"
          >
            {{ $t("globals.preferences.save") }}
          </button>
        </div>
      </div>

      <div v-else-if="isAppearanceTab" class="settings__system">
        <div class="settings__appearance">
          <label class="settings__appearance__label">
            {{ $t(currentFields[0].label) }}
          </label>

          <ThemeSwitcher
            v-model="form[currentFields[0].key]"
            :options="themeOptions"
            @update:model-value="onThemeToggle(currentFields[0].key)"
          />

          <button
            v-if="overridden[currentFields[0].key]"
            type="button"
            class="preferences__reset-btn"
            @click="onThemeReset(currentFields[0].key)"
            @mouseenter="onResetMouseEnter"
            @mouseleave="hideTooltip"
          >
            <i class="fa-solid fa-rotate-left"></i>
          </button>
        </div>
      </div>

      <div v-else-if="currentFields.length === 0" class="settings__empty">
        <i class="fa-solid fa-bell"></i>
        <p>{{ $t("globals.preferences.notifications_placeholder") }}</p>
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
                  $t("globals.preferences.current_system_value", {
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
              <template v-else-if="f.type === 'int'">
                <IntegerField v-model="form[f.key]" />
              </template>
              <template v-else-if="f.type === 'lang_switcher'">
                <Switcher
                  v-model="form[f.key]"
                  :options="languageOptions"
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
                @mouseenter="onResetMouseEnter"
                @mouseleave="hideTooltip"
              >
                <i class="fa-solid fa-rotate-left"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="settings__actions">
          <button
            type="button"
            class="settings__actions__reset"
            @click="discardChanges"
            :disabled="!isDirty"
          >
            {{ $t("globals.preferences.reset") }}
          </button>

          <button
            type="submit"
            class="settings__actions__save"
            :disabled="!isDirty || form.processing"
          >
            {{ $t("globals.preferences.save") }}
          </button>
        </div>
      </div>
    </form>
  </div>

  <AppTooltip
    :show="tooltip.show"
    :text="tooltip.text"
    :top="tooltip.top"
    :left="tooltip.left"
    placement="left"
  />
</template>

<style lang="scss" scoped>
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

.settings__system__form__field,
.settings__appearance {
  .preferences__reset-btn {
    margin-left: 10px;
    flex-shrink: 0;
    border: none;
    background: none;
    padding: 0;
    font-size: 0.7rem;
    font-weight: 500;
    color: var(--primary-color);
    cursor: pointer;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 4px;

    i {
      font-size: 0.65rem;
    }

    &:hover {
      text-decoration: underline;
    }
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

@media (max-width: 820px) {
  .settings__notifications {
    &__header,
    &__row {
      grid-template-columns: 1fr 60px 60px 80px;
      gap: 10px;
      padding: 12px 16px;
    }

    &__header__col {
      font-size: 0.7rem;
      justify-content: center;
    }

    &__row__reset .preferences__reset-btn {
      font-size: 0.65rem;
      padding: 2px 8px;
    }
  }
}

@media (max-width: 600px) {
  .settings__notifications {
    border-radius: 12px;
    margin: 0 -8px 24px;

    &__header {
      display: none;
    }

    &__row {
      grid-template-columns: 1fr;
      gap: 8px;
      padding: 16px;
      border-bottom: 1px solid var(--color-border-glass);

      &__label {
        order: 1;
      }

      &__col {
        justify-content: flex-start;
        gap: 8px;
        order: 2;

        &:first-of-type {
          margin-top: 4px;
        }

        &::before {
          content: attr(data-label);
          font-size: 0.7rem;
          font-weight: 500;
          color: var(--color-text-muted);
          min-width: 70px;
        }
      }

      &__col[data-label="Email"]::before {
        content: "📧 Email";
      }
      &__col[data-label="In-app"]::before {
        content: "🔔 In-app";
      }

      &__reset {
        order: 3;
        justify-content: flex-start;
        margin-top: 4px;
      }
    }
  }
}
</style>
