<template>
  <div class="top-bar">
    <transition name="slide-search">
      <form
        v-if="showSearch"
        class="search-bar "
      >
          <input placeholder="Search…" />
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
          <!-- Dropdown -->
        <transition name="fade">
          <ul v-if="showProfile" class="profile-dropdown card-shadow">
            <li><a href="/settings">Settings</a></li>
            <li><a href="/profile">My Profile</a></li>
            <li @click="logout"><a href="#">Logout</a></li>

          </ul>
        </transition>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm({})
const logout = () => {
  form.post('/ar-admin/logout')  
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