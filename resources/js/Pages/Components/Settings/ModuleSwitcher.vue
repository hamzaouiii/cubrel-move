<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useDropdownFlip } from "@/Composables/useDropdownFlip";

const props = defineProps({
  settingModule: Object,
  activeKey: { type: String, default: "edit" },
});

const page = usePage();
const modules = computed(() => page.props.settingsNav?.modules || []);

const TAB_SEGMENT = {
  edit: "module-settings",
  layouts: "layouts",
  fields: "fields",
  relationships: "relationships",
};

const root = ref(null);
const isOpen = ref(false);
const { flipUp, recalc } = useDropdownFlip(root, { menuHeight: 260 });

async function toggle() {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    await recalc();
  }
}

function close() {
  isOpen.value = false;
}

function selectModule(m) {
  if (m.id !== props.settingModule.id) {
    const segment = TAB_SEGMENT[props.activeKey] ?? "module-settings";
    router.visit(`/settings/modules/${m.id}/${segment}`);
  }
  close();
}

function handleClickOutside(event) {
  if (root.value && !root.value.contains(event.target)) {
    close();
  }
}

onMounted(() => document.addEventListener("click", handleClickOutside));
onBeforeUnmount(() => document.removeEventListener("click", handleClickOutside));
</script>

<template>
  <div
    ref="root"
    class="module-switcher"
    :style="{ '--module-color': settingModule.color }"
  >
    <button
      type="button"
      class="module-switcher__control"
      :class="{ 'is-open': isOpen }"
      @click="toggle"
    >
      <i class="fa-solid module-switcher__control__icon" :class="settingModule.icon"></i>
      <span class="module-switcher__control__label">{{ settingModule.label }}</span>
      <i
        class="fa-solid module-switcher__control__chevron"
        :class="isOpen ? 'fa-chevron-up' : 'fa-chevron-down'"
      ></i>
    </button>

    <transition name="dropdown-fade">
      <ul
        v-if="isOpen"
        class="module-switcher__menu"
        :class="{ 'module-switcher__menu--flip-up': flipUp }"
        role="listbox"
      >
        <li
          v-for="m in modules"
          :key="m.id"
          class="module-switcher__option"
          :class="{ 'is-active': m.id === settingModule.id }"
          role="option"
          :style="{ '--module-color': m.color }"
          @click="selectModule(m)"
        >
          <i class="fa-solid module-switcher__option__icon" :class="m.icon"></i>
          <span class="module-switcher__option__label">{{ m.label }}</span>
          <i v-if="m.id === settingModule.id" class="fa-solid fa-check module-switcher__option__check"></i>
        </li>
      </ul>
    </transition>
  </div>
</template>
