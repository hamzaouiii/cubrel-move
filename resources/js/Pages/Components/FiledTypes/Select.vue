<script setup>
/**
 *
 * for me to remember how to use without having to read the whole component everytime
 * Send an array of options like this :options=array[] and bind it to via v-model
 * options look like this:
 * 'value'
 * 'label'       => "{$city} (UTC{$offset})",
 * 'description' => $tz . ($abbr ? " • {$abbr}" : ''),
 *
 */
import {
  computed,
  ref,
  onMounted,
  onBeforeUnmount,
  nextTick,
  watch,
  getCurrentInstance,
} from "vue";

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
  modelValue: [String, Number, Boolean, Object, null],
  dropdown_list: Object,
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
  disabled: {
    type: Boolean,
    default: false,
  },
  searchable: {
    type: Boolean,
    default: true,
  },
  searchPlaceholder: {
    type: String,
    default: "",
  },
  nullable: {
    type: Boolean,
    default: false,
  },
  highlight: String,
});
const emit = defineEmits(["update:modelValue", "change"]);

const options = computed(() => {
  return props?.dropdown_list?.values || [];
});
const isOpen = ref(false);
const root = ref(null);

const search = ref("");
const searchInput = ref(null);

const normalizedOptions = computed(() => {
  let list = [];

  if (Array.isArray(options.value)) {
    list = options.value;
  } else if (options.value && typeof options.value === "object") {
    list = Object.values(options.value).flat();
  }
  if (props.nullable) {
    return [...list];
  }
  return [
    {
      value: null,
      label: "—",
    },
    ...list,
  ];
});

const selectedOption = computed(
  () =>
    normalizedOptions.value.find((o) => o.value === props.modelValue) ?? null,
);

const filteredOptions = computed(() => {
  const q = search.value.trim().toLowerCase();
  if (!props.searchable || !q) return normalizedOptions.value;

  return normalizedOptions.value.filter((o) => {
    const label = String(o.label ?? "").toLowerCase();
    const desc = String(o.description ?? "").toLowerCase();
    const val = String(o.value ?? "").toLowerCase();
    return label.includes(q) || desc.includes(q) || val.includes(q);
  });
});

const toggle = async () => {
  if (props.disabled) return;

  isOpen.value = !isOpen.value;

  if (isOpen.value) {
    await nextTick();
    if (props.searchable) {
      searchInput.value?.focus();
    }
  } else {
    search.value = "";
  }

  clearErrors();
};

const close = () => {
  isOpen.value = false;
  search.value = "";
};

const selectOption = (value) => {
  if (props.disabled) return;

  if (value !== props.modelValue) {
    emit("update:modelValue", value);
    emit("change", value);
  }

  close();
};

const handleClickOutside = (event) => {
  if (!root.value) return;
  if (!root.value.contains(event.target)) {
    close();
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});

const showError = ref(false);

watch(
  () => props.hasError,
  (val) => {
    showError.value = val;
  },
  { immediate: true },
);

const clearErrors = () => {
  showError.value = false;
};

const clearSelection = (e) => {
  e.stopPropagation();
  emit("update:modelValue", null);
  emit("change", null);
};

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
    v-if="mode === 'edit' || mode === 'settings' || mode === 'module-builder'"
  >
    <div class="select-field" ref="root">
      <div
        class="select-field__control"
        :class="{
          'is-open': isOpen,
          'is-invalid': showError,
          'is-disabled': disabled,
          'is-builder': mode === 'module-builder',
        }"
        @click="toggle"
      >
        <span class="select-field__selected">
          {{
            selectedOption?.label
              ? $t(selectedOption.label)
              : $t("settings.select")
          }}
        </span>
        <span class="select-field__icons">
          <i
            v-if="showError"
            class="error-icon fa-solid fa-circle-exclamation"
          ></i>

          <i
            v-if="modelValue !== null && modelValue !== '' && !disabled"
            class="fa-solid fa-xmark select-field__clear"
            @click="clearSelection"
          ></i>

          <i
            class="select-field__chevron fa-solid"
            :class="isOpen ? 'fa-chevron-up' : 'fa-chevron-down'"
          ></i>
        </span>
      </div>

      <transition name="dropdown-fade">
        <div
          v-if="isOpen"
          class="select-field__menu"
          role="listbox"
          @click.stop
        >
          <div v-if="searchable" class="select-field__search-wrapper">
            <input
              ref="searchInput"
              v-model="search"
              type="text"
              class="select-field__search-input"
              :placeholder="$t('settings.search_in_drop_down')"
              @keydown.stop
            />
          </div>

          <ul class="select-field__list">
            <li
              v-for="option in filteredOptions"
              :key="option.value"
              class="select-field__option"
              :class="{ 'is-active': option.value === modelValue }"
              role="option"
              @click="selectOption(option.value)"
            >
              <div class="select-field__option-label">
                {{ $t(option.label) }}
              </div>
              <div
                v-if="option.description"
                class="select-field__option-description"
              >
                {{ option.description }}
              </div>
            </li>

            <li
              v-if="filteredOptions.length === 0"
              class="select-field__no-results"
            >
              {{ $t("settings.dropdown_no_results") }}
            </li>
          </ul>
        </div>
      </transition>
    </div>
  </div>
  <span v-else-if="mode === 'profile-header'">
    {{ $t(selectedOption?.label) }}
  </span>
  <div v-else-if="mode === 'detail'">
    <span
      :class="[
        'record-layout__sections__item__layout__field__content ',
        { 'view-uneditable-field': readOnly },
      ]"
    >
      {{ $t(selectedOption?.label) }}
    </span>
  </div>
  <div v-else-if="mode === 'table'">
    <span v-if="searchable">
      <span v-html="highlightMatch($t(selectedOption?.label) ?? '—')"></span>
    </span>
    <span v-else>
      {{ $t(selectedOption?.label) || "—" }}
    </span>
  </div>

  <div v-else-if="mode === 'related-panel' || mode === 'linkingPanel'">
    {{ $t(selectedOption?.label) || "—" }}
  </div>
</template>
