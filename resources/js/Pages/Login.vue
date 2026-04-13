<script setup>
import { ref } from "vue";
import { useForm, usePage, Head } from "@inertiajs/vue3";
import Selectbox from "./Components/FiledTypes/Selectbox.vue";

const showPassword = ref(false);
const pageProps = usePage().props;
const showLogin = ref(true);
const appSettings = pageProps.appSettings;
const forgotSuccess = ref(false);

const form = useForm({
  username: "",
  password: "password123",
  remember: false,
});

const forgotForm = useForm({
  email: "",
});

const submit = () => {
  form.post("/login");
};

const submitForgot = () => {
  forgotForm.post("/forgot-password");
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
</script>

<template>
  <Head>
    <title>Cubrel</title>
  </Head>
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
      <div class="login__card__header">
        {{ $t("globals.login.welcome_back") }}
      </div>

      <div
        v-if="
          form.errors.general || (form.errors.username && !form.errors.password)
        "
        class="global-error"
      >
        {{ form.errors.general || form.errors.username }}
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
              :placeholder="$t('globals.login.username_placeholder')"
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
              :placeholder="$t('globals.login.password_placeholder')"
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
  <div class="login__card__header">
    {{ $t("globals.login.reset_password") }}
  </div>

  <div v-if="forgotSuccess" class="global-success">
    {{ $t("globals.login.reset_email_sent") }}
  </div>

  <template v-else>
    <div v-if="forgotForm.errors.general || forgotForm.errors.email" class="global-error">
      {{ forgotForm.errors.general || forgotForm.errors.email }}
    </div>

    <form @submit.prevent="submitForgot" novalidate class="login__card__form">
   <div class="form-group">
          <div class="input-wrapper">
            <i class="fa-solid fa-envelope input-icon"></i>

            <input
              id="email"
              v-model="forgotForm.email"
              type="text"
              class="form-input"
              :class="{ 'is-invalid': forgotForm.errors.email }"
              :placeholder="$t('globals.login.email_placeholder')"
              autocomplete="username"
              :disabled="forgotForm.processing"
            />
          </div>
        </div>
        <button
          type="submit"
          class="submit-btn"
          :disabled="!forgotForm.isDirty || forgotForm.processing"
        >
          <span v-if="!forgotForm.processing">
            {{ $t("globals.login.reset_btn") }}
          </span>

          <div v-else class="spinner"></div>
        </button>
        <div class="login__card__links">
          <span class="login__card__links__link" @click="openLogin">{{
            $t("globals.login.back_to_login")
          }}</span>
        </div>
    </form>
  </template>
</div>
  </div>
</template>
