<script setup>
import { ref, computed } from "vue";
import { usePage, Head, useForm, router } from "@inertiajs/vue3";
import ImageField from "@/Pages/Components/FiledTypes/ImageField.vue";
import InviteTeamForm from "@/Pages/Components/Onboarding/InviteTeamForm.vue";

const props = defineProps({
  steps: { type: Array, default: () => ["organisation", "demo-data", "invite"] },
});

const page = usePage();
const appSettings = page.props.appSettings;

const currentStepIndex = ref(0);
const currentStep = computed(() => props.steps[currentStepIndex.value]);
const seedingDemoData = ref(false);

const advance = () => {
  if (currentStepIndex.value < props.steps.length - 1) {
    currentStepIndex.value++;
  }
};

const orgForm = useForm({
  company_logo_url: appSettings.company_logo_url || "",
  company_name: appSettings.company_name || "",
  company_address: appSettings.company_address || "",
  company_phone: appSettings.company_phone || "",
  company_email: appSettings.company_email || "",
  company_website: appSettings.company_website || "",
});

const submitOrganisation = () => {
  orgForm
    .transform((data) => ({
      values: Object.entries(data).map(([key, value]) => ({ key, value })),
    }))
    .put("/settings/company-info", {
      preserveScroll: true,
      onSuccess: () => advance(),
    });
};

const populateDemoData = (populate) => {
  router.post(
    "/onboarding/demo-data",
    { populate },
    {
      preserveScroll: true,
      onStart: () => (seedingDemoData.value = populate),
      onFinish: () => (seedingDemoData.value = false),
      onSuccess: () => advance(),
    }
  );
};

const finish = (destination) => {
  router.post("/onboarding/finish", { destination });
};
</script>

<template>
  <Head>
    <title>Cubrel - {{ $t("globals.onboarding.title") }}</title>
  </Head>
  <div
    class="accept-invite"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div
      class="accept-invite__container"
      :class="{ 'accept-invite__container--wide': currentStep === 'invite' }"
    >
      <div class="accept-card__steps">
        <span
          v-for="(step, index) in steps"
          :key="step"
          class="accept-card__step-dot"
          :class="{ 'accept-card__step-dot--active': index === currentStepIndex }"
        />
      </div>

      <!-- Invite team (renders its own card chrome) -->
      <InviteTeamForm
        v-if="currentStep === 'invite'"
        @sent="finish('dashboard')"
        @skip="finish('dashboard')"
      />

      <div class="accept-card" v-else>
        <!-- Organisation info -->
        <template v-if="currentStep === 'organisation'">
          <div class="accept-card__header">
            <h1 class="accept-card__title">
              {{ $t("globals.onboarding.org_title") }}
            </h1>
            <p class="accept-card__subtitle">
              {{ $t("globals.onboarding.org_subtitle") }}
            </p>
          </div>
          <div class="accept-card__body">
            <div class="field field--logo">
              <label class="field__label">{{ $t("settings.fields.company_logo_url") }}</label>
              <ImageField
                v-model="orgForm.company_logo_url"
                mode="edit"
                size="lg"
                :related_label="orgForm.company_name"
              />
            </div>
            <div class="field">
              <label class="field__label">{{ $t("settings.fields.company_name") }}</label>
              <input v-model="orgForm.company_name" type="text" class="field__input" />
            </div>
            <div class="field">
              <label class="field__label">{{ $t("settings.fields.company_address") }}</label>
              <input v-model="orgForm.company_address" type="text" class="field__input" />
            </div>
            <div class="field">
              <label class="field__label">{{ $t("settings.fields.company_phone") }}</label>
              <input v-model="orgForm.company_phone" type="text" class="field__input" />
            </div>
            <div class="field">
              <label class="field__label">{{ $t("settings.fields.company_email") }}</label>
              <input v-model="orgForm.company_email" type="email" class="field__input" />
            </div>
            <div class="field">
              <label class="field__label">{{ $t("settings.fields.company_website") }}</label>
              <input v-model="orgForm.company_website" type="text" class="field__input" />
            </div>

            <button
              @click="submitOrganisation"
              :disabled="orgForm.processing"
              class="accept-card__submit"
            >
              {{ $t("globals.onboarding.continue") }}
            </button>
          </div>
        </template>

        <!-- Demo data -->
        <template v-else-if="currentStep === 'demo-data'">
          <div class="accept-card__header">
            <h1 class="accept-card__title">
              {{ $t("globals.onboarding.demo_title") }}
            </h1>
            <p class="accept-card__subtitle">
              {{ $t("globals.onboarding.demo_subtitle") }}
            </p>
          </div>
          <div class="accept-card__body">
            <button
              @click="populateDemoData(true)"
              :disabled="seedingDemoData"
              class="accept-card__submit"
            >
              <span v-if="!seedingDemoData">{{ $t("globals.onboarding.demo_yes") }}</span>
              <span v-else class="submit-spinner">
                <i class="fa-solid fa-atom fa-spin"></i>
                {{ $t("globals.onboarding.demo_seeding") }}
              </span>
            </button>
            <button
              @click="populateDemoData(false)"
              :disabled="seedingDemoData"
              class="accept-card__submit accept-card__submit--secondary"
            >
              {{ $t("globals.onboarding.demo_no") }}
            </button>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.accept-invite {
  --primary-color: var(--primary-color);
  --primary-dark: color-mix(in srgb, var(--primary-color) 80%, black);
  --text-main: #111827;
  --text-muted: #6b7280;
  --border-color: #e5e7eb;
  --bg-hover: #f9fafb;

  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Fira Sans", "Heebo", sans-serif;
  background-color: whitesmoke;

  &__container {
    position: relative;
    width: 100%;
    max-width: 480px;
    margin: 0 24px;
    z-index: 10;
    animation: slide-up 0.4s ease-out;

    &--wide {
      max-width: 640px;
    }
  }
}

.accept-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;

  &__steps {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 16px;
  }

  &__step-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--border-color);
    transition: background 0.2s ease;

    &--active {
      background: var(--primary-color);
    }
  }

  &__header {
    padding: 20px 36px 28px;
    text-align: center;
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
    padding: 0 36px 36px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  &__submit {
    width: 100%;
    padding: 13px;
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

    &:disabled {
      opacity: 0.8;
      cursor: not-allowed;
    }

    &--secondary {
      background: white;
      color: var(--text-main);
      border: 1px solid var(--border-color);

      &:hover:not(:disabled) {
        background: var(--bg-hover);
        transform: none;
      }
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

  &--logo {
    align-items: center;
    text-align: center;
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
    padding-left: 20px;
    padding-right: 20px;
  }
}
</style>
