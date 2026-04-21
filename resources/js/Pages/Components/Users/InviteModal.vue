<script setup>
import { computed, ref, getCurrentInstance } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
const emit = defineEmits(["close", "complete"]);
const props = defineProps({
  module: Object,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

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
    errorMessage.value = t("modules.users.modal.email_invalid_error");
    return;
  }

  // Check if email already exists in the objects array
  if (emails.value.some((e) => e.email === email)) {
    errorMessage.value = t("modules.users.modal.email_duplicate_error");
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
    errorMessage.value = t("modules.users.modal.no_email_error");
    return;
  }

  status.value = "sending";
  errorMessage.value = "";

  try {
    const response = await axios.post("/invites/bulk", {
      invites: emails.value,
    });

    status.value = "success";
    emit("complete", emails.value);
  } catch (error) {
    status.value = "error";
    console.error(error);
    errorMessage.value =
      error.response?.data?.message ?? t("modules.users.modal.generale_error");
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
            <div class="sending-loader"></div>
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
          <div v-if="emails.length === 1">
            <h3 class="invite-success-title">
              {{ $t("modules.users.modal.invitation_sent") }}
            </h3>
            <p class="invite-success-desc">
              {{
                $t("modules.users.modal.invitation_sent_desc", {
                  total: emails.length,
                })
              }}
            </p>
          </div>
          <div v-else>
            <h3 class="invite-success-title">
              {{ $t("modules.users.modal.invitation_sent_plural") }}
            </h3>
            <p class="invite-success-desc">
              {{
                $t("modules.users.modal.invitation_sent_desc_plural", {
                  total: emails.length,
                })
              }}
            </p>
          </div>
        </div>

        <div class="invite-card__footer" v-if="status !== 'success'">
          <div class="invite-card__footer__content">
            <span
              class="invite-count-text"
              v-if="status === 'idle' && emails.length > 0"
            >
              <span v-if="emails.length === 1">
                {{
                  $t("modules.users.modal.total_to_invite", {
                    total: emails.length,
                  })
                }}
              </span>

              <span v-else>
                {{
                  $t("modules.users.modal.total_to_invite_plural", {
                    total: emails.length,
                  })
                }}
              </span>
            </span>
            <span v-else></span>

            <div class="invite-actions">
              <button
                v-if="status === 'idle'"
                @click="closeModal"
                class="invite-card__button invite-card__button--secondary"
              >
                {{ $t("modules.users.modal.cancel") }}
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
