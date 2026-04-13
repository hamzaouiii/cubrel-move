<script setup>
import { useForm } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  email: String,
  token: String,
});

const page = usePage();
const appSettings = page.props.appSettings;

const form = useForm({
  name: "joe",
  username: "joe",
  password: "12345678",
  password_confirmation: "12345678",
});

const submit = () => {
  form.post(`/invites/${props.token}/accept`, {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>

<template>
  <div class="accept-invite">
    <div class="accept-invite__backdrop"></div>

    <div class="accept-invite__container">
      <div class="accept-card">
        <div class="accept-card__header">
          <div class="accept-card__logo">
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
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </div>
          <h1 class="accept-card__title">Create your account</h1>
          <p class="accept-card__subtitle">
            You've been invited to join. Complete your profile to get started.
          </p>
          <div class="accept-card__email-badge">
            <i class="fa-solid fa-envelope"></i>
            {{ email }}
          </div>
        </div>

        <div class="accept-card__body">
          <div class="field" :class="{ 'field--error': form.errors.name }">
            <label class="field__label">Full Name</label>
            <input
              v-model="form.name"
              type="text"
              class="field__input"
              placeholder="John Doe"
              autocomplete="name"
            />
            <span v-if="form.errors.name" class="field__error">
              {{ form.errors.name }}
            </span>
          </div>

          <div class="field" :class="{ 'field--error': form.errors.username }">
            <label class="field__label">Username</label>
            <input
              v-model="form.username"
              type="text"
              class="field__input"
              placeholder="John_Doe_123"
              autocomplete="username"
            />
            <span v-if="form.errors.username" class="field__error">
              {{ form.errors.username }}
            </span>
          </div>

          <div class="field" :class="{ 'field--error': form.errors.password }">
            <label class="field__label">Password</label>
            <input
              v-model="form.password"
              type="password"
              class="field__input"
              placeholder="Min. 8 characters"
              autocomplete="new-password"
            />
            <span v-if="form.errors.password" class="field__error">
              {{ form.errors.password }}
            </span>
          </div>

          <div
            class="field"
            :class="{ 'field--error': form.errors.password_confirmation }"
          >
            <label class="field__label">Confirm Password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              class="field__input"
              placeholder="Repeat your password"
              autocomplete="new-password"
            />
            <span v-if="form.errors.password_confirmation" class="field__error">
              {{ form.errors.password_confirmation }}
            </span>
          </div>

          <button
            @click="submit"
            :disabled="form.processing"
            class="accept-card__submit"
            :class="{ 'accept-card__submit--loading': form.processing }"
          >
            <span v-if="!form.processing">Create Account</span>
            <span v-else class="submit-spinner">
              <i class="fa-solid fa-circle-notch fa-spin"></i>
              Setting up your account...
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.accept-invite {
  --primary-color: #3b8bff;
  --primary-dark: color-mix(in srgb, #3b8bff 80%, black);
  --text-main: #111827;
  --text-muted: #6b7280;
  --border-color: #e5e7eb;
  --bg-hover: #f9fafb;
  --danger-color: #ef4444;

  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Fira Sans", "Heebo", sans-serif;

  &__backdrop {
    position: absolute;
    inset: 0;
    background: radial-gradient(
      circle at 20% 50%,
      rgba(59, 139, 255, 0.1),
      rgba(15, 23, 42, 0.6)
    );
    backdrop-filter: blur(8px);
  }

  &__container {
    position: relative;
    width: 100%;
    max-width: 480px;
    margin: 0 24px;
    z-index: 10;
    animation: slide-up 0.4s ease-out;
  }
}

.accept-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;

  &__header {
    padding: 36px 36px 28px;
    background: linear-gradient(135deg, var(--bg-hover) 0%, white 100%);
    border-bottom: 1px solid var(--border-color);
    text-align: center;
  }

  &__logo {
    width: 56px;
    height: 56px;
    background: color-mix(in srgb, var(--primary-color) 10%, white);
    color: var(--primary-color);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }

  &__title {
    margin: 0 0 8px;
    font-size: 22px;
    font-weight: 700;
    color: var(--text-main);
    letter-spacing: -0.02em;
  }

  &__subtitle {
    margin: 0 0 20px;
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.5;
  }

  &__email-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: color-mix(in srgb, var(--primary-color) 8%, white);
    border: 1px solid color-mix(in srgb, var(--primary-color) 20%, white);
    border-radius: 999px;
    font-size: 14px;
    font-weight: 500;
    color: var(--primary-color);

    i {
      font-size: 12px;
      opacity: 0.8;
    }
  }

  &__body {
    padding: 32px 36px 36px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  &__submit {
    width: 100%;
    padding: 13px;
    margin-top: 4px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover:not(:disabled) {
      background: var(--primary-dark);
      transform: translateY(-1px);
    }

    &:active:not(:disabled) {
      transform: translateY(0);
    }

    &--loading {
      opacity: 0.8;
      cursor: not-allowed;
    }
  }
}

.submit-spinner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;

  &__label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-main);
    letter-spacing: 0.01em;
  }

  &__input {
    padding: 11px 14px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 15px;
    color: var(--text-main);
    transition: all 0.2s ease;
    outline: none;
    font-family: inherit;

    &:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px
        color-mix(in srgb, var(--primary-color) 15%, transparent);
    }

    &::placeholder {
      color: #9ca3af;
    }
  }

  &--error &__input {
    border-color: var(--danger-color);

    &:focus {
      box-shadow: 0 0 0 3px
        color-mix(in srgb, var(--danger-color) 15%, transparent);
    }
  }

  &__error {
    font-size: 12px;
    color: var(--danger-color);
    display: flex;
    align-items: center;
    gap: 4px;

    &::before {
      content: "⚠";
      font-size: 11px;
    }
  }
}

@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 640px) {
  .accept-invite__container {
    margin: 0 16px;
  }

  .accept-card__header,
  .accept-card__body {
    padding: 24px 20px;
  }
}
</style>
