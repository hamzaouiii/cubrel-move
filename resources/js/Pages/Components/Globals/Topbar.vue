<script setup>
import {
  ref,
  onMounted,
  onBeforeUnmount,
  computed,
  getCurrentInstance,
} from "vue";
import { useForm, Link, usePage } from "@inertiajs/vue3";
import GlobalSearch from "@/Pages/Components/Globals/GlobalSearch.vue";
import NotificationBell from "@/Pages/Components/Globals/NotificationBell.vue";

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const form = useForm({});
const logout = () => {
  form.post("/logout");
};
const showSearch = ref(false);
const showProfile = ref(false);
const profileRef = ref(null);

const toggleProfile = () => {
  showProfile.value = !showProfile.value;
};
const toggleSearch = () => {
  showSearch.value = !showSearch.value;
};

const handleClickOutside = (event) => {
  if (profileRef.value && !profileRef.value.contains(event.target)) {
    showProfile.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const initials = computed(() => {
  const name = user.value?.name?.trim();
  if (!name) return "";
  const parts = name.split(/\s+/);
  return (parts[0][0] + (parts[1]?.[0] || "")).toUpperCase();
});

const roleLabel = computed(() => {
  if (user.value?.is_root) return t("globals.topbar.role_root");
  if (user.value?.is_admin) return t("globals.topbar.role_admin");
  return t("globals.topbar.role_member");
});
</script>
<template>
  <div class="topbar">
    <Link href="/" class="topbar__logo">
      <img src="/img/logo/default-monochrome.svg" alt="logo" width="240" />
    </Link>
    <GlobalSearch class="topbar__search"></GlobalSearch>

    <div class="topbar__actions">
      <div class="topbar__actions__icons">
        <NotificationBell />
        <div
          class="topbar__actions__icons__item profile"
          :class="{ 'profile--open': showProfile }"
          ref="profileRef"
          @click="toggleProfile"
        >
          <img
            v-if="user.avatar"
            :src="user.avatar"
            alt="avatar"
            class="profile__avatar"
          />
          <div v-else class="profile__avatar profile__avatar--fallback">
            {{ initials }}
          </div>
          <i
            :class="
              showProfile
                ? 'fa-solid fa-chevron-up'
                : 'fa-solid fa-chevron-down'
            "
          ></i>
          <transition name="fade">
            <div v-if="showProfile" class="profile-dropdown card-shadow">
              <Link href="/profile" class="profile-dropdown__header">
                <img
                  v-if="user.avatar"
                  :src="user.avatar"
                  alt="avatar"
                  class="profile-dropdown__avatar"
                />
                <div
                  v-else
                  class="profile-dropdown__avatar profile-dropdown__avatar--fallback"
                >
                  {{ initials }}
                </div>
                <div class="profile-dropdown__info">
                  <span class="profile-dropdown__name">
                    {{ user?.name || $t("globals.topbar.profile") }}
                  </span>
                  <span class="profile-dropdown__meta">
                    {{ roleLabel }} · {{ $t("globals.topbar.view_profile") }}
                  </span>
                </div>
              </Link>
              <div class="profile-dropdown__divider"></div>
              <ul class="profile-dropdown__menu">
                <li v-if="user.is_admin">
                  <Link href="/settings">
                    <i class="fa-solid fa-gears"></i>
                    {{ $t("globals.topbar.settings") }}
                  </Link>
                </li>
                <li>
                  <Link href="/preferences">
                    <i class="fa-solid fa-sliders"></i>
                    {{ $t("globals.preferences.label") }}
                  </Link>
                </li>
                <li>
                  <Link href="/about">
                    <i class="fa-solid fa-circle-info"></i>
                    {{ $t("globals.topbar.about") }}
                  </Link>
                </li>
              </ul>
              <div class="profile-dropdown__divider"></div>
              <ul class="profile-dropdown__menu profile-dropdown__menu--danger">
                <li @click="logout">
                  <a href="#">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    {{ $t("globals.topbar.logout") }}
                  </a>
                </li>
              </ul>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>
