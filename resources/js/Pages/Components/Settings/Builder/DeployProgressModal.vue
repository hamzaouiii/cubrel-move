<script setup>
import { ref, onMounted, computed, getCurrentInstance } from "vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import { useConfirm } from "@/Composables/useConfirm";

const { confirm } = useConfirm();

const props = defineProps({
  moduleId: {
    type: [String, Number],
    required: true,
  },
  formData: {
    type: Object,
    required: true,
  },
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const emit = defineEmits(["close", "complete"]);

const steps = ref([
  {
    id: "initialize",
    endpoint: "initialize",
    label: t("settings.modulebuilder.steps.initialize.label"),
    description: t("settings.modulebuilder.steps.initialize.desc"),
    status: "pending",
  },
  {
    id: "files",
    endpoint: "generate-files",
    label: t("settings.modulebuilder.steps.files.label"),
    description: t("settings.modulebuilder.steps.files.desc"),
    status: "pending",
  },
  {
    id: "labels",
    endpoint: "create-labels",
    label: t("settings.modulebuilder.steps.labels.label"),
    description: t("settings.modulebuilder.steps.labels.desc"),
    status: "pending",
  },
  {
    id: "table",
    endpoint: "create-table",
    label: t("settings.modulebuilder.steps.table.label"),
    description: t("settings.modulebuilder.steps.table.desc"),
    status: "pending",
  },
  {
    id: "fields",
    endpoint: "activate-fields",
    label: t("settings.modulebuilder.steps.fields.label"),
    description: t("settings.modulebuilder.steps.fields.desc"),
    status: "pending",
  },
]);

const isDeploying = ref(false);
const isComplete = ref(false);
const hasFailed = ref(false);
const failedStepIndex = ref(-1);
const currentStepIndex = ref(-1);
const errorMessage = ref("");
const isRollingBack = ref(false);

const startDeploy = async () => {
  isDeploying.value = true;
  hasFailed.value = false;
  failedStepIndex.value = -1;
  errorMessage.value = "";

  const baseUrl = `/settings/modulebuilder/${props.moduleId}/deploy`;

  for (let i = 0; i < steps.value.length; i++) {
    currentStepIndex.value = i;
    const step = steps.value[i];
    step.status = "running";

    try {
      const payload = i === 0 ? props.formData : {};
      await axios.post(`${baseUrl}/${step.endpoint}`, payload);
      step.status = "success";
    } catch (error) {
      step.status = "failed";
      hasFailed.value = true;
      failedStepIndex.value = i;

      const serverMessage =
        error.response?.data?.message ||
        t("settings.modulebuilder.errors.unexpected");
      // Formats as "Step Label: Error Message"
      errorMessage.value = `${step.label.replace("...", "")}: ${serverMessage}`;

      currentStepIndex.value = -1;
      isDeploying.value = false;
      emit("complete", { success: false, error: errorMessage.value });
      return;
    }
  }

  currentStepIndex.value = -1;
  isDeploying.value = false;
  isComplete.value = true;
};

const retryDeployment = () => {
  steps.value.forEach((step) => {
    step.status = "pending";
  });
  isComplete.value = false;
  hasFailed.value = false;
  failedStepIndex.value = -1;
  errorMessage.value = "";
  startDeploy();
};

const abortAndCleanup = async () => {
  const ok = await confirm({
    title: t("settings.modulebuilder.confirm_rollback"),
    message: t("settings.modulebuilder.rollback_message"),
    confirmText: t("modules.actions.delete_yes"),
    cancelText: t("modules.actions.delete_no"),
    danger: true,
  });

  if (!ok) return;

  isRollingBack.value = true;
  try {
    await axios.post(
      `/settings/modulebuilder/${props.moduleId}/deploy/rollback`,
    );
    emit("close");
  } catch (error) {
    errorMessage.value = t("settings.modulebuilder.errors.cleanup_failed");
    console.error(error);
  } finally {
    isRollingBack.value = false;
  }
};

const moduleUrl = computed(() => `/settings/modules/${props.moduleId}`);

onMounted(() => {
  startDeploy();
});
</script>
<template>
  <div class="deployment-modal">
    <!-- Backdrop with animated gradient -->
    <div class="deployment-modal__backdrop"></div>

    <!-- Modal Container -->
    <div class="deployment-modal__container">
      <!-- Progress Card -->
      <div class="deployment-card">
        <!-- Header with animated gradient line -->
        <div class="deployment-card__header">
          <div class="deployment-card__title-group">
            <h3 class="deployment-card__title">
              <template v-if="hasFailed">
                {{
                  $t("settings.modulebuilder.title.failed") ||
                  "Deployment Failed"
                }}
              </template>
              <template v-else-if="isComplete">
                {{ $t("settings.modulebuilder.title.complete") }}
              </template>
              <template v-else>
                {{ $t("settings.modulebuilder.title.deploying") }}
              </template>
            </h3>
            <p
              class="deployment-card__subtitle"
              v-if="!isComplete && !hasFailed"
            >
              {{ $t("settings.modulebuilder.subtitle.wait") }}
            </p>
            <p
              class="deployment-card__subtitle deployment-card__subtitle--success"
              v-else-if="isComplete"
            >
              {{ $t("settings.modulebuilder.subtitle.success") }}
            </p>
            <p
              class="deployment-card__subtitle deployment-card__subtitle--danger"
              v-else-if="hasFailed"
            >
              {{ errorMessage }}
            </p>
          </div>

          <!-- Overall progress indicator -->
          <div
            class="deployment-card__progress"
            v-if="!isComplete && !hasFailed"
          >
            <div class="progress-bar">
              <div
                class="progress-bar__fill"
                :style="{
                  width: `${(steps.filter((s) => s.status === 'success').length / steps.length) * 100}%`,
                }"
              ></div>
            </div>

            <span class="progress-text">
              {{ steps.filter((s) => s.status === "success").length }}/{{
                steps.length
              }}
              {{ $t("settings.modulebuilder.steps_completed") }}
            </span>
          </div>
        </div>

        <!-- Steps List -->
        <div class="deployment-steps">
          <div
            v-for="(step, index) in steps"
            :key="step.id"
            class="deployment-step"
            :class="[
              step.status,
              {
                current: index === currentStepIndex,
                failed: step.status === 'failed',
              },
            ]"
          >
            <!-- Step connector line (except for last step) -->
            <div
              v-if="index < steps.length - 1"
              class="deployment-step__connector"
              :class="{
                active: index < currentStepIndex,
                failed: hasFailed && index < failedStepIndex,
              }"
            ></div>

            <div class="deployment-step__content">
              <!-- Step icon with status -->
              <div class="deployment-step__icon-wrapper">
                <div class="deployment-step__icon">
                  <template v-if="step.status === 'pending'">
                    <svg
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                    >
                      <circle cx="12" cy="12" r="10" stroke-width="2" />
                    </svg>
                  </template>
                  <template v-else-if="step.status === 'running'">
                    <svg
                      class="deployment-step__spinner"
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                    >
                      <circle
                        cx="12"
                        cy="12"
                        r="10"
                        stroke-width="2"
                        stroke-dasharray="31.4 31.4"
                      />
                    </svg>
                  </template>
                  <template v-else-if="step.status === 'success'">
                    <svg
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                    >
                      <path
                        d="M20 6L9 17L4 12"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </template>
                  <template v-else-if="step.status === 'failed'">
                    <svg
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                    >
                      <path
                        d="M18 6L6 18M6 6L18 18"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </template>
                </div>
              </div>

              <!-- Step information -->
              <div class="deployment-step__info">
                <div class="deployment-step__label">{{ step.label }}</div>
                <div class="deployment-step__description">
                  {{ step.description }}
                </div>
              </div>

              <!-- Status badge -->
              <div class="deployment-step__badge" :class="step.status">
                <template v-if="step.status === 'pending'">
                  {{ $t("settings.modulebuilder.status.pending") }}
                </template>
                <template v-else-if="step.status === 'running'">
                  {{ $t("settings.modulebuilder.status.in_progress") }}
                </template>
                <template v-else-if="step.status === 'success'">
                  {{ $t("settings.modulebuilder.status.completed") }}
                </template>
                <template v-else-if="step.status === 'failed'">
                  {{ $t("settings.modulebuilder.status.failed") || "Failed" }}
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions Footer -->
        <div class="deployment-card__footer">
          <div class="deployment-card__footer-content">
            <div v-if="!isComplete && !hasFailed" class="deployment-message">
              <div class="deployment-message__dot"></div>
              <span class="deployment-message__text">
                {{
                  steps[currentStepIndex]?.label ||
                  $t("settings.modulebuilder.preparing")
                }}
              </span>
            </div>

            <div v-else-if="hasFailed" class="deployment-failed">
              <div class="deployment-failed__actions">
                <button
                  class="deployment-card__button deployment-card__button--retry"
                  @click="retryDeployment"
                  :disabled="isRollingBack"
                >
                  <i
                    class="fa-solid fa-rotate-right"
                    :class="{ 'fa-spin': isDeploying }"
                  ></i>
                  {{ $t("settings.modulebuilder.button.retry") || "Retry" }}
                </button>

                <button
                  class="deployment-card__button deployment-card__button--secondary"
                  @click="abortAndCleanup"
                  :disabled="isRollingBack"
                >
                  <i v-if="isRollingBack" class="fa-solid fa-atom fa-spin"></i>
                  <i v-else class="fa-solid fa-trash-can"></i>
                  {{
                    isRollingBack
                      ? "Cleaning..."
                      : $t("settings.modulebuilder.button.abort") ||
                        "Abort & Cleanup"
                  }}
                </button>
              </div>
            </div>

            <div v-else class="deployment-success">
              <div class="deployment-success__message">
                <svg
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="#10b981"
                >
                  <path
                    d="M20 6L9 17L4 12"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <span>{{ $t("settings.modulebuilder.success.message") }}</span>
              </div>
              <Link class="deployment-card__button" :href="moduleUrl">
                {{ $t("settings.modulebuilder.button.go_to_module") }}
                <i class="fa-solid fa-arrow-right"></i>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.deployment-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  font-family: "Fira Sans", "Heebo", sans-serif;

  &__backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(
      circle at 20% 50%,
      rgba(99, 102, 241, 0.1),
      rgba(15, 23, 42, 0.6)
    );
    backdrop-filter: blur(8px);
    animation: backdrop-fade 0.3s ease-out;
  }

  &__close {
    position: fixed;
    top: 2rem;
    right: 2rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 1010;
    backdrop-filter: blur(4px);

    &:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: rotate(90deg);
      border-color: rgba(255, 255, 255, 0.3);
    }
  }

  &__container {
    position: relative;
    width: 100%;
    max-width: 560px;
    margin: 0 24px;
    z-index: 1020;
    animation: modal-slide-up 0.4s ease-out;
  }
}

.deployment-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;

  &__header {
    padding: 32px 32px 24px;
    background: linear-gradient(135deg, #f9fafb 0%, white 100%);
    border-bottom: 1px solid #e5e7eb;
  }

  &__title-group {
    margin-bottom: 20px;
  }

  &__title {
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 600;
    color: #111827;
    letter-spacing: -0.02em;
  }

  &__subtitle {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;

    &--success {
      color: #10b981;
      font-weight: 500;
    }

    &--danger {
      color: var(--danger-color);
      font-weight: 500;
    }
  }

  &__progress {
    display: flex;
    align-items: center;
    gap: 16px;

    .progress-bar {
      flex: 1;
      height: 6px;
      background: #e5e7eb;
      border-radius: 3px;
      overflow: hidden;

      &__fill {
        height: 100%;
        background: linear-gradient(
          90deg,
          var(--module-color),
          color-mix(in srgb, var(--module-color) 80%, black)
        );
        border-radius: 3px;
        transition: width 0.3s ease;
      }
    }

    .progress-text {
      font-size: 13px;
      font-weight: 500;
      color: #6b7280;
      min-width: 100px;
      text-align: right;
    }
  }

  &__footer {
    padding: 20px 32px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;

    &__content {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
  }

  &__button {
    padding: 0.625rem 1.25rem;
    font-weight: 600;
    font-size: 0.9rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    border: 1px solid transparent;
    background: var(--module-color);
    color: #f3f4f6;

    &:hover {
      background: color-mix(in srgb, var(--module-color) 80%, black);
      transform: translateY(-1px);
    }

    &:active {
      transform: translateY(0);
    }

    i {
      transition: transform 0.2s ease;
    }

    &:hover i {
      transform: translateX(4px);
    }

    &--retry {
      background: var(--danger-color);

      &:hover {
        background: color-mix(in srgb, var(--danger-color) 80%, black);
      }

      i {
        margin-right: 8px;
      }

      &:hover i {
        transform: rotate(180deg);
      }
    }

    &--secondary {
      background: #6b7280;

      &:hover {
        background: #4b5563;
      }
    }
  }
}

.deployment-steps {
  padding: 24px 32px;
  background: white;
}

.deployment-step {
  position: relative;
  margin-bottom: 24px;

  &:last-child {
    margin-bottom: 0;

    .deployment-step__connector {
      display: none;
    }
  }

  &__connector {
    position: absolute;
    left: 20px;
    top: 40px;
    width: 2px;
    height: calc(100% - 16px);
    background: #e5e7eb;
    transition: background 0.3s ease;

    &.active {
      background: var(--module-color);
    }

    &.failed {
      background: var(--danger-color);
    }
  }

  &__content {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 12px 16px;
    background: white;
    border-radius: 16px;
    transition: all 0.3s ease;

    &:hover {
      background: #f9fafb;
    }
  }

  &.running &__content {
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.05), transparent);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
  }

  &.failed &__content {
    background: linear-gradient(90deg, rgba(239, 68, 68, 0.05), transparent);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);
  }

  &.success &__content {
    .deployment-step__label {
      color: #111827;
    }
  }

  &__icon-wrapper {
    flex-shrink: 0;
  }

  &__icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    border-radius: 50%;
    color: #9ca3af;
    transition: all 0.3s ease;

    .running & {
      background: rgba(99, 102, 241, 0.1);
      color: var(--module-color);
    }

    .failed & {
      background: rgba(239, 68, 68, 0.1);
      color: var(--danger-color);
    }

    .success & {
      background: color-mix(in srgb, #10b981 10%, white);
      color: #10b981;
    }
  }

  &__spinner {
    animation: spin 1s linear infinite;
    transform-origin: center;
  }

  &__info {
    flex: 1;
  }

  &__label {
    font-size: 15px;
    font-weight: 500;
    color: #4b5563;
    margin-bottom: 4px;
    transition: color 0.3s ease;

    .failed & {
      color: var(--danger-color);
    }
  }

  &__description {
    font-size: 13px;
    color: #9ca3af;
  }

  &__badge {
    padding: 4px 10px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 500;
    background: #f3f4f6;
    color: #6b7280;
    transition: all 0.3s ease;

    &.running {
      background: rgba(99, 102, 241, 0.1);
      color: var(--module-color);
    }

    &.failed {
      background: rgba(239, 68, 68, 0.1);
      color: var(--danger-color);
    }

    &.success {
      background: color-mix(in srgb, #10b981 10%, white);
      color: #10b981;
    }
  }
}

.deployment-message {
  display: flex;
  align-items: center;
  gap: 12px;

  &__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--module-color);
    animation: pulse 1.5s ease infinite;
  }

  &__text {
    font-size: 14px;
    color: #6b7280;
  }
}

.deployment-failed {
  flex: 1;

  &__message {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--danger-color);
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 16px;
  }

  &__actions {
    display: flex;
    gap: 12px;
  }
}

.deployment-success {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;

  &__message {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #10b981;
    font-size: 14px;
    font-weight: 500;
  }
}

// Animations
@keyframes backdrop-fade {
  from {
    opacity: 0;
    backdrop-filter: blur(0);
  }
  to {
    opacity: 1;
    backdrop-filter: blur(8px);
  }
}

@keyframes modal-slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.6;
    transform: scale(0.8);
  }
}

// Responsive adjustments
@media (max-width: 640px) {
  .deployment-modal__container {
    margin: 0 16px;
  }

  .deployment-card__header,
  .deployment-steps,
  .deployment-card__footer {
    padding: 24px 20px;
  }

  .deployment-step__badge {
    display: none;
  }

  .deployment-step__content {
    flex-wrap: wrap;
  }

  .deployment-step__info {
    width: calc(100% - 56px);
  }

  .deployment-card__footer__content {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }

  .deployment-success {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
    width: 100%;
  }

  .deployment-failed {
    width: 100%;

    &__actions {
      flex-direction: column;
      width: 100%;
    }
  }

  .deployment-card__button {
    width: 100%;
    justify-content: center;
  }
}
</style>
