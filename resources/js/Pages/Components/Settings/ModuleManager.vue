<script setup>
import { computed, inject } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import {  usePage, Link } from '@inertiajs/vue3'

defineOptions({
  layout: Layout,
});

const props = defineProps({
  modules: Object
})

const page = usePage()
const currentPath = page.url;
const module = computed(() => page.props.receivedItem || page.props)

const useModuleColors = inject('useModuleColors', () => false)
</script>

<template >
    <ul class="settings_items_modules">
      <Link v-for="m in modules"  :style="useModuleColors ? { '--module-color': m.color } : {'--module-color': '#000'}" :href="currentPath+'/'+m.id" >
        <i :class="['fa-solid', m.icon]"></i>
        {{ m.name }}
      </Link>
    </ul>
</template>