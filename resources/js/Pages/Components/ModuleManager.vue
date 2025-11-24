<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import {  usePage, Link } from '@inertiajs/vue3'

defineOptions({
  layout: Layout,
});

const props = defineProps({
  modules: Object
})


const page = usePage()
const module = computed(() => page.props.receivedItem || page.props)
console.log(props.modules)

</script>

<template >
    <ul class="settings_items_modules">
      <li v-for="m in modules" :style="{'--module-color': m.color}">
        <i :class="['fa-solid', m.icon]"></i>
        {{ m.name }}

      </li>
    </ul>
</template>