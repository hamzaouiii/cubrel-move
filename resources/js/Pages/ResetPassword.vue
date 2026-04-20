<script setup>
import { ref } from "vue";
import { useForm, usePage, Head } from "@inertiajs/vue3";

const props = defineProps({
  token: String,
  email: String,
});

const appSettings = usePage().props?.appSettings || {};

const showPassword = ref(false);
const showConfirm = ref(false);

const form = useForm({
  token: props.token,
  email: props.email ?? "",
  password: "",
  password_confirmation: "",
});

const submit = () => {
  form.post("/reset-password", {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>

<template>
  <Head><title>Cubrel – Reset Password</title></Head>

  <div
    class="reset"
    :style="[
      { '--primary-color': appSettings.primary_color },
      { '--danger-color': appSettings.danger_color },
    ]"
  >
    <div class="reset__card">
      <div class="reset__card__header">
        {{ $t("globals.login.new_password") }}
      </div>

      <div v-if="form.errors.email" class="global-error">
        {{ form.errors.email }}
      </div>

      <form @submit.prevent="submit" novalidate class="reset__card__form">
        <div class="form-group">
          <label for="email" class="form-label">
            {{ $t("globals.login.email") }}
          </label>
          <div class="input-wrapper input-wrapper--email">
            <i class="fa-solid fa-envelope input-icon"></i>
            <span>{{ form.email }}</span>
          </div>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">
            {{ $t("globals.login.new_password") }}
          </label>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock input-icon"></i>
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="form-input"
              :class="{ 'is-invalid': form.errors.password }"
              autocomplete="new-password"
              :disabled="form.processing"
            />
            <button
              type="button"
              class="password-toggle"
              @click="showPassword = !showPassword"
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

        <div class="form-group">
          <label for="password_confirmation" class="form-label">
            {{ $t("globals.login.confirm_password") }}
          </label>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock input-icon"></i>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              :type="showConfirm ? 'text' : 'password'"
              class="form-input"
              autocomplete="new-password"
              :disabled="form.processing"
            />
            <button
              type="button"
              class="password-toggle"
              @click="showConfirm = !showConfirm"
            >
              <i
                :class="
                  showConfirm ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'
                "
              ></i>
            </button>
          </div>
        </div>

        <button
          type="submit"
          class="submit-btn"
          :disabled="
            !form.password || !form.password_confirmation || form.processing
          "
        >
          <span v-if="!form.processing">
            {{ $t("globals.login.save_password") }}
          </span>
          <div v-else class="spinner"></div>
        </button>
      </form>
    </div>
  </div>
</template>
