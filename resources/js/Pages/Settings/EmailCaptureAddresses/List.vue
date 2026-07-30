<script setup>
import { computed, getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";
import SettingDropdownField from "@/Pages/Components/FiledTypes/SettingDropdownField.vue";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { error } = useAlerts();
const { confirm } = useConfirm();

const props = defineProps({
  addresses: Array,
  host: String,
  users: Array,
});

const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.inbound_email") },
];

const form = useForm({
  slug: "",
  label: "",
  owner_id: "",
});

const ownerOptions = computed(() => [
  {
    value: "",
    label: "globals.email_capture_addresses.labels.no_owner_option",
  },
  ...props.users.map((u) => ({ value: u.id, label: u.name })),
]);

const handleCreate = () => {
  form.post("/settings/email-capture-addresses", {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: (errors) =>
      Object.values(errors).forEach((message) => error(message)),
  });
};

const handleDelete = async (address) => {
  const confirmed = await confirm({
    title: t("globals.email_capture_addresses.messages.delete_confirm_title"),
    message: t("globals.email_capture_addresses.messages.delete_confirm", {
      slug: address.slug,
    }),
    highlight: address.slug,
    danger: true,
  });
  if (!confirmed) return;

  router.delete(`/settings/email-capture-addresses/${address.id}`, {
    preserveScroll: true,
    onError: () =>
      error(t("globals.email_capture_addresses.messages.delete_error")),
  });
};
</script>

<template>
  <Head>
    <title>{{ $t("globals.email_capture_addresses.labels.page_title") }}</title>
  </Head>

  <div class="settings email-capture-addresses">
    <div class="settings__module__header">
      <SettingsBreadcrumb :crumbs="crumbs" />
    </div>

    <div class="pdf-templates__header">
      <div class="pdf-templates__header__details">
        <span class="pdf-templates__header__details__title">
          {{ $t("globals.email_capture_addresses.labels.title") }}
        </span>
      </div>
    </div>

    <p class="email-capture-addresses__hint">
      {{ $t("globals.email_capture_addresses.hints.slug_hint") }}
    </p>

    <form class="email-capture-addresses__form" @submit.prevent="handleCreate">
      <div class="email-capture-addresses__form__field">
        <label>{{
          $t("globals.email_capture_addresses.labels.slug_label")
        }}</label>
        <div class="email-capture-addresses__form__address-input">
          <i v-if="form.errors.slug" class="fa-solid fa-circle-exclamation"></i>
          <input
            v-model="form.slug"
            type="text"
            :class="{ 'is-invalid': form.errors.slug }"
          />
          <span>@{{ host }}</span>
        </div>
      </div>

      <div class="email-capture-addresses__form__field">
        <label>{{
          $t("globals.email_capture_addresses.labels.label_label")
        }}</label>
        <div class="email-capture-addresses__form__address-input">
          <i
            v-if="form.errors.label"
            class="fa-solid fa-circle-exclamation"
          ></i>

          <input
            v-model="form.label"
            type="text"
            :class="{ 'is-invalid': form.errors.label }"
          />
        </div>
      </div>

      <div class="email-capture-addresses__form__field">
        <label>{{
          $t("globals.email_capture_addresses.labels.owner_label")
        }}</label>
        <SettingDropdownField
          v-model="form.owner_id"
          :options="ownerOptions"
          :searchable="true"
        />
      </div>

      <button type="submit" class="row-action-btn" :disabled="form.processing">
        {{ $t("globals.email_capture_addresses.buttons.create_btn") }}
      </button>
    </form>

    <div class="list-layout__table-scroll">
      <table class="list-layout__table">
        <thead>
          <tr>
            <th>
              {{ $t("globals.email_capture_addresses.labels.slug_column") }}
            </th>
            <th>
              {{ $t("globals.email_capture_addresses.labels.label_column") }}
            </th>
            <th>
              {{ $t("globals.email_capture_addresses.labels.owner_column") }}
            </th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template v-if="addresses.length === 0">
            <tr>
              <td colspan="4" class="pdf-templates__empty">
                {{
                  $t("globals.email_capture_addresses.messages.no_addresses")
                }}
              </td>
            </tr>
          </template>

          <tr v-for="a in addresses" :key="a.id">
            <td>{{ a.slug }}@{{ host }}</td>
            <td>{{ a.label }}</td>
            <td>{{ a.owner_name ?? "—" }}</td>
            <td class="row-actions">
              <button
                class="row-action-btn row-action-btn--delete"
                @click="handleDelete(a)"
              >
                <i class="fa-solid fa-trash"></i>
                {{ $t("globals.email_capture_addresses.buttons.delete_btn") }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
