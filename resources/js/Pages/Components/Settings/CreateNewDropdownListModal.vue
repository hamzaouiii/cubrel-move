<script setup>
import {
  ref,
  computed,
  getCurrentInstance,
  onMounted,
  onBeforeUnmount,
} from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";

const { error, info, success, clearAllAlerts } = useAlerts();

const generatedSystemKey = computed(() => {
  if (!form.key) return "";
  const name = form.key
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^-+|-+$/g, "");

  return name + "_list";
});

const generatedSystemvalue = (label) => {
  if (!label) return "";
  const value = label
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^-+|-+$/g, "");

  return value;
};
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const appSettings = usePage().props.appSettings;

const form = useForm({
  key: "",
  values: {},
});
let listItems = ref([]);
let newItem = ref({ label: "", value: "" });

const rowIsDirty = computed(() => {
  return newItem.value.label.length >= 3;
});

const valueExistsError = ref(false);

const addItem = () => {
  if (!rowIsDirty.value) {
    return;
  }
  if (listItems.value.some((item) => item.value === newItem.value)) {
    error("Value Already Exists");
    valueExistsError.value = true;
    return;
  }
  listItems.value.push({
    label: newItem.value.label,
    value: generatedSystemvalue(newItem.value.label),
  });

  newItem.value.value = "";
  newItem.value.label = "";
};
const deleteItem = (value) => {
  listItems.value = listItems.value.filter((i) => i.value != value);
};

const listIsDirty = computed(() => {
  return listItems.value.length && form.key.length;
});

const saveList = async () => {
  try {
    form.values = listItems.value;
    form.key = generatedSystemKey.value;

    info(t("modules.actions.saving"));
    const response = await axios.post("/settings/dropdowns_in_fields", form);
    clearAllAlerts();
    closeModalClicked();
    success(t("settings.dropdown.save_success"));
    emit("listCreated", response.data);
  } catch (e) {
    clearAllAlerts();
    error(t("settings.dropdown.save_error"));
    console.error(e);
  }
};

function handleKeydown(e) {
  if (e.ctrlKey && e.key === "s") {
    e.preventDefault();
    if (listIsDirty.value) {
      saveList();
    }
  }
}

onMounted(() => {
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleKeydown);
});

useUnsavedChangesGuard({
  getIsDirty: () => listIsDirty.value,
});
const emit = defineEmits(["onCloseModal", "listCreated"]);

const closeModalClicked = () => {
  emit("onCloseModal");
};
</script>

<template>
  <div class="create-dropdown-list-modal">
    <div class="create-dropdown-list-modal__close" @click="closeModalClicked">
      <i class="fa-solid fa-xmark"></i>
    </div>
    <div class="create-dropdown-list-modal__container">
      <div
        class="settings"
        :style="{ '--primary-color': appSettings.primary_color }"
      >
        <div class="settings__dropdown">
          <div class="settings__dropdown__edit">
            <form class="dropdown-form" action="" method="post">
              <div class="dropdown-form__item">
                <span class="dropdown-form__item__label"
                  ><label for="name">Name</label></span
                >
                <div class="dropdown-form__item__field">
                  <input type="text" v-model="form.key" maxlength="25" />
                </div>
              </div>
              <div class="dropdown-form__item" v-if="generatedSystemKey">
                <span class="dropdown-form__item__label"
                  ><label for="name">System Key</label></span
                >
                <div class="dropdown-form__item__field">
                  <span>{{ generatedSystemKey }}</span>
                </div>
              </div>
            </form>

            <div class="settings__dropdown__edit__header">
              <ul class="settings__dropdown__edit__header__info">
                <li class="settings__dropdown__edit__header__info__indicator">
                  <span>{{ $t("settings.dropdown.display_label") }}</span>
                  <span>{{ $t("settings.dropdown.value") }}</span>
                  <div></div>
                </li>
              </ul>
            </div>
            <ul>
              <li
                v-for="l in listItems"
                class="settings__dropdown__edit__value"
              >
                <div class="settings__dropdown__edit__value__item">
                  <span>{{ $t(l.label) }}</span>
                </div>

                <div class="settings__dropdown__edit__value__item">
                  <span>{{ $t(l.value) }}</span>
                </div>
                <div class="settings__dropdown__edit__value__actions">
                  <span
                    class="settings__dropdown__edit__value__actions__delete"
                    @click="deleteItem(l.value)"
                  >
                    <i class="fa-solid fa-trash-can"></i>
                    <i v-if="false" class="fa-solid fa-check"></i>
                  </span>
                </div>
              </li>
              <form class="settings__dropdown__edit__value">
                <div class="settings__dropdown__edit__value__item">
                  <input
                    type="text"
                    v-model="newItem.label"
                    @keyup.enter="addItem"
                  />
                </div>
                <div class="settings__dropdown__edit__value__item">
                  <input
                    type="text"
                    :value="generatedSystemvalue(newItem.label)"
                    readonly
                    disabled
                  />
                </div>
                <div class="settings__dropdown__edit__value__actions">
                  <span
                    class="settings__dropdown__edit__value__actions__add"
                    @click="addItem()"
                    :class="{ disabled: !rowIsDirty }"
                  >
                    <i class="fa-solid fa-plus"></i>
                  </span>
                </div>
              </form>
            </ul>

            <div class="settings__dropdown__edit__actions">
              <button
                type="submit"
                class="settings__dropdown__edit__actions__save btn"
                :disabled="!listIsDirty"
                @click="saveList()"
              >
                {{ $t("settings.save") }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
