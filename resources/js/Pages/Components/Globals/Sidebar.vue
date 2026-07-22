<script setup>
import {
  computed,
  ref,
  reactive,
  getCurrentInstance,
  watch,
  onMounted,
} from "vue";
import { useForm, Link, usePage } from "@inertiajs/vue3";
import AppTooltip from "./AppTooltip.vue";

const form = useForm({});
const logout = () => {
  form.post("/logout");
};

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const SIDEBAR_HIDDEN_KEY = "sidebar-hidden";
const page = usePage();
const modules = computed(() => page.props.modules ?? []);
const currentUrl = computed(() => page.url);
const appSettings = computed(() => page.props.appSettings ?? {});
const collapsedSidebar = ref(true);
const hiddenSidebar = ref(localStorage.getItem(SIDEBAR_HIDDEN_KEY) === "true");

function collapseSidebar() {
  collapsedSidebar.value = true;
}

function toggleSidebar() {
  collapsedSidebar.value = !collapsedSidebar.value;
}

function hideSidebar() {
  hiddenSidebar.value = true;
  collapsedSidebar.value = true;
  localStorage.setItem(SIDEBAR_HIDDEN_KEY, "true");
}

function showSidebar() {
  hiddenSidebar.value = false;
  collapsedSidebar.value = true;
  localStorage.setItem(SIDEBAR_HIDDEN_KEY, "false");
}

function syncContentOffset() {
  document.documentElement.style.setProperty(
    "--sidebar-content-offset",
    hiddenSidebar.value ? "0px" : "80px",
  );
  document.documentElement.classList.toggle(
    "sidebar-hidden",
    hiddenSidebar.value,
  );
}
onMounted(syncContentOffset);
watch(hiddenSidebar, syncContentOffset);
// temporary since this can be user based also, set tup
const categoryOrder = {
  sales: 1,
  revenue: 2,
  communication: 3,
  activities: 4,
  support: 5,
  general: 6,
};
// Group modules by category, excluding 'settings'
const groupedModules = computed(() => {
  const groups = {};
  modules.value.forEach((mod) => {
    const category = mod.category || "General";

    if (!groups[category]) {
      groups[category] = [];
    }
    groups[category].push(mod);
  });
  return Object.keys(groups)
    .sort((a, b) => {
      const weightA = categoryOrder[a.toLowerCase()] || 99;
      const weightB = categoryOrder[b.toLowerCase()] || 99;
      return weightA - weightB;
    })
    .reduce((obj, key) => {
      obj[key] = groups[key];
      return obj;
    }, {});
});

const tooltip = reactive({
  show: false,
  text: "",
  color: "",
  top: 0,
  left: 0,
});

const hideTooltip = () => {
  tooltip.show = false;
};

const onModuleMouseEnter = (event, mod) => {
  let text;
  let color;
  if (!collapsedSidebar.value) return;
  if (mod === "home") {
    text = t("globals.sidebar.home");
    color = appSettings.value.primary_color;
  } else {
    text = mod.label;
    color =
      appSettings.value.use_individual_module_colors === "0"
        ? appSettings.value.primary_color
        : mod.color;
  }
  const rect = event.currentTarget.getBoundingClientRect();

  tooltip.text = text;
  tooltip.color = color;

  tooltip.top = rect.top + rect.height / 2;
  tooltip.left = rect.right + 10;
  tooltip.show = true;
};

const onModuleMouseLeave = () => {
  hideTooltip();
};

const onCollapserMouseEnter = (event) => {
  const rect = event.currentTarget.getBoundingClientRect();

  tooltip.text = collapsedSidebar.value
    ? t("globals.sidebar.expand")
    : t("globals.sidebar.close");
  tooltip.color = appSettings.value.primary_color;
  tooltip.top = rect.top + rect.height / 2;
  tooltip.left = rect.right + 10;
  tooltip.show = true;
};

const onCollapserMouseLeave = () => {
  hideTooltip();
};

const onHideBtnMouseEnter = (event) => {
  const rect = event.currentTarget.getBoundingClientRect();

  tooltip.text = t("globals.sidebar.hide");
  tooltip.color = appSettings.value.primary_color;
  tooltip.top = rect.top + rect.height / 2;
  tooltip.left = rect.right + 10;
  tooltip.show = true;
};

const onHideBtnMouseLeave = () => {
  hideTooltip();
};

const onShowTabMouseEnter = (event) => {
  const rect = event.currentTarget.getBoundingClientRect();

  tooltip.text = t("globals.sidebar.show");
  tooltip.color = appSettings.value.primary_color;
  tooltip.top = rect.top + rect.height / 2;
  tooltip.left = rect.right + 10;
  tooltip.show = true;
};

const onShowTabMouseLeave = () => {
  hideTooltip();
};
</script>

<template>
  <aside
    :class="[
      'sidebar',
      {
        'sidebar--open': !collapsedSidebar,
        'sidebar--collapsed': collapsedSidebar,
        'sidebar--hidden': hiddenSidebar,
      },
    ]"
  >
    <div
      :class="[
        'sidebar__header',
        { 'sidebar__header--open': !collapsedSidebar },
      ]"
    >
      <div @click="toggleSidebar" class="sidebar__collapser">
        <div
          class="sidebar__collapser__icon"
          :style="{ '--primary-color': appSettings.primary_color }"
          @mouseenter="onCollapserMouseEnter($event)"
          @mouseleave="onCollapserMouseLeave"
        >
          <i
            :class="
              !collapsedSidebar ? 'fa-solid fa-angles-left' : 'fa-solid fa-bars'
            "
          ></i>
        </div>
      </div>
    </div>

    <div class="sidebar__module-list">
      <Link
        class="sidebar__module-list__item sidebar__module-list__item--home"
        :class="[{ active: currentUrl === '/' }]"
        @click="collapseSidebar()"
        @mouseenter="onModuleMouseEnter($event, 'home')"
        @mouseleave="onModuleMouseLeave"
        :style="{ '--module-color': appSettings.primary_color }"
        href="/"
      >
        <div
          :class="[
            'sidebar__module-list__item__label',
            { active: currentUrl === '/' },
          ]"
        >
          <i
            class="sidebar__module-list__item__label__icon fa-solid fa-house"
          ></i>
          <span
            :class="[
              'sidebar__module-list__item__label__text',
              { hide: collapsedSidebar },
            ]"
            >{{ $t("globals.sidebar.home") }}</span
          >
        </div>
      </Link>

      <template v-for="(mods, category) in groupedModules" :key="category">
        <hr class="sidebar__module-list__divider" />

        <div v-if="!collapsedSidebar" class="sidebar__category-label">
          {{ $t(`modules.categories.${category}`) }}
        </div>

        <Link
          :class="[
            'sidebar__module-list__item',
            { active: currentUrl.startsWith(mod.path) },
          ]"
          v-for="mod in mods"
          :key="mod.slug"
          :href="mod.path"
          @click="collapseSidebar()"
          :style="
            appSettings.use_individual_module_colors === '0'
              ? { '--module-color': appSettings.primary_color }
              : { '--module-color': mod.color }
          "
          @mouseenter="onModuleMouseEnter($event, mod)"
          @mouseleave="onModuleMouseLeave"
        >
          <div class="sidebar__module-list__item__label">
            <i
              :class="[
                'sidebar__module-list__item__label__icon',
                'fa-solid',
                mod.icon,
              ]"
            ></i>
            <span
              v-if="!collapsedSidebar"
              class="sidebar__module-list__item__label__text"
            >
              {{ mod.label }}
            </span>
          </div>
        </Link>
      </template>
    </div>

    <div v-if="collapsedSidebar" class="sidebar__footer">
      <div
        @click.stop="hideSidebar"
        class="sidebar__hide-btn"
        :style="{ '--primary-color': appSettings.primary_color }"
        @mouseenter="onHideBtnMouseEnter($event)"
        @mouseleave="onHideBtnMouseLeave"
      >
        <i class="fa-solid fa-eye-slash"></i>
      </div>
    </div>
  </aside>

  <div
    v-if="!collapsedSidebar"
    @click="toggleSidebar"
    class="sidebar__overlay"
  ></div>

  <div
    v-if="hiddenSidebar"
    @click="showSidebar"
    class="sidebar__show-tab"
    :style="{ '--primary-color': appSettings.primary_color }"
    @mouseenter="onShowTabMouseEnter($event)"
    @mouseleave="onShowTabMouseLeave"
  >
    <i class="fa-solid fa-angles-right"></i>
  </div>

  <AppTooltip
    :show="tooltip.show"
    :text="tooltip.text"
    :top="tooltip.top"
    :left="tooltip.left"
    :color="tooltip.color"
  />
</template>
