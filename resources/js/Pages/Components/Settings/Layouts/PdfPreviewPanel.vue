<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick, getCurrentInstance } from "vue";

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
  visible: Boolean,
  sections: Array,
  moduleSlug: String,
  moduleLabel: String,
});

const emit = defineEmits(["close"]);

const loading = ref(false);
const htmlContent = ref("");
const fetchError = ref("");
const bodyRef = ref(null);
const bodyWidth = ref(852);

// A4 width at 96 dpi; height is measured after the iframe loads
const A4_W = 794;
const frameH = ref(1200); // initial estimate, replaced on load

const scale = computed(() => Math.min(1, (bodyWidth.value - 48) / A4_W));
const wrapperW = computed(() => Math.round(A4_W * scale.value));
const wrapperH = computed(() => Math.round(frameH.value * scale.value));

function onIframeLoad(event) {
  try {
    const doc = event.target.contentDocument;
    if (doc?.documentElement) {
      frameH.value = doc.documentElement.scrollHeight || doc.body?.scrollHeight || 1200;
    }
  } catch {
    // srcdoc is same-origin, this won't throw
  }
}

function measureBody() {
  if (bodyRef.value) bodyWidth.value = bodyRef.value.clientWidth;
}

const refresh = async () => {
  if (!props.moduleSlug || loading.value) return;
  loading.value = true;
  fetchError.value = "";

  try {
    const res = await window.axios.post(
      "/settings/pdf-templates/preview",
      {
        module_slug: props.moduleSlug,
        definition: { sections: props.sections ?? [] },
      },
      { headers: { Accept: "text/html" }, responseType: "text" },
    );
    frameH.value = 1200; // reset before load event measures actual height
    htmlContent.value = res.data;
  } catch {
    fetchError.value = t("layouts.pdf_preview_error");
  } finally {
    loading.value = false;
  }
};

watch(
  () => props.visible,
  async (val) => {
    if (!val) return;
    document.body.style.overflow = "hidden";
    await nextTick();
    measureBody();
    if (props.moduleSlug) refresh();
  },
);

watch(
  () => props.visible,
  (val) => {
    if (!val) document.body.style.overflow = "";
  },
);

let ro = null;
onMounted(() => {
  if (typeof ResizeObserver !== "undefined") {
    ro = new ResizeObserver(measureBody);
    if (bodyRef.value) ro.observe(bodyRef.value);
  }
});
onUnmounted(() => {
  ro?.disconnect();
  document.body.style.overflow = "";
});
</script>

<template>
  <Teleport to="body">
    <Transition name="preview-backdrop">
      <div v-if="visible" class="preview-modal">
        <!-- Backdrop -->
        <div class="preview-modal__backdrop" @click="$emit('close')" />

        <!-- Dialog container -->
        <div class="preview-modal__container">
          <!-- Close button (above card, matching PdfModal style) -->
          <button class="preview-modal__close-btn" @click="$emit('close')">
            <svg
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
            >
              <path
                d="M18 6L6 18M6 6L18 18"
                stroke-width="2"
                stroke-linecap="round"
              />
            </svg>
          </button>

          <!-- Card -->
          <div class="preview-card">
            <!-- Header -->
            <div class="preview-card__header">
              <div class="preview-card__header-left">
                <i class="fa-solid fa-eye" style="color: #9ca3af"></i>
                <span class="preview-card__title">{{ $t('layouts.pdf_preview_title') }}</span>
                <span v-if="moduleSlug" class="preview-card__badge">{{
                  moduleLabel || moduleSlug
                }}</span>
              </div>
              <button
                class="preview-card__refresh"
                :disabled="loading || !moduleSlug"
                @click="refresh"
              >
                <i
                  class="fa-solid fa-rotate-right"
                  :style="
                    loading
                      ? { animation: 'preview-spin 0.8s linear infinite' }
                      : {}
                  "
                ></i>
                {{ $t('layouts.pdf_preview_refresh') }}
              </button>
            </div>

            <!-- Body -->
            <div ref="bodyRef" class="preview-card__body">
              <!-- No module -->
              <div v-if="!moduleSlug" class="preview-empty">
                <i
                  class="fa-solid fa-file-pdf"
                  style="font-size: 36px; color: #d1d5db"
                ></i>
                <p>{{ $t('layouts.pdf_preview_select_module') }}</p>
              </div>

              <!-- Loading skeleton -->
              <div v-else-if="loading" class="preview-skeleton">
                <!-- Header row -->
                <div class="skel-hdr">
                  <div>
                    <div
                      class="skel"
                      style="
                        width: 44px;
                        height: 44px;
                        border-radius: 8px;
                        margin-bottom: 8px;
                      "
                    ></div>
                    <div
                      class="skel"
                      style="
                        width: 130px;
                        height: 14px;
                        border-radius: 4px;
                        margin-bottom: 5px;
                      "
                    ></div>
                    <div
                      class="skel"
                      style="
                        width: 95px;
                        height: 10px;
                        border-radius: 4px;
                        margin-bottom: 4px;
                      "
                    ></div>
                    <div
                      class="skel"
                      style="width: 110px; height: 10px; border-radius: 4px"
                    ></div>
                  </div>
                  <div style="text-align: right">
                    <div
                      class="skel"
                      style="
                        width: 150px;
                        height: 28px;
                        border-radius: 4px;
                        margin-bottom: 6px;
                        margin-left: auto;
                      "
                    ></div>
                    <div
                      class="skel"
                      style="
                        width: 85px;
                        height: 11px;
                        border-radius: 4px;
                        margin-left: auto;
                        margin-bottom: 4px;
                      "
                    ></div>
                    <div
                      class="skel"
                      style="
                        width: 110px;
                        height: 11px;
                        border-radius: 4px;
                        margin-left: auto;
                      "
                    ></div>
                  </div>
                </div>
                <!-- Fields -->
                <div class="skel-fields" v-for="r in 2" :key="r">
                  <div v-for="c in 4" :key="c" style="flex: 1">
                    <div
                      class="skel"
                      style="
                        width: 55px;
                        height: 9px;
                        border-radius: 3px;
                        margin-bottom: 5px;
                      "
                    ></div>
                    <div
                      class="skel"
                      style="width: 100%; height: 13px; border-radius: 4px"
                    ></div>
                  </div>
                </div>
                <!-- Table header -->
                <div class="skel-tblhdr">
                  <div
                    class="skel"
                    style="
                      width: 30px;
                      height: 10px;
                      border-radius: 3px;
                      flex-shrink: 0;
                    "
                  ></div>
                  <div
                    class="skel"
                    style="flex: 1; height: 10px; border-radius: 3px"
                  ></div>
                  <div
                    class="skel"
                    style="width: 60px; height: 10px; border-radius: 3px"
                  ></div>
                  <div
                    class="skel"
                    style="width: 75px; height: 10px; border-radius: 3px"
                  ></div>
                </div>
                <!-- Table rows -->
                <div class="skel-tblrow" v-for="i in 3" :key="i">
                  <div
                    class="skel"
                    style="
                      width: 30px;
                      height: 10px;
                      border-radius: 3px;
                      flex-shrink: 0;
                    "
                  ></div>
                  <div
                    class="skel"
                    style="flex: 1; height: 10px; border-radius: 3px"
                  ></div>
                  <div
                    class="skel"
                    style="width: 60px; height: 10px; border-radius: 3px"
                  ></div>
                  <div
                    class="skel"
                    style="width: 75px; height: 10px; border-radius: 3px"
                  ></div>
                </div>
                <!-- Totals -->
                <div class="skel-totals">
                  <div v-for="i in 3" :key="i" class="skel-total-row">
                    <div
                      class="skel"
                      style="width: 70px; height: 10px; border-radius: 3px"
                    ></div>
                    <div
                      class="skel"
                      style="width: 65px; height: 10px; border-radius: 3px"
                    ></div>
                  </div>
                  <div class="skel-grand">
                    <div
                      class="skel"
                      style="width: 45px; height: 14px; border-radius: 3px"
                    ></div>
                    <div
                      class="skel"
                      style="width: 80px; height: 14px; border-radius: 3px"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- Error -->
              <div v-else-if="fetchError" class="preview-error">
                <i
                  class="fa-solid fa-circle-exclamation"
                  style="font-size: 28px; color: #ef4444"
                ></i>
                <p>{{ fetchError }}</p>
                <button class="preview-retry-btn" @click="refresh">
                  {{ $t('layouts.pdf_preview_retry') }}
                </button>
              </div>

              <!-- Rendered preview -->
              <div
                v-else-if="htmlContent"
                class="preview-scaler-wrap"
                :style="{ width: wrapperW + 'px', height: wrapperH + 'px' }"
              >
                <iframe
                  :srcdoc="htmlContent"
                  scrolling="no"
                  class="preview-iframe"
                  :style="{
                    width: A4_W + 'px',
                    height: frameH + 'px',
                    transform: `scale(${scale})`,
                  }"
                  @load="onIframeLoad"
                />
              </div>
            </div>
            <!-- /body -->
          </div>
          <!-- /card -->
        </div>
        <!-- /container -->
      </div>
      <!-- /modal -->
    </Transition>
  </Teleport>
</template>

<style lang="scss" scoped>
.preview-modal {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  font-family: "Fira Sans", "Heebo", sans-serif;

  &__backdrop {
    position: absolute;
    inset: 0;
    background: radial-gradient(
      circle at 20% 50%,
      rgba(99, 102, 241, 0.08),
      rgba(15, 23, 42, 0.55)
    );
    backdrop-filter: blur(8px);
    cursor: pointer;
  }

  &__container {
    position: relative;
    width: 92vw;
    max-width: 920px;
    height: 90vh;
    z-index: 1020;
    display: flex;
    flex-direction: column;
    animation: modal-slide-up 0.3s ease-out;
  }

  &__close-btn {
    position: absolute;
    top: -48px;
    right: 0;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    cursor: pointer;
    transition:
      background 0.2s,
      transform 0.2s;

    &:hover {
      background: rgba(255, 255, 255, 0.22);
      transform: rotate(90deg);
    }
  }
}

.preview-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;

  &__header {
    padding: 18px 24px;
    background: linear-gradient(135deg, #f9fafb 0%, #fff 100%);
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    gap: 12px;
  }

  &__header-left {
    display: flex;
    align-items: center;
    gap: 9px;
  }

  &__title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
  }

  &__badge {
    font-size: 11px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 99px;
  }

  &__refresh {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 500;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    background: #fff;
    color: #374151;
    cursor: pointer;
    transition:
      background 0.15s,
      border-color 0.15s;
    flex-shrink: 0;

    &:hover:not(:disabled) {
      background: #f9fafb;
      border-color: #d1d5db;
    }

    &:disabled {
      opacity: 0.45;
      cursor: not-allowed;
    }
  }

  &__body {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 0;
  }
}

/* ── Scaled iframe wrapper ─────────────────────────────────────── */
.preview-scaler-wrap {
  position: relative;
  overflow: hidden;
  border-radius: 3px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
}

.preview-iframe {
  border: none;
  display: block;
  transform-origin: top left;
}

/* ── Empty / error states ──────────────────────────────────────── */
.preview-empty,
.preview-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #9ca3af;
  font-size: 13px;
  text-align: center;
  padding: 60px 32px;
}

.preview-error {
  color: #374151;
}

.preview-retry-btn {
  padding: 7px 18px;
  border: 1px solid #fecaca;
  border-radius: 7px;
  background: #fff5f5;
  color: #dc2626;
  font-size: 13px;
  cursor: pointer;
  margin-top: 4px;
}

/* ── Loading skeleton ──────────────────────────────────────────── */
.preview-skeleton {
  background: #fff;
  border-radius: 4px;
  padding: 32px 36px;
  width: 100%;
  max-width: 794px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
}

.skel-hdr {
  display: flex;
  justify-content: space-between;
  margin-bottom: 28px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f3f4f6;
}

.skel-fields {
  display: flex;
  gap: 24px;
  margin-bottom: 20px;
}

.skel-tblhdr {
  display: flex;
  gap: 12px;
  padding: 10px 0;
  border-top: 1.5px solid #d1d5db;
  border-bottom: 1.5px solid #d1d5db;
  margin-bottom: 4px;
  margin-top: 12px;
}

.skel-tblrow {
  display: flex;
  gap: 12px;
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.skel-totals {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  margin-top: 16px;
  gap: 7px;
  width: 200px;
  margin-left: auto;
}

.skel-total-row {
  display: flex;
  justify-content: space-between;
  width: 100%;
}

.skel-grand {
  display: flex;
  justify-content: space-between;
  width: 100%;
  padding-top: 8px;
  border-top: 1.5px solid #111;
}

.skel {
  background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
  background-size: 200% 100%;
  animation: skel-shimmer 1.4s infinite;
}

/* ── Animations ────────────────────────────────────────────────── */
@keyframes skel-shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

@keyframes preview-spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes modal-slide-up {
  from {
    opacity: 0;
    transform: translateY(18px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ── Transition ────────────────────────────────────────────────── */
.preview-backdrop-enter-active {
  animation: backdrop-in 0.25s ease-out;
}
.preview-backdrop-leave-active {
  animation: backdrop-in 0.2s ease-in reverse;
}

@keyframes backdrop-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
