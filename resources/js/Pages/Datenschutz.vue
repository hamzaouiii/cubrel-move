<template>
  <Head title="Datenschutz – Automatisierung Regensburg" />
    <section class="privacy-content">
      <div class="figure">
        <img :src="pageFigure">
      </div>
      <div class="text">
      <h1>Datenschutzerklärung</h1>
      <div class="privacy-content-par">
        <h2>1. Verantwortlicher</h2>
        <h5>Verantwortlich für die Datenverarbeitung auf dieser Website ist:</h5>
        <p><strong>Mohammed Hamzaoui</strong><br>
        Ahornstr. 11<br>
        93087 Alteglofsheim<br>
        Deutschland<br>
        Telefon: 01523 1783593<br>
        E-Mail: <a href="mailto:kontakt@automatisierung-regensburg.de">kontakt@automatisierung-regensburg.de</a></p>
      </div>
      <div class="privacy-content-par">
        <h2>2. Allgemeine Hinweise</h2>
        <p>Der Schutz Ihrer persönlichen Daten ist mir ein besonderes Anliegen. Ich behandle Ihre personenbezogenen Daten vertraulich und entsprechend den gesetzlichen Datenschutzvorschriften sowie dieser Datenschutzerklärung.</p>
        <p>
          Beim Besuch dieser Website werden verschiedene personenbezogene Daten erhoben. Personenbezogene Daten sind Daten, mit denen Sie persönlich identifiziert werden können.
        </p>
      </div>
      <div class="privacy-content-par">
        <h2>3. Hosting</h2>
        <p>Diese Website wird bei Hetzner Online GmbH, Industriestr. 25, 91710 Gunzenhausen, gehostet.</p>
        <p>Hetzner verarbeitet personenbezogene Daten im Rahmen eines Auftragsverarbeitungsvertrags gemäß Art. 28 DSGVO.</p><br/>
        <p>Die Server von Hetzner befinden sich in Deutschland.</p>
      </div>
      <div class="privacy-content-par">
        <h2>4. Server-Log-Dateien</h2>
        <p>Der Provider dieser Seiten erhebt und speichert automatisch Informationen in sogenannten Server-Log-Dateien, die Ihr Browser automatisch übermittelt. Dies sind:</p>
        <ul style="list-style: circle !important; padding-left: 25px; padding-left: 25px; padding-top: 25px;">
          <li>Browsertyp und Browserversion</li>
          <li>verwendetes Betriebssystem</li>
          <li>Referrer URL</li>
          <li>Hostname des zugreifenden Rechners</li>
          <li>Uhrzeit der Serveranfrage</li>
          <li>IP-Adresse</li>
        </ul>
      </div>
      <div class="privacy-content-par">
        <h2>5. Kontaktformular / E-Mail-Kontakt</h2>
        <p>Wenn Sie mir per Kontaktformular oder E-Mail Anfragen zukommen lassen, werden Ihre Angaben inklusive der von Ihnen dort angegebenen Kontaktdaten zur Bearbeitung der Anfrage gespeichert.
          Diese Daten gebe ich nicht ohne Ihre Einwilligung weiter.
        </p>
        <p>
          Rechtsgrundlage für die Verarbeitung der Daten ist Art. 6 Abs. 1 lit. b DSGVO (vorvertragliche Maßnahmen bzw. Vertragserfüllung).
          Ihre Daten werden gelöscht, sobald der Zweck der Speicherung entfällt (z. B. nach abgeschlossener Bearbeitung Ihrer Anfrage).
        </p>
      </div>
      <div class="privacy-content-par">
        <h2>6. Cookies</h2>
          <p>Diese Website verwendet in der Regel <strong>keine Tracking Cookies.</strong></p>
          <p>Sollten einzelne technische Cookies erforderlich sein, erfolgt deren Einsatz auf Grundlage von Art. 6 Abs. 1 lit. f DSGVO</p>
          <p>Derzeit werden lediglich technisch notwendige Cookies (z. B. ein Sitzungs-Cookie und ein CSRF-Token) verwendet, die keine personenbezogenen Daten enthalten und nach Sitzungsende automatisch gelöscht werden.</p>
      </div>
      <div class="privacy-content-par">
        <h2>7. Analyse-Tools</h2>
        <p>Es werden <strong>Tracking-, Analyse- oder Marketing-Tools verwendet.</strong></p>
        <p>Es werden keine externen Ressourcen (z. B. Google Fonts, YouTube, Facebook, Maps) eingebunden. Alle Inhalte werden lokal bereitgestellt.</p>
      </div>
      <div class="privacy-content-par">
        <h2>8. Ihre Rechte</h2>
        <p>Sie haben im Rahmen der geltenden gesetzlichen Bestimmungen jederzeit das Recht auf:</p>
        <ul style="list-style: circle !important; padding-left: 25px; padding-top: 25px;">
          <li>Auskunft über Ihre gespeicherten personenbezogenen Daten (Art. 15 DSGVO)</li>
          <li>Berichtigung unrichtiger Daten (Art. 16 DSGVO)</li>
          <li>Löschung Ihrer Daten (Art. 17 DSGVO)</li>
          <li>Einschränkung der Verarbeitung (Art. 18 DSGVO)</li>
          <li>Datenübertragbarkeit (Art. 20 DSGVO)</li>
          <li>Widerspruch gegen die Verarbeitung (Art. 21 DSGVO)</li>
        </ul>
        <p>Hierzu können Sie sich jederzeit unter der oben angegebenen Adresse an mich wenden.</p>
      </div>
      <div class="privacy-content-par">      
        <h2>9. SSL- bzw. TLS-Verschlüsselung</h2>
        <p>Diese Seite nutzt aus Sicherheitsgründen und zum Schutz der Übertragung vertraulicher Inhalte eine SSL- bzw. TLS-Verschlüsselung.</p>
        <p>Eine verschlüsselte Verbindung erkennen Sie daran, dass die Adresszeile des Browsers von „http://“ auf „https://“ wechselt und an dem Schloss-Symbol in Ihrer Browserzeile.</p>
      </div>
      <div class="privacy-content-par">
        <h2>10. Widerspruch gegen Werbe-E-Mails</h2>
        <p>Der Nutzung von im Rahmen der Impressumspflicht veröffentlichten Kontaktdaten zur Übersendung von nicht ausdrücklich angeforderter Werbung wird widersprochen.</p>
      </div>
      </div>

    
    </section>

</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Layouts/Layout.vue'
import pageFigure from '@img/legal/schutz.svg'      
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
