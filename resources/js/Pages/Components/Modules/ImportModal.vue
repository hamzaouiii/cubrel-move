<script setup>
import { ref, computed, onBeforeUnmount, getCurrentInstance } from "vue";
import axios from "axios";
import ImportFieldSelect from "@/Pages/Components/Modules/ImportFieldSelect.vue";

const props = defineProps({
  moduleSlug: { type: String, required: true },
  fields: { type: Array, default: () => [] },
  maxFileSizeKb: { type: Number, default: null },
  // both sourced from config('import.*') via ListController — no hardcoded
  // copies here, same reasoning as maxFileSizeKb
  acceptedExtensions: { type: Array, default: () => [] },
  excludedFieldTypes: { type: Array, default: () => [] },
});

const emit = defineEmits(["close"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const maxSizeBytes = computed(() =>
  props.maxFileSizeKb ? props.maxFileSizeKb * 1024 : null,
);
const maxSizeLabel = computed(() =>
  props.maxFileSizeKb ? `${Math.round(props.maxFileSizeKb / 1024)}MB` : "",
);

const acceptAttr = computed(() =>
  props.acceptedExtensions.map((ext) => `.${ext}`).join(","),
);

const phase = ref("upload");
const canClose = computed(() => phase.value !== "processing");

const fileInput = ref(null);
const dragOver = ref(false);
const uploading = ref(false);
const uploadError = ref("");

const importId = ref(null);
const format = ref(null);
const delimiter = ref(",");
const headers = ref([]);
const sampleRows = ref([]);
const totalRows = ref(0);
const mapping = ref({});
const matchField = ref("");

const starting = ref(false);
const startError = ref("");

const pollTimer = ref(null);
const statusData = ref(null);

const mappableFields = computed(() =>
  (props.fields ?? []).filter(
    (f) =>
      !f.readonly &&
      !f.is_calculated &&
      !props.excludedFieldTypes.includes(f.type),
  ),
);

const mappedFieldNames = computed(() =>
  Object.values(mapping.value).filter((v) => !!v),
);

const mappedCount = computed(() => mappedFieldNames.value.length);

const matchFieldOptions = computed(() =>
  mappableFields.value.filter((f) => mappedFieldNames.value.includes(f.name)),
);

const columnFieldSelectOptions = computed(() => [
  { value: "", label: "globals.import.ignore_column" },
  ...mappableFields.value.map((f) => ({ value: f.name, label: f.label })),
]);

const matchFieldSelectOptions = computed(() => [
  { value: "", label: "globals.import.match_field_none" },
  ...matchFieldOptions.value.map((f) => ({ value: f.name, label: f.label })),
]);

const matchFieldLabel = computed(() => {
  if (!matchField.value) return t("globals.import.match_field_none");
  const field = mappableFields.value.find((f) => f.name === matchField.value);
  return field ? t(field.label) : matchField.value;
});

const canStart = computed(() => mappedCount.value > 0);

const validateFile = (file) => {
  const ext = file.name.split(".").pop()?.toLowerCase();

  if (
    props.acceptedExtensions.length &&
    !props.acceptedExtensions.includes(ext)
  ) {
    return t("globals.import.invalid_file_type");
  }
  if (maxSizeBytes.value && file.size > maxSizeBytes.value) {
    return t("globals.import.file_too_large", { size: maxSizeLabel.value });
  }
  return null;
};

const autoMapHeaders = () => {
  const map = {};
  headers.value.forEach((header) => {
    const normalized = header.trim().toLowerCase();
    const match = mappableFields.value.find(
      (f) =>
        f.name.toLowerCase() === normalized ||
        t(f.label).toLowerCase() === normalized,
    );
    map[header] = match ? match.name : "";
  });
  mapping.value = map;

  if (matchField.value && !mappedFieldNames.value.includes(matchField.value)) {
    matchField.value = "";
  }
};

const uploadFile = async (file) => {
  uploading.value = true;
  uploadError.value = "";

  const formData = new FormData();
  formData.append("file", file);

  try {
    const { data } = await axios.post(
      `/${props.moduleSlug}/import/preview`,
      formData,
    );

    importId.value = data.importId;
    format.value = data.format;
    delimiter.value = data.delimiter ?? ",";
    headers.value = data.headers ?? [];
    sampleRows.value = data.sampleRows ?? [];
    totalRows.value = data.totalRows ?? 0;

    autoMapHeaders();
    phase.value = "mapping";
  } catch (err) {
    uploadError.value =
      err.response?.data?.message ||
      err.message ||
      t("globals.import.unexpected_error");
  } finally {
    uploading.value = false;
  }
};

const handleFileSelected = (file) => {
  if (!file) return;
  const err = validateFile(file);
  if (err) {
    uploadError.value = err;
    return;
  }
  uploadFile(file);
};

const onFileInputChange = (e) => {
  handleFileSelected(e.target.files?.[0]);
  e.target.value = "";
};

const onDrop = (e) => {
  e.preventDefault();
  dragOver.value = false;
  handleFileSelected(e.dataTransfer.files?.[0]);
};

const openFilePicker = () => fileInput.value?.click();

const sampleValue = (header) => sampleRows.value?.[0]?.[header] ?? "";

const goToConfirm = () => {
  phase.value = "confirm";
};

const backToMapping = () => {
  phase.value = "mapping";
};

const stopPolling = () => {
  if (pollTimer.value) {
    clearInterval(pollTimer.value);
    pollTimer.value = null;
  }
};
const sleep = (ms) => {
  return new Promise((resolve) => setTimeout(resolve, ms));
};
const poll = async () => {
  try {
    const { data } = await axios.get(
      `/${props.moduleSlug}/import/${importId.value}/status`,
    );
    statusData.value = data;

    if (data.status === "completed" || data.status === "failed") {
      stopPolling();
      phase.value = data.status === "failed" ? "error" : "done";
    }
  } catch {
    stopPolling();
    startError.value = t("globals.import.unexpected_error");
    phase.value = "error";
  }
};

const startPolling = () => {
  poll();
  pollTimer.value = setInterval(poll, 1500);
};

const startImport = async () => {
  starting.value = true;
  startError.value = "";

  try {
    const { data } = await axios.post(
      `/${props.moduleSlug}/import/${importId.value}/start`,
      {
        mapping: mapping.value,
        matchField: matchField.value || null,
      },
    );
    phase.value = "processing";
    await sleep(1500);

    if (data.status === "completed" || data.status === "failed") {
      statusData.value = data;
      phase.value = data.status === "failed" ? "error" : "done";
    } else {
      startPolling();
    }
  } catch (err) {
    startError.value =
      err.response?.data?.message || t("globals.import.unexpected_error");
  } finally {
    starting.value = false;
  }
};

const disableStartBtn = computed(() => {
  return starting.value || totalRows.value == 0;
});

const retry = () => {
  phase.value = "upload";
  importId.value = null;
  headers.value = [];
  sampleRows.value = [];
  mapping.value = {};
  matchField.value = "";
  statusData.value = null;
  startError.value = "";
  uploadError.value = "";
};

const close = () => {
  if (!canClose.value) return;
  stopPolling();
  emit("close", { imported: phase.value === "done" });
};

onBeforeUnmount(() => stopPolling());
</script>

<template>
  <div class="pdf-modal import-modal">
    <div class="pdf-modal__backdrop" @click="close"></div>

    <div class="pdf-modal__container import-modal__container">
      <div class="deployment-card">
        <div class="deployment-card__header">
          <div class="deployment-card__title-group">
            <h3 class="deployment-card__title">
              <template v-if="phase === 'upload'">{{
                $t("globals.import.modal_title_upload")
              }}</template>
              <template v-else-if="phase === 'mapping'">{{
                $t("globals.import.modal_title_mapping")
              }}</template>
              <template v-else-if="phase === 'confirm'">{{
                $t("globals.import.modal_title_confirm")
              }}</template>
              <template v-else-if="phase === 'processing'">{{
                $t("globals.import.modal_title_processing")
              }}</template>
              <template v-else-if="phase === 'done'">{{
                $t("globals.import.modal_title_done")
              }}</template>
              <template v-else>{{
                $t("globals.import.modal_title_error")
              }}</template>
            </h3>

            <p class="deployment-card__subtitle" v-if="phase === 'upload'">
              {{ $t("globals.import.modal_sub_upload") }}
            </p>
            <p
              class="deployment-card__subtitle"
              v-else-if="phase === 'mapping'"
            >
              {{ $t("globals.import.modal_sub_mapping") }}
            </p>
            <p
              class="deployment-card__subtitle deployment-card__subtitle--danger"
              v-else-if="phase === 'error'"
            >
              {{ startError || statusData?.failedReason }}
            </p>
          </div>
        </div>

        <div class="pdf-modal__body import-modal__body">
          <template v-if="phase === 'upload'">
            <div
              class="import-modal__dropzone"
              :class="{ 'import-modal__dropzone--over': dragOver }"
              @click="openFilePicker"
              @dragover.prevent="dragOver = true"
              @dragleave.prevent="dragOver = false"
              @drop="onDrop"
            >
              <template v-if="uploading">
                <div class="saving-loader import-modal__loader">
                  <div class="lds-ripple">
                    <div></div>
                    <div></div>
                  </div>
                </div>
              </template>
              <template v-else>
                <i
                  class="fa-solid fa-cloud-arrow-up import-modal__dropzone-icon"
                ></i>
                <span class="import-modal__dropzone-label">{{
                  $t("globals.import.drop_zone_label")
                }}</span>
              </template>
            </div>
            <input
              ref="fileInput"
              type="file"
              :accept="acceptAttr"
              class="import-modal__file-input"
              @change="onFileInputChange"
            />
            <p v-if="uploadError" class="import-modal__error-text">
              {{ uploadError }}
            </p>
          </template>

          <template v-else-if="phase === 'mapping'">
            <div class="import-modal__meta-row">
              <span class="import-modal__badge">{{
                $t("globals.import.detected_format", {
                  format: format?.toUpperCase(),
                })
              }}</span>
              <span class="import-modal__badge" v-if="format === 'csv'">
                {{
                  $t("globals.import.detected_delimiter", {
                    delimiter:
                      delimiter === ";"
                        ? $t("globals.import.delimiter_semicolon")
                        : $t("globals.import.delimiter_comma"),
                  })
                }}
              </span>
            </div>

            <div class="import-modal__table-wrap">
              <table class="import-modal__table">
                <thead>
                  <tr>
                    <th>{{ $t("globals.import.column_header") }}</th>
                    <th>{{ $t("globals.import.sample_value") }}</th>
                    <th>{{ $t("globals.import.target_field") }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="header in headers" :key="header">
                    <td class="import-modal__col-name">{{ header }}</td>
                    <td class="import-modal__col-sample">
                      {{ sampleValue(header) }}
                    </td>
                    <td>
                      <ImportFieldSelect
                        v-model="mapping[header]"
                        :options="columnFieldSelectOptions"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="import-modal__match-field">
              <label class="import-modal__match-label">{{
                $t("globals.import.match_field_label")
              }}</label>
              <ImportFieldSelect
                v-model="matchField"
                :options="matchFieldSelectOptions"
              />
              <p class="import-modal__hint">
                {{ $t("globals.import.match_field_hint") }}
              </p>
            </div>
          </template>

          <template v-else-if="phase === 'confirm'">
            <div class="import-modal__summary">
              <div class="import-modal__summary-row">
                <span> {{ $t("globals.import.summary_mapped") }}</span>
                <span class="import-modal__highlight">{{
                  `${mappedCount} ${$t("modules.of")} ${headers.length}`
                }}</span>
              </div>
              <div class="import-modal__summary-row">
                <span>{{ $t("globals.import.match_field_label") }}:</span>
                <span class="import-modal__highlight">{{
                  matchFieldLabel
                }}</span>
              </div>
              <div class="import-modal__summary-row">
                <span> {{ $t("globals.import.total_rows_label") }}</span>
                <span class="import-modal__highlight">
                  {{ totalRows }}
                </span>
              </div>
            </div>
            <p v-if="startError" class="import-modal__error-text">
              {{ startError }}
            </p>
          </template>

          <template v-else-if="phase === 'processing'">
            <div class="saving-loader import-modal__loader">
              <div class="lds-ripple">
                <div></div>
                <div></div>
              </div>
            </div>

            <div class="import-modal__progress-label">
              {{
                $t("globals.import.progress_label", {
                  processed: statusData?.processedRows ?? 0,
                  total: statusData?.totalRows ?? totalRows,
                })
              }}
            </div>
          </template>

          <template v-else-if="phase === 'done'">
            <div class="import-modal__results">
              <div class="import-modal__result import-modal__result--created">
                <i class="fas fa-check-circle"></i>
                {{
                  $t("globals.import.result_created", {
                    count: statusData?.createdCount ?? 0,
                  })
                }}
              </div>
              <div class="import-modal__result import-modal__result--updated">
                <i class="fas fa-sync-alt"></i>
                {{
                  $t("globals.import.result_updated", {
                    count: statusData?.updatedCount ?? 0,
                  })
                }}
              </div>
              <div
                class="import-modal__result import-modal__result--skipped"
                v-if="statusData?.skippedCount"
              >
                <i class="fas fa-ban"></i>
                {{
                  $t("globals.import.result_skipped", {
                    count: statusData?.skippedCount ?? 0,
                  })
                }}
              </div>
            </div>

            <div v-if="statusData?.skippedCount" class="import-modal__errors">
              <h4 class="import-modal__errors-heading">
                {{ $t("globals.import.errors_heading") }}
              </h4>
              <ul class="import-modal__errors-list">
                <li v-for="(err, idx) in statusData?.errors ?? []" :key="idx">
                  {{ $t("globals.import.errors_row_label", { row: err.row }) }}:
                  {{ err.reason }}
                </li>
              </ul>
              <p v-if="statusData?.errorsTruncated" class="import-modal__hint">
                {{
                  $t("globals.import.errors_truncated", {
                    count: statusData?.errors?.length ?? 0,
                  })
                }}
              </p>
            </div>
          </template>

          <template v-else-if="phase === 'error'">
            <div class="pdf-modal__error">
              <div class="pdf-modal__error__icon">
                <i class="fa-solid fa-xmark"></i>
              </div>
            </div>
          </template>
        </div>

        <div class="deployment-card__footer">
          <div class="deployment-card__footer__content">
            <template v-if="phase === 'upload'">
              <span class="deployment-card__footer__content__hint">{{
                $t("globals.import.upload_hint", { size: maxSizeLabel })
              }}</span>
            </template>

            <template v-else-if="phase === 'mapping'">
              <button
                class="deployment-card__button deployment-card__button--secondary"
                @click="close"
              >
                {{ $t("globals.import.cancel") }}
              </button>
              <button
                class="deployment-card__button"
                :disabled="!canStart"
                @click="goToConfirm"
              >
                {{ $t("globals.import.next_button") }}
              </button>
            </template>

            <template v-else-if="phase === 'confirm'">
              <button
                class="deployment-card__button deployment-card__button--secondary"
                @click="backToMapping"
              >
                {{ $t("globals.import.back_button") }}
              </button>
              <button
                class="deployment-card__button"
                :class="{
                  'deployment-card__button--disabled': disableStartBtn,
                }"
                @click="startImport"
              >
                {{ $t("globals.import.start_button") }}
              </button>
            </template>

            <template v-else-if="phase === 'processing'">
              <div class="deployment-message">
                <div class="deployment-message__dot"></div>
                <span class="deployment-message__text">{{
                  $t("globals.import.processing_msg")
                }}</span>
              </div>
            </template>

            <template v-else-if="phase === 'done'">
              <button class="deployment-card__button" @click="close">
                {{ $t("globals.import.close") }}
              </button>
            </template>

            <template v-else-if="phase === 'error'">
              <div class="deployment-failed__actions">
                <button
                  class="deployment-card__button deployment-card__button--retry"
                  @click="retry"
                >
                  {{ $t("globals.import.retry") }}
                </button>
                <button
                  class="deployment-card__button deployment-card__button--secondary"
                  @click="close"
                >
                  {{ $t("globals.import.close") }}
                </button>
              </div>
            </template>
          </div>
        </div>
      </div>

      <button class="pdf-modal__close" @click="close" :disabled="!canClose">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>
</template>
