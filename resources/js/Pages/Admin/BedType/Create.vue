<template>
    <Modal title="Nuevo Tipo de Cama" formId="createBedTypeForm">
        <form @submit.prevent="submitCreate" method="post" id="createBedTypeForm" class="gap-inputs">
            <div class="row">
                <BaseInput
                    label="Nombre"
                    v-model="form.name"
                    :error="form.errors.name"
                    placeholder="nombre"
                    required
                />
            </div>
            <div class="row">
                <BaseInput
                    label="Capacidad"
                    type="number"
                    min="1"
                    max="2"
                    v-model="form.capacity"
                    :error="form.errors.capacity"
                    placeholder="capacidad"
                    required
                />
            </div>
        </form>
    </Modal>
</template>
<script setup>
import Modal from "../../../Components/Modal.vue";
import {useForm} from "@inertiajs/vue3";
import BaseInput from "../../../Components/BaseInput.vue";
import {inject} from "vue";

const form = useForm({
    name: '',
    capacity: '',
});

const closeModal = inject('closeModal');
const submitCreate = () => {
    form.post(route('admin.bedTypes.store'), {
        onSuccess: () => closeModal()
    })
}

</script>
