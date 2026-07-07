<script setup>
import { ref, computed, getCurrentInstance } from "vue";
import { usePage, Head, useForm, router } from "@inertiajs/vue3";
import ImageField from "@/Pages/Components/FiledTypes/ImageField.vue";
import InviteTeamForm from "@/Pages/Components/Onboarding/InviteTeamForm.vue";
import ConfirmOverlay from "@/Pages/Components/Globals/ConfirmOverlay.vue";
import { useConfirm } from "@/Composables/useConfirm";

const props = defineProps({
  steps: {
    type: Array,
    default: () => ["organisation", "demo-data", "invite"],
  },
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { confirm } = useConfirm();

const page = usePage();
const appSettings = page.props.appSettings;

const STEP_META = {
  organisation: { icon: "fa-building" },
  "demo-data": { icon: "fa-database" },
  invite: { icon: "fa-users" },
};
const stepLabel = (step) =>
  t(
    `globals.onboarding.step_${step === "demo-data" ? "demo_data" : step}`
  );

const currentStepIndex = ref(0);
const currentStep = computed(() => props.steps[currentStepIndex.value]);
const seedingDemoData = ref(false);

const advance = () => {
  if (currentStepIndex.value < props.steps.length - 1) {
    currentStepIndex.value++;
  }
};

const goBack = () => {
  if (currentStepIndex.value > 0) {
    currentStepIndex.value--;
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

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const orgSubmitAttempted = ref(false);

const orgValidationErrors = computed(() => {
  const errors = {};
  if (
    orgForm.company_email.trim() &&
    !EMAIL_REGEX.test(orgForm.company_email.trim())
  ) {
    errors.company_email = t("modules.users.modal.email_invalid_error");
  }
  return errors;
});

const orgErrors = computed(() =>
  orgSubmitAttempted.value ? orgValidationErrors.value : {}
);

const submitOrganisation = () => {
  orgSubmitAttempted.value = true;
  if (Object.keys(orgValidationErrors.value).length > 0) return;

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

const busy = computed(() => orgForm.processing || seedingDemoData.value);

const skipOnboarding = async () => {
  const ok = await confirm({
    title: t("globals.onboarding.skip_confirm_title"),
    message: t("globals.onboarding.skip_confirm_message"),
    confirmText: t("globals.onboarding.skip_button"),
    cancelText: t("globals.confirm.cancel_text"),
  });
  if (ok) finish("dashboard");
};
</script>

<template>
  <Head>
    <title>Cubrel - {{ $t("globals.onboarding.title") }}</title>
  </Head>
  <ConfirmOverlay />
  <div
    class="onboarding"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--danger-color': appSettings.danger_color,
    }"
  >
    <div class="onboarding__topbar">
      <img
        class="onboarding__logo"
        src="img/logo/default-monochrome.svg"
        alt="Cubrel"
      />
      <button
        type="button"
        class="onboarding__skip"
        :disabled="busy"
        @click="skipOnboarding"
      >
        {{ $t("globals.onboarding.skip_button") }}
        <i class="fa-solid fa-arrow-right-long"></i>
      </button>
    </div>

    <div
      class="onboarding__container"
      :class="{ 'onboarding__container--wide': currentStep === 'invite' }"
    >
      <button
        v-if="currentStepIndex > 0"
        type="button"
        class="onboarding__back"
        :disabled="busy"
        @click="goBack"
      >
        <i class="fa-solid fa-arrow-left-long"></i>
        {{ $t("globals.onboarding.back_button") }}
      </button>

      <ol class="stepper">
        <li
          v-for="(step, index) in steps"
          :key="step"
          class="stepper__item"
          :class="{
            'stepper__item--active': index === currentStepIndex,
            'stepper__item--done': index < currentStepIndex,
          }"
        >
          <span class="stepper__circle">
            <i v-if="index < currentStepIndex" class="fa-solid fa-check"></i>
            <i v-else :class="['fa-solid', STEP_META[step].icon]"></i>
          </span>
          <span class="stepper__label">{{ stepLabel(step) }}</span>
          <span
            v-if="index < steps.length - 1"
            class="stepper__line"
            :class="{ 'stepper__line--done': index < currentStepIndex }"
          ></span>
        </li>
      </ol>

      <Transition name="step" mode="out-in">
        <!-- Invite team (renders its own card chrome) -->
        <InviteTeamForm
          v-if="currentStep === 'invite'"
          key="invite"
          @sent="finish('dashboard')"
          @skip="finish('dashboard')"
        />

        <div class="card" v-else :key="currentStep">
          <!-- Organisation info -->
          <template v-if="currentStep === 'organisation'">
            <div class="card__header">
              <h1 class="card__title">
                {{ $t("globals.onboarding.org_title") }}
              </h1>
              <p class="card__subtitle">
                {{ $t("globals.onboarding.org_subtitle") }}
              </p>
            </div>
            <div class="card__body">
              <div class="field field--logo">
                <label class="field__label">{{
                  $t("settings.fields.company_logo_url")
                }}</label>
                <ImageField
                  v-model="orgForm.company_logo_url"
                  mode="edit"
                  size="lg"
                  :related_label="orgForm.company_name"
                />
              </div>
              <div class="field">
                <label class="field__label">{{
                  $t("settings.fields.company_name")
                }}</label>
                <div class="field__input-wrap">
                  <i class="fa-solid fa-building field__icon"></i>
                  <input
                    v-model="orgForm.company_name"
                    type="text"
                    class="field__input"
                  />
                </div>
              </div>
              <div class="field">
                <label class="field__label">{{
                  $t("settings.fields.company_address")
                }}</label>
                <div class="field__input-wrap">
                  <i class="fa-solid fa-location-dot field__icon"></i>
                  <input
                    v-model="orgForm.company_address"
                    type="text"
                    class="field__input"
                  />
                </div>
              </div>
              <div class="field-row">
                <div class="field">
                  <label class="field__label">{{
                    $t("settings.fields.company_phone")
                  }}</label>
                  <div class="field__input-wrap">
                    <i class="fa-solid fa-phone field__icon"></i>
                    <input
                      v-model="orgForm.company_phone"
                      type="text"
                      class="field__input"
                    />
                  </div>
                </div>
                <div class="field" :class="{ 'field--error': orgErrors.company_email }">
                  <label class="field__label">{{
                    $t("settings.fields.company_email")
                  }}</label>
                  <div class="field__input-wrap">
                    <i class="fa-solid fa-envelope field__icon"></i>
                    <input
                      v-model="orgForm.company_email"
                      type="email"
                      class="field__input"
                    />
                  </div>
                  <span v-if="orgErrors.company_email" class="field__error">
                    {{ orgErrors.company_email }}
                  </span>
                </div>
              </div>
              <div class="field">
                <label class="field__label">{{
                  $t("settings.fields.company_website")
                }}</label>
                <div class="field__input-wrap">
                  <i class="fa-solid fa-globe field__icon"></i>
                  <input
                    v-model="orgForm.company_website"
                    type="text"
                    class="field__input"
                  />
                </div>
              </div>

              <button
                @click="submitOrganisation"
                :disabled="orgForm.processing"
                class="card__submit"
              >
                <span v-if="!orgForm.processing">{{
                  $t("globals.onboarding.continue")
                }}</span>
                <span v-else class="submit-spinner">
                  <i class="fa-solid fa-atom fa-spin"></i>
                </span>
              </button>
            </div>
          </template>

          <!-- Demo data -->
          <template v-else-if="currentStep === 'demo-data'">
            <div class="card__header">
              <h1 class="card__title">
                {{ $t("globals.onboarding.demo_title") }}
              </h1>
              <p class="card__subtitle">
                {{ $t("globals.onboarding.demo_subtitle") }}
              </p>
            </div>
            <div class="card__body">
              <div class="options">
                <button
                  type="button"
                  class="option-card"
                  :disabled="busy"
                  @click="populateDemoData(true)"
                >
                  <span class="option-card__icon">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                  </span>
                  <span class="option-card__title">{{
                    $t("globals.onboarding.demo_yes")
                  }}</span>
                  <span
                    v-if="seedingDemoData !== true"
                    class="option-card__desc"
                  >
                    {{ $t("globals.onboarding.demo_yes_desc") }}
                  </span>
                  <span v-else class="submit-spinner submit-spinner--card">
                    <i class="fa-solid fa-atom fa-spin"></i>
                    {{ $t("globals.onboarding.demo_seeding") }}
                  </span>
                </button>
                <button
                  type="button"
                  class="option-card"
                  :disabled="busy"
                  @click="populateDemoData(false)"
                >
                  <span class="option-card__icon">
                    <i class="fa-solid fa-file"></i>
                  </span>
                  <span class="option-card__title">{{
                    $t("globals.onboarding.demo_no")
                  }}</span>
                  <span class="option-card__desc">
                    {{ $t("globals.onboarding.demo_no_desc") }}
                  </span>
                </button>
              </div>
            </div>
          </template>
        </div>
      </Transition>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.onboarding {
  --primary-color: var(--primary-color);
  --primary-dark: color-mix(in srgb, var(--primary-color) 80%, black);
  --primary-soft: color-mix(in srgb, var(--primary-color) 10%, white);
  --primary-softer: color-mix(in srgb, var(--primary-color) 5%, white);
  --primary-ring: color-mix(in srgb, var(--primary-color) 16%, transparent);
  --text-main: #111827;
  --text-muted: #6b7280;
  --border-color: #e5e7eb;
  --bg-hover: #f9fafb;
  --danger-color: #ef4444;

  position: relative;
  inset: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  font-family: "Fira Sans", "Heebo", sans-serif;
  background:
    radial-gradient(
      1200px 600px at 50% -10%,
      var(--primary-softer) 0%,
      transparent 60%
    ),
    whitesmoke;
  padding: 28px 24px 60px;
  box-sizing: border-box;

  &__topbar {
    width: 100%;
    max-width: 640px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    animation: fade-in 0.4s ease-out;
  }

  &__logo {
    height: 26px;
    width: auto;
  }

  &__skip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    padding: 8px 4px;
    transition: color 0.2s ease;

    i {
      font-size: 11px;
      transition: transform 0.2s ease;
    }

    &:hover:not(:disabled) {
      color: var(--text-main);

      i {
        transform: translateX(2px);
      }
    }

    &:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }
  }

  &__container {
    position: relative;
    width: 100%;
    max-width: 480px;
    z-index: 10;

    &--wide {
      max-width: 640px;
    }
  }

  &__back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    padding: 6px 4px;
    margin-bottom: 12px;
    transition: color 0.2s ease;

    i {
      font-size: 11px;
      transition: transform 0.2s ease;
    }

    &:hover:not(:disabled) {
      color: var(--text-main);

      i {
        transform: translateX(-2px);
      }
    }

    &:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }
  }
}

.stepper {
  display: flex;
  align-items: flex-start;
  justify-content: center;
  list-style: none;
  margin: 0 0 28px;
  padding: 0;

  &__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
    max-width: 140px;
  }

  &__circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: 1.5px solid var(--border-color);
    color: var(--text-muted);
    font-size: 13px;
    z-index: 2;
    transition: all 0.25s ease;
  }

  &__label {
    margin-top: 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    letter-spacing: 0.01em;
    transition: color 0.25s ease;
    text-align: center;
  }

  &__line {
    position: absolute;
    top: 18px;
    left: calc(50% + 24px);
    right: calc(-50% + 24px);
    height: 2px;
    background: var(--border-color);
    z-index: 1;

    &--done {
      background: var(--primary-color);
    }
  }

  &__item--active &__circle {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: var(--primary-soft);
    box-shadow: 0 0 0 4px var(--primary-ring);
  }

  &__item--active &__label {
    color: var(--text-main);
  }

  &__item--done &__circle {
    border-color: var(--primary-color);
    background: var(--primary-color);
    color: white;
  }

  &__item--done &__label {
    color: var(--text-main);
  }
}

.card {
  background: white;
  border-radius: 20px;
  border: 1px solid var(--border-color);
  box-shadow:
    0 25px 50px -20px var(--primary-ring),
    0 12px 24px -12px rgba(0, 0, 0, 0.12);
  overflow: hidden;

  &__header {
    padding: 32px 36px 24px;
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
    padding: 4px 36px 36px;
    display: flex;
    flex-direction: column;
    gap: 18px;
  }

  &__submit {
    width: 100%;
    padding: 13px;
    margin-top: 4px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px -2px var(--primary-ring);

    &:hover:not(:disabled) {
      background: var(--primary-dark);
      transform: translateY(-1px);
      box-shadow: 0 6px 16px -2px var(--primary-ring);
    }

    &:active:not(:disabled) {
      transform: translateY(0);
    }

    &:disabled {
      opacity: 0.8;
      cursor: not-allowed;
      transform: none;
    }
  }
}

.submit-spinner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;

  &--card {
    font-size: 13px;
    font-weight: 600;
    color: var(--primary-color);
  }
}

.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;

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

  &__input-wrap {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--bg-hover);
    border: 1.5px solid transparent;
    border-radius: 10px;
    transition: all 0.2s ease;

    &:focus-within {
      background: white;
      border-color: var(--primary-color);
    }
  }

  &__icon {
    margin-left: 14px;
    font-size: 13px;
    color: var(--text-muted);
    flex-shrink: 0;
  }

  &__input {
    all: unset;
    box-sizing: border-box;
    width: 100%;
    padding: 11px 14px;
    font-size: 15px;
    color: var(--text-main);
    font-family: inherit;
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

  &--error &__input-wrap {
    border-color: var(--danger-color);
  }
}

.options {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.option-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 8px;
  padding: 24px 18px;
  background: var(--bg-hover);
  border: 1.5px solid var(--border-color);
  border-radius: 14px;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s ease;
  min-height: 168px;
  justify-content: center;

  &:hover:not(:disabled) {
    border-color: var(--primary-color);
    background: var(--primary-softer);
    transform: translateY(-2px);
    box-shadow: 0 12px 24px -12px var(--primary-ring);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
  }

  &__icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-soft);
    color: var(--primary-color);
    font-size: 16px;
    margin-bottom: 4px;
  }

  &__title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-main);
  }

  &__desc {
    font-size: 12.5px;
    color: var(--text-muted);
    line-height: 1.4;
  }
}

.step-enter-active,
.step-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}

.step-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

.step-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 640px) {
  .onboarding {
    padding: 20px 16px 40px;

    &__topbar {
      margin-bottom: 20px;
    }
  }

  .card__header,
  .card__body {
    padding-left: 20px;
    padding-right: 20px;
  }

  .field-row {
    grid-template-columns: 1fr;
    gap: 18px;
  }

  .options {
    grid-template-columns: 1fr;
  }

  .stepper__label {
    display: none;
  }
}
</style>
