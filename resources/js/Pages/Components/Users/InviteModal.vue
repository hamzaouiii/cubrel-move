<script setup>
import { computed, ref } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
const emit = defineEmits(["close", "complete"]);
const props = defineProps({
  module: Object,
});
const emailInput = ref("");
const emails = ref([]);
const status = ref("idle");
const errorMessage = ref("");

const closeModal = () => {
  emit("close");
};
const page = usePage();
const appSettings = page.props.appSettings;

const addEmail = () => {
  const email = emailInput.value.trim().toLowerCase();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!email) return;

  if (!emailRegex.test(email)) {
    errorMessage.value = "Please enter a valid email address.";
    return;
  }

  // Check if email already exists in the objects array
  if (emails.value.some((e) => e.email === email)) {
    errorMessage.value = "This email is already in the list.";
    return;
  }

  // Push as an object with default is_admin: false
  emails.value.push({
    email: email,
    is_admin: false,
  });

  emailInput.value = "";
  errorMessage.value = "";
};

const removeEmail = (emailToRemove) => {
  emails.value = emails.value.filter((e) => e.email !== emailToRemove);
};

const sendInvites = async () => {
  if (emails.value.length === 0) {
    errorMessage.value = "Please add at least one email address.";
    return;
  }

  status.value = "sending";
  errorMessage.value = "";

  try {
    const response = await axios.post("/invites/bulk", {
      invites: emails.value,
    });
    console.log(response);

    status.value = "success";

    setTimeout(() => {
      emit("complete", emails.value);
    }, 1500);
  } catch (error) {
    status.value = "error";
    console.log(error);
    errorMessage.value =
      error.response?.data?.message ??
      "Something went wrong. Please try again.";
  }
};

const color = computed(() => props.module.color);
</script>

<template>
  <div
    class="invite-modal"
    :style="{
      '--module-color': color,
      '--danger-color': appSettings.danger_color,
    }"
  >
    <div class="invite-modal__backdrop" @click="closeModal"></div>

    <button
      v-if="status !== 'sending'"
      class="invite-modal__close"
      @click="closeModal"
    >
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>

    <div class="invite-modal__container">
      <div class="invite-card">
        <div class="invite-card__header" v-if="status != 'success'">
          <div class="invite-card__title-group">
            <h2 class="invite-card__title">
              {{ $t("modules.users.modal.title") }}
            </h2>
            <p class="invite-card__subtitle">
              {{ $t("modules.users.modal.subtitle") }}
            </p>
          </div>

          <div v-if="status === 'sending'" class="invite-card__progress">
            <div class="progress-bar">
              <div class="progress-bar__fill"></div>
            </div>
            <div class="progress-text">
              {{ $t("modules.users.modal.progress_text") }}
            </div>
          </div>
        </div>

        <div
          class="invite-content"
          v-if="status === 'idle' || status === 'error'"
        >
          <div class="invite-input-group">
            <input
              v-model="emailInput"
              @keydown.enter.prevent="addEmail"
              type="email"
              placeholder="name@company.com"
              class="invite-input"
            />
            <button @click="addEmail" class="invite-btn-add">
              <i class="fa-solid fa-plus"></i>
            </button>
          </div>
          <p v-if="errorMessage" class="invite-error-text">
            {{ errorMessage }}
          </p>

          <div class="invite-list" v-if="emails.length > 0">
            <div v-for="item in emails" :key="item.email" class="invite-row">
              <div class="invite-row__info">
                <span class="invite-row__email">{{ item.email }}</span>
                <label class="invite-row__admin-toggle">
                  <Checkbox
                    v-model="item.is_admin"
                    :module-color="color"
                    mode="users-modal"
                  ></Checkbox>
                  <span class="admin-label-text">
                    {{ $t("modules.users.modal.admin_label_text") }}</span
                  >
                </label>
              </div>

              <button
                @click="removeEmail(item.email)"
                class="invite-row__remove"
                title="Remove email"
              >
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>
          </div>

          <div v-else class="invite-empty-state">
            {{ $t("modules.users.modal.invite_empty") }}
          </div>
        </div>

        <div
          class="invite-content invite-content--centered"
          v-if="status === 'success'"
        >
          <div class="invite-success-icon">
            <svg
              width="32"
              height="32"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </div>
          <h3 class="invite-success-title">Invitations Sent!</h3>
          <p class="invite-success-desc">
            Successfully sent {{ emails.length }} invitation{{
              emails.length > 1 ? "s" : ""
            }}.
          </p>
        </div>

        <div class="invite-card__footer" v-if="status !== 'success'">
          <div class="invite-card__footer__content">
            <span class="invite-count-text" v-if="status === 'idle'">
              {{
                $t("modules.users.modal.total_to_invite", {
                  total: emails.length,
                })
              }}
            </span>
            <span v-else></span>

            <div class="invite-actions">
              <button
                v-if="status === 'idle'"
                @click="closeModal"
                class="invite-card__button invite-card__button--secondary"
              >
                Cancel
              </button>
              <button
                @click="sendInvites"
                :disabled="status === 'sending' || emails.length === 0"
                class="invite-card__button"
                :class="{
                  'invite-card__button--disabled': emails.length === 0,
                }"
              >
                {{
                  status === "sending"
                    ? $t("modules.users.modal.progress_text")
                    : $t("modules.users.modal.send_invites")
                }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.invite-modal {
  --primary-color: var(--module-color);
  --primary-dark: color-mix(in srgb, var(--module-color) 80%, black);
  --danger-color: var(--danger-color);
  --text-main: #111827;
  --text-muted: #6b7280;
  --border-color: #e5e7eb;
  --bg-hover: #f9fafb;

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
    padding: 0;

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

.invite-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;

  &__header {
    padding: 32px 32px 24px;
    background: linear-gradient(135deg, var(--bg-hover) 0%, white 100%);
    border-bottom: 1px solid var(--border-color);
  }

  &__title-group {
    margin-bottom: 8px;
  }

  &__title {
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 600;
    color: var(--text-main);
    letter-spacing: -0.02em;
  }

  &__subtitle {
    margin: 0;
    color: var(--text-muted);
    font-size: 14px;
    line-height: 1.5;
  }

  &__progress {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 20px;

    .progress-bar {
      flex: 1;
      height: 6px;
      background: var(--border-color);
      border-radius: 3px;
      overflow: hidden;

      &__fill {
        height: 100%;
        width: 100%;
        background: linear-gradient(
          90deg,
          var(--primary-color),
          var(--primary-dark)
        );
        border-radius: 3px;
        transform-origin: left;
        animation: loading-bar 2s ease infinite;
      }
    }

    .progress-text {
      font-size: 13px;
      font-weight: 500;
      color: var(--text-muted);
      min-width: 60px;
      text-align: right;
    }
  }

  &__footer {
    padding: 20px 32px;
    background: var(--bg-hover);
    border-top: 1px solid var(--border-color);

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
    background: var(--primary-color);
    color: white;

    &:hover:not(:disabled) {
      background: var(--primary-dark);
      transform: translateY(-1px);
    }

    &:active:not(:disabled) {
      transform: translateY(0);
    }

    &--secondary {
      background: white;
      color: var(--text-main);
      border: 1px solid var(--border-color);

      &:hover:not(:disabled) {
        background: var(--bg-hover);
        border-color: #d1d5db;
      }
    }

    &--disabled {
      opacity: 0.5;
      cursor: not-allowed;
      &:hover {
        transform: none;
      }
    }
  }
}

.invite-content {
  padding: 24px 32px;
  background: white;
  min-height: 200px;

  &--centered {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 48px 32px;
  }
}

.invite-input-group {
  display: flex;
  gap: 12px;
  margin-bottom: 8px;

  .invite-input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 15px;
    color: var(--text-main);
    transition: all 0.2s ease;
    outline: none;

    &:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    &::placeholder {
      color: #9ca3af;
    }
  }

  .invite-btn-add {
    padding: 0 20px;
    background: var(--bg-hover);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-weight: 600;
    color: var(--text-main);
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
      background: #f3f4f6;
      border-color: #d1d5db;
    }
  }
}

.invite-error-text {
  margin: 0 0 16px 0;
  font-size: 13px;
  color: var(--danger-color);
}

/* New List Layout */
.invite-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 20px;
  max-height: 260px;
  overflow-y: auto;
  padding-right: 4px; // small buffer for scrollbar

  &::-webkit-scrollbar {
    width: 6px;
  }
  &::-webkit-scrollbar-track {
    background: transparent;
  }
  &::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
  }
}

.invite-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: white;
  border: 1px solid var(--border-color);
  border-radius: 10px;
  transition: all 0.2s ease;

  &:hover {
    border-color: #d1d5db;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
  }

  &__info {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  &__email {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-main);
  }

  &__admin-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;

    .admin-label-text {
      font-size: 13px;
      color: var(--text-muted);
      transition: color 0.2s;
    }

    &:hover .admin-label-text {
      color: var(--text-main);
    }
  }

  &__remove {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    transition: all 0.2s;

    &:hover {
      background: rgba(239, 68, 68, 0.1);
      color: var(--danger-color);
    }
  }
}

.invite-empty-state {
  margin-top: 20px;
  padding: 32px 0;
  text-align: center;
  font-size: 14px;
  color: #9ca3af;
  border: 2px dashed var(--border-color);
  border-radius: 12px;
}

.invite-actions {
  display: flex;
  gap: 12px;
}

.invite-count-text {
  font-size: 14px;
  color: var(--text-muted);
  font-weight: 500;
}

/* Success State Styles */
.invite-success-icon {
  width: 64px;
  height: 64px;
  background: color-mix(in srgb, #10b981 10%, white);
  color: #10b981;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  animation: modal-slide-up 0.4s ease-out;
}

.invite-success-title {
  font-size: 20px;
  font-weight: 600;
  color: var(--text-main);
  margin: 0 0 8px;
}

.invite-success-desc {
  color: var(--text-muted);
  font-size: 15px;
  margin: 0;
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

@keyframes loading-bar {
  0% {
    transform: scaleX(0.1);
    opacity: 0.8;
  }
  50% {
    transform: scaleX(0.5);
    opacity: 1;
  }
  100% {
    transform: scaleX(1);
    opacity: 0.8;
  }
}

// Responsive adjustments
@media (max-width: 640px) {
  .invite-modal__container {
    margin: 0 16px;
  }
  .invite-card__header,
  .invite-content,
  .invite-card__footer {
    padding: 24px 20px;
  }
  .invite-card__footer__content {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
    text-align: center;
  }
  .invite-actions {
    flex-direction: column;
    width: 100%;
  }
  .invite-card__button {
    width: 100%;
    justify-content: center;
  }
}
</style>
