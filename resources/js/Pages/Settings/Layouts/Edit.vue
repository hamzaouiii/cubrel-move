<script setup>
import { computed, ref, watch, getCurrentInstance } from 'vue'
import Layout from '@/Layouts/Layout.vue'
import { Head, usePage, Link, useForm } from '@inertiajs/vue3'
import LayoutListEditor from '@/Pages/Components/Settings/LayoutListEditor.vue'
import LayoutRecordEditor from '@/Pages/Components/Settings/LayoutRecordEditor.vue'

const { proxy } = getCurrentInstance()
const t = proxy.$t

defineOptions({
  layout: Layout,
})

const props = defineProps({
  module: Object,
  type: String, // 'list' or 'record'
  defaultLayout: Object,
  fields: Object,
})

// Reactive data
const listColumns = ref([])
const recordSections = ref([])

// Computed properties
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
    if (field?.key) map[field.key] = field
  }
  return map
})

// List layout specific
const listLayoutColumnConfigs = computed(() => {
  const def = currentLayout.value
  return def?.columns && Array.isArray(def.columns) ? def.columns : []
})

const selectedListColumnsFromDb = computed(() => {
  return listLayoutColumnConfigs.value
    .map((col) => {
      const field = fieldByKey.value[col?.key]
      if (!field) return null

      return {
        ...col,
        field,
        label: col.label ?? field.label ?? col.key,
      }
    })
    .filter(Boolean)
})

// Record layout specific
const recordLayoutSectionConfigs = computed(() => {
  if (props.type !== 'record') return []
  const def = currentLayout.value
  return def?.sections && Array.isArray(def.sections) ? def.sections : []
})

const recordLayoutFromDB = computed(() => {
  if (props.type !== 'record') return []
  
  return recordLayoutSectionConfigs.value
    .map((section) => {
      const layout = (section.layout || [])
        .map((col) => {
          const field = fieldByKey.value[col?.key]
          if (!field) return null

          return {
            ...col,
            field,
            label: col.label ?? field.label ?? col.key,
          }
        })
        .filter(Boolean)

      return {
        ...section,
        layout
      }
    })
})

// Watchers
watch(
  selectedListColumnsFromDb,
  (val) => {
    listColumns.value = [...val]
  },
  { immediate: true }
)

watch(
  recordLayoutFromDB,
  (val) => {
    recordSections.value = [...val]
  },
  { immediate: true }
)

// Available fields for list layout
const availableFields = computed(() => {
  if (props.type !== 'list') return []
  
  const usedKeys = new Set(listColumns.value.map((col) => col?.key).filter(Boolean))
  return moduleFields.value.filter((field) => !usedKeys.has(field.key))
})

// Clean data for comparison/saving
const cleanedListColumns = computed(() => 
  listColumns.value.map((col) => {
    const { field, ...rest } = col || {}
    return rest
  })
)

const cleanedRecordSections = computed(() =>
  recordSections.value.map((section) => ({
    ...section,
    layout: (section.layout || []).map((col) => {
      const { field, ...rest } = col || {}
      return rest
    })
  }))
)

const cleanedColumnsFromDb = computed(() =>
  listLayoutColumnConfigs.value.map((col) => {
    const { field, ...rest } = col || {}
    return rest
  })
)

// Dirty detection
const isDirty = computed(() => {
  if (props.type === 'list') {
    const current = JSON.stringify(cleanedListColumns.value)
    const original = JSON.stringify(cleanedColumnsFromDb.value)
    return current !== original
  } else if (props.type === 'record') {
    const current = JSON.stringify(cleanedRecordSections.value)
    const original = JSON.stringify(recordLayoutSectionConfigs.value)
    return current !== original
  }
  return false
})

// Form
const form = useForm({
  module_id: props.module.id,
  type: props.type,
  definition: currentLayout.value || 
    (props.type === 'list' ? { columns: [] } : { sections: [] }),
})

// Reset function
const resetToDatabaseValue = () => {
  if (props.type === 'list') {
    listColumns.value = [...selectedListColumnsFromDb.value]
  } else if (props.type === 'record') {
    recordSections.value = [...recordLayoutFromDB.value]
  }
  
  form.definition = currentLayout.value || {}
  form.clearErrors()
}

// Save function
const saveLayout = () => {
  let definition = { ...(currentLayout.value || {}) }
  
  if (props.type === 'list') {
    definition.columns = cleanedListColumns.value
  } else if (props.type === 'record') {
    definition.sections = cleanedRecordSections.value
  }
  
  form.definition = definition

  const url = `/settings/customisation/layouts/${props.module.id}/${props.type}`

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      form.clearErrors()
    },
  })
}

const availableRecordFields = computed(() => {
  if (props.type !== 'record') return []
  
  const usedKeys = new Set()
  recordSections.value.forEach(section => {
    (section.layout || []).forEach(col => {
      if (col?.key) usedKeys.add(col.key)
    })
  })
  
  return moduleFields.value.filter(field => !usedKeys.has(field.key))
})


// Section management functions (for record layout)
const addNewSection = () => {
  recordSections.value.push({
    name: `Section ${recordSections.value.length + 1}`,
    layout: []
  })
}

const removeSection = (index) => {
  if (recordSections.value.length > 1) {
    recordSections.value.splice(index, 1)
  }
}

const addColumnToSection = (sectionIndex) => {
  if (recordSections.value[sectionIndex]) {
    recordSections.value[sectionIndex].layout.push({
      key: '',
      width: 100,
      label: ''
    })
  }
}

const removeColumnFromSection = (sectionIndex, columnIndex) => {
  if (recordSections.value[sectionIndex]?.layout) {
    recordSections.value[sectionIndex].layout.splice(columnIndex, 1)
  }
}
</script>

<template>
  <Head>
    <title> {{ type }} > {{ module.label }} > {{ $t('layouts.label') }} > {{ $t('settings.label') }}</title>
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
      <!-- List Layout Editor -->
      <LayoutListEditor
        v-if="type === 'list'"
        v-model:columns="listColumns"
        :available-fields="availableFields"
      />

      <div v-if="type === 'record'" class="record-layout-editor-wrapper">
        <LayoutRecordEditor
          v-model:sections="recordSections"
          :available-fields="availableRecordFields"
          :field-by-key="fieldByKey"
        />
      </div>

      <!-- Common actions for both types -->
      <div class="layout_editor_actions">
        <button
          @click="resetToDatabaseValue()"
          class="reset-btn"
          type="reset"
          :disabled="!isDirty"
        >
          {{ form.processing ? 'Resetting...' : 'Reset' }}
        </button>

        <button
          @click="saveLayout()"
          type="submit"
          :disabled="!isDirty || form.processing"
        >
          {{ form.processing ? 'Saving...' : 'Save Layout' }}
        </button>
      </div>
    </div>
  </div>
</template>
