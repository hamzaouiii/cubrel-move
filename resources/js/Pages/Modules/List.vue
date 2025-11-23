<script setup>
  import { Head, usePage, Link, router } from '@inertiajs/vue3'
  import { computed, ref, onMounted, onBeforeUnmount} from 'vue'

  import Layout from '@/Layouts/Layout.vue';
  import Pagination from '../Components/Pagination.vue';

  defineOptions({
    layout: Layout,
  });

  const { props } = usePage();

const pageProps = defineProps({
  module: Object,
  title: String,
  items: Array,
  meta: Object,
  listLayout: Object,
  filters: Object
})

const recordsNumber = computed(() => pageProps.items?.length ?? 0)

const recordsNumberPhrase = computed(() => {
  if (!pageProps.meta) {
    return '(0)'
  }

  return `${recordsNumber.value} of ${pageProps.meta.total}`
})

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

  // --- SEARCH LOGIC ---

  const search = ref(pageProps.filters.search ?? '')

  const performSearch = (page = 1) => {
    router.get(
      window.location.pathname,
      {
        search: search.value || undefined,
        page,
      },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      }
    )
  }


  const handleSearchInput = () => {
    // Trigger search when at least 3 characters, or when cleared (0) to reset
    if (search.value.length >= 3 || search.value.length === 0) {
      performSearch(1)
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
  <div class="ar-main-container">
    <div class="ar-main-container_header">
      <div class="ar-main-container_header_details">
        <h1 class="ar-main-container_header_details_title">{{title}}</h1> 
        <span class="ar-main-container_header_details_meta">{{ recordsNumberPhrase  }}</span>
      </div>
      <div class="ar-main-container_header_actions" ref="actionDropDownref">
        <div class="input-group"  :style="{ '--background-color': module.color}">
          <input
            type="text"
            name="search"
            class="search-input"
            aria-label="Text input with segmented dropdown button"
            placeholder="Search in this list"
            v-model="search"
            @input="handleSearchInput"
            @keydown.enter.prevent="performSearch(1)"
          >
          <button
            type="button"
            class="main-btn"
           
          >
            Create
          </button>
          <button
            @click="toggleActionDropDown"
            type="button"
            class="dropdown-btn"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            
          >
            <i :class="showActionDropDown ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"></i>
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>
          <transition name="fade">
            <ul v-if="showActionDropDown" class="dropdown-menu dropdown-menu-end show">
              <li><a class="dropdown-item" href="#">Module Settings</a></li>
              <li><a class="dropdown-item disabled" href="#">Export</a></li>
              <li><a class="dropdown-item" href="#">Something else here</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#">Bulk Action</a></li>
              <li><a class="dropdown-item" href="#" style="color: salmon">Delete</a></li>
            </ul>
          </transition>
        </div>
      </div>
    </div>

    <div v-if="meta && meta.total != 0" class="ar-main-container_content">
      <div>
        <table class="ar-main-container_content_table" :style="{ '--module-color': module.color}">
          <thead>
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
            <Link
              v-for="item in items"
              :key="item.id"
              as="tr"
              class="clickable-row"
              :href="`/${module.slug}/${item.id}`"
            >
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
                <template v-else-if="col.format === 'datetime' && item[col.key]">
                  {{ formatDate(item[col.key]) }}
                </template>

                <!-- Truncate long strings -->
                <template v-else-if="item[col.key] && item[col.key].length > 62">
                  {{ item[col.key].substring(0, 64) + '...' }}
                </template>

                <!-- Default: plain text with '-' fallback -->
                <template v-else>
                  {{ item[col.key] ?? '-' }}
                </template>
              </td>
            </Link>
          </tbody>
        </table>
      </div>
      <Pagination :meta="meta" />
    </div>
  </div>
</template>
