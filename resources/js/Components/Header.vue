<template>
  <header :class="{scrolled : isScrolled}">
      <Link class="logo"  href="/#home">
        <img src="/img/logo/logo.png" alt="Logo" />
      </Link>
        <nav :class="{showMobileMenu: isOpen}">
          <div class='burger' @click="isOpen = !isOpen">
            <span></span>
            <span></span>
            <span></span>
          </div> 
          <ul>
            <Link @click="isOpen = !isOpen" href="/#leistungen">Leistungen</Link>
            <Link @click="isOpen = !isOpen" href="/#ablauf">Ablauf</Link>
            <Link @click="isOpen = !isOpen" href="/#preise">Preise</Link>
            <Link @click="isOpen = !isOpen" href="/#kontakt">Kontakt</Link>
          </ul>
        </nav>

  </header>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref ,onMounted, onUnmounted} from 'vue';

let isScrolled = ref(false);
let isOpen = ref(false);

const onScroll = () => {
    isScrolled.value = window.scrollY > 260;
  if(window.scrollY < 20){
    isScrolled.value = false;
  }
}

onMounted(() => {
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
});
onUnmounted(() => window.removeEventListener('scroll', onScroll));

 </script>

