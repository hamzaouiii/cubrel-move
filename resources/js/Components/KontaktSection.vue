<template>
  <section id="kontakt" class="contact contact-section contact-style-3">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xxl-5 col-xl-5 col-lg-7 col-md-10">
          <div class="section-title text-center mb-50">
            <h3 class="mb-15">Kontakt</h3>
            <p>Senden Sie Ihre Anfrage, Sie erhalten in der Regel noch am selben Tag eine Antwort.</p>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-4">
          <div class="contact-image text-lg-right wow fadeInUp" data-wow-delay=".5s">
            <img src="img/contact/contact-img.svg" alt="Illustration Kontakt" />
          </div>
        </div>

        <div class="col-lg-8">
          <div class="contact-form-wrapper">
            <div v-if="flash" class="flash" role="alert">
              {{ flash }}
            </div>
            <div v-else-if="rateError" class="rate-error" role="alert">
              {{ rateError }}
            </div>
            <div v-else-if="showLoader" class="loader-container text-center py-5">
              <div class='loader' role="status" style="width:3rem;height:3rem;">
              </div>
              <p class="mt-3 mb-0">Nachricht wird gesendet...</p>
            </div>

            <form v-else @submit.prevent="submit" novalidate>
              <div class="row">
                <div class="col-md-6">
                  <div class="single-input">
                    <input
                      id="name"
                      type="text"
                      class="form-input"
                      :class="{ 'is-invalid': form.errors.name }"
                      placeholder="Name"
                      v-model="form.name"
                      autocomplete="name"
                      @blur="validateField('name')"
                    />
                    <i class="fa-solid fa-user"></i>
                    <div v-if="form.errors.name" class="invalid-feedback">
                      {{ form.errors.name }}
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="single-input">
                    <input
                      id="email"
                      type="email"
                      class="form-input"
                      :class="{ 'is-invalid': form.errors.email }"
                      placeholder="Email"
                      v-model="form.email"
                      autocomplete="email"
                      @blur="validateField('email')"
                    />
                    <i class="fa-solid fa-envelope"></i>
                    <div v-if="form.errors.email" class="invalid-feedback">
                      {{ form.errors.email }}
                    </div>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="single-input">
                    <input
                      id="phone"
                      type="text"
                      class="form-input"
                      :class="{ 'is-invalid': form.errors.phone }"
                      placeholder="Telefonnummer"
                      v-model="form.phone"
                      autocomplete="tel"
                      @blur="validateField('phone')"
                    />
                    <i class="fa-solid fa-phone"></i>
                    <div v-if="form.errors.phone" class="invalid-feedback ">
                      {{ form.errors.phone }}
                    </div>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="single-input">
                    <textarea
                      id="message"
                      class="form-input"
                      :class="{ 'is-invalid': form.errors.message }"
                      placeholder="Nachricht"
                      rows="6"
                      v-model="form.message"
                      @blur="validateField('message')"
                    ></textarea>
                    <i class="fa-solid fa-message"></i>
                    <div v-if="form.errors.message" class="invalid-feedback">
                      {{ form.errors.message }}
                    </div>
                  </div>
                </div>

                <!-- Submit -->
                <div class="col-md-12">
                  <div class="form-button">
                    <button type="submit" class="button" :disabled="form.processing">
                      <i class="fa-regular fa-paper-plane"></i>
                      <span v-if="!form.processing">Senden</span>
                      <span v-else>Senden…</span>
                    </button>
                  </div>
                </div>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
  import { ref } from 'vue'
  import { useForm } from '@inertiajs/vue3'

  const endpoint = typeof route === 'function' ? route('contact.store') : '/contact'
  const REQUIRED_CONTACT_MSG = 'E-Mail oder Telefonnummer ist erforderlich.'

  const flash = ref('')
  const showLoader = ref(false)
  const rateError = ref('')

  const form = useForm({
    name: '',
    email: '',
    phone: '',
    message: '',
  });

  function isEmail(v) {
    if (!v) return false 
    const emailRegex =
      /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z]{2,})+$/
    return emailRegex.test(v)
    }

  function trimAll() {
    form.name = form.name?.trim() ?? ''
    form.email = form.email?.trim() ?? ''
    form.phone = form.phone?.trim() ?? ''
    form.message = form.message?.trim() ?? ''
  }

  function clearFieldError(field) {
    if (typeof form.clearErrors === 'function') {
      form.clearErrors(field)
    } else {
      if (form.errors && form.errors.hasOwnProperty(field)) delete form.errors[field]
    }
  }

  function validateField(field) {
    clearFieldError(field)
    const v = (form[field] ?? '').toString()

    switch (field) {
      case 'name':
        if (!v) return form.setError('name', 'Name ist erforderlich.')
        if (v.length > 150) return form.setError('name', 'Maximal 150 Zeichen.')
        break

      case 'email':
        enforceEitherOr('email')
        if (v) {
          if (v.length > 190) return form.setError('email', 'Maximal 190 Zeichen.')
          if (!isEmail(v)) return form.setError('email', 'Bitte eine gültige E-Mail angeben.')
        }
        break

      case 'phone':
        if (v && v.length > 50) return form.setError('phone', 'Maximal 50 Zeichen.')
        enforceEitherOr('phone')
        break

      case 'message':
        if (v.length > 5000) return form.setError('message', 'Maximal 5000 Zeichen.')
        break
    }
  }

  function enforceEitherOr(trigger) {
    const e = (form.email || '').trim()
    const p = (form.phone || '').trim()

    if (!e && !p) {
      if (!form.errors.email) form.setError('email', REQUIRED_CONTACT_MSG)
      if (!form.errors.phone) form.setError('phone', REQUIRED_CONTACT_MSG)
    } else {
      if (trigger === 'email' && e && form.errors.phone === REQUIRED_CONTACT_MSG) {
        clearFieldError('phone')
      }
      if (trigger === 'phone' && p && form.errors.email === REQUIRED_CONTACT_MSG) {
        clearFieldError('email')
      }
    }
  }

  function hasRealErrors() {
    return Object.values(form.errors).filter(Boolean).length > 0
  }

  function validateAll() {
    if (typeof form.clearErrors === 'function') form.clearErrors()
    trimAll();
    ['name', 'email', 'phone', 'message'].forEach(validateField)
    enforceEitherOr()
    return !hasRealErrors()
  }

  const submit = async () => {
    flash.value = ''
    rateError.value = ''
    if (!validateAll()) return
    showLoader.value = true
      // await new Promise(resolve => setTimeout(resolve, 2000))
    form.post(endpoint, {
      preserveScroll: true,
      onSuccess: () => {
        flash.value = 'Nachricht gesendet. Vielen Dank!'
        form.reset()
        if (typeof form.clearErrors === 'function') form.clearErrors()
      },
      onError: (errors) => {
        const msg = errors?.message || 'Zu viele Anfragen. Bitte versuchen Sie es später erneut.'
        rateError.value = msg
        form.setError('message', msg)
      },
      onFinish: () => {
        showLoader.value = false

      },
    })
  }
</script>
