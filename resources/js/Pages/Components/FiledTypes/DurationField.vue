<script setup>
const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: "",
  },
  mode: {
    type: String,
    default: "edit",
  },
  searchable: Boolean,
  highlight: String,
});

// field is designed only forr meetings module
const formatDuration = (value) => {
  if (value === null || value === "" || isNaN(parseInt(value))) return "—";

  const totalMinutes = parseInt(value);
  const days = Math.floor(totalMinutes / 1440);
  const hours = Math.floor((totalMinutes % 1440) / 60);
  const minutes = totalMinutes % 60;

  const parts = [];
  if (days) parts.push(`${days}d`);
  if (hours) parts.push(`${hours}h`);
  if (minutes || parts.length === 0) parts.push(`${minutes}m`);

  return parts.join(" ");
};

const escapeRegExp = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

const highlightMatch = (text) => {
  if (!props.highlight || !props.highlight.trim()) return text;

  const term = escapeRegExp(props.highlight.trim());
  const regex = new RegExp(`(${term})`, "gi");

  return text.replace(regex, '<span class="search-highlight">$1</span>');
};
</script>

<template>
  <div
    v-if="
      mode === 'table' || mode === 'related-panel' || mode === 'linkingPanel'
    "
    class="duration-field duration-field--table"
  >
    <span
      v-if="searchable"
      v-html="highlightMatch(formatDuration(modelValue))"
    ></span>
    <span v-else class="duration-table-text">{{
      formatDuration(modelValue)
    }}</span>
  </div>

  <div
    v-else-if="mode === 'detail'"
    class="duration-field duration-field--detail display-field"
  >
    <i class="duration-detail-icon fa-regular fa-clock"></i>
    <span class="duration-value">{{ formatDuration(modelValue) }}</span>
  </div>

  <div v-else class="duration-field duration-field--readonly">
    <span class="duration-value">{{ formatDuration(modelValue) }}</span>
  </div>
</template>
