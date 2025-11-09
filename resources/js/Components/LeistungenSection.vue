<template>
  <section id="leistungen" class="task feature-section feature-style-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xxl-5 col-xl-5 col-lg-7 col-md-8">
          <div class="section-title text-center mb-60">
            <h3 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Wir digitalisieren wiederkehrende Abläufe</h3>
          </div>
        </div>
      </div>

      <div class="row justify-content-center">
        <div class="col-lg-6 col-xl-6">
          <div class="task-image text-lg-right wow fadeInUp" data-wow-delay=".5s">
            <img src="img/tasks/task.svg" alt="">
          </div>
        </div>
        <div class="row">
          <div class="col-lg-3 col-md-6">
            <div class="single-feature wow fadeInUp" data-wow-delay=".2s">
              <div class="icon">
                <i class="fa-solid fa-users-gear"></i>
                <svg width="110" height="72" viewBox="0 0 110 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M110 54.7589C110 85.0014 85.3757 66.2583 55 66.2583C24.6243 66.2583 0 85.0014 0 54.7589C0 24.5164 24.6243 0 55 0C85.3757 0 110 24.5164 110 54.7589Z" fill="#EBF4FF"/>
                  </svg>                  
              </div>
              <div class="content">
                <h5>Mini CRM Systeme</h5>
                <p>Wir entwickeln kleine CRM Lösungen, mit denen Sie Kunden, Angebote und Rechnungen an einem Ort verwalten. Aus Formularen oder Excel Daten entstehen automatisch saubere PDFs mit Logo und Positionen.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="single-feature wow fadeInUp" data-wow-delay=".4s">
              <div class="icon">
                <i class="fa-solid fa-link"></i>
                <svg width="110" height="72" viewBox="0 0 110 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M110 54.7589C110 85.0014 85.3757 66.2583 55 66.2583C24.6243 66.2583 0 85.0014 0 54.7589C0 24.5164 24.6243 0 55 0C85.3757 0 110 24.5164 110 54.7589Z" fill="#EBF4FF"/>
                  </svg> 
              </div>
              <div class="content">
                <h5>System und Datenintegration</h5>
                <p>Wir verbinden Ihre bestehenden Systeme, damit Informationen automatisch fließen. Ob CRM, E-Mail, Buchhaltung oder Onlineshop</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="single-feature wow fadeInUp" data-wow-delay=".6s">
              <div class="icon">
                <i class="fa-solid fa-chart-line"></i>
                <svg width="110" height="72" viewBox="0 0 110 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M110 54.7589C110 85.0014 85.3757 66.2583 55 66.2583C24.6243 66.2583 0 85.0014 0 54.7589C0 24.5164 24.6243 0 55 0C85.3757 0 110 24.5164 110 54.7589Z" fill="#EBF4FF"/>
                  </svg> 
              </div>
              <div class="content">
                <h5>Reporting & Prozessanalyse</h5>
                <p>Wir automatisieren die Datenauswertung und erstellen individuelle Dashboards oder PDF-Berichte mit Ihren wichtigsten Kennzahlen.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="single-feature wow fadeInUp" data-wow-delay=".2s">
              <div class="icon">
                <i class="fa-solid fa-code"></i>
                <svg width="110" height="72" viewBox="0 0 110 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M110 54.7589C110 85.0014 85.3757 66.2583 55 66.2583C24.6243 66.2583 0 85.0014 0 54.7589C0 24.5164 24.6243 0 55 0C85.3757 0 110 24.5164 110 54.7589Z" fill="#EBF4FF"/>
                  </svg> 
              </div>
              <div class="content">
                <h5>Web Entwickelung</h5>
                <p>Wir erstellen moderne, leistungsstarke Weblösungen, die perfekt zu Ihren Geschäftsprozessen passen. Vom individuellen Kundenportal über Schnittstellen bis hin zu komplett angepassten Unternehmensanwendungen</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>
<style lang="scss" scoped>
.row {
  display: flex;
  flex-wrap: wrap; /* keep responsive behavior */
}

.col-lg-3,
.col-md-6 {
  display: flex; /* make column stretch its child */
}

.single-feature {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  flex: 1 1 auto;        /* allow it to grow evenly */
  height: 100%;          /* equalize height across all */
  padding: 2rem;
  border-radius: 20px;
  background: #fff;
  box-shadow: 5px 18px 130px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
    @media (max-width: 768px) {
        transform: scale(0.86);

  }
  &:hover {
    transform: scale(1.1);
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.1);
  }

  .icon {
    text-align: center;
    margin-bottom: 1.5rem;

    i {
      font-size: 2rem;
      color: #0d6efd;
      margin-bottom: 0.5rem;
    }

    svg {
      display: block;
      margin: 0 auto;
    }
  }

  .content {
    flex-grow: 1; /* pushes footer or buttons down if added */
    text-align: center;

    h5 {
      font-weight: 700;
      margin-bottom: 0.75rem;
      color: #1a1a1a;
    }

    p {
      color: #555;
      font-size: 0.95rem;
    }
  }
}

</style>