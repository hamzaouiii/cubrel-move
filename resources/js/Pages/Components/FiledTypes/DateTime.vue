<script setup>
import {
  computed,
  ref,
  onMounted,
  onUnmounted,
  watch,
  getCurrentInstance,
} from "vue";
import { formatDateTime, formatDate } from "@/utils/datetime";
import { usePage } from "@inertiajs/vue3";
const props = defineProps({
  modelValue: [Date, String, null],
  mode: {
    type: String,
    default: "edit",
  },
  hasError: {
    type: Boolean,
    default: false,
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: "datetime",
    validator: (value) => ["date", "datetime"].includes(value),
  },
  placeholder: String,
  minDate: [Date, String],
  maxDate: [Date, String],
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

const showDatePicker = ref(false);
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
  if (!selectedHour.value) return "";

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
    selectedHour.value = 0;
    selectedMinute.value = 0;
    isAm.value = true;

    showDatePicker.value = false;
    showTimePicker.value = true;
    emitValue();
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
  if (!target.closest(".module-datetime")) {
    showDatePicker.value = false;
    showTimePicker.value = false;
  }
};
const appSettings = usePage().props.appSettings;

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

const showError = ref(false);

watch(
  () => props.hasError,
  (val) => {
    showError.value = val;
  },
  { immediate: true },
);

const clearErrors = () => {
  showError.value = false;
};
</script>
<template>
  <div v-if="mode === 'edit'">
    <div v-if="readOnly">
      <span class="'text-field module-datetime--readonly'">
        {{
          type === "date"
            ? formatDate(modelValue, appSettings)
            : formatDateTime(modelValue, appSettings)
        }}
      </span>
    </div>
    <div
      v-else
      class="module-datetime"
      :class="{
        'module-datetime--error': showError,
        'module-datetime--readonly': readOnly,
      }"
    >
      <div class="picker-container" @click="clearErrors">
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
          <i
            v-if="showError"
            class="error-icon fa-solid fa-circle-exclamation"
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
          <i
            v-if="showError"
            class="error-icon fa-solid fa-circle-exclamation"
          ></i>
        </div>

        <div v-if="showDatePicker" class="picker-popup date-popup">
          <div class="picker-header">
            <button @click="prevMonth" class="nav-btn">
              <i class="fas fa-chevron-left"></i>
            </button>
            <div class="current-month">
              {{ currentMonth }} {{ currentYear }}
            </div>
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
    </div>
  </div>
  <div v-else-if="mode === 'detail'">
    <span :class="['text-field', { 'module-datetime--readonly': readOnly }]">
      {{
        type === "date"
          ? formatDate(modelValue, appSettings)
          : formatDateTime(modelValue, appSettings)
      }}
    </span>
  </div>
  <div
    v-else-if="
      mode === 'table' || mode === 'related-panel' || mode === 'linkingPanel'
    "
  >
    <span>
      {{
        type === "date"
          ? formatDate(modelValue, appSettings)
          : formatDateTime(modelValue, appSettings)
      }}
    </span>
  </div>
</template>
