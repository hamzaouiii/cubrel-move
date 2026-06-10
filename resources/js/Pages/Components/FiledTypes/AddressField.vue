<script setup>
import { computed, ref, watch, getCurrentInstance } from "vue";
import { useClipboard } from "@/Composables/useClipboard";
import Select from "@/Pages/Components/FiledTypes/Select.vue";
import { countriesDropdown, countryMap } from "@/utils/countries";

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
  modelValue: {
    type: Object,
    default: null,
  },
  mode: {
    type: String,
    default: "edit",
  },
  hasError: {
    type: Boolean,
    default: false,
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  highlight: String,
  searchable: Boolean,
  showMap: {
    type: Boolean,
    default: false,
  },
  autocomplete: {
    type: Boolean,
    default: false,
  },
});

const val = computed(() => props.modelValue ?? {});

const updateSub = (key, newVal) => {
  emit("update:modelValue", { ...val.value, [key]: newVal || null });
};

const isEmpty = computed(
  () => !props.modelValue || !Object.values(props.modelValue).some(Boolean),
);

const countryLabel = computed(() => {
  if (!val.value.country) return null;
  const key = countryMap[val.value.country];
  return key ? t(key) : val.value.country;
});

const street = computed(() => val.value.street || null);
const postalCode = computed(() => val.value.postal_code || null);
const city = computed(() => val.value.city || null);
const state = computed(() => val.value.state || null);

const line2Formatted = computed(() => {
  const parts = [postalCode.value, city.value].filter(Boolean);
  return parts.length ? parts.join(" ") : null;
});

const line3Formatted = computed(() => {
  const parts = [state.value, countryLabel.value].filter(Boolean);
  return parts.length ? parts.join(", ") : null;
});

const summary = computed(() => {
  const parts = [
    street.value,
    line2Formatted.value,
    line3Formatted.value,
  ].filter(Boolean);
  return parts.length ? parts.join(", ") : "—";
});

const showError = ref(false);
const { copied, copy } = useClipboard();

watch(
  () => props.hasError,
  (v) => {
    showError.value = v;
  },
  { immediate: true },
);

const clearErrors = () => {
  showError.value = false;
};

// Highlight match for search
const escapeRegExp = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

const highlightMatch = (text) => {
  if (!text) return "-";
  if (!props.highlight || !props.highlight.trim()) return text;

  const term = escapeRegExp(props.highlight.trim());
  const regex = new RegExp(`(${term})`, "gi");

  return text
    .toString()
    .replace(regex, '<span class="search-highlight">$1</span>');
};
</script>

<template>
  <div
    v-if="mode === 'edit' || mode === 'settings'"
    class="address-field address-field--edit"
    :class="{
      'address-field--error': showError,
      'address-field--readonly': readOnly,
      'address-field--settings': mode === 'settings',
      'address-field--has-value': !isEmpty,
    }"
  >
    <div class="address-fields-wrapper">
      <div class="address-icon-wrapper">
        <i class="address-icon fa-solid fa-location-dot"></i>
      </div>

      <div class="address-inputs">
        <div class="address-row address-row--full">
          <div class="input-group">
            <label class="input-label">{{ t("modules.address.street") }}</label>
            <input
              type="text"
              :value="val.street ?? ''"
              :disabled="readOnly"
              class="address-input address-input--street"
              @input="
                updateSub('street', $event.target.value);
                clearErrors();
              "
            />
          </div>
        </div>

        <div class="address-row address-row--split">
          <div class="input-group input-group--small">
            <label class="input-label">{{
              t("modules.address.postal_code")
            }}</label>
            <input
              type="text"
              :value="val.postal_code ?? ''"
              :disabled="readOnly"
              class="address-input address-input--postal"
              @input="
                updateSub('postal_code', $event.target.value);
                clearErrors();
              "
            />
          </div>

          <div class="input-group input-group--grow">
            <label class="input-label">{{ t("modules.address.city") }}</label>
            <input
              type="text"
              :value="val.city ?? ''"
              :disabled="readOnly"
              class="address-input address-input--city"
              @input="
                updateSub('city', $event.target.value);
                clearErrors();
              "
            />
          </div>
        </div>

        <div class="address-row address-row--split">
          <div class="input-group input-group--grow">
            <label class="input-label">{{ t("modules.address.state") }}</label>
            <input
              type="text"
              :value="val.state ?? ''"
              :disabled="readOnly"
              class="address-input address-input--state"
              @input="
                updateSub('state', $event.target.value);
                clearErrors();
              "
            />
          </div>

          <div class="input-group input-group--grow">
            <label class="input-label">{{
              t("modules.address.country")
            }}</label>
            <Select
              :modelValue="val.country ?? null"
              :dropdown_list="countriesDropdown"
              :searchable="true"
              :nullable="true"
              :disabled="readOnly"
              mode="edit"
              class="address-country-select"
              @update:modelValue="
                updateSub('country', $event);
                clearErrors();
              "
            />
          </div>
        </div>
      </div>
    </div>

    <span v-if="showError" class="error-icon-container">
      <i class="error-icon fa-solid fa-circle-exclamation"></i>
    </span>
  </div>

  <div v-else-if="mode === 'detail'">
    <div
      class="address-field address-field--detail display-field"
      :class="{
        'address-field--readonly': readOnly,
        'address-field--empty': isEmpty,
      }"
    >
      <i class="address-detail-icon fa-solid fa-location-dot"></i>

      <div class="address-detail-content" v-if="!isEmpty">
        <div class="address-detail-line" v-if="street">
          <i class="fa-solid fa-road"></i>
          <span>{{ street }}</span>
        </div>
        <div class="address-detail-line" v-if="line2Formatted">
          <i class="fa-solid fa-envelope"></i>
          <span>{{ line2Formatted }}</span>
        </div>
        <div class="address-detail-line" v-if="line3Formatted">
          <i class="fa-solid fa-globe"></i>
          <span>{{ line3Formatted }}</span>
        </div>
      </div>

      <div v-else class="address-empty">
        <span>—</span>
      </div>

      <div class="address-actions">
        <button
          v-if="!isEmpty && !readOnly"
          class="copy-btn"
          @click.stop.prevent="copy(summary)"
          :title="copied ? 'Copied!' : 'Copy address'"
        >
          <i
            :class="
              copied ? 'fa-solid fa-check text-success' : 'fa-regular fa-copy'
            "
          ></i>
        </button>
      </div>
    </div>
  </div>

  <div v-else-if="mode === 'table'">
    <div class="address-field address-field--table">
      <i class="fa-solid fa-location-dot address-table-icon"></i>
      <span v-if="searchable" v-html="highlightMatch(summary)"></span>
      <span v-else class="address-table-text">{{ summary }}</span>
    </div>
  </div>

  <div v-else-if="mode === 'related-panel' || mode === 'linkingPanel'">
    <div class="address-field address-field--related">
      <i class="fa-solid fa-location-dot address-related-icon"></i>
      <span class="address-related-text">{{ summary }}</span>
    </div>
  </div>
</template>
