<script setup>
import { ref, getCurrentInstance } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { success, error: showError } = useAlerts();

const props = defineProps({
  sourceModule: { type: String, required: true },
  sourceRecord: { type: Object, required: true },
  transformations: { type: Array, default: () => [] },
});

const emit = defineEmits(["close", "created"]);

const phase = ref("select");
const selectedTransformation = ref(null);
const existingLink = ref(null);
const errorMessage = ref("");

const canClose = () => phase.value !== "running";

const close = () => {
  if (!canClose()) return;
  emit("close");
};

const selectTransformation = async (transformation) => {
  selectedTransformation.value = transformation;
  phase.value = "checking";

  try {
    const { data } = await axios.get(
      `/transformations/${transformation.id}/${props.sourceRecord.id}/preview`,
    );

    if (data.existing_link) {
      existingLink.value = data.existing_link;
      phase.value = "confirm";
    } else {
      run(false);
    }
  } catch (e) {
    errorMessage.value =
      e.response?.data?.message ??
      t("globals.transformations.messages.run_error");
    phase.value = "error";
  }
};

const run = async (skipLink) => {
  phase.value = "running";

  try {
    const { data } = await axios.post(
      `/transformations/${selectedTransformation.value.id}/${props.sourceRecord.id}/run`,
      { skip_link: skipLink },
    );

    success(
      t("globals.transformations.messages.run_success", {
        name: data.record.name,
      }),
      {
        action: {
          label: t("modules.actions.open"),
          onClick: () => router.visit(`/${data.module}/${data.record.id}`),
        },
      },
    );

    emit("created", data);
    emit("close");
  } catch (e) {
    errorMessage.value =
      e.response?.data?.message ??
      t("globals.transformations.messages.run_error");
    phase.value = "error";
  }
};

const backToSelect = () => {
  selectedTransformation.value = null;
  existingLink.value = null;
  errorMessage.value = "";
  phase.value = "select";
};
</script>

<template>
  <div class="pdf-modal">
    <div class="pdf-modal__backdrop" @click="close"></div>

    <div class="pdf-modal__container">
      <div class="deployment-card">
        <div class="deployment-card__header">
          <div class="deployment-card__title-group">
            <h3 class="deployment-card__title">
              <template v-if="phase === 'select'">{{
                $t("globals.transformations.labels.modal_title_select")
              }}</template>
              <template v-else-if="phase === 'checking'">{{
                $t("globals.transformations.labels.modal_title_checking")
              }}</template>
              <template v-else-if="phase === 'confirm'">{{
                $t("globals.transformations.labels.modal_title_confirm")
              }}</template>
              <template v-else-if="phase === 'running'">{{
                $t("globals.transformations.labels.modal_title_running")
              }}</template>
              <template v-else>{{
                $t("globals.transformations.labels.modal_title_error")
              }}</template>
            </h3>

            <p class="deployment-card__subtitle" v-if="phase === 'select'">
              {{ $t("globals.transformations.hints.modal_sub_select") }}
            </p>
            <p
              class="deployment-card__subtitle deployment-card__subtitle--danger"
              v-else-if="phase === 'error'"
            >
              {{ errorMessage }}
            </p>
          </div>
        </div>

        <div class="pdf-modal__body">
          <template v-if="phase === 'select'">
            <div
              v-for="transformation in transformations"
              :key="transformation.id"
              class="pdf-template-row"
              @click="selectTransformation(transformation)"
            >
              <div class="pdf-template-row__icon">
                <i
                  :class="[
                    'fa-solid',
                    transformation.icon || 'fa-arrow-right-arrow-left',
                  ]"
                ></i>
              </div>

              <div class="pdf-template-row__info">
                <span class="pdf-template-row__name">{{
                  transformation.name
                }}</span>
              </div>

              <div class="pdf-template-row__action">
                <i class="fa-solid fa-chevron-right"></i>
              </div>
            </div>

            <div
              v-if="!transformations.length"
              class="pdf-modal__spinner-label"
            >
              {{ $t("globals.transformations.messages.no_transformations") }}
            </div>

            <a
              class="pdf-template-row transformation-modal__create-new"
              :href="`/settings/transformations/create?source_module=${sourceModule}`"
            >
              <div class="pdf-template-row__icon">
                <i class="fa-solid fa-plus"></i>
              </div>
              <div class="pdf-template-row__info">
                <span class="pdf-template-row__name">{{
                  $t("globals.transformations.labels.create_new_link")
                }}</span>
              </div>
            </a>
          </template>

          <template v-else-if="phase === 'checking' || phase === 'running'">
            <div class="pdf-modal__spinner-wrap">
              <div class="saving-loader import-modal__loader">
                <div class="lds-ripple">
                  <div></div>
                  <div></div>
                </div>
              </div>
              <div class="pdf-modal__spinner-label">
                {{
                  phase === "checking"
                    ? $t("globals.transformations.messages.checking_conflicts")
                    : $t("globals.transformations.messages.creating_record", {
                        target: selectedTransformation?.target_module,
                      })
                }}
              </div>
            </div>
          </template>

          <template v-else-if="phase === 'confirm'">
            <p class="transformation-modal__link-conflict">
              <i class="fa-solid fa-triangle-exclamation"></i>
              {{
                $t("globals.transformations.messages.link_conflict_warning", {
                  name: existingLink?.name,
                })
              }}
            </p>
          </template>

          <template v-else-if="phase === 'error'">
            <div class="pdf-modal__error">
              <div class="pdf-modal__error__icon">
                <i class="fa-solid fa-xmark"></i>
              </div>
              <div class="pdf-modal__error__label">
                {{ errorMessage }}
              </div>
            </div>
          </template>
        </div>

        <div class="deployment-card__footer">
          <div class="deployment-card__footer__content">
            <template v-if="phase === 'confirm'">
              <div style="display: flex; gap: 10px">
                <button
                  class="deployment-card__button deployment-card__button--secondary"
                  @click="backToSelect"
                >
                  {{ $t("globals.transformations.buttons.cancel") }}
                </button>
                <button
                  class="deployment-card__button deployment-card__button--secondary"
                  @click="run(true)"
                >
                  {{
                    $t(
                      "globals.transformations.buttons.create_without_link_btn",
                    )
                  }}
                </button>
                <button class="deployment-card__button" @click="run(false)">
                  {{ $t("globals.transformations.buttons.override_link_btn") }}
                </button>
              </div>
            </template>

            <template v-else-if="phase === 'error'">
              <div class="deployment-failed">
                <div class="deployment-failed__actions">
                  <button
                    class="deployment-card__button deployment-card__button--retry"
                    @click="backToSelect"
                  >
                    <i
                      class="fa-solid fa-rotate-right"
                      style="margin-right: 6px"
                    ></i>
                    {{ $t("globals.transformations.buttons.cancel") }}
                  </button>
                </div>
              </div>
            </template>

            <template v-else>
              <span style="font-size: 13px; color: #9ca3af">
                {{
                  transformations.length === 1
                    ? $t(
                        "globals.transformations.messages.transformations_count_one",
                        {
                          count: transformations.length,
                        },
                      )
                    : $t(
                        "globals.transformations.messages.transformations_count_many",
                        {
                          count: transformations.length,
                        },
                      )
                }}
              </span>
            </template>
          </div>
        </div>
      </div>

      <button class="pdf-modal__close" @click="close" :disabled="!canClose()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>
</template>
