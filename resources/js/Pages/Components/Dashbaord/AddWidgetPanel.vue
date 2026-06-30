<script setup>
import { ref, computed } from "vue";
import { WIDGET_REGISTRY, WIDGET_TYPES } from "./WidgetRegistry.js";

const props = defineProps({
  activeLayout:    { type: Array,  required: true },
  modules:         { type: Array,  default: () => [] },
  editingInstance: { type: Object, default: null },
});

const emit = defineEmits(["close", "add", "remove", "add-instance", "update-instance"]);

// When editing, jump straight into the config form for that widget type.
const configuringType = ref(props.editingInstance?.type ?? null);

// ── Legacy widgets (existing string-ID-based widgets) ─────────────────────────
const allLegacyWidgets = computed(() =>
  Object.entries(WIDGET_REGISTRY).map(([id, def]) => ({
    id,
    label: def.label,
    description: def.description,
    icon: def.icon,
    active: props.activeLayout.some(
      (item) => typeof item === "string" && item === id,
    ),
  })),
);

const activeLegacyWidgets = computed(() =>
  allLegacyWidgets.value.filter((w) => w.active),
);
const availableLegacyWidgets = computed(() =>
  allLegacyWidgets.value.filter((w) => !w.active),
);

// ── Configurable types ────────────────────────────────────────────────────────
const configurableTypes = computed(() =>
  Object.entries(WIDGET_TYPES).map(([key, def]) => ({ key, ...def })),
);

const configuringDef = computed(() =>
  configuringType.value ? WIDGET_TYPES[configuringType.value] : null,
);

function onInstanceSubmit(instance) {
  if (props.editingInstance) {
    emit("update-instance", instance);
  } else {
    emit("add-instance", instance);
    configuringType.value = null;
  }
  emit("close");
}
</script>

<template>
  <Teleport to="body">
    <div class="wp-modal">
      <div class="wp-modal__backdrop" @click="emit('close')"></div>

      <button class="wp-modal__close" @click="emit('close')">
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>

      <div class="wp-modal__container">
        <div class="wp">
          <!-- Header -->
          <div class="wp__header">
            <div>
              <h2 class="wp__title">
                <template v-if="configuringType">
                  <button v-if="!editingInstance" class="wp__back" @click="configuringType = null">
                    <i class="fa-solid fa-arrow-left"></i>
                  </button>
                  {{ editingInstance ? $t('globals.dashboard.edit_widget') : configuringDef.label }}
                </template>
                <template v-else>{{
                  $t("globals.dashboard.customize_title")
                }}</template>
              </h2>
              <p class="wp__subtitle">
                <template v-if="configuringType">{{
                  $t("globals.dashboard.configure_subtitle")
                }}</template>
                <template v-else>{{
                  $t("globals.dashboard.customize_subtitle")
                }}</template>
              </p>
            </div>
          </div>

          <!-- Body -->
          <div class="wp__body">
            <!-- ── Config form view ─────────────────────────────────────────── -->
            <component
              v-if="configuringType"
              :is="configuringDef.configComponent"
              :modules="modules"
              :editing-instance="editingInstance"
              :default-cols="configuringDef.defaultCols"
              @submit="onInstanceSubmit"
              @cancel="editingInstance ? emit('close') : (configuringType = null)"
            />

            <!-- ── Widget list view ────────────────────────────────────────── -->
            <template v-else>
              <!-- Active legacy widgets -->
              <section v-if="activeLegacyWidgets.length">
                <div class="wp__section-label">
                  {{ $t("globals.dashboard.section_active") }}
                  <span class="wp__count">{{
                    activeLegacyWidgets.length
                  }}</span>
                </div>
                <div class="wp__list">
                  <div
                    v-for="w in activeLegacyWidgets"
                    :key="w.id"
                    class="wp__item wp__item--active"
                  >
                    <div class="wp__item-icon"><i :class="w.icon"></i></div>
                    <div class="wp__item-info">
                      <span class="wp__item-name">{{ $t(w.label) }}</span>
                      <span class="wp__item-desc">{{ $t(w.description) }}</span>
                    </div>
                    <button
                      class="wp__item-btn wp__item-btn--remove"
                      :title="$t('globals.dashboard.remove_widget')"
                      @click="emit('remove', w.id)"
                    >
                      <i class="fa-solid fa-minus"></i>
                    </button>
                  </div>
                </div>
              </section>

              <!-- Available legacy widgets -->
              <section
                v-if="availableLegacyWidgets.length"
                :style="activeLegacyWidgets.length ? 'margin-top:24px' : ''"
              >
                <div class="wp__section-label">
                  {{ $t("globals.dashboard.section_available") }}
                  <span class="wp__count">{{
                    availableLegacyWidgets.length
                  }}</span>
                </div>
                <div class="wp__list">
                  <div
                    v-for="w in availableLegacyWidgets"
                    :key="w.id"
                    class="wp__item"
                  >
                    <div class="wp__item-icon"><i :class="w.icon"></i></div>
                    <div class="wp__item-info">
                      <span class="wp__item-name">{{ $t(w.label) }}</span>
                      <span class="wp__item-desc">{{ $t(w.description) }}</span>
                    </div>
                    <button
                      class="wp__item-btn wp__item-btn--add"
                      :title="$t('globals.dashboard.add_widget')"
                      @click="emit('add', w.id)"
                    >
                      <i class="fa-solid fa-plus"></i>
                    </button>
                  </div>
                </div>
              </section>

              <!-- Configurable types -->
              <section style="margin-top: 24px">
                <div class="wp__section-label">
                  {{ $t("globals.dashboard.section_configurable") }}
                  <span class="wp__count">{{ configurableTypes.length }}</span>
                </div>
                <div class="wp__list">
                  <div
                    v-for="t in configurableTypes"
                    :key="t.key"
                    class="wp__item"
                  >
                    <div class="wp__item-icon"><i :class="t.icon"></i></div>
                    <div class="wp__item-info">
                      <span class="wp__item-name">{{ $t(t.label) }}</span>
                      <span class="wp__item-desc">{{ t.description }}</span>
                    </div>
                    <button
                      class="wp__item-btn wp__item-btn--add"
                      :title="$t('globals.dashboard.configure_add')"
                      @click="configuringType = t.key"
                    >
                      <i class="fa-solid fa-plus"></i>
                    </button>
                  </div>
                </div>
              </section>

              <div
                v-if="
                  !availableLegacyWidgets.length &&
                  !activeLegacyWidgets.length &&
                  !configurableTypes.length
                "
                class="wp__empty"
              >
                {{ $t("globals.dashboard.no_widgets") }}
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
