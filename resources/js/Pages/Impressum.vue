<template>
  <Head title="Impressum – Automatisierung Regensburg" />
   <section class="privacy-content">

    <div class="figure">
      <img :src="pageFigure">
    </div>
    <div class="text">
      <h1>Impressum</h1>

      <div class="privacy-content-par">
        <h2>Angaben gemäß § 5 TMG</h2>
        <p><strong>Simo Hamzaoui</strong><br>
        Ahornstr. 11<br>
        93087 Alteglofsheim<br>
        Deutschland</p>
      </div>

      <div class="privacy-content-par">
        <h2>Kontakt</h2>
        <p>Telefon: 01523 1783593<br>
        E-Mail: <a href="mailto:kontakt@automatisierung-regensburg.de">kontakt@automatisierung-regensburg.de</a></p>
      </div>

      <div class="privacy-content-par">
        <h2>Berufsbezeichnung</h2>
        <p>Freiberufler (Webentwicklung / Automatisierungslösungen)</p>
      </div>

      <div class="privacy-content-par">
        <h2>Umsatzsteuer-ID</h2>
        <p>Nicht umsatzsteuerpflichtig gemäß § 19 UStG (Kleinunternehmerregelung)</p>
      </div>

      <div class="privacy-content-par">
        <h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>
        <p>Simo Hamzaoui<br>
        Ahornstr. 11<br>
        93087 Alteglofsheim</p>
      </div>

      <div class="privacy-content-par">
        <h2>Haftungsausschluss</h2>
        <h3>Haftung für Inhalte</h3>
        <p>Als Diensteanbieter bin ich gemäß § 7 Abs. 1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich.</p>
        <p>Nach §§ 8 bis 10 TMG bin ich jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.</p>
        <p>Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich.</p>
        <p>Bei Bekanntwerden von entsprechenden Rechtsverletzungen werde ich diese Inhalte umgehend entfernen.</p>

      </div>

      <div class="privacy-content-par">
        <h3>Urheberrecht</h3>
        <p>Die durch den Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht.</p>
        <p>Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechts bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers.</p>
        <p>Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen Gebrauch gestattet.</p>
        <p>Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet.</p>
        <p>Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitte ich um einen entsprechenden Hinweis.</p>
        <p>Bei Bekanntwerden von Rechtsverletzungen werde ich derartige Inhalte umgehend entfernen.</p>

      </div>
    </div>
  </section>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Layouts/Layout.vue'
import pageFigure from '@img/legal/gdpr.svg'      
import { onMounted } from 'vue'
defineOptions({ layout: Layout })
onMounted(() => {
      window.scrollTo({
      top: 0,
      left: 100,
      behavior: "smooth",
    });

  })
</script>
