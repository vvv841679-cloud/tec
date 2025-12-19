<template>
    <section id="booking" class="booking section" style="min-height: 100vh; padding: 3rem 0;">
        <div class="container d-flex justify-content-center">
            <div class="booking-form-section" style="width: 600px; max-width: 100%;">
                <div class="form-container w-full">
                    <form class="reservation-form" @submit.prevent="submitLogin" method="POST">
                        <div class="form-section mb-3">
                            <h4>Completar Registro</h4>
                            <div class="form-grid">
                                <div class="form-group full-width mb-0">
                                    <label for="email" class="form-label required">Nombre</label>
                                    <input type="text" class="form-control" id="email"
                                           v-model="form.first_name"
                                           :class="{'is-invalid': form.errors.first_name}"
                                           placeholder="Tu nombre" required="">
                                    <div class="invalid-feedback" v-show="form.errors.first_name"
                                         v-text="form.errors.first_name"></div>
                                </div>
                                <div class="form-group full-width mb-0">
                                    <label for="email" class="form-label required">Apellido</label>
                                    <input type="text" class="form-control" id="email"
                                           v-model="form.last_name"
                                           :class="{'is-invalid': form.errors.last_name}"
                                           placeholder="Tu apellido" required="">
                                    <div class="invalid-feedback" v-show="form.errors.last_name"
                                         v-text="form.errors.last_name"></div>
                                </div>
                                <div class="form-group full-width mb-0">
                                    <label for="email" class="form-label">Celular</label>
                                    <input type="text" class="form-control" id="email"
                                           v-model="form.mobile"
                                           :class="{'is-invalid': form.errors.mobile}"
                                           placeholder="Tu celular">
                                    <div class="invalid-feedback" v-show="form.errors.mobile"
                                         v-text="form.errors.mobile"></div>
                                </div>
                                <div class="form-group full-width mb-0">
                                    <label for="email" class="form-label required">Contraseña</label>
                                    <input type="password" class="form-control" id="email"
                                           v-model="form.password"
                                           :class="{'is-invalid': form.errors.password}"
                                           placeholder="Tu contraseña" required="">
                                    <div class="invalid-feedback" v-show="form.errors.password"
                                         v-text="form.errors.password"></div>
                                </div>
                                <div class="form-group full-width mb-0">
                                    <label for="email" class="form-label required">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="email"
                                           v-model="form.password_confirmation"
                                           :class="{'is-invalid': form.errors.password_confirmation}"
                                           placeholder="Confirmar contraseña" required="">
                                    <div class="invalid-feedback" v-show="form.errors.password_confirmation"
                                         v-text="form.errors.password_confirmation"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-door-open me-2" style="font-size: 18px"></i>
                                Completar Registro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import {useForm} from "@inertiajs/vue3";

const form = useForm({
    first_name: '',
    last_name: '',
    mobile: '',
    password: '',
    password_confirmation: '',
})

const submitLogin = () => {
    form.post(route('completeRegister'));
}
</script>

