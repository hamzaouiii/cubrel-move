<script setup>
import {
  computed,
  ref,
  onMounted,
  onUnmounted,
  watch,
  getCurrentInstance,
} from "vue";

const props = defineProps({
  modelValue: [Date, String, null],
  type: {
    type: String,
    default: "datetime",
    validator: (value) => ["date", "datetime"].includes(value),
  },
  placeholder: String,
  minDate: [Date, String],
  maxDate: [Date, String],
  error: String,
  disabled: Boolean,
  format: {
    type: String,
    default: "",
  },
  showAmPm: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue", "change"]);
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const showDatePicker = ref(true);
const showTimePicker = ref(false);
const currentDate = ref(new Date());
const selectedDate = ref(null);
const selectedHour = ref(0);
const selectedMinute = ref(0);
const isAm = ref(true);

const currentMonth = computed(() => {
  return t(
    `calendar.months.${months[currentDate.value.getMonth()].toLowerCase()}`,
  );
});

const currentYear = computed(() => currentDate.value.getFullYear());

const hours = computed(() => {
  if (props.showAmPm) {
    return Array.from({ length: 12 }, (_, i) => i + 1);
  }
  return Array.from({ length: 24 }, (_, i) => i);
});

const minutes = computed(() => Array.from({ length: 12 }, (_, i) => i * 5));

const daysInMonth = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();

  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const daysCount = lastDay.getDate();

  const startDay = new Date(firstDay);
  startDay.setDate(firstDay.getDate() - firstDay.getDay());

  const days = [];
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  for (let i = 0; i < 42; i++) {
    const date = new Date(startDay);
    date.setDate(startDay.getDate() + i);

    const isCurrentMonth = date.getMonth() === month;
    const isToday = date.toDateString() === today.toDateString();
    const isSelected = selectedDate.value
      ? date.toDateString() === selectedDate.value.toDateString()
      : false;

    const isDisabled = isDateDisabled(date);

    days.push({
      date,
      isCurrentMonth,
      isToday,
      isSelected,
      isDisabled,
    });
  }

  return days;
});

const displayValue = computed(() => {
  if (!selectedDate.value) return "";

  const date = selectedDate.value;
  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0");
  const year = date.getFullYear();

  if (props.format) {
    return props.format
      .replace("DD", day)
      .replace("MM", month)
      .replace("YYYY", year.toString());
  }

  return `${year}-${month}-${day}`;
});

const displayTime = computed(() => {
  if (!selectedHour.value && !selectedMinute.value) return "";

  let hour = selectedHour.value;
  if (props.showAmPm) {
    if (!isAm.value && hour < 12) hour += 12;
    if (isAm.value && hour === 12) hour = 0;
  }

  return `${hour.toString().padStart(2, "0")}:${selectedMinute.value.toString().padStart(2, "0")}`;
});
const isValidDate = (date) => {
  return date instanceof Date && !isNaN(date.getTime());
};
watch(
  () => props.modelValue,
  (value) => {
    if (!value) {
      selectedDate.value = null;
      selectedHour.value = 0;
      selectedMinute.value = 0;
      isAm.value = true;
      return;
    }

    const date = typeof value === "string" ? new Date(value) : value;
    if (isValidDate(date)) {
      selectedDate.value = new Date(
        date.getFullYear(),
        date.getMonth(),
        date.getDate(),
      );

      if (props.type === "datetime") {
        selectedHour.value = date.getHours();
        selectedMinute.value = date.getMinutes();

        if (props.showAmPm) {
          isAm.value = date.getHours() < 12;
        }
      }
    }
  },
  { immediate: true },
);

const months = [
  "january",
  "february",
  "march",
  "april",
  "may",
  "june",
  "july",
  "august",
  "september",
  "october",
  "november",
  "december",
];

const weekDays = [
  "sunday",
  "monday",
  "tuesday",
  "wednesday",
  "thursday",
  "friday",
  "saturday",
];

const getWeekdayShort = computed(() => {
  return weekDays.map((d) => {
    return `calendar.weekdays_short.${d}`;
  });
});

const toggleDatePicker = () => {
  if (props.disabled) return;
  showDatePicker.value = !showDatePicker.value;
  showTimePicker.value = false;
};

const toggleTimePicker = () => {
  if (props.disabled || props.type !== "datetime") return;
  showTimePicker.value = !showTimePicker.value;
  showDatePicker.value = false;
};

const prevMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() - 1,
    1,
  );
};

const nextMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() + 1,
    1,
  );
};

const selectDate = (date) => {
  if (isDateDisabled(date)) return;

  selectedDate.value = new Date(
    date.getFullYear(),
    date.getMonth(),
    date.getDate(),
  );

  if (props.type === "date") {
    emitValue();
  }

  if (props.type === "datetime") {
    showDatePicker.value = false;
    showTimePicker.value = true;
  } else {
    showDatePicker.value = false;
  }
};

const selectHour = (hour) => {
  selectedHour.value = hour;
  emitValue();
};

const selectMinute = (minute) => {
  selectedMinute.value = minute;
  emitValue();
};

const toggleAmPm = (am) => {
  isAm.value = am;
  emitValue();
};

const selectToday = () => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (!isDateDisabled(today)) {
    selectDate(today);
    selectedHour.value = new Date().getHours();
    selectedMinute.value = Math.floor(new Date().getMinutes() / 5) * 5;
    isAm.value = selectedHour.value < 12;
    emitValue();
  }
};

const clear = () => {
  selectedDate.value = null;
  selectedHour.value = 12;
  selectedMinute.value = 0;
  isAm.value = true;
  emitValue();
  showDatePicker.value = false;
  showTimePicker.value = false;
};

const emitValue = () => {
  if (!selectedDate.value) {
    emit("update:modelValue", null);
    emit("change", null);
    return;
  }

  let date = new Date(selectedDate.value);

  if (props.type === "datetime") {
    let hour = selectedHour.value;
    if (props.showAmPm) {
      if (!isAm.value && hour < 12) hour += 12;
      if (isAm.value && hour === 12) hour = 0;
    }

    date.setHours(hour, selectedMinute.value, 0, 0);
  }

  emit("update:modelValue", date);
  emit("change", date);
};

const isDateDisabled = (date) => {
  if (props.disabled) return true;

  const checkDate = new Date(date);
  checkDate.setHours(0, 0, 0, 0);

  if (props.minDate) {
    const min =
      typeof props.minDate === "string"
        ? new Date(props.minDate)
        : props.minDate;
    min.setHours(0, 0, 0, 0);
    if (checkDate < min) return true;
  }

  if (props.maxDate) {
    const max =
      typeof props.maxDate === "string"
        ? new Date(props.maxDate)
        : props.maxDate;
    max.setHours(0, 0, 0, 0);
    if (checkDate > max) return true;
  }

  return false;
};

const handleKeydown = (e) => {
  if (e.key === "Escape") {
    showDatePicker.value = false;
    showTimePicker.value = false;
  }
  if (e.key === "Enter" || e.key === " ") {
    e.preventDefault();
    toggleDatePicker();
  }
};

const handleClickOutside = (e) => {
  const target = e.target;
  if (!target.closest(".datetime-picker")) {
    showDatePicker.value = false;
    showTimePicker.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});

defineExpose({
  clear,
  selectToday,
  toggleDatePicker,
  toggleTimePicker,
});
</script>
<template>
  <div class="datetime-picker" :class="{ 'has-error': error }">
    <div class="picker-container">
      <div class="date-input" @click="toggleDatePicker">
        <i class="fas fa-calendar"></i>
        <input
          type="text"
          :value="displayValue"
          :placeholder="placeholder"
          readonly
          class="picker-input"
          @keydown="handleKeydown"
        />
        <i
          v-if="modelValue"
          class="fas fa-times clear-btn"
          @click.stop="clear"
        ></i>
      </div>

      <div
        v-if="type === 'datetime'"
        class="time-input"
        @click="toggleTimePicker"
      >
        <i class="fas fa-clock"></i>
        <input
          type="text"
          :value="displayTime"
          :placeholder="t('calendar.time_format')"
          readonly
          class="picker-input"
        />
      </div>

      <div v-if="showDatePicker" class="picker-popup date-popup">
        <div class="picker-header">
          <button @click="prevMonth" class="nav-btn">
            <i class="fas fa-chevron-left"></i>
          </button>
          <div class="current-month">{{ currentMonth }} {{ currentYear }}</div>
          <button @click="nextMonth" class="nav-btn">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>

        <div class="weekdays">
          <div v-for="day in getWeekdayShort" :key="day" class="weekday">
            {{ $t(day) }}
          </div>
        </div>

        <div class="days-grid">
          <div
            v-for="day in daysInMonth"
            :key="day.date.getTime()"
            :class="[
              'day',
              {
                today: day.isToday,
                selected: day.isSelected,
                'current-month': day.isCurrentMonth,
                disabled: day.isDisabled,
              },
            ]"
            @click="selectDate(day.date)"
          >
            {{ day.date.getDate() }}
          </div>
        </div>

        <div class="quick-actions">
          <button @click="selectToday" class="quick-btn">
            {{ $t("calendar.today") }}
          </button>
          <button @click="clear" class="quick-btn">
            {{ $t("calendar.clear") }}
          </button>
        </div>
      </div>

      <div
        v-if="showTimePicker && type === 'datetime'"
        class="picker-popup time-popup"
      >
        <div class="time-header">Select Time</div>

        <div class="time-selector">
          <div class="hour-selector">
            <div class="time-label">Hour</div>
            <div class="time-scroll">
              <button
                v-for="hour in hours"
                :key="hour"
                :class="['time-option', { selected: selectedHour === hour }]"
                @click="selectHour(hour)"
              >
                {{ hour.toString().padStart(2, "0") }}
              </button>
            </div>
          </div>

          <div class="minute-selector">
            <div class="time-label">Minute</div>
            <div class="time-scroll">
              <button
                v-for="minute in minutes"
                :key="minute"
                :class="[
                  'time-option',
                  { selected: selectedMinute === minute },
                ]"
                @click="selectMinute(minute)"
              >
                {{ minute.toString().padStart(2, "0") }}
              </button>
            </div>
          </div>

          <div v-if="showAmPm" class="ampm-selector">
            <div class="time-label">AM/PM</div>
            <button
              :class="['ampm-btn', { selected: isAm }]"
              @click="toggleAmPm(true)"
            >
              AM
            </button>
            <button
              :class="['ampm-btn', { selected: !isAm }]"
              @click="toggleAmPm(false)"
            >
              PM
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="error" class="error-message">
      <i class="fas fa-exclamation-circle"></i> {{ error }}
    </div>
  </div>
</template>
<style scoped>
.datetime-picker {
  position: relative;
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  width: 100%;
}

.picker-container {
  display: flex;
  gap: 8px;
  align-items: center;
}

.date-input,
.time-input {
  position: relative;
  display: flex;
  align-items: center;
  background: white;
  padding: 0 12px;
  height: 40px;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 160px;
  border-radius: 8px;
}

.date-input:hover,
.time-input:hover {
  border-color: #94a3b8;
}

.date-input:focus-within,
.time-input:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.has-error .date-input,
.has-error .time-input {
  border-color: #ef4444;
}

.has-error .date-input:focus-within,
.has-error .time-input:focus-within {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.date-input i,
.time-input i {
  color: #94a3b8;
  margin-right: 8px;
}

.picker-input {
  border: none;
  outline: none;
  background: transparent;
  width: 100%;
  font-size: 14px;
  color: #1e293b;
  cursor: pointer;
}

.picker-input::placeholder {
  color: #94a3b8;
}

.clear-btn {
  margin-left: auto;
  margin-right: 0;
  cursor: pointer;
  transition: color 0.2s;
  color: #94a3b8;
}

.clear-btn:hover {
  color: #64748b;
}

/* Popup styles */
.picker-popup {
  position: absolute;
  top: calc(100% + 8px);
  background: white;
  border-radius: 12px;
  box-shadow:
    0 10px 25px rgba(0, 0, 0, 0.1),
    0 20px 48px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  padding: 16px;
  min-width: 300px;
  border: 1px solid #e2e8f0;
}

.date-popup {
  left: 0;
}

.time-popup {
  left: 168px;
}

.picker-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.nav-btn {
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  color: #64748b;
}

.nav-btn:hover {
  background: #f8fafc;
  border-color: #94a3b8;
}

.current-month {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
  margin-bottom: 8px;
}

.weekday {
  text-align: center;
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
  padding: 4px;
}

.days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
}

.day {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  color: #1e293b;
  border: 1px solid transparent;
}

.day:hover:not(.disabled) {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.day.current-month {
  color: #1e293b;
}

.day:not(.current-month) {
  color: #94a3b8;
}

.day.today {
  background: #dbeafe;
  color: #1d4ed8;
  font-weight: 600;
}

.day.selected {
  background: #3b82f6;
  color: white;
  font-weight: 600;
}

.day.disabled {
  color: #cbd5e1;
  cursor: not-allowed;
  background: #f8fafc;
}

.day.disabled:hover {
  background: #f8fafc;
  border-color: transparent;
}

.quick-actions {
  display: flex;
  gap: 8px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}

.quick-btn {
  flex: 1;
  padding: 8px 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 13px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.quick-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

/* Time picker styles */
.time-header {
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 16px;
  font-size: 14px;
}

.time-selector {
  display: flex;
  gap: 16px;
  max-height: 300px;
}

.hour-selector,
.minute-selector,
.ampm-selector {
  flex: 1;
}

.time-label {
  font-size: 12px;
  color: #64748b;
  margin-bottom: 8px;
  font-weight: 500;
}

.time-scroll {
  max-height: 240px;
  overflow-y: auto;
  padding-right: 4px;
}

.time-scroll::-webkit-scrollbar {
  width: 4px;
}

.time-scroll::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 2px;
}

.time-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}

.time-option {
  width: 100%;
  padding: 8px 12px;
  margin-bottom: 4px;
  background: #f8fafc;
  border: 1px solid transparent;
  border-radius: 6px;
  font-size: 13px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.time-option:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.time-option.selected {
  background: #3b82f6;
  color: white;
  font-weight: 600;
  border-color: #2563eb;
}

.ampm-selector {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.ampm-btn {
  padding: 10px 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 13px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.ampm-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.ampm-btn.selected {
  background: #3b82f6;
  color: white;
  font-weight: 600;
  border-color: #2563eb;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #ef4444;
  margin-top: 6px;
}

.error-message i {
  font-size: 14px;
}

/* Animation for popups */
.picker-popup {
  animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
