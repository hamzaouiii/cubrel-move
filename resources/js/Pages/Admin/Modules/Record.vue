

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
    record: Object
  })
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
</script>

<template>
    <Head>
    <title>{{record.first_name}} {{ record.last_name }} - {{title}} - Automatisierung Regensburg</title>
  </Head>
  <div>
    
  </div>
    <div class="ar-main-container">
        <div class="ar-main-container_header">
            <div class="ar-main-container_header_details">
              <h1 class="ar-main-container_header_details_title">{{record.first_name}} {{ record.last_name }}</h1> 
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
    </div>
</template>
