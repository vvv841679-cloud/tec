<template>
    <Modal title="Nuevo País" formId="createCountryForm">
        <form @submit.prevent="submitCreate" method="post" id="createCountryForm" class="gap-inputs">
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
                    label="Abreviatura"
                    v-model="form.short"
                    :error="form.errors.short"
                    placeholder="abreviatura"
                    required
                />
            </div>
        </form>
    </Modal>
</template>
<script setup lang="ts">
import Modal from "../../../Components/Modal.vue";
import {useForm} from "@inertiajs/vue3";
import BaseInput from "../../../Components/BaseInput.vue";
import {inject} from "vue";

const form = useForm({
    name: '',
    short: '',
});

const closeModal = inject('closeModal');
const submitCreate = () => {
    form.post(route('admin.countries.store'), {
        onSuccess: () => closeModal()
    })
}

</script>
