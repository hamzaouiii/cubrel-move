<script setup>
import {
  computed,
  ref,
  onMounted,
  onBeforeUnmount,
  nextTick,
  getCurrentInstance,
} from "vue";

const props = defineProps({
  modelValue: [String, Number, Boolean, Object, null],
  options: Array,
  isDraft: Boolean,
});
const emit = defineEmits([
  "update:modelValue",
  "change",
  "onOpenCreateDialog",
  "onOpenEditDialog",
]);
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const isOpen = ref(false);
const root = ref(null);

const search = ref("");
const searchInput = ref(null);

const filteredOptions = computed(() => {
  const q = search.value.trim().toLowerCase();

  return props.options.filter((o) => {
    const key = String(o.key ?? "").toLowerCase();
    return key.includes(q);
  });
});

const selectedOption = computed(
  () => props.options.find((o) => o.id === props.modelValue)?.key ?? null,
);

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
  if (value !== props.modelValue) {
    emit("update:model-value", value);
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

const createClicked = () => {
  emit("onOpenCreateDialog");
};

const editClicked = () => {
  emit("onOpenEditDialog");
};
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
        {{ selectedOption ?? $t("settings.select_dropdown_list") }}
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
        <div class="field-dropdown__search-wrapper">
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
            :key="option.key"
            class="field-dropdown__option"
            role="option"
            @click="selectOption(option.id)"
          >
            <div class="field-dropdown__option-label">
              {{ option.key }}
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
  <div class="dropdown-selector__actions">
    <button
      v-if="!isDraft"
      :disabled="selectedOption === null"
      @click.prevent="editClicked(selectedOption)"
    >
      <i class="fa-solid fa-pen-to-square"></i>
    </button>
    <button @click.prevent="createClicked">
      <i class="fa-solid fa-circle-plus"></i>
    </button>
  </div>
</template>
