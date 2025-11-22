<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, computed } from 'vue'

defineOptions({
  layout: AdminLayout,
})

const props = defineProps({
  modules: {
    type: Array,
    required: true,
  },
})

const search = ref('')

const filteredModules = computed(() => {
  if (!search.value) {
    return props.modules
  }

  const term = search.value.toLowerCase()

  return props.modules.filter((m) => {
    return (
      (m.name && m.name.toLowerCase().includes(term)) ||
      (m.slug && m.slug.toLowerCase().includes(term)) ||
      (m.table_name && m.table_name.toLowerCase().includes(term))
    )
  })
})

const moduleTypeBadgeClass = (mod) => {
  return mod.is_custom ? 'bg-info' : 'bg-dark'
}

const moduleTypeLabel = (mod) => {
  return mod.is_custom ? 'Custom Module' : 'System Module'
}
</script>

<template>
  <Head title="Module Manager - Automatisierung Regensburg" />

  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h4 mb-1">Module Manager</h1>
        <p class="text-muted mb-0">
          Overview of all modules, layouts, and fields.
        </p>
      </div>

      <!-- Placeholder for future "Create Module" button -->
      <!--
      <button class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>
        New Module
      </button>
      -->
    </div>

    <!-- Search + stats -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-center">
          <div class="col-md-6">
            <label class="form-label mb-1">Search</label>
            <input
              v-model="search"
              type="text"
              class="form-control"
              placeholder="Search by name, slug or table name..."
            />
          </div>
          <div class="col-md-6 text-md-end">
            <div class="small text-muted mb-1">Stats</div>
            <div class="fw-semibold">
              Showing {{ filteredModules.length }} of {{ modules.length }} modules
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modules accordion -->
    <div class="accordion" id="modulesAccordion">
      <div
        v-for="(mod, index) in filteredModules"
        :key="mod.id"
        class="accordion-item mb-2 border"
      >
        <h2 class="accordion-header" :id="`heading-${mod.id}`">
          <button
            class="accordion-button d-flex justify-content-between align-items-center"
            :class="{ collapsed: index !== 0 }"
            type="button"
            data-bs-toggle="collapse"
            :data-bs-target="`#collapse-${mod.id}`"
            :aria-expanded="index === 0 ? 'true' : 'false'"
            :aria-controls="`collapse-${mod.id}`"
          >
            <div>
              <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold">
                  {{ mod.name }} <small class="text-muted">({{ mod.slug }})</small>
                </span>
                <span class="badge" :class="moduleTypeBadgeClass(mod)">
                  {{ moduleTypeLabel(mod) }}
                </span>
                <span
                  class="badge"
                  :class="mod.is_active ? 'bg-success' : 'bg-secondary'"
                >
                  {{ mod.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <div class="small text-muted mt-1">
                Table:
                <code>{{ mod.table_name || '-' }}</code>
                &middot;
                Handler:
                <code>{{ mod.handler_class || '-' }}</code>
              </div>
            </div>

            <div class="text-end ms-3 small text-muted d-none d-md-block">
              <div>
                Layouts:
                <span class="fw-semibold">
                  {{ (mod.layouts && mod.layouts.length) || 0 }}
                </span>
              </div>
              <div>
                Fields:
                <span class="fw-semibold">
                  {{ (mod.fields && mod.fields.length) || 0 }}
                </span>
              </div>
            </div>
          </button>
        </h2>

        <div
          :id="`collapse-${mod.id}`"
          class="accordion-collapse collapse"
          :class="{ show: index === 0 }"
          :aria-labelledby="`heading-${mod.id}`"
          data-bs-parent="#modulesAccordion"
        >
          <div class="accordion-body bg-light">
            <div class="row g-3">
              <!-- Layouts -->
              <div class="col-12 col-lg-6">
                <div class="card h-100">
                  <div class="card-header py-2">
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="fw-semibold">Layouts</span>
                      <span class="badge bg-light text-dark">
                        {{ (mod.layouts && mod.layouts.length) || 0 }} entries
                      </span>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div v-if="mod.layouts && mod.layouts.length" class="table-responsive">
                      <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                          <tr>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Default</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="layout in mod.layouts" :key="layout.id">
                            <td>
                              <span class="badge bg-light text-dark">
                                {{ layout.type }}
                              </span>
                            </td>
                            <td>{{ layout.name || '-' }}</td>
                            <td>
                              <span
                                v-if="layout.is_list_default"
                                class="badge bg-primary me-1"
                              >
                                Default List
                              </span>
                              <span
                                v-if="layout.is_record_default"
                                class="badge bg-secondary"
                              >
                                Default Record
                              </span>
                              <span
                                v-if="!layout.is_list_default && !layout.is_record_default"
                                class="text-muted small"
                              >
                                –
                              </span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div v-else class="p-3 text-muted small">
                      No layouts have been defined for this module.
                    </div>
                  </div>
                </div>
              </div>

              <!-- Fields -->
              <div class="col-12 col-lg-6">
                <div class="card h-100">
                  <div class="card-header py-2">
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="fw-semibold">Fields</span>
                      <span class="badge bg-light text-dark">
                        {{ (mod.fields && mod.fields.length) || 0 }} entries
                      </span>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div v-if="mod.fields && mod.fields.length" class="table-responsive">
                      <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                          <tr>
                            <th>Field</th>
                            <th>Label</th>
                            <th>Type</th>
                            <th>Required</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="field in mod.fields" :key="field.id">
                            <td><code>{{ field.name }}</code></td>
                            <td>{{ field.label || '-' }}</td>
                            <td>
                              <span class="badge bg-light text-dark">
                                {{ field.type || 'string' }}
                              </span>
                            </td>
                            <td>
                              <span
                                v-if="field.required"
                                class="badge bg-danger"
                              >
                                Required
                              </span>
                              <span
                                v-else
                                class="badge bg-secondary"
                              >
                                Optional
                              </span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div v-else class="p-3 text-muted small">
                      No field definitions exist for this module yet.
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Optional footer for actions -->
          </div>
        </div>
      </div>

      <div v-if="!filteredModules.length" class="text-center text-muted py-5">
        No modules found.
      </div>
    </div>
  </div>
</template>
