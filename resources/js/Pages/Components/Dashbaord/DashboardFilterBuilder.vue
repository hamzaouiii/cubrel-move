<script setup>
import { ref, computed, watch, getCurrentInstance } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import FieldRenderer from '@/Pages/Components/Globals/FieldRenderer.vue'
import { EMPTY_OPERATORS, BETWEEN_OPERATORS, booleanOptions } from './dashboardUi.js'

const { proxy } = getCurrentInstance()
const t = proxy.$t

// Single source of truth: config/filter_operators.php and config/dashboard.php,
// shared via DashboardController::index() (same pattern as FilterZone.vue's
// `filterOperators` prop from ListController).
const page             = usePage()
const filterOperators  = computed(() => page.props.filterOperators ?? {})
const dashboardConfig  = computed(() => page.props.dashboardConfig ?? {})

// The implicit owner_id scope (and thus this toggle) never applies to admins
// or org-wide types — DashboardController checks isAdmin() first. Hiding it
// avoids implying a scope that isn't actually in effect for this user.
const user = computed(() => page.props.auth?.user ?? {})
const scopeAppliesToUser = computed(() =>
  !user.value.is_admin && !(dashboardConfig.value.org_wide_types ?? []).includes(user.value.type)
)


const props = defineProps({
  module:          { type: String,  default: null },
  modelValue:      { type: Array,   default: () => [] },
  matchType:       { type: String,  default: 'all' },
  showAllRecords:  { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'update:matchType', 'update:showAllRecords'])

const filterableFields = ref([])
const loading          = ref(false)

watch(() => props.module, async (slug) => {
  filterableFields.value = []
  if (!slug) return
  loading.value = true
  try {
    const { data } = await axios.get(`/dashboard/filterable-fields/${slug}`)
    filterableFields.value = data
  } finally {
    loading.value = false
  }
}, { immediate: true })

const fieldOptions = computed(() =>
  filterableFields.value.map(f => ({ value: f.name, label: f.label }))
)

function getFieldDef(name) {
  return filterableFields.value.find(f => f.name === name) ?? null
}

function operatorsFor(fieldName) {
  const field = getFieldDef(fieldName)
  const ops   = filterOperators.value.by_type?.[field?.type] ?? filterOperators.value.default ?? []
  return ops.map(op => ({ value: op, label: t(`modules.filters.operators.${op}`) }))
}

function valueFieldType(fieldDef) {
  if (!fieldDef) return 'text'
  if (dashboardConfig.value.boolean_field_types?.includes(fieldDef.type)) return 'select'
  if (fieldDef.type === 'record') return 'text'
  return fieldDef.type
}

function valueRelatedField(fieldDef) {
  if (!fieldDef) return null
  if (dashboardConfig.value.dropdown_field_types?.includes(fieldDef.type) && fieldDef.dropdown_list) {
    return { dropdown_list: fieldDef.dropdown_list }
  }
  if (dashboardConfig.value.boolean_field_types?.includes(fieldDef.type)) {
    return { dropdown_list: { values: booleanOptions(t) } }
  }
  return null
}

// ── Conditions CRUD ───────────────────────────────────────────────────────────

function addCondition() {
  emit('update:modelValue', [...props.modelValue, { field: null, operator: null, value: null }])
}

function removeCondition(i) {
  const next = [...props.modelValue]
  next.splice(i, 1)
  emit('update:modelValue', next)
}

function patchCondition(i, delta) {
  emit('update:modelValue', props.modelValue.map((c, idx) => idx === i ? { ...c, ...delta } : c))
}

function onOperatorChange(i, op) {
  patchCondition(i, { operator: op, value: BETWEEN_OPERATORS.includes(op) ? [null, null] : null })
}

function updateBetween(i, subIdx, val) {
  const cur = props.modelValue[i]
  const arr  = [...(Array.isArray(cur.value) ? cur.value : [null, null])]
  arr[subIdx] = val
  patchCondition(i, { value: arr })
}
</script>

<template>
  <div class="dfb" v-if="module">
    <div class="dfb__scope" v-if="scopeAppliesToUser">
      <label class="dfb__scope-toggle">
        <input
          type="checkbox"
          :checked="showAllRecords"
          @change="emit('update:showAllRecords', $event.target.checked)"
        />
        <span class="dfb__scope-slider"></span>
      </label>
      <div class="dfb__scope-text">
        <span class="dfb__scope-label">{{ $t('globals.dashboard.show_all_records') }}</span>
        <span class="dfb__scope-hint">{{ $t('globals.dashboard.show_all_records_hint') }}</span>
      </div>
    </div>

    <div class="dfb__header">
      <span class="dfb__title">{{ $t('globals.dashboard.filters') }}</span>
      <button class="dfb__add-btn" type="button" :disabled="loading" @click="addCondition">
        <i class="fa-solid fa-plus"></i>
        {{ $t('globals.dashboard.filter_add') }}
      </button>
    </div>

    <p v-if="!modelValue.length" class="dfb__empty">{{ $t('globals.dashboard.filter_empty') }}</p>

    <div v-else class="dfb__conditions">
      <div v-for="(cond, i) in modelValue" :key="i" class="dfb__row">

        <!-- Field picker -->
        <div class="dfb__cell dfb__cell--field">
          <FieldRenderer
            :field="{ type: 'select' }"
            :model-value="cond.field ?? null"
            :related_field="{ dropdown_list: { values: fieldOptions } }"
            mode="dashboard"
            @update:modelValue="patchCondition(i, { field: $event, operator: null, value: null })"
          />
        </div>

        <!-- Operator picker -->
        <div class="dfb__cell dfb__cell--op" v-if="cond.field">
          <FieldRenderer
            :field="{ type: 'select' }"
            :model-value="cond.operator ?? null"
            :related_field="{ dropdown_list: { values: operatorsFor(cond.field) } }"
            mode="dashboard"
            @update:modelValue="onOperatorChange(i, $event)"
          />
        </div>

        <!-- Value input(s) -->
        <template v-if="cond.field && cond.operator && !EMPTY_OPERATORS.includes(cond.operator)">
          <!-- Between: two inputs -->
          <div v-if="BETWEEN_OPERATORS.includes(cond.operator)" class="dfb__cell dfb__cell--val dfb__cell--between">
            <FieldRenderer
              :field="{ type: valueFieldType(getFieldDef(cond.field)) }"
              :related_field="valueRelatedField(getFieldDef(cond.field))"
              :model-value="Array.isArray(cond.value) ? cond.value[0] : null"
              mode="dashboard"
              @update:modelValue="updateBetween(i, 0, $event)"
            />
            <span class="dfb__sep">–</span>
            <FieldRenderer
              :field="{ type: valueFieldType(getFieldDef(cond.field)) }"
              :related_field="valueRelatedField(getFieldDef(cond.field))"
              :model-value="Array.isArray(cond.value) ? cond.value[1] : null"
              mode="dashboard"
              @update:modelValue="updateBetween(i, 1, $event)"
            />
          </div>

          <!-- Single value -->
          <div v-else class="dfb__cell dfb__cell--val">
            <FieldRenderer
              :field="{ type: valueFieldType(getFieldDef(cond.field)) }"
              :related_field="valueRelatedField(getFieldDef(cond.field))"
              :model-value="cond.value"
              mode="dashboard"
              @update:modelValue="patchCondition(i, { value: $event })"
            />
          </div>
        </template>

        <button class="dfb__remove" type="button" title="Remove" @click="removeCondition(i)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- Match type toggle (only when >1 conditions) -->
      <div class="dfb__match" v-if="modelValue.length > 1">
        <span class="dfb__match-label">{{ $t('globals.dashboard.filter_match') }}</span>
        <div class="dfb__match-btns">
          <button
            type="button"
            class="dfb__match-btn"
            :class="{ 'dfb__match-btn--active': matchType === 'all' }"
            @click="emit('update:matchType', 'all')"
          >{{ $t('globals.dashboard.filter_match_all') }}</button>
          <button
            type="button"
            class="dfb__match-btn"
            :class="{ 'dfb__match-btn--active': matchType === 'any' }"
            @click="emit('update:matchType', 'any')"
          >{{ $t('globals.dashboard.filter_match_any') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
