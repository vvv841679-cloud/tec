<template>
    <div class="page page-center h-fullscreen">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <img width="70" src="/resources/images/Homa.png" alt="logo"/> <span
                class="bold h3 brand-color">Hotel </span>
            </div>
            <div class="alert alert-success" role="alert" v-if="flash?.message">
                {{ flash.message }}
            </div>
            <form class="card card-md" @submit.prevent="submitForm" autocomplete="off">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Olvidé mi contraseña</h2>
                    <p class="text-secondary mb-4">Ingresa tu correo electrónico y tu contraseña será restablecida y enviada por correo.</p>
                    <div class="mb-3">
                        <base-input
                            label="Correo electrónico"
                            v-model="form.email"
                            :error="form.errors.email"
                            placeholder="Ingresa tu correo"
                            required/>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary btn-4 w-100">
                            <IconMail class="icon icon-2" />
                            Enviarme nueva contraseña
                        </button>
                    </div>
                </div>
            </form>
            <div class="text-center text-secondary mt-3">Olvídalo, <Link :href="route('admin.login')">llévame de vuelta</Link> a la pantalla de inicio de sesión.</div>
        </div>
    </div>
</template>

<script setup>
import {useForm} from "@inertiajs/vue3";
import BaseInput from "../../../Components/BaseInput.vue";
import "@tabler/core/dist/css/tabler.min.css";
import {IconMail} from '@tabler/icons-vue';

defineOptions({ layout: null })

const {flash} = defineProps({
    flash: {}
})

const form = useForm({
    email: '',
})

const submitForm = () => {
    form.post(route('admin.password.email'));
}
</script>

