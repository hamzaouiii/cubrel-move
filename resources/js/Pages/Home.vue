<template>
  <Head title="Automatisierung Regensburg – Digitale Lösungen und Prozessautomatisierung für kleine Unternehmen" />
  <HomeSection />
  <LeistungenSection />
  <AblaufSection />
  <PreiseSection />
  <KontaktSection />
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Layouts/Layout.vue'
import HomeSection from '@/Components/HomeSection.vue'
import LeistungenSection from '@/Components/LeistungenSection.vue'
import AblaufSection from '@/Components/AblaufSection.vue'
import PreiseSection from '@/Components/PreiseSection.vue'
import KontaktSection from '@/Components/KontaktSection.vue'

defineOptions({ layout: Layout })
</script>
