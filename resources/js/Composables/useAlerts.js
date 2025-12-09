// composables/useAlerts.js
import { ref } from 'vue';

export function useAlerts() {
  const alerts = ref([]);
  let nextId = 1;

  const addAlert = (alert) => {
    const alertWithId = {
      id: nextId++,
      message: alert.message,
      type: alert.type || 'info',
      dismissible: alert.dismissible,
      autoDismiss: alert.autoDismiss || true,
      timeout: alert.timeout || 5000
    };

    alerts.value.push(alertWithId);

    // Auto-dismiss if enabled
    if (alertWithId.autoDismiss) {
      setTimeout(() => {
        removeAlert(alertWithId.id);
      }, alertWithId.timeout);
    }

    return alertWithId.id;
  };

  const removeAlert = (id) => {
    const index = alerts.value.findIndex(alert => alert.id === id);
    if (index !== -1) {
      alerts.value.splice(index, 1);
    }
  };

  const clearAllAlerts = () => {
    alerts.value = [];
  };

  // Convenience methods for different alert types
  const success = (message, options = {}) => {
    return addAlert({ message, type: 'success', ...options });
  };

  const error = (message, options = {}) => {
    return addAlert({ message, type: 'error', ...options });
  };

  const warning = (message, options = {}) => {
    return addAlert({ message, type: 'warning', ...options });
  };

  const info = (message, options = {}) => {
    return addAlert({ message, type: 'info', ...options });
  };

  return {
    alerts,
    addAlert,
    removeAlert,
    clearAllAlerts,
    success,
    error,
    warning,
    info
  };
}