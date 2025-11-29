<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, Link, useForm } from '@inertiajs/vue3'

defineOptions({
  layout: Layout,
});

const props = defineProps({
  item: Object,
})

const page = usePage()
const module = computed(() => page.props.item || page.props)

// normalize initial values: convert 1/0 (string or int) → true/false for bools
const normalizedValues = props.item.values.map(v => ({
  ...v,
  value:
    v.type === 'bool'
      ? v.value == 1 || v.value === '1'
      : v.value
}))

const form = useForm({
  values: normalizedValues,
})

const inputTypeFor = (type) => {
  if (type === 'string') return 'text'
  if (type === 'bool') return 'checkbox'
  if (type === 'color') return 'color'
  if (type === 'json') return 'multiselect'
  if (type === 'int') return 'number'
  return 'text'
}
const saveSetting = () => {
  form.put(`/settings/${props.item.id}`, {
    preserveScroll: true,
  })
}

const resetForm = () => {
  form.reset()
}

// explicit isDirty function for template usage
const isDirty = () => form.isDirty
</script>

<template>
  <Head>
    <title>{{ item.name }} - Settings - Automatisierung Regensburg</title>
  </Head>

  <div class="settings">
    <div class="settings_header">
      <div class="settings_header_title">
        <h5><Link href="/settings">Settings</Link></h5>
        <span>></span>
        <h6>{{ item.name }}</h6>
      </div>
      <div class="settings_header_action">
      </div>
    </div>

    <div class="settings_system">
      <form @submit.prevent="saveSetting" class="settings_system_form">
        <div
          v-for="(i, index) in form.values"
          :key="i.id || i.key || index"
          class="settings_system_form_field"
        >
          <label>{{ i.label || i.key }}</label>

          <!-- bool → checkbox -->
          <template v-if="i.type === 'bool'">
            <input
              type="checkbox"
              v-model="form.values[index].value"
            />
          </template>

          <!-- other types -->
          <template v-else>
            <input
              :type="inputTypeFor(i.type)"
              v-model="form.values[index].value"
            />
          </template>
        </div>

        <div class="settings_system_form_actions">
          <button
            type="button"
            class="reset-btn"
            @click="resetForm"
            :disabled="!isDirty()"
          >
            Reset
          </button>

          <button
            type="submit"
            :disabled="!isDirty() || form.processing"
          >
            Save
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
