<script setup>
import { computed, ref, onMounted, reactive, getCurrentInstance } from "vue";
import { useForm, Link, usePage, router } from "@inertiajs/vue3";
import AppTooltip from "./AppTooltip.vue";

const form = useForm({});
const logout = () => {
  form.post("/logout");
};

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const SIDEBAR_KEY = "sidebar-collapsed";
const page = usePage();
const modules = computed(() => page.props.modules ?? []);
const currentUrl = computed(() => page.url);
const appSettings = usePage().props.appSettings;
const collapsedSidebar = ref(true);

function collapseSidebar() {
  collapsedSidebar.value = true;
}

function toggleSidebar() {
  collapsedSidebar.value = !collapsedSidebar.value;
}

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
    text = t("sidebar.home");
    color = appSettings.primary_color;
  } else {
    text = mod.label;
    color =
      appSettings.use_individual_module_colors == "0"
        ? appSettings.primary_color
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
    ? t("sidebar.expand")
    : t("sidebar.close");
  tooltip.color = appSettings.primary_color;
  tooltip.top = rect.top + rect.height / 2;
  tooltip.left = rect.right + 10;
  tooltip.show = true;
};

const onCollapserMouseLeave = () => {
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
      },
    ]"
  >
    <div @click="toggleSidebar" class="sidebar__collapser">
      <div
        class="sidebar__collapser__icon"
        :style="{ '--setting-primary-color': appSettings.primary_color }"
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
            >{{ $t("sidebar.home") }}</span
          >
        </div>
      </Link>
      <hr class="sidebar__module-list__divider" />
      <Link
        :class="[
          'sidebar__module-list__item',
          { active: currentUrl.startsWith(mod.path) },
        ]"
        v-for="mod in modules"
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
    </div>

    <div class="sidebar__footer"></div>
  </aside>
  <div
    v-if="!collapsedSidebar"
    @click="toggleSidebar"
    class="sidebar__overlay"
  ></div>

  <AppTooltip
    :show="tooltip.show"
    :text="tooltip.text"
    :top="tooltip.top"
    :left="tooltip.left"
    :color="tooltip.color"
  />
</template>
