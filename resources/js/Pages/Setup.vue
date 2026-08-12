<script setup>
import { usePage, Head, useForm } from "@inertiajs/vue3";

const props = defineProps({
  token: String,
  invalid: Boolean,
  locale: String,
  email: String,
});

const page = usePage();
const appSettings = page.props.appSettings;

const form = useForm({
  first_name: "",
  last_name: "",
  username: "",
  email: props.email || "",
  password: "",
  password_confirmation: "",
  locale: props.locale,
});

const submit = () => {
  form.post(`/setup/${props.token}`, {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>

<template>
  <Head>
    <title>Cubrel - {{ $t("globals.login.setup_title") }}</title>
  </Head>
  <div
    class="accept-invite"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="accept-invite__container">
      <div class="accept-card" v-if="invalid">
        <div class="accept-card__header">
          {{ $t("globals.login.setup_invalid") }}
        </div>
      </div>
      <div class="accept-card" v-else>
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
          <h1 class="accept-card__title">
            {{ $t("globals.login.setup_title") }}
          </h1>
          <p class="accept-card__subtitle">
            {{ $t("globals.login.setup_subtitle") }}
          </p>
        </div>

        <form class="accept-card__body" @submit.prevent="submit">
          <div class="field">
            <label class="field__label">{{
              $t("globals.login.first_name")
            }}</label>
            <div
              class="field__control"
              :class="{ 'field__control--error': form.errors.first_name }"
            >
              <input
                v-model="form.first_name"
                type="text"
                class="field__input"
                :placeholder="$t('globals.login.first_name_placeholder')"
                autocomplete="name"
                @input="form.clearErrors('first_name')"
              />
              <span v-if="form.errors.first_name" class="field__error-icon">
                <i class="fa-solid fa-circle-exclamation"></i>
              </span>
            </div>
            <span v-if="form.errors.first_name" class="field__error">
              {{ form.errors.first_name }}
            </span>
          </div>
          <div class="field">
            <label class="field__label">{{
              $t("globals.login.last_name")
            }}</label>
            <div
              class="field__control"
              :class="{ 'field__control--error': form.errors.last_name }"
            >
              <input
                v-model="form.last_name"
                type="text"
                class="field__input"
                :placeholder="$t('globals.login.last_name_placeholder')"
                autocomplete="name"
                @input="form.clearErrors('last_name')"
              />
              <span v-if="form.errors.last_name" class="field__error-icon">
                <i class="fa-solid fa-circle-exclamation"></i>
              </span>
            </div>
            <span v-if="form.errors.last_name" class="field__error">
              {{ form.errors.last_name }}
            </span>
          </div>

          <div class="field">
            <label class="field__label">{{
              $t("globals.login.username")
            }}</label>
            <div
              class="field__control"
              :class="{ 'field__control--error': form.errors.username }"
            >
              <input
                v-model="form.username"
                type="text"
                class="field__input"
                :placeholder="$t('globals.login.username_placeholder')"
                autocomplete="username"
                @input="form.clearErrors('username')"
              />
              <span v-if="form.errors.username" class="field__error-icon">
                <i class="fa-solid fa-circle-exclamation"></i>
              </span>
            </div>
            <span v-if="form.errors.username" class="field__error">
              {{ form.errors.username }}
            </span>
          </div>

          <div class="field">
            <label class="field__label">{{
              $t("globals.login.email")
            }}</label>
            <div
              class="field__control field__control--email"
              :class="{
                'field__control--error': form.errors.email,
                'field__control--readonly': !!props.email,
              }"
            >
              <i class="field__icon fa-regular fa-envelope"></i>
              <input
                v-model="form.email"
                type="email"
                class="field__input"
                :placeholder="$t('globals.login.email_placeholder')"
                autocomplete="email"
                :readonly="!!props.email"
                @input="form.clearErrors('email')"
              />
              <span v-if="form.errors.email" class="field__error-icon">
                <i class="fa-solid fa-circle-exclamation"></i>
              </span>
            </div>
            <span v-if="form.errors.email" class="field__error">
              {{ form.errors.email }}
            </span>
          </div>

          <div class="field">
            <label class="field__label">{{
              $t("globals.login.password")
            }}</label>
            <div
              class="field__control"
              :class="{ 'field__control--error': form.errors.password }"
            >
              <input
                v-model="form.password"
                type="password"
                class="field__input"
                :placeholder="$t('globals.login.password_placeholder')"
                autocomplete="new-password"
                @input="form.clearErrors('password')"
              />
              <span v-if="form.errors.password" class="field__error-icon">
                <i class="fa-solid fa-circle-exclamation"></i>
              </span>
            </div>
            <span v-if="form.errors.password" class="field__error">
              {{ form.errors.password }}
            </span>
          </div>

          <div class="field">
            <label class="field__label">{{
              $t("globals.login.confirm_password")
            }}</label>
            <div
              class="field__control"
              :class="{
                'field__control--error': form.errors.password_confirmation,
              }"
            >
              <input
                v-model="form.password_confirmation"
                type="password"
                class="field__input"
                :placeholder="$t('globals.login.confirm_password_placeholder')"
                autocomplete="new-password"
                @input="form.clearErrors('password_confirmation')"
              />
              <span
                v-if="form.errors.password_confirmation"
                class="field__error-icon"
              >
                <i class="fa-solid fa-circle-exclamation"></i>
              </span>
            </div>
            <span v-if="form.errors.password_confirmation" class="field__error">
              {{ form.errors.password_confirmation }}
            </span>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="accept-card__submit"
            :class="{ 'accept-card__submit--loading': form.processing }"
          >
            <span v-if="!form.processing">{{
              $t("globals.login.setup_button")
            }}</span>
            <span v-else class="submit-spinner">
              <i class="fa-solid fa-atom fa-spin"></i>
              {{ $t("globals.login.setting_up_account") }}
            </span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.accept-invite {
  --primary-color: var(--primary-color);
  --primary-dark: color-mix(in srgb, var(--primary-color) 80%, black);
  --text-main: var(--color-text-heading);
  --text-muted: var(--color-text-muted);
  --border-color: var(--color-border);
  --bg-hover: var(--color-bg-muted);
  --danger-color: #ef4444;

  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Fira Sans", "Heebo", sans-serif;
  background-color: var(--color-bg-app);

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
  background: var(--color-bg-surface);
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px var(--color-shadow-elevated);
  overflow: hidden;

  &__header {
    padding: 36px 36px 28px;
    background: linear-gradient(135deg, var(--bg-hover) 0%, var(--color-bg-surface) 100%);
    border-bottom: 1px solid var(--border-color);
    text-align: center;
  }

  &__logo {
    width: 56px;
    height: 56px;
    background: color-mix(in srgb, var(--primary-color) 10%, var(--color-bg-surface));
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
    margin: 0;
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.5;
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

  &__control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 2.8rem;
    padding: 0 14px;
    border-radius: 6px;
    border: 1.5px solid var(--border-color);
    background: linear-gradient(135deg, var(--color-bg-surface) 0%, var(--color-bg-surface) 100%);
    box-shadow: 0 1px 3px var(--color-shadow-strong);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);

    &:focus-within {
      border-color: var(--primary-color);
      background: var(--color-bg-surface);

      .field__icon {
        color: var(--primary-color);
      }
    }

    &--error {
      border-color: var(--danger-color);
      background: var(--color-danger-bg-subtle);

      .field__icon {
        color: var(--danger-color);
      }
    }

    &--readonly {
      background: var(--bg-hover);
      border-color: var(--border-color);
      cursor: default;

      .field__icon {
        color: var(--color-text-faint);
      }

      .field__input {
        cursor: default;
        color: var(--text-muted);
      }
    }
  }

  &__icon {
    color: var(--color-text-faint);
    font-size: 0.9rem;
    flex-shrink: 0;
    transition: color 0.2s ease;
  }

  &__input {
    all: unset;
    flex: 1;
    font-size: 15px;
    color: var(--text-main);
    font-family: inherit;
    letter-spacing: 0.3px;

    &::placeholder {
      color: var(--color-text-faint);
    }
  }

  &__error-icon {
    display: flex;
    align-items: center;
    flex-shrink: 0;

    i {
      color: var(--danger-color);
      font-size: 1rem;
      animation: pulse 1s ease-in-out;
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
