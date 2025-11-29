
<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3'
import ModuleManager from '@/Pages/Components/Settings/ModuleManager.vue';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  item: Object,
})

const page = usePage()
const module = computed(() => page.props.item || page.props)

</script>
<template>
    <Head>
    <title>{{item.name }} - Settings - Automatisierung Regensburg</title>
  </Head>
  <div class="settings">
      <div class="settings_header">
      <div class="settings_header_title">
        <h5><Link href="/settings">Settings</Link></h5>
        <span>></span> 
        <h6>{{item.name }}</h6>
    </div>
      <div class="settings_header_action">
      </div>
    </div>
    <div class="settings_system">
      <form action="#" class="settings_system_form">
        <div v-for="i in item.values" class="settings_system_form_field">
          <label>{{ i.key }}</label>
          <input type="text" v-model="i.value"/>
        </div>
      </form>
    </div>
  </div>
</template>