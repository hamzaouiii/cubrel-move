<script setup>
import { computed, ref, watch, getCurrentInstance, onMounted, nextTick } from 'vue'
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
const showAlert = ref(false)
const alertType = ref('') // 'success' or 'error'
const alertMessage = ref('')
const alertTimeout = ref(null)

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

// Alert functions
const showSuccessAlert = (message) => {
  showAlert.value = true
  alertType.value = 'success'
  alertMessage.value = message || 'Layout saved successfully!'
  nextTick(() => {
    scrollToAlert()
  })
  
  // Auto-hide after 5 seconds
  if (alertTimeout.value) clearTimeout(alertTimeout.value)
  alertTimeout.value = setTimeout(() => {
    showAlert.value = false
  }, 5000)
}

const showErrorAlert = (message) => {
  showAlert.value = true
  alertType.value = 'error'
  alertMessage.value = message || 'Failed to save layout. Please try again.'
    nextTick(() => {
    scrollToAlert()
  })
  // Auto-hide after 8 seconds for errors
  if (alertTimeout.value) clearTimeout(alertTimeout.value)
  alertTimeout.value = setTimeout(() => {
    showAlert.value = false
  }, 8000)
}

const hideAlert = () => {
  showAlert.value = false
  if (alertTimeout.value) {
    clearTimeout(alertTimeout.value)
    alertTimeout.value = null
  }
}

// Check for flash messages on component mount
onMounted(() => {
  // Check if there are any flash messages from the server
  const flash = usePage().props.flash
  if (flash?.success) {
    showSuccessAlert(flash.success)
  } else if (flash?.error) {
    showErrorAlert(flash.error)
  }
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
  
  // Show reset confirmation
  showSuccessAlert('Layout reset to original values.')
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
      
      // Check if there's a success message from the backend
      const flash = usePage().props.flash
      if (flash?.success) {
        showSuccessAlert(flash.success)
      } else {
        showSuccessAlert('Layout saved successfully!')
      }
    },
    onError: (errors) => {
      // Show error alert with the first error message
      const firstError = Object.values(errors)[0]
      showErrorAlert(firstError || 'An error occurred while saving the layout.')
      
      // Log errors for debugging
      console.error('Save layout errors:', errors)
    }
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


const scrollToAlert = () => {
  nextTick(() => {
    const alertElement = document.querySelector('.layout-editor_alerts')
    if (alertElement) {
      alertElement.scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
      })
    } else {
      // Fallback to top of page
      scrollToTop()
    }
  })
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
      <!-- Alert Area -->
      <div v-if="showAlert" class="layout-editor_alerts">
        <div 
          :class="[
            'alert',
            alertType === 'success' ? 'alert-success' : 'alert-error'
          ]"
          role="alert"
        >
          <div class="alert-content">
            <span class="alert-icon">
              <i v-if="alertType === 'success'" class="fa-solid fa-check-circle"></i>
              <!-- <i v-if="true" class="fa-solid fa-check-circle"></i> -->
              <i v-if="alertType === 'error'" class="fa-solid fa-exclamation-circle"></i>
            </span>
            <span class="alert-message">{{ alertMessage }}</span>
          </div>
          <button 
            @click="hideAlert" 
            class="alert-close"
            type="button"
            aria-label="Close alert"
          >
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Form Errors Display -->
      <div v-if="form.hasErrors" class="layout-editor_errors">
        <div class="alert alert-error" role="alert">
          <div class="alert-content">
            <span class="alert-icon">
              <i class="fa-solid fa-exclamation-triangle"></i>
            </span>
            <div class="error-list">
              <div v-for="(error, field) in form.errors" :key="field" class="error-item">
                {{ error }}
              </div>
            </div>
          </div>
          <button 
            @click="form.clearErrors()" 
            class="alert-close"
            type="button"
            aria-label="Clear errors"
          >
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
      </div>

      <!-- List Layout Editor -->
      <LayoutListEditor
        v-if="type === 'list'"
        v-model:columns="listColumns"
        :available-fields="availableFields"
      />

      <!-- Record Layout Editor -->
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
          :disabled="!isDirty || form.processing"
        >
          {{ form.processing ? $t('layouts.resetting') : $t('layouts.reset') }}
        </button>

        <button
          @click="saveLayout()"
          type="submit"
          :disabled="!isDirty || form.processing"
          class="save-btn"
        >
          {{ form.processing ? $t('layouts.saving') : $t('layouts.save_layout') }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Alert Styles */
.layout-editor_alerts,
.layout-editor_errors {
  margin-bottom: 24px;
  animation: slideDown 0.3s ease-out;
}

.alert {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  animation: fadeIn 0.3s ease-out;
}

.alert-success {
  border: 3px solid transparent;        /* thickness */
  background:
    linear-gradient(rgba(44, 169, 90, 0.883), rgba(44, 169, 90, 0.76)) padding-box,
    linear-gradient(90deg, #18d1ff, #9daf00, #319197) border-box; /* border */
  color: #fff;
  padding: 16px 20px;
  background-clip: padding-box, border-box; /* (optional; most browsers infer it) */
  border-radius: 25px 5px 25px 5px; 

}

.alert-error {
  border: 3px solid transparent;        /* thickness */
  border-radius: 5px;
  background:
    linear-gradient(rgba(250, 128, 114, 0.765), rgba(250, 128, 114, 0.883)) padding-box,        /* inner fill */
    linear-gradient(90deg, #ff7a18, #af002d, #319197) border-box; /* border */
  color: #fff;
  padding: 16px 20px;
  background-clip: padding-box, border-box; /* (optional; most browsers infer it) */
  border-radius: 25px 5px 25px 5px; 

}

.alert-content {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.alert-icon {
  font-size: 18px;
  flex-shrink: 0;
  margin-top: 2px;
}

.alert-message {
  font-size: 14px;
  line-height: 1.5;
}

.error-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.error-item {
  font-size: 14px;
  line-height: 1.4;
}

.alert-close {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  padding: 4px;
  margin-left: 12px;
  opacity: 0.7;
  transition: opacity 0.2s;
  flex-shrink: 0;
  border-radius: 4px;
}

.alert-close:hover {
  opacity: 1;
  background-color: rgba(0, 0, 0, 0.05);
}

.alert-success .alert-close:hover {
  background-color: rgba(6, 95, 70, 0.1);
}

.alert-error .alert-close:hover {
  background-color: rgba(153, 27, 27, 0.1);
}



/* Animations */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>