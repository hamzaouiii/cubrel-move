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
  <div class="module-dropdown" ref="root">
    <div
      class="module-dropdown__button"
      :class="{
        'is-open': isOpen,
        'is-invalid': error,
        'is-disabled': disabled,
      }"
      @click="toggle"
    >
      <span class="module-dropdown__selected">
        {{ $t(selectedOption?.label) ?? $t("settings.select") }}
      </span>
      <i
        class="module-dropdown__icon"
        :class="isOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"
      ></i>
    </div>

    <transition name="dropdown-fade">
      <div
        v-if="isOpen"
        class="module-dropdown_menu"
        role="listbox"
        @click.stop
      >
        <!-- Search -->
        <div v-if="searchable" class="module-dropdown_search">
          <input
            ref="searchInput"
            v-model="search"
            type="text"
            class="module-dropdown_search_input"
            :placeholder="t('settings.search_in_drop_down')"
            @keydown.stop
          />
        </div>

        <ul class="module-dropdown_list">
          <li
            v-for="option in filteredOptions"
            :key="option.value"
            class="module-dropdown_option"
            :class="{ 'is-active': option.value === modelValue }"
            role="option"
            @click="selectOption(option.value)"
          >
            <div class="module-dropdown_option_label">
              {{ $t(option.label) }}
            </div>
            <div
              v-if="option.description"
              class="module-dropdown_option_description"
            >
              {{ $t(option.description) }}
            </div>
          </li>

          <li
            v-if="filteredOptions.length === 0"
            class="module-dropdown_no_results"
          >
            {{ $t("settings.dropdown_no_results") }}
          </li>
        </ul>
      </div>
    </transition>

    <div v-if="error" class="invalid-feedback d-block mt-1">
      {{ error }}
    </div>
  </div>
</template>
