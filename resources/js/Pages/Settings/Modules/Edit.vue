<script setup>
import { reactive, computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, Link, useForm } from '@inertiajs/vue3'

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object
})
const form = useForm({ ...props.module })
const editableModule = reactive({ ...props.module })
editableModule.show_in_sidebar = Boolean(editableModule.show_in_sidebar)
const editableFields = computed(() => {
  const ignore = ['id', 'created_at', 'updated_at','can_view','can_create','can_edit', 'can_delete', 'path', 'sort_order','is_active','table_name', 'model_class', 'slug', 'handler_class']  
  return Object.entries(editableModule).filter(([key]) => !ignore.includes(key))
})

const labelFor = (key) => {
  return key
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase())
}

const inputTypeFor = (key, value) => {
  if (key === 'show_in_sidebar') return 'checkbox'
  if (typeof value === 'number') return 'number'
  if (key === 'color') return 'color'
  if ( key === 'description') return 'textarea'
  return 'text'

}

const isDirty = computed(() => {
  return editableFields.value.some(([key, value]) => {
    const original = props.module[key]
    const current = editableModule[key]

    if (typeof original === 'number' && typeof current === 'boolean') {
      return Boolean(original) !== current
    }

    return original !== current
  })
})
const saveRecord = () =>{
  const url = "/settings/customisation/modules/"+props.module.id
  const payload = editableModule
  form.transform(() => payload)
  .put(url, {
     onSuccess: () => {null},
     onError: () => {console.error('Error saving record:', form.errors)}
  })
}

const resetForm= () =>{
   Object.keys(editableModule).forEach(key => {
    editableModule[key] = props.module[key]
  })
}
</script>

<template >
    <Head>
    <title>{{ module.name }} - Automatisierung Regensburg</title>
  </Head>

  <div class="edit-module">
      <div class="settings_header_title">
        <h5><Link href="/settings">Settings</Link></h5>
        <span>></span> 
        <h5><Link href="/settings/customisation/modules">Modules</Link></h5>
        <span>></span> 
        <h6>{{module.name }}</h6>
    </div>
    <form @submit.prevent="saveRecord">

      <div
        v-for="[key, value] in editableFields"
        :key="key"
        class="edit-element"
      >
        <label class="">
          {{ labelFor(key) }}
        </label>

          <input
            v-if="inputTypeFor(key, value) === 'checkbox'" 
            class=""
            type="checkbox"
            v-model="editableModule[key]"
          />
          <textarea
           v-else-if="inputTypeFor(key, value) === 'textarea'"
            class=""
            v-model="editableModule[key]"
          ></textarea>

          <input
            v-else
            class=""
            :type="inputTypeFor(key, value)"
            v-model="editableModule[key]"
          />
      </div>
      <div class="actions" :style="{'--module-color': editableModule.color}">
        <button @click="resetForm()" class="reset-btn" type="reset" :disabled="!isDirty" >Reset</button>

        <button   type="submit" :disabled="!isDirty">Save</button>
      </div>
    </form>
  </div>


</template>
