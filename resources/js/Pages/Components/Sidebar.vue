<template>
  
    <aside :class="{'sidebar': !collapsedSidebar, 'collapsed': collapsedSidebar} ">

      <div @click="toggleSidebar" class="collapser">
        <div v-if="collapsedSidebar" class="collapser_icon">
          <i class="fa-solid fa-bars" ></i>
        </div>
        <div v-else class="collapser_icon">
          <i class="fa-solid fa-down-left-and-up-right-to-center"></i>
        </div>
      </div>
      <div class="module_list">
        <Link class="link-item" v-for="mod in modules" :key="mod.slug" :href="mod.path" :style="{ '--module-color': mod.color }">
          <div  v-if="!collapsedSidebar" class="module-color" >
            <i v-if="mod.icon" :class="['fa-solid', mod.icon]" ></i>
          </div>
          <div  :class="['link-label',{ active: currentUrl.startsWith(mod.path)  }]">
            <i v-if="mod.icon" :class="['fa-solid', mod.icon]"></i>
            <span v-if="!collapsedSidebar" >{{ mod.label }}</span>
          </div>
        </Link>
      </div>
      <div class="sidebar-footer">

      </div>
    </aside>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'

const form = useForm({})
const logout = () => {
  form.post('/logout')  
}
const page = usePage()
const modules = computed(() => page.props.modules ?? [])
const currentUrl = computed(() => page.url)


const collapsedSidebar = ref(false);
const toggleSidebar = () => {
  collapsedSidebar.value = !collapsedSidebar.value
}


</script>