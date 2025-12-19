<template>
    <section id="booking" class="booking" style="min-height: 100vh; padding: 3rem 0;">
        <div class="d-flex justify-content-center">
            <div class="booking-form-section" style="width: 600px; max-width: 100%;">
                <div class="alert alert-success" role="alert" v-if="flash?.message">
                    {{ flash.message }}
                </div>
                <div class="form-container w-full">
                    <form class="reservation-form" @submit.prevent="submitLogin" method="POST">
                        <div class="form-section mb-3">
                            <h4>Recuperar Contraseña</h4>
                            <div class="form-grid">
                                <div class="form-group full-width mb-0">
                                    <label for="email" class="form-label required">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="email"
                                           v-model="form.email"
                                           :class="{'is-invalid': form.errors.email}"
                                           placeholder="ejemplo@gmail.com" required="">
                                    <div class="invalid-feedback" v-show="form.errors.email"
                                         v-text="form.errors.email"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <div>
                                    ¿Ya tienes una cuenta? <Link :href="route('loginForm')">Iniciar Sesión</Link>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-lock me-2" style="font-size: 18px"></i>
                                Recuperar Contraseña
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

const {flash} = defineProps({
    flash: {}
})

const form = useForm({
    email: '',
})

const submitLogin = () => {
    form.post(route('password.email'));
}
</script>

