<script setup>
import { computed, ref } from "vue";

const props = defineProps({
  fields: {
    type: Object,
    default: () => ({ related_fields: [] }),
  },
  showFields: {
    type: Boolean,
    default: false,
  },
  selectedFields: {
    type: Array,
    default: [],
  },
});
const emit = defineEmits(["update:selectedFields"]);

const selectField = (field) => {
  const updated = [...props.selectedFields];

  if (!updated.some((f) => f.name === field.name)) {
    updated.push({ name: field.name, label: field.label, type: field.type });
  }

  emit("update:selectedFields", updated);
};

const removeFieldFromSelected = (field) => {
  const updated = props.selectedFields.filter((f) => f.name !== field.name);

  emit("update:selectedFields", updated);
};

const filteredFields = computed(() => {
  const fieldsArray = Object.values(props.fields.related_fields);

  return fieldsArray.filter(
    (f) => !props.selectedFields.some((sf) => sf.name === f.name),
  );
});

const emitUpdatedSelectedFields = () => {
  emit("update:selectedFields", propsselectedFields);
};
</script>

<template>
  <div class="related-fields__header" v-if="selectedFields.length">
    <div v-for="f in selectedFields" class="related-fields__header__field">
      <span class="related-fields__header__field__label">{{
        $t(f.label)
      }}</span>
      <span
        class="related-fields__header__field__action"
        @click="removeFieldFromSelected(f)"
      >
        <i class="fa-solid fa-times"></i>
      </span>
    </div>
  </div>
  <div class="related-fields__list" v-if="showFields">
    <div
      v-for="field in filteredFields"
      :key="field.name"
      class="related-fields__list__item"
      @click="selectField(field)"
      :class="{
        'related-fields__list__item--selected': selectedFields.includes(field),
      }"
    >
      {{ $t(field.label) }}
    </div>
  </div>
</template>
