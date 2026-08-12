<script setup>
import { ref, computed, getCurrentInstance } from "vue";
import axios from "axios";

const props = defineProps({
  mode: { type: String, default: "single" },
  moduleSlug: { type: String, required: true },
  recordId: { type: String, default: "" },
  recordName: { type: String, default: "" },
  selection: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["close", "cancel"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const phase = ref("select");
const selectedFormat = ref(null);
const blobUrl = ref(null);
const errorMessage = ref("");
const downloadFilename = ref("");

const canClose = computed(() => phase.value !== "generating");

const formats = [
  {
    id: "json",
    name: "JSON",
    description: t("globals.export.format_json_description"),
  },
  {
    id: "csv",
    name: "CSV",
    description: t("globals.export.format_csv_description"),
  },
];

const isBulk = computed(() => props.mode === "bulk");

const generate = async (format) => {
  selectedFormat.value = format;
  phase.value = "generating";
  blobUrl.value = null;
  errorMessage.value = "";

  downloadFilename.value = isBulk.value
    ? `${props.moduleSlug}-export.${format.id}`.toLowerCase()
    : `${props.moduleSlug}-${props.recordName || props.recordId}.${format.id}`
        .toLowerCase()
        .replace(/\s+/g, "-");

  try {
    const response = isBulk.value
      ? await axios.post(
          `/${props.moduleSlug}/export`,
          { ...props.selection, format: format.id },
          { responseType: "blob" },
        )
      : await axios.get(
          `/${props.moduleSlug}/${props.recordId}/export?format=${format.id}`,
          { responseType: "blob" },
        );
    blobUrl.value = URL.createObjectURL(response.data);
    phase.value = "ready";
    triggerDownload();
  } catch (err) {
    errorMessage.value =
      err.response?.data?.message ||
      err.message ||
      t("globals.export.unexpected_error");
    phase.value = "error";
  }
};

const triggerDownload = () => {
  if (!blobUrl.value) return;
  const a = document.createElement("a");
  a.href = blobUrl.value;
  a.download = downloadFilename.value;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
};

const retry = () => {
  if (selectedFormat.value) {
    generate(selectedFormat.value);
  } else {
    phase.value = "select";
  }
};

const close = () => {
  if (!canClose.value) return;
  if (blobUrl.value) URL.revokeObjectURL(blobUrl.value);
  emit("close");
};

const cancel = () => {
  if (!canClose.value) return;
  if (blobUrl.value) URL.revokeObjectURL(blobUrl.value);
  emit("cancel");
};
</script>

<template>
  <div class="pdf-modal">
    <div class="pdf-modal__backdrop" @click="cancel"></div>

    <div class="pdf-modal__container">
      <div class="deployment-card">

        <div class="deployment-card__header">
          <div class="deployment-card__title-group">
            <h3 class="deployment-card__title">
              <template v-if="phase === 'select'">{{
                isBulk
                  ? $t("globals.export.modal_title_select_bulk")
                  : $t("globals.export.modal_title_select")
              }}</template>
              <template v-else-if="phase === 'generating'">{{
                $t("globals.export.modal_title_generating")
              }}</template>
              <template v-else-if="phase === 'ready'">{{
                $t("globals.export.modal_title_ready")
              }}</template>
              <template v-else>{{
                $t("globals.export.modal_title_error")
              }}</template>
            </h3>

            <p class="deployment-card__subtitle" v-if="phase === 'select'">
              {{
                isBulk
                  ? $t("globals.export.modal_sub_select_bulk")
                  : $t("globals.export.modal_sub_select")
              }}
            </p>
            <p
              class="deployment-card__subtitle"
              v-else-if="phase === 'generating'"
            >
              {{ $t("globals.export.modal_sub_generating") }}
            </p>
            <p
              class="deployment-card__subtitle deployment-card__subtitle--success"
              v-else-if="phase === 'ready'"
            >
              {{ $t("globals.export.modal_sub_ready") }}
            </p>
            <p
              class="deployment-card__subtitle deployment-card__subtitle--danger"
              v-else-if="phase === 'error'"
            >
              {{ errorMessage }}
            </p>
          </div>

          <div class="pdf-modal__generating-bar" v-if="phase === 'generating'">
            <div class="pdf-modal__generating-bar__track">
              <div class="pdf-modal__generating-bar__fill"></div>
            </div>
          </div>
        </div>

        <div class="pdf-modal__body">

          <template v-if="phase === 'select'">
            <div
              v-for="format in formats"
              :key="format.id"
              class="pdf-template-row"
              @click="generate(format)"
            >
              <div class="pdf-template-row__icon">
                <i
                  v-if="format.id === 'json'"
                  class="fa-solid fa-file-code"
                ></i>
                <i v-else class="fa-solid fa-file-csv"></i>
              </div>

              <div class="pdf-template-row__info">
                <span class="pdf-template-row__name">{{ format.name }}</span>
                <span class="export-modal__format-description">{{
                  format.description
                }}</span>
              </div>

              <div class="pdf-template-row__action">
                <i class="fa-solid fa-chevron-right"></i>
              </div>
            </div>
          </template>

          <template v-else-if="phase === 'generating'">
            <div class="pdf-modal__spinner-wrap">
              <div class="saving-loader import-modal__loader">
                <div class="lds-ripple">
                  <div></div>
                  <div></div>
                </div>
              </div>
              <div class="pdf-modal__spinner-label">
                {{
                  $t("globals.export.modal_building", {
                    format: selectedFormat?.name,
                  })
                }}
              </div>
            </div>
          </template>

          <template v-else-if="phase === 'ready'">
            <div class="pdf-modal__ready">
              <div class="pdf-modal__ready__icon">
                <i class="fa-solid fa-check"></i>
              </div>
              <div class="pdf-modal__ready__label">
                {{
                  $t("globals.export.modal_ready_label", {
                    format: selectedFormat?.name,
                  })
                }}
              </div>
            </div>
          </template>

          <template v-else-if="phase === 'error'">
            <div class="pdf-modal__error">
              <div class="pdf-modal__error__icon">
                <i class="fa-solid fa-xmark"></i>
              </div>
              <div class="pdf-modal__error__label">
                {{
                  $t("globals.export.modal_error_label", {
                    format: selectedFormat?.name,
                  })
                }}
              </div>
            </div>
          </template>
        </div>

        <div class="deployment-card__footer">
          <div class="deployment-card__footer__content">

            <div v-if="phase === 'generating'" class="deployment-message">
              <div class="deployment-message__dot"></div>
              <span class="deployment-message__text">{{
                $t("globals.export.modal_generating_msg")
              }}</span>
            </div>

            <template v-else-if="phase === 'ready'">
              <div class="deployment-success">
                <div class="deployment-success__message">
                  <i class="fa-solid fa-check"></i>
                  <span>{{ $t("globals.export.modal_download_started") }}</span>
                </div>
                <div style="display: flex; gap: 10px">
                  <button
                    class="deployment-card__button"
                    @click="triggerDownload"
                  >
                    <i
                      class="fa-solid fa-download"
                      style="margin-right: 6px"
                    ></i>
                    {{ $t("globals.export.modal_download_again") }}
                  </button>
                  <button
                    class="deployment-card__button deployment-card__button--secondary"
                    @click="close"
                  >
                    {{ $t("globals.export.modal_close") }}
                  </button>
                </div>
              </div>
            </template>

            <template v-else-if="phase === 'error'">
              <div class="deployment-failed">
                <div class="deployment-failed__actions">
                  <button
                    class="deployment-card__button deployment-card__button--retry"
                    @click="retry"
                  >
                    <i
                      class="fa-solid fa-rotate-right"
                      style="margin-right: 6px"
                    ></i>
                    {{ $t("globals.export.modal_retry") }}
                  </button>
                  <button
                    class="deployment-card__button deployment-card__button--secondary"
                    @click="phase = 'select'"
                  >
                    {{ $t("globals.export.modal_choose_format") }}
                  </button>
                  <button
                    class="deployment-card__button deployment-card__button--secondary"
                    @click="close"
                  >
                    {{ $t("globals.export.modal_cancel") }}
                  </button>
                </div>
              </div>
            </template>

            <template v-else>
              <span style="font-size: 13px; color: var(--color-text-faint)">
                {{ $t("globals.export.modal_format_hint") }}
              </span>
            </template>
          </div>
        </div>
      </div>

      <button class="pdf-modal__close" @click="cancel" :disabled="!canClose">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
.export-modal__format-description {
  font-size: 12px;
  color: var(--color-text-faint);
  font-weight: normal;
}
</style>
