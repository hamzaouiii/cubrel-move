<script setup>
import { ref, reactive, computed, watch, getCurrentInstance } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import FieldRenderer from '@/Pages/Components/Globals/FieldRenderer.vue'
import DashboardFilterBuilder from './DashboardFilterBuilder.vue'
import WidthPicker from './WidthPicker.vue'
import { buildOptions } from './dashboardUi.js'
import ColorPicker from '@/Pages/Components/FiledTypes/ColorPicker.vue'

const { proxy } = getCurrentInstance()
const t = proxy.$t
const dashboardConfig = usePage().props.dashboardConfig

const props = defineProps({
  modules:         { type: Array,  required: true },
  editingInstance: { type: Object, default: null },
  defaultCols:     { type: Number, default: 4 },
})

const emit = defineEmits(['submit', 'cancel'])

const cfg = props.editingInstance?.config ?? {}

const form = reactive({
  module:           cfg.module    ?? '',
  dateField:        cfg.dateField ?? '',
  metric:           { type: cfg.metric?.type ?? 'count', field: cfg.metric?.field ?? '' },
  interval:         cfg.interval  ?? 'month',
  chartType:        cfg.chartType ?? 'bar',
  dateRange:        cfg.dateRange ?? 'last_6_months',
  color:            cfg.color     ?? null,
  label:            cfg.label     ?? '',
  filters:          cfg.filters         ?? [],
  filtersMatchType: cfg.filtersMatchType ?? 'all',
  showAllRecords:   cfg.showAllRecords   ?? false,
  cols:             props.editingInstance?.cols ?? props.defaultCols,
})

const fields        = ref([])
const loadingFields = ref(false)

const dateFields    = computed(() => fields.value.filter(f => dashboardConfig.date_field_types.includes(f.type)))
const numericFields = computed(() => fields.value.filter(f => dashboardConfig.numeric_field_types.includes(f.type)))
const needsField    = computed(() => ['sum', 'avg'].includes(form.metric.type))

const canSubmit = computed(() => {
  if (!form.module || !form.dateField) return false
  if (needsField.value && !form.metric.field) return false
  return true
})

const moduleOptions    = computed(() => props.modules.map(m => ({ value: m.slug, label: m.name })))
const dateFieldOptions = computed(() => dateFields.value.map(f => ({ value: f.name, label: f.label })))
const numericOptions   = computed(() => numericFields.value.map(f => ({ value: f.name, label: f.label })))

const metricTypeOptions = buildOptions(t, dashboardConfig.allowed_metrics)
const intervalOpts      = buildOptions(t, dashboardConfig.allowed_intervals)
const chartTypeOptions  = buildOptions(t, dashboardConfig.allowed_chart_types)
const dateRangeOpts     = buildOptions(t, dashboardConfig.allowed_date_ranges)

watch(() => form.module, async (slug) => {
  fields.value = []
  if (!slug) { form.dateField = ''; form.metric.field = ''; return }
  loadingFields.value = true
  try {
    const { data } = await axios.get(`/dashboard/module-fields/${slug}`)
    fields.value = data
    if (!form.dateField    && cfg.dateField)        form.dateField    = cfg.dateField
    if (!form.metric.field && cfg.metric?.field)    form.metric.field = cfg.metric.field
  } finally {
    loadingFields.value = false
  }
}, { immediate: true })

watch(() => form.metric.type, () => { form.metric.field = '' })

function submit() {
  if (!canSubmit.value) return
  emit('submit', {
    instanceId: props.editingInstance?.instanceId ?? crypto.randomUUID(),
    type:       'time-series',
    cols:       form.cols,
    config: {
      module:    form.module,
      dateField: form.dateField,
      metric:    { ...form.metric },
      interval:  form.interval,
      chartType: form.chartType,
      dateRange: form.dateRange,
      color:     form.color,
      label:     form.label.trim() || null,
      filters:          form.filters,
      filtersMatchType: form.filtersMatchType,
      showAllRecords:   form.showAllRecords,
    },
  })
}
</script>

<template>
  <div class="tsf">
    <!-- Module -->
    <div class="tsf__field">
      <label class="tsf__label">{{ $t('globals.dashboard.field_module') }}</label>
      <FieldRenderer
        :field="{ type: 'select' }"
        :model-value="form.module || null"
        :related_field="{ dropdown_list: { values: moduleOptions } }"
        mode="dashboard"
        @update:modelValue="form.module = $event ?? ''"
      />
    </div>

    <template v-if="form.module">
      <!-- Date field -->
      <div class="tsf__field">
        <label class="tsf__label">{{ $t('globals.dashboard.field_date_field') }}</label>
        <div v-if="loadingFields" class="tsf__hint">{{ $t('globals.dashboard.loading_fields') }}</div>
        <template v-else>
          <FieldRenderer
            :field="{ type: 'select' }"
            :model-value="form.dateField || null"
            :related_field="{ dropdown_list: { values: dateFieldOptions } }"
            mode="dashboard"
            @update:modelValue="form.dateField = $event ?? ''"
          />
          <p v-if="!dateFields.length" class="tsf__hint tsf__hint--warn">
            No date fields found on this module.
          </p>
        </template>
      </div>

      <!-- Metric -->
      <div class="tsf__field">
        <label class="tsf__label">{{ $t('globals.dashboard.field_metric') }}</label>
        <div class="tsf__row">
          <div class="tsf__col--narrow">
            <FieldRenderer
              :field="{ type: 'select', nullable: true }"
              :model-value="form.metric.type"
              :related_field="{ dropdown_list: { values: metricTypeOptions } }"
              mode="dashboard"
              @update:modelValue="form.metric.type = $event"
            />
          </div>
          <div v-if="needsField" class="tsf__col--fill">
            <FieldRenderer
              :field="{ type: 'select' }"
              :model-value="form.metric.field || null"
              :related_field="{ dropdown_list: { values: numericOptions } }"
              mode="dashboard"
              @update:modelValue="form.metric.field = $event ?? ''"
            />
          </div>
        </div>
      </div>

      <!-- Interval + Chart type -->
      <div class="tsf__row tsf__row--2col">
        <div class="tsf__field">
          <label class="tsf__label">{{ $t('globals.dashboard.field_group_by') }}</label>
          <FieldRenderer
            :field="{ type: 'select', nullable: true }"
            :model-value="form.interval"
            :related_field="{ dropdown_list: { values: intervalOpts } }"
            mode="dashboard"
            @update:modelValue="form.interval = $event"
          />
        </div>
        <div class="tsf__field">
          <label class="tsf__label">{{ $t('globals.dashboard.field_chart_type') }}</label>
          <FieldRenderer
            :field="{ type: 'select', nullable: true }"
            :model-value="form.chartType"
            :related_field="{ dropdown_list: { values: chartTypeOptions } }"
            mode="dashboard"
            @update:modelValue="form.chartType = $event"
          />
        </div>
      </div>

      <!-- Date range -->
      <div class="tsf__field">
        <label class="tsf__label">{{ $t('globals.dashboard.field_date_range') }}</label>
        <FieldRenderer
          :field="{ type: 'select', nullable: true }"
          :model-value="form.dateRange"
          :related_field="{ dropdown_list: { values: dateRangeOpts } }"
          mode="dashboard"
          @update:modelValue="form.dateRange = $event"
        />
      </div>

      <!-- Chart color -->
      <div class="tsf__field">
        <label class="tsf__label">{{ $t('globals.dashboard.field_color') }}</label>
        <ColorPicker :model-value="form.color" @update:modelValue="form.color = $event ?? null" />
      </div>

      <!-- Label -->
      <div class="tsf__field">
        <label class="tsf__label">{{ $t('globals.dashboard.field_label') }} <span class="tsf__opt">({{ $t('globals.dashboard.optional') }})</span></label>
        <FieldRenderer
          :field="{ type: 'text' }"
          :model-value="form.label"
          mode="dashboard"
          @update:modelValue="form.label = $event"
        />
      </div>

      <!-- Width -->
      <div class="tsf__field">
        <label class="tsf__label">{{ $t('globals.dashboard.field_width') }}</label>
        <WidthPicker v-model="form.cols" />
      </div>
    </template>

    <!-- Filters -->
    <DashboardFilterBuilder
      :module="form.module"
      :model-value="form.filters"
      :match-type="form.filtersMatchType"
      :show-all-records="form.showAllRecords"
      @update:model-value="form.filters = $event"
      @update:match-type="form.filtersMatchType = $event"
      @update:show-all-records="form.showAllRecords = $event"
    />

    <!-- Actions -->
    <div class="tsf__actions">
      <button class="tsf__btn tsf__btn--secondary" type="button" @click="emit('cancel')">{{ $t('globals.confirm.cancel_text') }}</button>
      <button
        class="tsf__btn tsf__btn--primary"
        type="button"
        :disabled="!canSubmit"
        @click="submit"
      >
        {{ editingInstance ? $t('globals.dashboard.save_changes') : $t('globals.dashboard.add_widget') }}
      </button>
    </div>
  </div>
</template>
