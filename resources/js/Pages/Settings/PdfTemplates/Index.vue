<script setup>
import { computed, ref, nextTick, getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import Pagination from "@/Pages/Components/Globals/Pagination.vue";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { success, error } = useAlerts();
const { confirm } = useConfirm();
const appSettings = usePage().props.appSettings;

const props = defineProps({
  templates: Array,
  pdf_modules: Object,
  meta: Object,
  filters: Object,
});

const search = ref(props.filters?.search ?? "");
const showSearch = ref(!!props.filters?.search);
const searchInput = ref(null);

const toggleSearch = () => {
  if (showSearch.value) {
    showSearch.value = false;
    search.value = "";
    performSearch();
  } else {
    showSearch.value = true;
    nextTick(() => searchInput.value?.focus());
  }
};

const performSearch = (page = 1) => {
  router.get(
    window.location.pathname,
    { search: search.value || undefined, page },
    { preserveState: true, preserveScroll: true, replace: true },
  );
};

const grouped = computed(() => {
  const map = {};
  for (const tpl of props.templates) {
    if (!map[tpl.module_slug]) map[tpl.module_slug] = [];
    map[tpl.module_slug].push(tpl);
  }
  return map;
});

const moduleLabel = (slug) => props.pdf_modules?.[slug]?.label ?? slug;

const hidePagination = computed(
  () => (props.meta?.total ?? 0) <= (props.meta?.perPage ?? 15),
);

const setDefault = (id) => {
  router.post(
    `/settings/pdf-templates/${id}/default`,
    {},
    {
      preserveScroll: true,
      onSuccess: () => success(t("globals.pdf_templates.default_updated")),
      onError: () => error(t("globals.pdf_templates.default_update_error")),
    },
  );
};

const handleDelete = async (tpl) => {
  const confirmed = await confirm({
    title: t("globals.pdf_templates.delete_confirm_title"),
    message: t("globals.pdf_templates.delete_template_confirm", {
      template: tpl.name,
    }),
    highlight: tpl.name,
    danger: true,
  });
  if (!confirmed) return;

  router.delete(`/settings/pdf-templates/${tpl.id}`, {
    preserveScroll: true,
    onSuccess: () => success(t("globals.pdf_templates.deleted")),
    onError: () => error(t("globals.pdf_templates.delete_error")),
  });
};

const metaSentence = computed(() => {
  return `${props.templates?.length ?? null} ${t("modules.of")} ${props.meta.total}`;
});
</script>

<template>
  <Head>
    <title>{{ $t("globals.pdf_templates.page_title") }}</title>
  </Head>

  <div
    class="settings pdf-templates"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--module-color': appSettings.primary_color,
      '--secondary-color': appSettings.secondary_color,
      '--danger-color': appSettings.danger_color,
    }"
  >
    <div class="settings__module__header">
      <Link href="/settings">
        <i class="fa-solid fa-arrow-left"></i>
        {{ $t("settings.back_to_settings") }}
      </Link>
    </div>

    <div class="pdf-templates__header">
      <div class="pdf-templates__header__details">
        <span class="pdf-templates__header__details__title">
          {{ $t("globals.pdf_templates.page_title") }}
        </span>
        <span class="pdf-templates__header__details__meta">
          {{ metaSentence ?? "" }}
        </span>
      </div>

      <div class="pdf-templates__header__actions">
        <Transition name="slide-search">
          <div class="pdf-templates__header__actions__search" v-if="showSearch">
            <input
              ref="searchInput"
              v-model="search"
              type="text"
              :placeholder="$t('settings.dropdown.search')"
              @keyup.enter="performSearch()"
            />
          </div>
        </Transition>
        <button @click="toggleSearch">
          <i
            class="fa-solid"
            :class="showSearch ? 'fa-xmark' : 'fa-magnifying-glass'"
          ></i>
        </button>
        <Link href="/settings/pdf-templates/create">
          <i class="fa-solid fa-plus"></i>
        </Link>
      </div>
    </div>

    <div class="list-layout__table-scroll">
      <table class="list-layout__table">
        <tbody>
          <template v-if="templates.length === 0">
            <tr>
              <td colspan="2" class="pdf-templates__empty">
                {{
                  !filters?.search
                    ? $t("globals.pdf_templates.no_templates")
                    : $t("settings.no_results")
                }}
              </td>
            </tr>
          </template>

          <template v-else>
            <template v-for="(group, slug) in grouped" :key="slug">
              <tr class="pdf-templates__group-row">
                <td colspan="2">{{ moduleLabel(slug) }}</td>
              </tr>
              <tr v-for="tpl in group" :key="tpl.id">
                <td>
                  <div class="pdf-templates__cell-name">
                    <i
                      class="fa-solid fa-file-pdf"
                      :class="
                        tpl.is_default
                          ? 'pdf-templates__icon--active'
                          : 'pdf-templates__icon--muted'
                      "
                    ></i>
                    <span>{{ tpl.name }}</span>
                    <span v-if="tpl.is_default" class="pdf-templates__badge">
                      {{ $t("globals.pdf_templates.default_badge") }}
                    </span>
                  </div>
                </td>
                <td class="row-actions">
                  <button
                    v-if="!tpl.is_default"
                    class="row-action-btn row-action-btn--primary"
                    @click="setDefault(tpl.id)"
                    :title="$t('globals.pdf_templates.set_as_default_btn')"
                  >
                    <i class="fa-regular fa-circle-dot"></i>
                    {{ $t("globals.pdf_templates.set_as_default_btn") }}
                  </button>
                  <Link
                    class="row-action-btn row-action-btn--revoke"
                    :href="`/settings/pdf-templates/${tpl.id}`"
                  >
                    <i class="fa-solid fa-pen-to-square"></i>
                    {{ $t("globals.pdf_templates.edit_btn") }}
                  </Link>
                  <button
                    class="row-action-btn row-action-btn--delete"
                    @click="handleDelete(tpl)"
                  >
                    <i class="fa-solid fa-trash"></i>
                    {{ $t("globals.pdf_templates.delete_btn") }}
                  </button>
                </td>
              </tr>
            </template>
          </template>
        </tbody>
      </table>
    </div>

    <div class="list-layout__pagination" v-if="!hidePagination">
      <Pagination v-if="meta && meta.total !== 0" :meta="meta" />
    </div>
  </div>
</template>
