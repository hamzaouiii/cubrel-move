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
  getCurrentInstance,
} from "vue";

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
  modelValue: [String, Number, Boolean, Object, null],
  options: {
    type: Array,
    required: true,
  },
  label: {
    type: String,
    default: "",
  },
  placeholder: {
    type: String,
    default: "",
  },
  error: {
    type: String,
    default: "",
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
});

const emit = defineEmits(["update:modelValue", "change"]);

const isOpen = ref(false);
const root = ref(null);

const search = ref("");
const searchInput = ref(null);

const normalizedOptions = computed(() => {
  if (Array.isArray(props.options)) return props.options;
  if (props.options && typeof props.options === "object") {
    return Object.values(props.options).flat();
  }
  return [];
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
</script>

<template>
  <div class="field-dropdown" ref="root">
    <div
      class="field-dropdown__control"
      :class="{
        'is-open': isOpen,
        'is-invalid': error,
        'is-disabled': disabled,
      }"
      @click="toggle"
    >
      <span class="field-dropdown__selected">
        {{ selectedOption?.label ?? $t("settings.select") }}
      </span>

      <i
        class="field-dropdown__chevron fa-solid"
        :class="isOpen ? 'fa-chevron-up' : 'fa-chevron-down'"
      ></i>
    </div>

    <transition name="dropdown-fade">
      <div
        v-if="isOpen"
        class="field-dropdown__menu"
        role="listbox"
        @click.stop
      >
        <div v-if="searchable" class="field-dropdown__search-wrapper">
          <input
            ref="searchInput"
            v-model="search"
            type="text"
            class="field-dropdown__search-input"
            :placeholder="$t('settings.search_in_drop_down')"
            @keydown.stop
          />
        </div>

        <ul class="field-dropdown__list">
          <li
            v-for="option in filteredOptions"
            :key="option.value"
            class="field-dropdown__option"
            :class="{ 'is-active': option.value === modelValue }"
            role="option"
            @click="selectOption(option.value)"
          >
            <div class="field-dropdown__option-label">
              {{ option.label }}
            </div>
            <div
              v-if="option.description"
              class="field-dropdown__option-description"
            >
              {{ option.description }}
            </div>
          </li>

          <li
            v-if="filteredOptions.length === 0"
            class="field-dropdown__no-results"
          >
            {{ $t("settings.dropdown_no_results") }}
          </li>
        </ul>
      </div>
    </transition>
  </div>
</template>
