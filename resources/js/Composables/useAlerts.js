import { ref } from 'vue'
const alerts = ref([])

let nextId = 1

const addAlert = (type, message, options = {}) => {
  if (!message) return

  const id = nextId++
  alerts.value.push({
    id,
    type,
    message,
    dismissible: options.dismissible ?? true,
    timeout: options.timeout ?? 5000,
  })

  if (options.timeout !== 0) {
    setTimeout(() => {
      removeAlert(id)
    }, options.timeout ?? 5000)
  }
}

const removeAlert = (id) => {
  alerts.value = alerts.value.filter((a) => a.id !== id)
}

const clearAllAlerts = () => {
  alerts.value = []
}

const success = (message, options = {}) =>
  addAlert('success', message, options)

const error = (message, options = {}) =>
  addAlert('error', message, options)

const info = (message, options = {}) =>
  addAlert('info', message, options)

export function useAlerts() {
  // everyone gets the SAME alerts ref and helper functions
  return {
    alerts,
    success,
    error,
    info,
    removeAlert,
    clearAllAlerts,
  }
}
