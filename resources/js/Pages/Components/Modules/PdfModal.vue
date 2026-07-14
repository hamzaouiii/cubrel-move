<script setup>
import { ref, computed, onMounted, getCurrentInstance } from "vue";
import axios from "axios";

const props = defineProps({
  moduleSlug: { type: String, required: true },
  recordId: { type: String, required: true },
  templates: { type: Array, default: () => [] },
  recordName: { type: String, default: "" },
});

const emit = defineEmits(["close"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;

// ── State ────────────────────────────────────────────────────────────────────
// phases: select | generating | ready | error
const phase = ref("select");
const selectedTemplate = ref(null);
const blobUrl = ref(null);
const errorMessage = ref("");
const downloadFilename = ref("");

// ── Computed ─────────────────────────────────────────────────────────────────
const defaultTemplate = computed(
  () => props.templates.find((t) => t.is_default) ?? props.templates[0] ?? null,
);

const canClose = computed(() => phase.value !== "generating");

// ── Actions ───────────────────────────────────────────────────────────────────
const generate = async (template) => {
  selectedTemplate.value = template;
  phase.value = "generating";
  blobUrl.value = null;
  errorMessage.value = "";

  const url = `/${props.moduleSlug}/${props.recordId}/pdf?template=${template.id}`;
  downloadFilename.value =
    `${props.moduleSlug}-${props.recordName || props.recordId}.pdf`
      .toLowerCase()
      .replace(/\s+/g, "-");

  try {
    const response = await axios.get(url, { responseType: "blob" });
    blobUrl.value = URL.createObjectURL(response.data);
    phase.value = "ready";
    triggerDownload();
  } catch (err) {
    errorMessage.value =
      err.response?.data?.message ||
      err.message ||
      "An unexpected error occurred while generating the PDF.";
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
  if (selectedTemplate.value) {
    generate(selectedTemplate.value);
  } else {
    phase.value = "select";
  }
};

const close = () => {
  if (!canClose.value) return;
  if (blobUrl.value) URL.revokeObjectURL(blobUrl.value);
  emit("close");
};

// ── Mount ─────────────────────────────────────────────────────────────────────
onMounted(() => {
  if (props.templates.length === 1) {
    generate(props.templates[0]);
  } else if (defaultTemplate.value) {
    // Stay on select phase but pre-highlight default
  }
});
</script>

<template>
  <div class="pdf-modal">
    <div class="pdf-modal__backdrop" @click="close"></div>

    <div class="pdf-modal__container">
      <div class="deployment-card">
        <!-- ── Header ─────────────────────────────────────────────────────── -->
        <div class="deployment-card__header">
          <div class="deployment-card__title-group">
            <h3 class="deployment-card__title">
              <template v-if="phase === 'select'">{{
                $t("globals.pdf_templates.modal_title_select")
              }}</template>
              <template v-else-if="phase === 'generating'">{{
                $t("globals.pdf_templates.modal_title_generating")
              }}</template>
              <template v-else-if="phase === 'ready'">{{
                $t("globals.pdf_templates.modal_title_ready")
              }}</template>
              <template v-else>{{
                $t("globals.pdf_templates.modal_title_error")
              }}</template>
            </h3>

            <p class="deployment-card__subtitle" v-if="phase === 'select'">
              {{ $t("globals.pdf_templates.modal_sub_select") }}
            </p>
            <p
              class="deployment-card__subtitle"
              v-else-if="phase === 'generating'"
            >
              {{ $t("globals.pdf_templates.modal_sub_generating") }}
            </p>
            <p
              class="deployment-card__subtitle deployment-card__subtitle--success"
              v-else-if="phase === 'ready'"
            >
              {{ $t("globals.pdf_templates.modal_sub_ready") }}
            </p>
            <p
              class="deployment-card__subtitle deployment-card__subtitle--danger"
              v-else-if="phase === 'error'"
            >
              {{ errorMessage }}
            </p>
          </div>

          <!-- progress bar while generating -->
          <div class="pdf-modal__generating-bar" v-if="phase === 'generating'">
            <div class="pdf-modal__generating-bar__track">
              <div class="pdf-modal__generating-bar__fill"></div>
            </div>
          </div>
        </div>

        <!-- ── Body ───────────────────────────────────────────────────────── -->
        <div class="pdf-modal__body">
          <!-- Select phase -->
          <template v-if="phase === 'select'">
            <div
              v-for="t in templates"
              :key="t.id"
              class="pdf-template-row"
              :class="{ 'pdf-template-row--default': t.is_default }"
              @click="generate(t)"
            >
              <div class="pdf-template-row__icon">
                <i class="fa-solid fa-file-pdf"></i>
              </div>

              <div class="pdf-template-row__info">
                <span class="pdf-template-row__name">{{ t.name }}</span>
                <span v-if="t.is_default" class="pdf-template-row__badge">{{
                  $t("globals.pdf_templates.default_badge")
                }}</span>
              </div>

              <div class="pdf-template-row__action">
                <i class="fa-solid fa-chevron-right"></i>
              </div>
            </div>
          </template>

          <!-- Generating phase -->
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
                  $t("globals.pdf_templates.modal_building", {
                    name: selectedTemplate?.name,
                  })
                }}
              </div>
            </div>
          </template>

          <!-- Ready phase -->
          <template v-else-if="phase === 'ready'">
            <div class="pdf-modal__ready">
              <div class="pdf-modal__ready__icon">
                <i class="fa-solid fa-check"></i>
              </div>
              <div class="pdf-modal__ready__label">
                {{
                  $t("globals.pdf_templates.modal_ready_label", {
                    name: selectedTemplate?.name,
                  })
                }}
              </div>
            </div>
          </template>

          <!-- Error phase -->
          <template v-else-if="phase === 'error'">
            <div class="pdf-modal__error">
              <div class="pdf-modal__error__icon">
                <i class="fa-solid fa-xmark"></i>
              </div>
              <div class="pdf-modal__error__label">
                {{
                  $t("globals.pdf_templates.modal_error_label", {
                    name: selectedTemplate?.name,
                  })
                }}
              </div>
            </div>
          </template>
        </div>

        <!-- ── Footer ─────────────────────────────────────────────────────── -->
        <div class="deployment-card__footer">
          <div class="deployment-card__footer__content">
            <!-- Generating footer -->
            <div v-if="phase === 'generating'" class="deployment-message">
              <div class="deployment-message__dot"></div>
              <span class="deployment-message__text">{{
                $t("globals.pdf_templates.modal_generating_msg")
              }}</span>
            </div>

            <!-- Ready footer -->
            <template v-else-if="phase === 'ready'">
              <div class="deployment-success">
                <div class="deployment-success__message">
                  <i class="fa-solid fa-check"></i>
                  <span>{{
                    $t("globals.pdf_templates.modal_download_started")
                  }}</span>
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
                    {{ $t("globals.pdf_templates.modal_download_again") }}
                  </button>
                  <button
                    class="deployment-card__button deployment-card__button--secondary"
                    @click="close"
                  >
                    {{ $t("globals.pdf_templates.modal_close") }}
                  </button>
                </div>
              </div>
            </template>

            <!-- Error footer -->
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
                    {{ $t("globals.pdf_templates.modal_retry") }}
                  </button>
                  <button
                    v-if="templates.length > 1"
                    class="deployment-card__button deployment-card__button--secondary"
                    @click="phase = 'select'"
                  >
                    {{ $t("globals.pdf_templates.modal_choose_template") }}
                  </button>
                  <button
                    class="deployment-card__button deployment-card__button--secondary"
                    @click="close"
                  >
                    {{ $t("globals.pdf_templates.modal_cancel") }}
                  </button>
                </div>
              </div>
            </template>

            <!-- Select footer -->
            <template v-else>
              <span style="font-size: 13px; color: #9ca3af">
                {{
                  templates.length !== 1
                    ? $t("globals.pdf_templates.modal_count_many", {
                        count: templates.length,
                      })
                    : $t("globals.pdf_templates.modal_count_one", {
                        count: templates.length,
                      })
                }}
              </span>
            </template>
          </div>
        </div>
      </div>

      <!-- Close button -->
      <button class="pdf-modal__close" @click="close" :disabled="!canClose">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>
</template>
