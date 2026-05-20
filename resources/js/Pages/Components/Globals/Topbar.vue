<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
import { useForm, Link, usePage } from "@inertiajs/vue3";
import GlobalSearch from "@/Pages/Components/Globals/GlobalSearch.vue";

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
</script>
<template>
  <div class="topbar">
    <Link href="/" class="topbar__logo">
      <img src="/img/logo/default-monochrome.svg" alt="logo" width="240" />
    </Link>
    <GlobalSearch class="topbar__search"></GlobalSearch>

    <div class="topbar__actions">
      <div class="topbar__actions__icons">
        <div
          class="topbar__actions__icons__item profile"
          ref="profileRef"
          @click="toggleProfile"
        >
          <img
            src="\img\profile\20.png"
            class="rounded-circle"
            width="36"
            height="36"
            alt="avatar"
          />
          <i
            :class="
              showProfile
                ? 'fa-solid fa-chevron-up'
                : 'fa-solid fa-chevron-down'
            "
          ></i>
          <transition name="fade">
            <ul v-if="showProfile" class="profile-dropdown card-shadow">
              <li v-if="user.is_admin">
                <Link href="/settings">
                  <i class="fa-solid fa-gears"></i>
                  {{ $t("globals.topbar.settings") }}
                </Link>
              </li>
              <li>
                <Link href="/profile">
                  <i class="fa-solid fa-id-card-clip"></i>
                  {{ user?.name || user?.name || $t("globals.topbar.profile") }}
                </Link>
              </li>
              <li @click="logout">
                <a href="#">
                  <i class="fa-solid fa-arrow-right-from-bracket"></i>
                  {{ $t("globals.topbar.logout") }}
                </a>
              </li>
            </ul>
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>
