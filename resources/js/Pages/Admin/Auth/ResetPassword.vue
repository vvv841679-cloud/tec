<template>
    <div class="page page-center h-fullscreen">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <img width="70" src="/resources/images/Homa.png" alt="logo"/> <span
                class="bold h3 brand-color">Hotel  LA PASCANA</span>
            </div>
            <form class="card card-md" @submit.prevent="submitForm" method="POST" autocomplete="off">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Restablecer contraseña</h2>
                    <div class="mb-3">
                        <base-input
                            label="Correo electrónico"
                            v-model="form.email"
                            :error="form.errors.email"
                            placeholder="Ingresa tu correo"
                            required/>
                    </div>
                    <div class="mb-3">
                        <base-input
                            type="password"
                            label="Contraseña"
                            v-model="form.password"
                            :error="form.errors.password"
                            placeholder="Ingresa tu contraseña"
                            required/>
                    </div>
                    <div class="mb-3">
                        <base-input
                            type="password"
                            label="Confirmar Contraseña"
                            v-model="form.password_confirmation"
                            :error="form.errors.password_confirmation"
                            placeholder="Confirma tu contraseña"
                            required/>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary btn-4 w-100">
                            <IconLockOpen2 class="icon icon-2" />
                            Restablecer mi contraseña
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import {useForm} from "@inertiajs/vue3";
import BaseInput from "../../../Components/BaseInput.vue";
import "@tabler/core/dist/css/tabler.min.css";
import {IconLockOpen2} from "@tabler/icons-vue";

defineOptions({ layout: null })

const {email, token} = defineProps({
    email: '',
    token: '',
})

const form = useForm({
    email: email,
    token: token,
    password: '',
    password_confirmation: '',
})

const submitForm = () => {
    form.post(route('admin.password.update'));
}
</script>

