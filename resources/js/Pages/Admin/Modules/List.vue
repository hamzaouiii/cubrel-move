

<script setup>
  import { Head, usePage } from '@inertiajs/vue3'
  import { computed, ref, onMounted, onBeforeUnmount} from 'vue'

  import AdminLayout from '@/Layouts/AdminLayout.vue';
  import Pagination from '../Components/Pagination.vue';

  defineOptions({
    layout: AdminLayout,
  });

  const { props } = usePage();
  defineProps({
    module: Object,
    title: String,
    items: Array,
    meta: Object,
    listLayout: Object
  })

  let records_number_phrase;
  const recordsNumber = computed(() => props.items.length);
  if (props.meta) {
    records_number_phrase = recordsNumber.value+" of "+props.meta.total
  }
  else {
    records_number_phrase = "(0)"

  }
  const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('de-DE', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    });
  };

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
    <title>{{title}} - Automatisierung Regensburg</title>
  </Head>
  <div class="list">
    <div class="list_header">
      <div class="list_header_details">
        <h1 class="list_header_details_title">{{title}}</h1> 
        <span class="list_header_details_meta" >{{ records_number_phrase}}</span>
      </div>
      <div class="list_header_actions" ref="actionDropDownref">
        <div class="input-group" >
          <input type="text" class="form-control" aria-label="Text input with segmented dropdown button" placeholder="Search in this list">
          <button type="button" class="btn btn-outline-secondary" :style="{ background: module.color, color: 'white' }">Create</button>
          <button  @click="toggleActionDropDown" type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" :style="{ background: module.color, color: 'white' }">
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>
        <transition name="fade">
          <ul  v-if="showActionDropDown" class="dropdown-menu dropdown-menu-end show">
            <li ><a  class="dropdown-item disabled" href="#">Module Settings</a></li>
            <li><a class="dropdown-item disabled" href="#">Export</a></li>
            <li><a class="dropdown-item" href="#">Something else here</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Bulk Action</a></li>
            <li><a class="dropdown-item " href="#" style="color: salmon">Delete</a></li>
          </ul>
        </transition>

        </div>
      </div>

    </div>
    <div v-if="meta && meta.total  !=0"  class="list_content">
      <div class="">
        <table class="list_content_table" :style="{ '--module-color': module.color}">
          <thead  >
            <tr>
              <th
                v-for="col in listLayout?.columns || []"
                :key="col.key"
                scope="col"
              >
                {{ col.label }}
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td
                v-for="col in listLayout?.columns || []"
                :key="col.key"
              >
                <!-- Email as mailto-link -->
                <template v-if="col.key === 'email' && item[col.key]">
                  <a :href="'mailto:' + item[col.key]">
                    {{ item[col.key] }}
                  </a>
                </template>

                <!-- Datetime formatting based on layout definition -->
                <template v-else-if="col.length === 'datetime' && item[col.key]">
                  {{ formatDate(item[col.key]) }}
                </template>

                <!-- Truncate long strings -->
                  <template v-else-if="item[col.key] && item[col.key].length > 62">
                    {{ item[col.key].substring(0, 64) + "..." }}
                  </template>

                <!-- Default: plain text with '-' fallback -->
                <template v-else>
                  {{ item[col.key] ?? '-' }}
                </template>
              </td>
            </tr>

            <tr v-if="!items.length">
              <td
                :colspan="(listLayout?.columns?.length || 0)"
                class="text-center"
              >
                Keine Einträge gefunden.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :meta="meta" />
    </div>
  </div>
</template>
