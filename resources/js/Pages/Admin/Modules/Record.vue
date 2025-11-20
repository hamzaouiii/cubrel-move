

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
  import { Head, usePage, Link } from '@inertiajs/vue3'
  import { computed, ref, onMounted, onBeforeUnmount} from 'vue'

defineOptions({
  layout: AdminLayout,
});
  const props = defineProps({
    module: Object,
    title: String,
    record: Object,
    recordLayout: Object
  })
console.log(props.recordLayout)
  
  const showActionDropDown = ref(false)
  const actionDropDownref = ref(null)

  const toggleActionDropDown = () => {
    showActionDropDown.value = !showActionDropDown.value
  }

  const handleClickOutsideActionDropDown = (event) => {
    if (actionDropDownref.value && !actionDropDownref.value.contains(event.target)) {
      showActionDropDown.value = false
    }
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
    <title>{{record.name}} - {{title}} - Automatisierung Regensburg</title>
  </Head>
  <div>
    
  </div>
    <div class="ar-main-container" :style="{ '--module-color': module.color}">
        <div class="ar-main-container_header">
            <div class="ar-main-container_header_details">
              <h1 class="ar-main-container_header_details_title">{{ record.name }}</h1> 
            </div>
            <div class="ar-main-container_header_actions" ref="actionDropDownref">
                <div class="input-group" >
                  <button type="button" class="btn btn-outline-secondary" :style="{ background: module.color, color: 'white' }">Edit</button>
                  <button  @click="toggleActionDropDown" type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" :style="{ background: module.color, color: 'white' }">
                    <span class="visually-hidden">Toggle Dropdown</span>
                  </button>
                <transition name="fade">
                  <ul  v-if="showActionDropDown" class="dropdown-menu dropdown-menu-end show">
                    <li ><a  class="dropdown-item disabled" href="#">Share</a></li>
                    <li><a class="dropdown-item disabled" href="#">Export</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Conver</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item " href="#" style="color: salmon">Delete</a></li>
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
                  <span class="field">
                      <template  v-if="f.format === 'datetime' && record[f.key]">
                        {{ formatDate(record[f.key]) }}
                      </template>
                      <template  v-else>
                        {{ record[f.key] }}
                      </template>

                  </span>

                </div>
              </div>
          </div>
        </div>
    </div>
</template>
