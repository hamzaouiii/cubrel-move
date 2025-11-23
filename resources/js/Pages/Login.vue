<template>
  <Head>
    <title>Admin Section - Automatisierung Regensburg</title>
  </Head>

  <div class="login d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="card shadow-lg p-4" style="max-width: 460px; width: 100%;">
      <div class="text-center mb-4">
        <img src="/android-chrome-512x512.png" alt="logo" class="mb-3 rounded-circle" style="width: 48px; height: 48px;" />
        <div class="fw-semibold">Your Credentials for the Admin Section</div>
      </div>

      <form @submit.prevent="submit" novalidate>
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input
            id="username"
            v-model="form.username"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': form.errors.username }"
            placeholder="Enter your username"
            :disabled="form.processing"
          />
          <div v-if="form.errors.username" class="invalid-feedback">
            {{ form.errors.username }}
          </div>
        </div>

        <div class="mb-3 position-relative">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="form-control"
              :class="{ 'is-invalid': form.errors.password }"
              placeholder="Enter your password"
              autocomplete="current-password"
              :disabled="form.processing"
            />
            <button
              type="button"
              class="btn btn-outline-secondary"
              @click="showPassword = !showPassword"
            >
              <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
            </button>
          </div>
          <div v-if="form.errors.password" class="invalid-feedback d-block">
            {{ form.errors.password }}
          </div>
        </div>

        <button
          type="submit"
          class="btn w-100 mb-3 submit-btn"
          :disabled="form.processing"
        >
          <span v-if="!form.processing">Get In</span>
          <div v-else class="spinner-border spinner-border-sm text-light"></div>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'

const showPassword = ref(false)

const form = useForm({
  username: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login')
}
</script>

<style scoped>
.submit-btn {
  background-color: #0d6efd;
  color: white;
}
.submit-btn:hover {
  background-color: #0d6dfdbd;
  color: white;

}
</style>
