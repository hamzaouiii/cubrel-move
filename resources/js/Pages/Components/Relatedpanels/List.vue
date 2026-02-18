<script setup>
import { ref, computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";

const props = defineProps({
  relationships: { type: Object, required: true },
  layout: { type: Object, required: true },
});

const page = usePage();

const relationshipMap = computed(() => {
  return Object.values(props.relationships).reduce((acc, rel) => {
    acc[rel.name] = rel;
    return acc;
  }, {});
});

const panels = computed(() => props.layout?.panels ?? []);

const openPanels = ref(
  Object.values(props.relationships)
    .filter((r) => r.records?.length)
    .map((r) => r.name),
);

const togglePanel = (name) => {
  const index = openPanels.value.indexOf(name);
  index === -1
    ? openPanels.value.push(name)
    : openPanels.value.splice(index, 1);
};

const modules = computed(() => page.props.modules);

const getModule = (slug) => modules.value.find((m) => m.slug === slug);

const getRelatedIcon = (slug) => getModule(slug)?.icon;
const getRelatedColor = (slug) => getModule(slug)?.color;
const getRelatedRecordurl = (slug, id) => `/${slug}/${id}`;
</script>

<template>
  <div class="relatedpanels">
    <ul class="relatedpanels__container">
      <div
        v-for="(col, colIndex) in panels"
        :key="colIndex"
        class="relatedpanels__container__column"
      >
        <li
          v-for="panel in col.layout || []"
          :key="panel.name"
          class="relatedpanels__item"
        >
          <div
            v-if="relationshipMap[panel.name]"
            @click="togglePanel(panel.name)"
            class="relatedpanels__item__header"
          >
            <div
              class="relatedpanels__item__header__title"
              :style="{
                '--related-color': getRelatedColor(
                  relationshipMap[panel.name].related_slug,
                ),
              }"
            >
              <i
                :class="
                  getRelatedIcon(relationshipMap[panel.name].related_slug)
                "
              ></i>
              {{ $t(relationshipMap[panel.name].label) }}
            </div>

            <div class="relatedpanels__item__header__count">
              {{ relationshipMap[panel.name].records?.length ?? 0 }}
            </div>
          </div>

          <Transition name="expand">
            <div
              v-if="
                relationshipMap[panel.name] && openPanels.includes(panel.name)
              "
              class="relatedpanels__item__body"
            >
              <ul class="related-records">
                <ul class="related-records__header">
                  <li>Name</li>
                  <li>Description</li>
                </ul>

                <li
                  v-for="record in relationshipMap[panel.name].records"
                  :key="record.id"
                >
                  <ul class="related-records__body">
                    <li>
                      <Link
                        :href="
                          getRelatedRecordurl(
                            relationshipMap[panel.name].related_slug,
                            record.id,
                          )
                        "
                      >
                        {{ record.name }}
                      </Link>
                    </li>
                    <li>{{ record.description }}</li>
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
