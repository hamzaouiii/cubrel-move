<script setup>
import { computed } from "vue";

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean, Object, null],
    required: true,
  },
  value: {
    type: [String, Number, Boolean, Object],
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  color: {
    type: String,
    required: false,
  },
  name: {

    type: String,
    default: "custom-radio-group",
  },
});

const emit = defineEmits(["update:modelValue"]);

const isChecked = computed(() => props.modelValue === props.value);

const select = () => {
  if (props.disabled) return;
  emit("update:modelValue", props.value);
};

const boxColor = props.color ?? "#2563eb";
</script>

<template>
  <label
    :style="{ '--radio-color': boxColor }"
    class="custom-radio"
    :class="{
      'is-checked': isChecked,
      'is-disabled': disabled,
    }"
  >
    <input
      type="radio"
      class="custom-radio__native"
      :name="name"
      :checked="isChecked"
      :disabled="disabled"
      @change="select"
    />

    <div class="custom-radio__visual">
      <div class="custom-radio__dot"></div>
    </div>

    <span class="custom-radio__label">
      <slot />
    </span>
  </label>
</template>

<style scoped lang="scss">
.custom-radio {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
  position: relative;
  font-size: 0.95rem;
  color: var(--color-text-strong);
  transition: color 0.2s ease;

  &__native {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;

    &:focus-visible + .custom-radio__visual {
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--radio-color) 20%, transparent);
      border-color: var(--radio-color);
    }
  }

  &__visual {
    width: 20px;
    height: 20px;
    border: 2px solid var(--color-border-muted);
    border-radius: 50%;
    background: var(--color-bg-surface);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
  }

  &__dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: white;
    transform: scale(0);
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  &:hover:not(.is-disabled) {
    color: var(--color-text-heading);

    .custom-radio__visual {
      border-color: var(--radio-color);
      background-color: var(--color-bg-muted);
    }
  }

  &.is-checked {
    .custom-radio__visual {
      border-color: var(--radio-color);
      background: var(--radio-color);

      box-shadow: 0 2px 4px var(--color-shadow-strong);
    }

    .custom-radio__dot {
      transform: scale(1);
    }
  }

  &.is-disabled {
    opacity: 0.5;
    cursor: not-allowed;

    .custom-radio__visual {
      background: var(--color-bg-subtle);
      border-color: var(--color-border);
    }
  }
}
</style>
