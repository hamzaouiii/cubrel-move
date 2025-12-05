<script setup>
import { ref, watch, computed, getCurrentInstance } from 'vue'

const props = defineProps({
  columns: {
    type: Array,
    default: () => [],
  },
  availableFields: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:columns'])

const internalColumns = ref([...props.columns])
const internalAvailable = ref([...props.availableFields])

watch(
  () => props.columns,
  (val) => {
    internalColumns.value = [...val]
  },
  { deep: true }
)

watch(
  () => props.availableFields,
  (val) => {
    internalAvailable.value = [...val]
  },
  { deep: true }
)

// --- translation helper for script side ---
const { proxy } = getCurrentInstance()
const t = proxy.$t

// { list: 'columns' | 'available', index: number } | null
const dragging = ref(null)
// same shape, for current drop zone highlight
const originOffset = ref({ x: 0, y: 0 })
const dragOver = ref(null)
const ghostWidth = ref(null)
const ghostHeight = ref(null)
// position of our custom ghost
const dragPosition = ref({ x: 0, y: 0 })
// small trailing dots
const dragTrails = ref([])
let trailCounter = 0

// tiny transparent image to hide native ghost
const transparentPixel =
  'data:image/gif;base64,R0lGODlhAQABAAAAACw='
const dragImage = new Image()
dragImage.src = transparentPixel

const ghostLabel = computed(() => {
  if (!dragging.value) return ''
  const { list, index } = dragging.value
  if (list === 'available') {
    const item = internalAvailable.value[index]
    return item ? t(item.label) ?? item.key : ''
  } else {
    const item = internalColumns.value[index]
    return item ? t(item.label) ?? item.key : ''
  }
})

const startDrag = (listName, index, event) => {
  dragging.value = { list: listName, index }
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
    // Firefox needs some data
    event.dataTransfer.setData('text/plain', `${listName}:${index}`)
    // hide the default ghost; we render our own
    try {
      event.dataTransfer.setDragImage(dragImage, 0, 0)
    } catch (e) {
      // ignore if browser complains
    }
  }
  const el = event.target.closest('.lle-item')
  if (el) {
    ghostWidth.value = el.offsetWidth + 'px'
    ghostHeight.value = el.offsetHeight + 'px'
    const rect = el.getBoundingClientRect()
    originOffset.value = {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top
    }
  } else {
    ghostWidth.value = null
    ghostHeight.value = null
  }

}

const endDrag = () => {
  dragging.value = null
  dragOver.value = null
  dragTrails.value = []
}

const setDragOver = (listName, index, event) => {
  event.preventDefault()
  dragOver.value = { list: listName, index }
}

const clearDragOver = () => {
  dragOver.value = null
}

const isItemDragging = (listName, index) => {
  return (
    dragging.value &&
    dragging.value.list === listName &&
    dragging.value.index === index
  )
}

const isDropZoneActive = (listName, index) => {
  return (
    dragOver.value &&
    dragOver.value.list === listName &&
    dragOver.value.index === index
  )
}

const isListDragTarget = (listName) => {
  return dragOver.value && dragOver.value.list === listName
}

const moveWithinList = (listRef, fromIndex, toIndex) => {
  if (fromIndex === toIndex) return
  const list = [...listRef.value]
  const [item] = list.splice(fromIndex, 1)
  list.splice(toIndex, 0, item)
  listRef.value = list
}

const moveBetweenLists = (fromRef, toRef, fromIndex, toIndex) => {
  const from = [...fromRef.value]
  const to = [...toRef.value]
  const [item] = from.splice(fromIndex, 1)
  to.splice(toIndex, 0, item)
  fromRef.value = from
  toRef.value = to
}

const onDropOnColumns = (targetIndex, event) => {
  event.preventDefault()
  if (!dragging.value) return

  const { list, index } = dragging.value

  if (list === 'columns') {
    moveWithinList(internalColumns, index, targetIndex)
  } else if (list === 'available') {
    moveBetweenLists(internalAvailable, internalColumns, index, targetIndex)
  }

  emitUpdatedColumns()
  endDrag()
}

const onDropOnAvailable = (targetIndex, event) => {
  event.preventDefault()
  if (!dragging.value) return

  const { list, index } = dragging.value

  if (list === 'available') {
    moveWithinList(internalAvailable, index, targetIndex)
  } else if (list === 'columns') {
    moveBetweenLists(internalColumns, internalAvailable, index, targetIndex)
    emitUpdatedColumns()
  }

  endDrag()
}

const allowDrop = (event) => {
  event.preventDefault()
}

const emitUpdatedColumns = () => {
  const clean = internalColumns.value.map((col) => {
    const { field, ...rest } = col
    return rest
  })
  emit('update:columns', clean)
}

// ---- custom ghost + trail ----
const onGlobalDragOver = (event) => {
  if (!dragging.value) return
  const x = event.clientX + 8
  const y = event.clientY + 8
  dragPosition.value = { x, y }
  createTrail(event.clientX, event.clientY)
}

const createTrail = (x, y) => {
  const id = trailCounter++
  dragTrails.value.push({ id, x, y })
  setTimeout(() => {
    dragTrails.value = dragTrails.value.filter((d) => d.id !== id)
  }, 400)
}
</script>

<template>
  <div
    class="layout-list-editor"
    @dragover="onGlobalDragOver"
  >
    <div class="lle-panels">
      <!-- AVAILABLE FIELDS -->
      <div class="lle-panel lle-panel--available">
        <div class="lle-header">
          <h5>{{ $t('layouts.available_fields') }}</h5>
          <small>{{ $t('layouts.available_fields_hint') }}</small>
        </div>

        <ul
          class="lle-list"
          :class="{ 'lle-list--drag-target': isListDragTarget('available') }"
        >
          <!-- Top drop zone (before first item) -->
          <li
            class="lle-drop-zone"
            :class="{ 'lle-drop-zone--active': isDropZoneActive('available', 0) }"
            @dragover="setDragOver('available', 0, $event)"
            @drop="onDropOnAvailable(0, $event)"
          />

          <!-- Item + drop zone after it -->
          <template v-for="(field, index) in internalAvailable" :key="field.key">
            <li
              class="lle-item"
              :class="{ 'lle-item--dragging': isItemDragging('available', index) }"
              draggable="true"
              @dragstart="startDrag('available', index, $event)"
              @dragend="endDrag"
            >
              <span class="lle-item-handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>
              <span class="lle-item-label">
                {{ $t(field.label) ?? field.key }}
              </span>
            </li>

            <!-- Drop zone *after* this item -->
            <li
              class="lle-drop-zone"
              :class="{
                'lle-drop-zone--active': isDropZoneActive('available', index + 1),
              }"
              @dragover="setDragOver('available', index + 1, $event)"
              @drop="onDropOnAvailable(index + 1, $event)"
            />
          </template>
        </ul>

      </div>

      <!-- SELECTED COLUMNS -->
      <div class="lle-panel lle-panel--columns">
        <div class="lle-header">
          <h5>{{ $t('layouts.list_columns') }}</h5>
          <small>{{ $t('layouts.list_columns_hint') }}</small>
        </div>

<ul
  class="lle-list"
  :class="{ 'lle-list--drag-target': isListDragTarget('columns') }"
>
  <!-- Top drop zone (before first column) -->
  <li
    class="lle-drop-zone"
    :class="{ 'lle-drop-zone--active': isDropZoneActive('columns', 0) }"
    @dragover="setDragOver('columns', 0, $event)"
    @drop="onDropOnColumns(0, $event)"
  />

  <!-- Column item + drop zone after -->
  <template v-for="(col, index) in internalColumns" :key="col.key">
    <li
      class="lle-item"
      :class="{ 'lle-item--dragging': isItemDragging('columns', index) }"
      draggable="true"
      @dragstart="startDrag('columns', index, $event)"
      @dragend="endDrag"
    >
      <span class="lle-item-handle">
        <i class="fa-solid fa-grip-vertical"></i>
      </span>

      <div class="lle-item-main">
        <span class="lle-item-label">
          {{ $t(col.label) ?? col.key }}
        </span>

        <span class="lle-item-meta">
          <!-- sortable badge later if you want -->
        </span>
      </div>
    </li>

    <!-- Drop zone *after* this column -->
    <li
      class="lle-drop-zone"
      :class="{
        'lle-drop-zone--active': isDropZoneActive('columns', index + 1),
      }"
      @dragover="setDragOver('columns', index + 1, $event)"
      @drop="onDropOnColumns(index + 1, $event)"
    />
  </template>
</ul>

      </div>
    </div>

    <!-- Custom drag ghost following the cursor -->
    <div
      v-if="dragging"
      class="lle-drag-ghost"
      :style="{ 
        top: dragPosition.y - originOffset.y + 'px',
        left: dragPosition.x - originOffset.x + 'px',
        width: ghostWidth || 'auto', 
        height: ghostHeight || 'auto'  
        }"
    >
      <span class="lle-item-handle">
        <i class="fa-solid fa-grip-vertical"></i>
      </span>
      <span class="lle-item-label">
        {{ ghostLabel }}
      </span>
    </div>

    <!-- Trail dots -->
    <div
      v-for="dot in dragTrails"
      :key="dot.id"
      class="lle-trail-dot"
      :style="{ top: dot.y + 'px', left: dot.x + 'px' }"
    ></div>
  </div>
</template>
