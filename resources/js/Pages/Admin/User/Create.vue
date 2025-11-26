<template>
    <Modal title="Nuevos Usuarios" formId="createUserForm">
        <form @submit.prevent="submitCreate" method="post" id="createUserForm" class="gap-inputs">
            <div class="row">
                <div class="col-lg-6">
                    <BaseInput
                        label="Nombre"
                        v-model="form.first_name"
                        :error="form.errors.first_name"
                        placeholder="su nombre"
                        required
                    />
                </div>
                <div class="col-lg-6">
                    <BaseInput
                        label="Apellido"
                        v-model="form.last_name"
                        :error="form.errors.last_name"
                        placeholder="su apellido"
                        required
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <BaseInput
                        label="Correo Electrónico"
                        type="email"
                        v-model="form.email"
                        :error="form.errors.email"
                        placeholder="su correo electrónico"
                        required
                    />
                </div>
                <div class="col-lg-6">
                    <BaseInput
                        label="Contraseña"
                        type="password"
                        v-model="form.password"
                        :error="form.errors.password"
                        placeholder="su contraseña"
                        required
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <SelectBox v-model="form.roles"
                               :options="roles"
                               name="roles"
                               :error="form.errors.roles || form.errors['roles.0']"
                               multiple=""
                               label="Seleccionar Roles">
                    </SelectBox>
                </div>
                <div class="col-lg-6">
                    <div class="form-label">Sexo</div>
                    <RadioButton v-model="form.sex" :options="sexes"/>
                </div>
            </div>
        </form>
    </Modal>
</template>
<script setup lang="ts">
import Modal from "../../../Components/Modal.vue";
import {useForm} from "@inertiajs/vue3";
import BaseInput from "../../../Components/BaseInput.vue";
import {inject, defineProps} from "vue";
import RadioButton from "../../../Components/RadioButton.vue";
import SelectBox from "../../../Components/SelectBox.vue";


defineProps({
    'roles': Object,
    'sexes': Object
});

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    sex: '',
    roles: [],
});


const closeModal = inject('closeModal');
const submitCreate = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => closeModal()
    })
}

</script>
