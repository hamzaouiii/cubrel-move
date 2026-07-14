<script setup>
import { ref, computed, onBeforeUnmount, getCurrentInstance } from "vue";

const props = defineProps({
  modelValue: { type: String, default: "" },
  options: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:modelValue"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const trigger = ref(null);
const isOpen = ref(false);
const menuStyle = ref({});

const selectedOption = computed(
  () => props.options.find((o) => o.value === props.modelValue) ?? null,
);

const closeOnScroll = (event) => {
  if (menuEl.value?.contains(event.target)) return;
  close();
};

const open = () => {
  const rect = trigger.value?.getBoundingClientRect();
  if (!rect) return;

  menuStyle.value = {
    position: "fixed",
    top: `${rect.bottom + 4}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`,
    zIndex: 2000,
  };

  isOpen.value = true;
  window.addEventListener("scroll", closeOnScroll, true);
  window.addEventListener("resize", closeOnScroll);
  document.addEventListener("mousedown", handleClickOutside);
  document.addEventListener("keydown", handleKeydown);
};

const close = () => {
  isOpen.value = false;
  window.removeEventListener("scroll", closeOnScroll, true);
  window.removeEventListener("resize", closeOnScroll);
  document.removeEventListener("mousedown", handleClickOutside);
  document.removeEventListener("keydown", handleKeydown);
};

const toggle = () => (isOpen.value ? close() : open());

const selectOption = (value) => {
  emit("update:modelValue", value);
  close();
};

const menuEl = ref(null);
const handleClickOutside = (event) => {
  if (trigger.value?.contains(event.target)) return;
  if (menuEl.value?.contains(event.target)) return;
  close();
};

const handleKeydown = (event) => {
  if (event.key === "Escape") close();
};

onBeforeUnmount(() => close());
</script>

<template>
  <div class="select-field" ref="trigger">
    <div
      class="select-field__control"
      :class="{ 'is-open': isOpen }"
      @click="toggle"
    >
      <span class="select-field__selected">{{
        selectedOption ? t(selectedOption.label) : ""
      }}</span>
      <span class="select-field__icons">
        <i
          class="select-field__chevron fa-solid"
          :class="isOpen ? 'fa-chevron-up' : 'fa-chevron-down'"
        ></i>
      </span>
    </div>

    <Teleport to="body">
      <div
        v-if="isOpen"
        ref="menuEl"
        class="select-field__menu"
        :style="menuStyle"
        role="listbox"
      >
        <ul class="select-field__list">
          <li
            v-for="option in options"
            :key="option.value"
            class="select-field__option"
            :class="{ 'is-active': option.value === modelValue }"
            role="option"
            @click="selectOption(option.value)"
          >
            <div class="select-field__option-label">{{ t(option.label) }}</div>
          </li>
        </ul>
      </div>
    </Teleport>
  </div>
</template>
