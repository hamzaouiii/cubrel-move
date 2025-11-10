<template>
      <footer class="footer footer-style-4">
      <div class="container">
        <div class="widget-wrapper">
          <div class="row">
            <div class="col-xl-4 col-lg-4 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".2s">
                <div class="logo">
                  <Link href="/"> <img src="/img/logo/vector/default-monochrome.svg" alt="logo"> </Link>
                </div>
              </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".4s">
                <h6>Rechtliches</h6>
                <ul class="links">
                  <li> <Link href="/impressum"  >Impressum</Link> </li>
                  <li> <Link href="/datenschutz"  >Datenschutzerklärung</Link> </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="copyright-wrapper wow fadeInUp" data-wow-delay=".2s">
         <p>&copy; 2025 <a href="https://hamzaoui.cc/" target="_blank">Simo Hamzaoui</a> – automatisierung-regensburg.de</p>
        </div>
      </div>
    </footer>

</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>