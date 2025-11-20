<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount, reactive } from 'vue'

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  module: Object,
  title: String,
  record: Object,
  recordLayout: Object
})


// State for editing mode
const isEditing = ref(false)
const showActionDropDown = ref(false)
const actionDropDownref = ref(null)

// Create a reactive copy of the record for editing
const editableRecord = reactive({ ...props.record })

const toggleActionDropDown = () => {
  showActionDropDown.value = !showActionDropDown.value
}

const handleClickOutsideActionDropDown = (event) => {
  if (actionDropDownref.value && !actionDropDownref.value.contains(event.target)) {
    showActionDropDown.value = false
  }
}

// Edit/Save functionality
const enableEditing = () => {
  isEditing.value = true
}

const saveRecord = () => {
  //later
  // Use Inertia.js to POST the modified data
  // router.post(route('records.update', props.record.id), {
  //   _method: 'put', // Use PUT method for update
  //   ...editableRecord
  // }, {
  //   onSuccess: () => {
  //     isEditing.value = false
  //     // The page will be reloaded with updated data from the backend
  //   },
  //   onError: (errors) => {
  //     console.error('Error saving record:', errors)
  //     // Handle errors here (show validation messages, etc.)
  //   }
  // })
}

const cancelEditing = () => {
  // Reset the editableRecord to original values
  Object.assign(editableRecord, props.record)
  isEditing.value = false
}

onMounted(() => {
  document.addEventListener('click', handleClickOutsideActionDropDown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutsideActionDropDown)
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
  
  <div class="ar-main-container" :style="{ '--module-color': module.color }">
    <div class="ar-main-container_header">
      <div class="ar-main-container_header_details">
        <h1 class="ar-main-container_header_details_title">{{ record.name }}</h1>
      </div>
      <div class="ar-main-container_header_actions" ref="actionDropDownref">
        <div class="input-group">
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
          <div v-else class="btn-group">
            <button 
              type="button" 
              class="btn btn-success" 
              @click="saveRecord"
            >
              Save
            </button>
            <button 
              type="button" 
              class="btn btn-secondary" 
              @click="cancelEditing"
            >
              Cancel
            </button>
          </div>

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
                    v-model="editableRecord[f.key]"
                    :placeholder="editableRecord[f.key]"
                  />
                </template>
                <template v-else>
                  <input 
                    type="text" 
                    v-model="editableRecord[f.key]"
                  />
                </template>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>