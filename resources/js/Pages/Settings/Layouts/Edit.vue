<script setup>
import { computed, ref, watch, getCurrentInstance } from 'vue'
import Layout from '@/Layouts/Layout.vue'
import { Head, usePage, Link, useForm } from '@inertiajs/vue3'
import LayoutListEditor from '@/Pages/Components/Settings/LayoutListEditor.vue'

const { proxy } = getCurrentInstance()
const t = proxy.$t

defineOptions({
  layout: Layout,
})

const props = defineProps({
  item: Object,         // current Layout model if exists
  module: Object,       // module config
  type: String,         // 'list' | 'record'
  defaultLayout: Object,
  fields: Object,       // module fields
})

/**
 * DB-backed layout definition (custom for this module & type,
 * or fallback to default/global).
 */
const currentLayout = computed(() => {
  const custom = props.module.layouts?.find(
    (layout) => layout.type === props.type,
  )

  return custom?.definition || props.defaultLayout?.definition || null
})

const moduleFields = computed(() => {
  return props.fields ?? []
})

const fieldByKey = computed(() => {
  const map = {}
  for (const field of moduleFields.value) {
    map[field.key] = field
  }
  return map
})

/**
 * Columns as stored in DB layout definition.
 */
const layoutColumnConfigs = computed(() => {
  const def = currentLayout.value
  if (!def || !Array.isArray(def.columns)) return []
  return def.columns
})

/**
 * Columns enriched with field metadata & label
 * – this reflects the DB state, not the live edited one.
 */
const selectedColumnsFromDb = computed(() => {
  return layoutColumnConfigs.value
    .map((col) => {
      const field = fieldByKey.value[col.key]
      if (!field) return null

      return {
        ...col,
        field,
        label: col.label ?? field.label ?? col.key,
      }
    })
    .filter(Boolean)
})

/**
 * Editable columns used by the LayoutListEditor (v-model:columns).
 * We initialise from DB state and then let the child mutate this.
 */
const columns = ref([])

watch(
  selectedColumnsFromDb,
  (val) => {
    // on first load or DB change (e.g. reset), sync editable state
    columns.value = [...val]
  },
  { immediate: true },
)

/**
 * Available fields: all module fields that are NOT currently used
 * in the edited columns list.
 */
const availableFields = computed(() => {
  const usedKeys = new Set(columns.value.map((col) => col.key))
  return moduleFields.value.filter((field) => !usedKeys.has(field.key))
})

/**
 * Clean helper: strip the attached "field" object before saving / comparing.
 */
const cleanedColumns = computed(() =>
  columns.value.map((col) => {
    const { field, ...rest } = col
    return rest
  }),
)

const cleanedColumnsFromDb = computed(() =>
  layoutColumnConfigs.value.map((col) => {
    const { field, ...rest } = col
    return rest
  }),
)

/**
 * Dirty detection: compare current edited columns to DB columns
 * (keys, order & basic config).
 */
const isDirty = computed(() => {
  const current = JSON.stringify(cleanedColumns.value)
  const original = JSON.stringify(cleanedColumnsFromDb.value)
  return current !== original
})

/**
 * Inertia form for saving the layout.
 */
const form = useForm({
  module_id: props.module.id,
  type: props.type,
  definition: currentLayout.value ?? { columns: cleanedColumnsFromDb.value },
})

/**
 * Reset edited columns back to DB values.
 */
const resetToDatabaseValue = () => {
  columns.value = [...selectedColumnsFromDb.value]

  form.definition = currentLayout.value ?? {
    columns: cleanedColumnsFromDb.value,
  }

  form.clearErrors()
}

/**
 * Save current layout columns to backend.
 * Adjust the URL/route to match your app.
 */
const saveLayout = () => {
  const definition = {
    ...(currentLayout.value || {}),
    columns: cleanedColumns.value,
  }

  form.definition = definition

  // TODO: adjust URL to your actual route
  const url = `/settings/customisation/layouts/${props.module.id}/${props.type}`

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      form.clearErrors()
    },
  })
}
</script>


<template>
  <Head>
    <title>{{ module.label }} - {{ $t('settings.label') }}</title>
  </Head>

  <div class="layout">
    <div class="settings_header">
      <div class="settings_header_title">
        <h5><Link href="/settings">{{ $t('settings.label') }}</Link></h5>
        <span>></span>
        <h5><Link href="/settings/customisation/layouts">{{ $t('layouts.label') }}</Link></h5>
        <span>></span>
        <h5><Link :href="'/settings/customisation/layouts/' + module.id">{{ module.label }}</Link></h5>
        <span>></span>
        <h6>{{ type }}</h6>
      </div>
    </div>

    <div class="layout_editor">
      <LayoutListEditor
        v-if="type === 'list'"
        v-model:columns="columns"
        :available-fields="availableFields"
      />

      <div class="layout_editor_actions">
        <button
          @click="resetToDatabaseValue()"
          class="reset-btn"
          type="reset"
          :disabled="!isDirty"
        >
          Reset
        </button>

        <button
          @click="saveLayout()"
          type="submit"
          :disabled="!isDirty || form.processing"
        >
          Save
        </button>
      </div>
    </div>
  </div>
</template>
