<template>
  <div class="top-bar">
  <div class="logo">
    <Link href="/" class="logo">
    <img src="/img/logo/logo.svg" alt="logo"  width="240"height="180" />
  </Link>
  </div>
  <div class="actions">
    <transition name="slide-search">
      <form
        v-if="showSearch"
        class="search-bar "
      >
          <input :placeholder="$t('topbar.global_search')" />
      </form>
    </transition>

    <div class="icons">
      <div @click="toggleSearch">
        <i class="fa-solid fa-magnifying-glass"></i>
      </div>
      <div><i class="fa-solid fa-bell"></i></div>
      <div class="profile"  ref="profileRef" @click="toggleProfile">
        <img
          src="\img\profile\40.jpg"
          class="rounded-circle"
          width="36"
          height="36"
          alt="avatar"
        />
        <i :class="showProfile ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"></i>
        <transition name="fade">
          <ul v-if="showProfile" class="profile-dropdown card-shadow">
            <li>
              <Link href="/settings">
                <i class="fa-solid fa-gears"></i>
                {{$t('topbar.settings')}}
              </Link>
          </li>
            <li>
              <Link href="/profile">
                <i class="fa-solid fa-id-card-clip"></i>
                {{$t('topbar.profile')}}
              </Link>
            </li>
            <li @click="logout">
              <a href="#">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                {{$t('topbar.logout')}}
              </a>
            </li>

          </ul>
        </transition>
      </div>
    </div>
  </div>

  </div>
</template>
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useForm, Link} from '@inertiajs/vue3'

const form = useForm({})
const logout = () => {
  form.post('/logout')  
}
const showSearch = ref(false)
const showProfile = ref(false)
const profileRef = ref(null)

const toggleProfile = () => {
  showProfile.value = !showProfile.value
}
const toggleSearch = () => {
  showSearch.value = !showSearch.value
}


const handleClickOutside = (event) => {
  if (profileRef.value && !profileRef.value.contains(event.target)) {
    showProfile.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>