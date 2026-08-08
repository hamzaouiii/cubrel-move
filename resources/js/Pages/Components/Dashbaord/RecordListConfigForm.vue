<script setup>
import { reactive, computed } from 'vue'
import FieldRenderer from '@/Pages/Components/Globals/FieldRenderer.vue'
import DashboardFilterBuilder from './DashboardFilterBuilder.vue'
import WidthPicker from './WidthPicker.vue'

const props = defineProps({
  modules:         { type: Array,  required: true },
  editingInstance: { type: Object, default: null },
  defaultCols:     { type: Number, default: 1 },
})

const emit = defineEmits(['submit', 'cancel'])

const cfg = props.editingInstance?.config ?? {}

const form = reactive({
  module:           cfg.module          ?? '',
  limit:            cfg.limit           ?? 10,
  label:            cfg.label           ?? '',
  filters:          cfg.filters         ?? [],
  filtersMatchType: cfg.filtersMatchType ?? 'all',
  showAllRecords:   cfg.showAllRecords   ?? false,
  cols:             props.editingInstance?.cols ?? props.defaultCols,
})

const canSubmit = computed(() => !!form.module)

const moduleOptions = computed(() =>
  props.modules.map(m => ({ value: m.slug, label: m.label }))
)

function submit() {
  if (!canSubmit.value) return
  emit('submit', {
    instanceId: props.editingInstance?.instanceId ?? crypto.randomUUID(),
    type:       'record-list',
    cols:       form.cols,
    config: {
      module:  form.module,
      limit:            form.limit || 10,
      label:            form.label.trim() || null,
      filters:          form.filters,
      filtersMatchType: form.filtersMatchType,
      showAllRecords:   form.showAllRecords,
    },
  })
}
</script>

<template>
  <div class="tsf">
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
      <div class="tsf__field">
        <label class="tsf__label">{{ $t('globals.dashboard.field_max_rows') }}</label>
        <FieldRenderer
          :field="{ type: 'integer', min: 1, max: 50 }"
          :model-value="form.limit"
          mode="dashboard"
          @update:modelValue="form.limit = $event"
        />
      </div>

      <div class="tsf__field">
        <label class="tsf__label">{{ $t('globals.dashboard.field_label') }} <span class="tsf__opt">({{ $t('globals.dashboard.optional') }})</span></label>
        <FieldRenderer
          :field="{ type: 'text' }"
          :model-value="form.label"
          mode="dashboard"
          @update:modelValue="form.label = $event"
        />
      </div>

      <div class="tsf__field">
        <label class="tsf__label">{{ $t('globals.dashboard.field_width') }}</label>
        <WidthPicker v-model="form.cols" />
      </div>
    </template>

    <DashboardFilterBuilder
      :module="form.module"
      :model-value="form.filters"
      :match-type="form.filtersMatchType"
      :show-all-records="form.showAllRecords"
      @update:model-value="form.filters = $event"
      @update:match-type="form.filtersMatchType = $event"
      @update:show-all-records="form.showAllRecords = $event"
    />

    <div class="tsf__actions">
      <button class="tsf__btn tsf__btn--secondary" type="button" @click="emit('cancel')">{{ $t('globals.confirm.cancel_text') }}</button>
      <button class="tsf__btn tsf__btn--primary" type="button" :disabled="!canSubmit" @click="submit">
        {{ editingInstance ? $t('globals.dashboard.save_changes') : $t('globals.dashboard.add_widget') }}
      </button>
    </div>
  </div>
</template>
