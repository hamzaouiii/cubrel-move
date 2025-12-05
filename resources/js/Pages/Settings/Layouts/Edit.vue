<script setup>
import { computed, getCurrentInstance} from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, Link, useForm } from '@inertiajs/vue3'
import LayoutListEditor from '@/Pages/Components/Settings/LayoutListEditor.vue';
import { ref } from 'vue'


const { proxy } = getCurrentInstance()
const t = proxy.$t 
defineOptions({
  layout: Layout,
});
const props = defineProps({
  item: Object,
  module: Object,
  type: String,
  defaultLayout: Object,
  fields: Object
})

const currentLayout = computed(() => {
  const custom = props.module.layouts?.find(
    layout => layout.type === props.type
  )

  return custom?.definition || props.defaultLayout?.definition || null
})

const moduleFields = computed(() => {
  return props.fields ?? []
})

const fieldByKey = computed(() => {
  const map = {}
  for (const field of moduleFields.value) {
    // adjust `field.name` if your identifier is called differently
       map[field.key] = field
  }
  return map
})

const layoutColumnConfigs = computed(() => {
  const def = currentLayout.value
  if (!def || !Array.isArray(def.columns)) return []
  return def.columns
})

const selectedColumns = computed(() => {
  return layoutColumnConfigs.value
    .map(col => {
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

const availableFields = computed(() => {
  const usedKeys = new Set(layoutColumnConfigs.value.map(col => col.key))
  return moduleFields.value.filter(field => !usedKeys.has(field.key))
})


</script>

<template>
      <Head>
      <title>{{module.label}} - {{ $t('settings.label')}}</title>
    </Head>
    <div class="layout">
      <div class="settings_header">
        <div class="settings_header_title">
          <h5><Link href="/settings">{{ $t('settings.label')  }} </Link></h5>
          <span>></span>
          <h5><Link href="/settings/customisation/layouts">{{ $t('layouts.label')  }} </Link></h5>
          <span>></span>
          <h5><Link :href="'/settings/customisation/layouts/'+module.id">{{ module.label }} </Link></h5>
          <span>></span>
          <h6>{{type }}</h6>
        </div>
      </div>

      <div class="layout_editor">
        <LayoutListEditor 
          v-if="type === 'list'"
          ref="componentRef"
          v-model:columns="selectedColumns"
          :available-fields="availableFields"
        />

      </div>
    </div>
</template>