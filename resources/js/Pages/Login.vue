<script setup>
import { ref } from "vue";
import { useForm, usePage, Head } from "@inertiajs/vue3";
import Selectbox from "./Components/FiledTypes/Selectbox.vue";
import Alerts from "./Components/Globals/Alerts.vue";
import { useAlerts } from "@/Composables/useAlerts";

const { alerts, success, error, info } = useAlerts();

const showPassword = ref(false);
const pageProps = usePage().props;
const showLogin = ref(true);
const appSettings = pageProps.appSettings;
const forgotSuccess = ref(false);

const form = useForm({
  username: "",
  password: "",
  remember: false,
});

const forgotForm = useForm({
  email: "",
});

const submit = () => {
  form.post("/login", {
    onError: (e) => {
      error(e.general);
    },
  });
};

const submitForgot = () => {
  forgotForm.post("/forgot-password", {
    onSuccess: () => {
      forgotSuccess.value = true;
      forgotForm.reset("email");
    },
    onError: (e) => {
      error(e.email);
    },
  });
};

const openForgot = () => {
  showLogin.value = false;
};
const openLogin = () => {
  showLogin.value = true;
};
const isDirty = () => {
  return form.isDirty && form.username;
};
const isValidEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
const isForgotFormDirty = () => {
  return forgotForm.isDirty && isValidEmail(forgotForm.email);
};
</script>

<template>
  <Head>
    <title>Cubrel</title>
  </Head>
  <Alerts :alerts="alerts"></Alerts>
  <div
    class="login"
    :style="[
      { '--primary-color': appSettings.primary_color },
      { '--danger-color': appSettings.danger_color },
    ]"
  >
    <div class="login__mural">
      <div class="login__mural__logo">
        <img src="img/logo/default-monochrome-white.svg" />
      </div>
    </div>
    <div class="login__card" v-if="showLogin">
      <img
        class="login__card__logo"
        src="img/logo/default-monochrome.svg"
        alt="Cubrel"
      />
      <div class="login__card__header">
        {{ $t("globals.login.welcome_back") }}
      </div>

      <form @submit.prevent="submit" novalidate class="login__card__form">
        <div class="form-group">
          <label for="username" class="form-label">
            {{ $t("globals.login.username") }}
          </label>

          <div class="input-wrapper">
            <i class="fa-solid fa-user input-icon"></i>

            <input
              id="username"
              v-model="form.username"
              type="text"
              class="form-input"
              :class="{ 'is-invalid': form.errors.username }"
              autocomplete="username"
              :disabled="form.processing"
            />
          </div>

          <div
            v-if="form.errors.username && form.errors.password"
            class="error-message"
          >
            {{ form.errors.username }}
          </div>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">
            {{ $t("globals.login.password") }}
          </label>

          <div class="input-wrapper">
            <i class="fa-solid fa-lock input-icon"></i>

            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="form-input"
              :class="{ 'is-invalid': form.errors.password }"
              autocomplete="current-password"
              :disabled="form.processing"
            />

            <button
              type="button"
              class="password-toggle"
              @click="showPassword = !showPassword"
              :aria-label="$t('globals.login.toggle_password')"
            >
              <i
                :class="
                  showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'
                "
              ></i>
            </button>
          </div>

          <div v-if="form.errors.password" class="error-message">
            {{ form.errors.password }}
          </div>
        </div>

        <button
          type="submit"
          class="submit-btn"
          :disabled="!isDirty() || form.processing"
        >
          <span v-if="!form.processing">
            {{ $t("globals.login.login_button") }}
          </span>

          <div v-else class="spinner"></div>
        </button>
        <div class="login__card__links">
          <label for="remember" class="remember-me">
            <Selectbox
              v-model="form.remember"
              :color="appSettings.primary_color"
            ></Selectbox>
            <span class="remember-me__text">
              {{ $t("globals.login.remember_me") }}
            </span>
          </label>

          <span class="login__card__links__link" @click="openForgot">{{
            $t("globals.login.forgot_password")
          }}</span>

          <div
            v-if="form.errors.username && form.errors.password"
            class="error-message"
          >
            {{ form.errors.username }}
          </div>
        </div>
      </form>
    </div>

    <div class="login__card" v-else-if="!showLogin">
      <img
        class="login__card__logo"
        src="img/logo/default-monochrome.svg"
        alt="Cubrel"
      />
      <div class="login__card__header" v-if="!forgotSuccess">
        {{ $t("globals.login.reset_password") }}
      </div>

      <div v-if="forgotSuccess" class="global-success">
        {{ $t("globals.login.reset_email_sent") }}
        <div class="login__card__links">
          <span class="login__card__links__link" @click="openLogin">
            <i class="fa-solid fa-chevron-left"></i>
            {{ $t("globals.login.back_to_login") }}</span
          >
        </div>
      </div>

      <template v-else>
        <form
          @submit.prevent="submitForgot"
          novalidate
          class="login__card__form"
        >
          <div class="form-group">
            <label for="email" class="form-label">
              {{ $t("globals.login.email") }}
            </label>
            <div class="input-wrapper">
              <i class="fa-solid fa-envelope input-icon"></i>

              <input
                id="email"
                v-model="forgotForm.email"
                type="text"
                class="form-input"
                autocomplete="username"
                :disabled="forgotForm.processing"
              />
            </div>
          </div>

          <button
            type="submit"
            class="submit-btn"
            :disabled="!isForgotFormDirty() || forgotForm.processing"
          >
            <span v-if="!forgotForm.processing">
              {{ $t("globals.login.reset_btn") }}
            </span>

            <div v-else class="spinner"></div>
          </button>
          <div class="login__card__links">
            <span class="login__card__links__link" @click="openLogin">
              <i class="fa-solid fa-chevron-left"></i>

              {{ $t("globals.login.back_to_login") }}</span
            >
          </div>
        </form>
      </template>
    </div>
  </div>
</template>
