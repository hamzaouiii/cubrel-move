<script setup>
import { ref, computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";

const props = defineProps({
  relationships: {
    type: Object,
    required: true,
  },
});

const page = usePage();

const panels = computed(() => Object.values(props.relationships));

const columns = computed(() => {
  return panels.value.reduce(
    (acc, panel, index) => {
      acc[index % 2].push(panel);
      return acc;
    },
    [[], []],
  );
});

const showPanelBody = ref(
  panels.value
    .filter((panel) => panel.records?.length > 0)
    .map((panel) => panel.name),
);

const togglePanel = (panel) => {
  const index = showPanelBody.value.indexOf(panel.name);

  if (index === -1) {
    showPanelBody.value.push(panel.name);
  } else {
    showPanelBody.value.splice(index, 1);
  }
};

const getModule = (slug) => {
  return page.props.modules.find((module) => module.slug === slug);
};

const getRelatedIcon = (slug) => getModule(slug)?.icon;
const getRelatedColor = (slug) => getModule(slug)?.color;
const getRelatedRecordurl = (slug, id) => {
  return `/${slug}/${id}`;
};
</script>

<template>
  <div class="relatedpanels">
    <ul class="relatedpanels__container">
      <div
        v-for="(column, colIndex) in columns"
        :key="colIndex"
        class="relatedpanels__container__column"
      >
        <li
          v-for="panel in column"
          :key="panel.name"
          class="relatedpanels__item"
        >
          <div @click="togglePanel(panel)" class="relatedpanels__item__header">
            <div
              class="relatedpanels__item__header__title"
              :style="{
                '--related-color': getRelatedColor(panel.related_slug),
              }"
            >
              <i :class="getRelatedIcon(panel.related_slug)"></i>
              {{ $t(panel.label) }}
            </div>

            <div class="relatedpanels__item__header__count">
              {{ panel.records?.length ?? 0 }}
            </div>
          </div>
          <Transition name="expand">
            <div
              v-if="showPanelBody.includes(panel.name)"
              class="relatedpanels__item__body"
            >
              <ul class="related-records">
                <ul class="related-records__header">
                  <li>Dame</li>
                  <li>Description</li>
                </ul>
                <li v-for="related in panel.records" :key="related.id">
                  <ul class="related-records__body">
                    <li>
                      <Link
                        :href="
                          getRelatedRecordurl(panel.related_slug, related.id)
                        "
                      >
                        {{ related.name }}</Link
                      >
                    </li>
                    <li>{{ related.description }}</li>
                  </ul>
                </li>
              </ul>
            </div>
          </Transition>
        </li>
      </div>
    </ul>
  </div>
</template>
