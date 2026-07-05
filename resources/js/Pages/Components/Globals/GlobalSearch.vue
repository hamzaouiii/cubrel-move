<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useOS } from "@/utils/osDetections.js";
import SearchOverlay from "@/Pages/Components/Overlays/SearchOverlay.vue";

const os = useOS();

const open = ref(false);

const openSearchOverlay = () => {
  open.value = true;
};
const closeSearchOverlay = () => {
  open.value = false;
};

const handleKeydown = (e) => {
  const modifierPressed = os.isMac ? e.metaKey : e.ctrlKey;
  if (modifierPressed && e.key.toLowerCase() === "k") {
    e.preventDefault();
    open.value = true;
  }

  if (e.key === "Escape") {
    closeSearchOverlay();
  }
};

onMounted(() => {
  document.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
  <div class="search-trigger">
    <div class="search-trigger__box" @click="openSearchOverlay()">
      <span>{{ $t("globals.global_search.search") }}</span>
      <span class="search-trigger__box__shortcut">{{
        $t(
          `globals.global_search.shortcut.${
            os.modifierSymbol?.toLowerCase() || "ctrl"
          }`
        ) + " + K"
      }}</span>
    </div>
  </div>
  <SearchOverlay
    v-if="open"
    @on-close-overlay="closeSearchOverlay"
  ></SearchOverlay>
</template>
