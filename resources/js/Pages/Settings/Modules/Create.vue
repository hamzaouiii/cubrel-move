<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import IconPicker from '@/Pages/Components/Settings/IconPicker.vue';
 

defineOptions({
  layout: Layout,
})

const defaultValues = {
  name: '',
  label: '',
  icon: '',
  color: '#0d6efd',
  show_in_sidebar: true,
  description: '',
  slug: ''
}

const form = useForm({ ...defaultValues })

const isDirty = computed(() => {
  if(form.name.length < 4) return false
  return true
})

const slug = computed(() => {
  return form.name
    .toLowerCase()
    .normalize("NFD")                
    .replace(/[\u0300-\u036f]/g, "") 
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "-")     
    .replace(/^-+|-+$/g, "")          
})
const resetModule = () => {
  Object.keys(defaultValues).forEach((key) => {
    form[key] = defaultValues[key]
  })

  form.clearErrors()
}

const saveModule = () => {
  form.transform(data => ({ ...data, slug: slug.value }))
  .post('/settings/customisation/modules/create')
}
</script>

<template>
  <Head>
    <title>{{ $t('settings.create_new_module')  }} - {{ $t('settings.label')  }} </title>
  </Head>

  <div class="module-manager create-module">
    <div class="settings_header">
      <div class="settings_header_title">
        <h5><Link href="/settings">{{ $t('settings.label')  }} </Link></h5>
        <span>></span>
        <h5><Link href="/settings/customisation/modules">{{ $t('settings.modules.label')  }} </Link></h5>
        <span>></span>
        <h6>{{ $t('settings.create_new_module')  }}</h6>
      </div>
    </div>

    <form @submit.prevent="saveModule">
      <div>
        <div class="create-element">
          <label>
            {{ $t('settings.modules.name')  }}
          </label>
          <input
            class=""
            type="text"
            name="name"
            v-model="form.name"
            :placeholder= "$t('settings.modules.name_placeholder')"
          />
        </div>
        <div class="create-element">
          <label>
            {{ $t('settings.modules.slug')  }}
          </label>
          <input
            class="slug"
            type="text"
            name="slug"
            :value="slug"
            disabled

          />
        </div>
        <div class="create-element">
          <label>
            {{ $t('settings.modules.icon')  }}
          </label>
          <IconPicker v-model="form.icon"   :color="form.color"/>
        </div>

        <div class="create-element">
          <label>
            {{ $t('settings.modules.color')  }}
          </label>
          <input
            class=""
            type="color"
            name="color"
            v-model="form.color"
          />
        </div>

        <div class="create-element">
          <label>
            {{ $t('settings.modules.show_in_sidebar')  }}
          </label>
          <input
            class=""
            type="checkbox"
            v-model="form.show_in_sidebar"
          />
        </div>

        <div class="create-element">
          <label>
            {{ $t('settings.modules.description')  }}
          </label>
          <textarea
            class=""
            v-model="form.description"
          ></textarea>
        </div>
      </div>

      <div class="create-actions">
        <button
          class="reset-btn"
          type="button"
          @click="resetModule"
          v-if="isDirty"
        >
          Cancel
        </button>

        <button
          type="submit"
          :disabled="!isDirty"
        >
          Save
        </button>
      </div>
    </form>
  </div>
</template>
