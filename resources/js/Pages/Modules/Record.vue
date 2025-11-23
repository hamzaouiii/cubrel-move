
<script setup>
import Layout from '@/Layouts/Layout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
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

// Use Inertia form as editable state
const form = useForm({ ...props.record })

// State for editing mode
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

// compare original record with form state
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

  // build URL manually, no Ziggy/route()
  const moduleSlug = props.module.slug ?? props.module
  const url = `/${moduleSlug}/${props.record.id}`
  form
    .transform(() => payload)
    .put(url, {
      onSuccess: () => {
        isEditing.value = false
      },
      onError: () => {
        // validation errors available in form.errors
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
</script>

<template>
  <Head>
    <title>{{ record.name }} - {{ title }} - Automatisierung Regensburg</title>
  </Head>
  
  <div class="ar-main-container" :style="{ '--module-color': module.color }" >
    <div class="ar-main-container_header">
      <div class="ar-main-container_header_details">
        <h1 class="ar-main-container_header_details_title">{{ record.name }}</h1>
      </div>
      <div class="ar-main-container_header_actions" ref="actionDropDownref">
        <div class="input-group">
          <button 
            v-if="isEditing"
            type="button" 
            class="btn btn-outline-secondary" 
            :style="{ color: module.color }"
            @click="cancelEditing"
          >
            Cancel
          </button>
          
          <!-- Edit/Save Button -->
          <button 
            v-if="!isEditing"
            type="button" 
            class="btn btn-outline-secondary" 
            :style="{ background: module.color, color: 'white' }"
            @click="enableEditing"
          >
            Edit
          </button>
          
          <!-- Save/Cancel Buttons when editing -->
            <button   v-else
              type="button" 
              class="btn btn-outline-secondary" 
              :style="{ background: module.color, color: 'white' }"
                :disabled="!isDirty"
                @click="saveRecord"
            >
              Save
            </button>


          <button 
            @click="toggleActionDropDown" 
            type="button" 
            class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
            data-bs-toggle="dropdown" 
            aria-expanded="false" 
            :style="{ background: module.color, color: 'white' }"
          >
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>
          
          <transition name="fade">
            <ul v-if="showActionDropDown" class="dropdown-menu dropdown-menu-end show">
              <li><a class="dropdown-item disabled" href="#">Share</a></li>
              <li><a class="dropdown-item disabled" href="#">Export</a></li>
              <li><a class="dropdown-item" href="#">Something else here</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#">Convert</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" style="color: salmon">Delete</a></li>
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
            <span class="label">
              {{ f.label }}:
            </span>
            
            <!-- Dynamic field - span when viewing, input when editing -->
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