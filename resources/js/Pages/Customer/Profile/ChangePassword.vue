<template>
    <div class="container">
        <Head title="cambiar contraseña"/>
        <div class="row g-2 align-items-center mb-2">
            <div class="col">
                <h2 class="page-title text-capitalize">Cambiar Contraseña</h2>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Cambiar Contraseña</div>
            <div class="card-body">
                <form id="createRoomTypes" method="post" @submit.prevent="handleForm"
                      class="gap-inputs">
                    <div class="row">
                        <div class="col-6">
                            <base-input
                                type="password"
                                label="Contraseña Actual"
                                v-model="form.current_password"
                                :error="form.errors.current_password"
                                required
                                placeholder="Contraseña actual"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <base-input
                                type="password"
                                label="Nueva Contraseña"
                                v-model="form.password"
                                :error="form.errors.password"
                                required
                                placeholder="Nueva contraseña"/>
                        </div>
                        <div class="col-6">
                            <base-input
                                type="password"
                                label="Confirmar Contraseña"
                                v-model="form.password_confirmation"
                                :error="form.errors.password_confirmation"
                                required
                                placeholder="Confirmar contraseña"/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary ms-auto" form="createRoomTypes">
                    <IconDeviceFloppy class="icon"/>
                    <span>Guardar</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import {useForm, usePage} from "@inertiajs/vue3";
import BaseInput from "../../../Components/BaseInput.vue";
import {IconDeviceFloppy, IconArrowLeft} from '@tabler/icons-vue';

const page = usePage();

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const handleForm = () => {
    form.post(route('customer.password.save'), {
        onSuccess: () => {
            form.reset();
        }
    });
}
</script>
