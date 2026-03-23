<script setup>
import { ref } from "vue";
import { useForm, usePage, Head } from "@inertiajs/vue3";

const showPassword = ref(false);
const pageProps = usePage().props;

console.log(pageProps);
const form = useForm({
  username: "",
  password: "",
  remember: false,
});

const submit = () => {
  form.post("/login", {
    onStart: () => console.log("START"),
    onSuccess: () => console.log("SUCCESS"),
    onError: (errors) => console.log("ERRORS:", errors),
    onFinish: () => console.log("FINISH"),
  });
};
</script>

<template>
  <Head>
    <title>Admin Section - Automatisierung Regensburg</title>
  </Head>
  <div class="login-page">
    <div class="admin-login">
      <div class="login-header">
        <div class="logo-wrapper">
          <img src="/android-chrome-512x512.png" alt="logo" class="logo" />
        </div>
        <h1 class="title">Welcome Back</h1>
        <p class="subtitle">
          Enter your credentials to access the admin panel.
        </p>
      </div>
      <!-- Global Error (Auth / Permission) -->
      <div
        v-if="
          form.errors.general || (form.errors.username && !form.errors.password)
        "
        class="global-error"
      >
        {{ form.errors.general || form.errors.username }}
      </div>

      <form @submit.prevent="submit" novalidate>
        <div class="form-group">
          <label for="username" class="form-label">Username</label>
          <div class="input-wrapper">
            <input
              id="username"
              v-model="form.username"
              type="text"
              class="form-input"
              :class="{ 'is-invalid': form.errors.username }"
              placeholder="e.g. admin_user"
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
          <label for="password" class="form-label">Password</label>
          <div class="input-wrapper">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="form-input"
              :class="{ 'is-invalid': form.errors.password }"
              placeholder="••••••••"
              autocomplete="current-password"
              :disabled="form.processing"
            />
            <button
              type="button"
              class="password-toggle"
              @click="showPassword = !showPassword"
              aria-label="Toggle password visibility"
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

        <button type="submit" class="submit-btn" :disabled="!form.isDirty">
          <span v-if="!form.processing">Secure Login</span>
          <div v-else class="spinner"></div>
        </button>
      </form>
    </div>
  </div>
</template>

<style lang="scss">
// Variables - Modernized Palette
$primary-color: #4f46e5; // Deeper Indigo
$primary-hover: #4338ca;
$primary-light: #e0e7ff;
$error-color: #ef4444;
$text-color: #111827;
$text-light: #6b7280;
$border-color: #e5e7eb;
$background: #ffffff;
$input-bg: #f9fafb;

.global-error {
  margin-bottom: 1.25rem;
  padding: 0.85rem 1rem;
  border-radius: 0.75rem;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: $error-color;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
  backdrop-filter: blur(6px);

  animation: shake 0.25s ease;

  &::before {
    content: "⚠ ";
    margin-right: 0.25rem;
  }
}

@keyframes shake {
  0% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-3px);
  }
  50% {
    transform: translateX(3px);
  }
  75% {
    transform: translateX(-2px);
  }
  100% {
    transform: translateX(0);
  }
}
// Full Page Wrapper
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  // Subtle animated mesh-like gradient background
  background: linear-gradient(135deg, #f3f4f6 0%, #e0e7ff 50%, #ede9fe 100%);
  background-size: 200% 200%;
  animation: gradientBG 15s ease infinite;
}

@keyframes gradientBG {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

// Container
.admin-login {
  width: 100%;
  max-width: 420px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 2.5rem 2rem;
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.6);
  box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.05),
    0 8px 10px -6px rgba(0, 0, 0, 0.01);

  // Header Section
  .login-header {
    text-align: center;
    margin-bottom: 2.5rem;

    .logo-wrapper {
      display: inline-flex;
      padding: 0.5rem;
      background: white;
      border-radius: 1rem;
      box-shadow: 0 4px 6px 5px rgba(0, 0, 0, 0.05);
      margin-bottom: 1.25rem;

      .logo {
        width: 56px;
        height: 56px;
        border-radius: 0.5rem;
        object-fit: cover;
      }
    }

    .title {
      font-size: 1.5rem;
      font-weight: 700;
      color: $text-color;
      margin: 0 0 0.5rem 0;
      letter-spacing: -0.025em;
    }

    .subtitle {
      font-size: 0.875rem;
      color: $text-light;
      margin: 0;
    }
  }

  // Form Group
  .form-group {
    margin-bottom: 1.5rem;

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: #374151;
    }
  }

  // Input Field
  .input-wrapper {
    position: relative;

    .form-input {
      width: 100%;
      padding: 0.875rem 1rem;
      font-size: 0.95rem;
      color: $text-color;
      background-color: $input-bg;
      border: 1.5px solid transparent;
      border-radius: 0.75rem;
      transition: all 0.2s ease;
      box-sizing: border-box;

      &::placeholder {
        color: #9ca3af;
      }

      &:hover:not(:disabled) {
        background-color: #f3f4f6;
      }

      &:focus {
        outline: none;
        background-color: $background;
        border-color: $primary-color;
        box-shadow: 0 0 0 4px $primary-light;
      }

      &:disabled {
        background-color: #f3f4f6;
        cursor: not-allowed;
        opacity: 0.7;
      }

      &.is-invalid {
        border-color: $error-color;
        background-color: #fef2f2;

        &:focus {
          box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }
      }
    }

    // Password Toggle Button
    .password-toggle {
      position: absolute;
      right: 0.5rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: $text-light;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 0.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;

      &:hover {
        color: $primary-color;
        background-color: $primary-light;
      }

      i {
        font-size: 1rem;
      }
    }
  }

  // Error Message
  .error-message {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: $error-color;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-weight: 500;

    &::before {
      content: "!";
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 14px;
      height: 14px;
      background: $error-color;
      color: white;
      border-radius: 50%;
      font-size: 0.65rem;
      font-weight: bold;
    }
  }

  // Submit Button
  .submit-btn {
    width: 100%;
    margin-top: 1rem;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    font-weight: 600;
    color: white;
    background: $primary-color;
    border: none;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);

    &:hover:not(:disabled) {
      background: $primary-hover;
      transform: translateY(-2px);
      box-shadow: 0 6px 8px -1px rgba(79, 70, 229, 0.4);
    }

    &:active:not(:disabled) {
      transform: translateY(0);
      box-shadow: 0 2px 4px -1px rgba(79, 70, 229, 0.3);
    }

    &:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .spinner {
      width: 1.25rem;
      height: 1.25rem;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-top-color: white;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }
  }
}

// Animation
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

// Dark Mode Support
@media (prefers-color-scheme: dark) {
  $dark-bg: #111827;
  $dark-card: #1f2937;
  $dark-text: #f9fafb;
  $dark-border: #374151;
  $dark-input: #0f172a;

  .login-page {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #111827 100%);
  }

  .admin-login {
    background: rgba(31, 41, 55, 0.8);
    border-color: rgba(255, 255, 255, 0.1);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);

    .login-header {
      .logo-wrapper {
        background: $dark-input;
        border: 1px solid $dark-border;
      }
      .title {
        color: $dark-text;
      }
      .subtitle {
        color: #9ca3af;
      }
    }

    .form-group .form-label {
      color: #e5e7eb;
    }

    .input-wrapper {
      .form-input {
        background-color: $dark-input;
        color: $dark-text;

        &:hover:not(:disabled) {
          background-color: #1e293b;
        }

        &:focus {
          background-color: $dark-card;
          box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);
        }
      }

      .password-toggle {
        &:hover {
          background-color: #374151;
        }
      }
    }
  }
}
</style>
