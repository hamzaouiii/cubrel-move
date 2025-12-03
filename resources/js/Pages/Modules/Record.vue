
<script setup>
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount, reactive } from 'vue'

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  title: String,
  record: Object,
  recordLayout: Object
})
import { getCurrentInstance } from 'vue'

const { proxy } = getCurrentInstance()
const t = proxy.$t


const form = useForm({ ...props.record })

const isEditing = ref(false)
const showActionDropDown = ref(false)
const actionDropDownref = ref(null)

const editableRecord = reactive({ ...props.record })

const toggleActionDropDown = () => {
  showActionDropDown.value = !showActionDropDown.value
}

const handleClickOutsideActionDropDown = (event) => {
  if (actionDropDownref.value && !actionDropDownref.value.contains(event.target)) {
    showActionDropDown.value = false
  }
}

const hasRecordChanged = (original, edited) => {
  for (const key of Object.keys(edited)) {
    const originalValue = original[key]
    const editedValue = edited[key]

    if (originalValue !== editedValue) {
      return true
    }
  }
  return false
}

const isDirty = computed(() => hasRecordChanged(props.record, form))
const enableEditing = () => {
  isEditing.value = true
}

const getChangedData = (original, edited) => {
  const changed = {}

  for (const key of Object.keys(edited)) {
    if (original[key] !== edited[key]) {
      changed[key] = edited[key]
    }
  }

  return changed
}

const saveRecord = () => {
  const payload = getChangedData(props.record, form)

  if (Object.keys(payload).length === 0) {
    isEditing.value = false
    return
  }

  const moduleSlug = props.module.slug ?? props.module
  const url = `/${moduleSlug}/${props.record.id}`
  form
    .transform(() => payload)
    .put(url, {
      onSuccess: () => {
        isEditing.value = false
      },
      onError: () => {
        console.error('Error saving record:', form.errors)
      },
    })


}

function handleKeydown(e) {
  // CTRL + S
  if (e.ctrlKey && e.key === 's') {
    e.preventDefault()
    if (isEditing.value) {
      saveRecord()
    }
  }

  // CTRL + E -> enable editing
  if (e.ctrlKey && e.key === 'e') {
    e.preventDefault()
    enableEditing()
  }

  // ESC -> cancel editing
  if (e.key === 'Escape') {
    cancelEditing()
  }
}

const cancelEditing = () => {
  form.reset()          // reset to original props.record
  isEditing.value = false
}

onMounted(() => {
  document.addEventListener('click', handleClickOutsideActionDropDown)
  window.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutsideActionDropDown)
  window.removeEventListener('keydown', handleKeydown)
})

const formatDate = (value) => {
  if (!value) return '-';
  return new Date(value).toLocaleDateString('de-DE', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  });
};

const appSettings = usePage().props.appSettings

</script>

<template>
  <Head>
    <title>{{ record.name }} - {{ title }} - Automatisierung Regensburg</title>
  </Head>
  
  <div class="ar-main-container"  :style="appSettings.use_individual_module_colors == '0' ? {'--module-color': appSettings.primary_color} : { '--module-color': module.color } " >
    <div class="ar-main-container_header">
      <div class="ar-main-container_header_details">
        <h1 class="ar-main-container_header_details_title">{{ record.label }}</h1>
      </div>
      <div class="ar-main-container_header_actions" ref="actionDropDownref" >
        <div class="input-group" >
          <button 
            v-if="isEditing"
            type="button" 
            class="record-main-btn cancel-btn" 
            @click="cancelEditing"
          >
            {{ $t('modules.actions.cancel') }}
          </button>
          
          <button 
            v-if="!isEditing"
            type="button" 
            class="record-main-btn" 
            @click="enableEditing"
          >
            {{ $t('modules.actions.edit') }}
          </button>
          
            <button   v-else
              type="button" 
              class="record-main-btn" 
                :disabled="!isDirty"
                @click="saveRecord"
            >
              {{ $t('modules.actions.save') }}
            </button>


          <button 
            @click="toggleActionDropDown" 
            type="button" 
            class="record-dropdown-btn" 
            data-bs-toggle="dropdown" 
            aria-expanded="false" 
            
          >
            <i :class="showActionDropDown ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"></i>
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>
          
          <transition name="fade">
            <ul v-if="showActionDropDown" class="dropdown-menu dropdown-menu-end show">
              <li><a class="dropdown-item disabled" href="#">{{ $t('modules.actions.share') }}</a></li>
              <li><a class="dropdown-item disabled" href="#">{{ $t('modules.actions.export') }}</a></li>
              <li><a class="dropdown-item" href="#">{{ $t('modules.actions.placeholder') }}</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#">{{ $t('modules.actions.bulk_action') }}</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" style="color: salmon">{{ $t('modules.actions.delete') }}</a></li>
            </ul>
          </transition>
        </div>
      </div>
    </div>
    
    <div class="ar-main-container_content">
      <div class="ar-main-container_content_section card-shadow" v-for="s in recordLayout.sections">
        <div class="ar-main-container_content_section_title">
          {{ s.name }}
        </div>
        <div class="ar-main-container_content_section_layout">
          <div v-for="f in s.layout" class="ar-main-container_content_section_layout_field">
            <span class="ar-main-container_content_section_layout_field_label">
              {{ $t((f.label)) }}:
            </span>
            
            <div  v-if="!isEditing" class="field" @click="enableEditing">
                <!-- View Mode -->
                <template v-if="f.format === 'datetime' && record[f.key]">
                  <span>
                    {{ formatDate(record[f.key]) }}
                  </span>
                </template>
                <template v-else>
                  <span>
                  {{ record[f.key] }}
                  </span>

                </template>
            </div>
            <div class="field editing-mode" v-else>
                <!-- Edit Mode -->
                <template v-if="f.format === 'datetime'">
                  <input 
                    type="date" 
                    v-model="form[f.key]"
                    :placeholder="form[f.key]"
                  />
                </template>
                <template v-else>
                  <input 
                    type="text" 
                    v-model="form[f.key]"
                  />
                </template>
           </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>