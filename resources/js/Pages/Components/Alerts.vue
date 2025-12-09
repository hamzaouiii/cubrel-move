<script setup>
import { onMounted, onUnmounted, ref, computed } from 'vue'

const timers = ref({})

const props = defineProps({
  alerts: {
    type: Array,
    default: () => []
  }
})

const normalizedAlerts = computed(() =>
  props.alerts.map(a => ({
    id: a.id ?? null,
    message: a.message ?? '',
    type: a.type ?? 'info',
    dismissible: a.dismissible ?? false,
    progressable: a.progressable ?? false,
    duration: a.duration ?? 5000,
  }))
)

const closeAlert = (id) => {
  const index = props.alerts.findIndex(a => a.id === id)
  if (index !== -1) {
    props.alerts.splice(index, 1)
  }

  if (timers.value[id]) {
    clearTimeout(timers.value[id])
    delete timers.value[id]
  }
}

// const startProgressTimer = (alert) => {
//   const id = alert.id
//   if (!id) return 

//   const duration = alert.duration || 1000

//   if (timers.value[id]) {
//     clearTimeout(timers.value[id])
//   }

//   timers.value[id] = setTimeout(() => {
//     closeAlert(id)
//   }, duration)
// }

// onMounted(() => {
//   props.alerts.forEach((alert) => {
//     if (alert.dismissible !== true ) {
//       startProgressTimer(alert)
//     }
//   })
// })

// onUnmounted(() => {
//   Object.values(timers.value).forEach(timer => {
//     clearTimeout(timer)
//   })
//   timers.value = {}
// })


</script>
<template>
  <div class="alerts-zone">
    <div 
      v-for="(alert, index) in normalizedAlerts" 
      :key="alert.id"
      class="alerts-zone_alert"
      :class="`alerts-zone_alert-${alert.type}`"
    >
      <span class="alerts-zone_alert_text">
        {{ alert.message }}
      </span>
      <span  v-if="alert.dismissible !== false" 
        class="alerts-zone_alert_close"
        @click="closeAlert(alert.id)">
        <i class="fa-solid fa-times"></i>
      </span>
    </div>
   </div>

</template>