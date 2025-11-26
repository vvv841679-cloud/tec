<template>
    <Modal title="Nuevas Habitaciones" formId="createRoomForm">
        <form @submit.prevent="submitCreate" method="post" id="createRoomForm" class="gap-inputs">
            <div class="row">
                <BaseInput
                    type="number"
                    min="1"
                    label="Número de Habitación"
                    v-model="form.room_number"
                    :error="form.errors.room_number"
                    placeholder="Su número de habitación"
                    required/>
            </div>
            <div class="row">
                <select-box
                    label="Tipo de Habitación"
                    placeholder="Elija Su Tipo de Habitación"
                    v-model="form.room_type_id"
                    :options="roomTypes"
                    required
                    :error="form.errors.room_type_id" />
            </div>
            <div class="row">
                <BaseInput
                    type="number"
                    min="1"
                    label="Número de Piso"
                    v-model="form.floor_number"
                    :error="form.errors.floor_number"
                    placeholder="Su número de piso"
                    required/>
            </div>
            <div class="row">
                <select-box
                    label="Estado"
                    v-model="form.status"
                    :options="statuses"
                    required
                    :error="form.errors.status" />
            </div>
            <div class="row">
                <select-box
                    label="Preferencia de Fumador"
                    v-model="form.smoking_preference"
                    :options="smoking"
                    required
                    :error="form.errors.smoking_preference" />
            </div>
        </form>
    </Modal>
</template>
<script setup lang="ts">
import Modal from "../../../Components/Modal.vue";
import {useForm} from "@inertiajs/vue3";
import BaseInput from "../../../Components/BaseInput.vue";
import {inject} from "vue";
import SelectBox from "../../../Components/SelectBox.vue";

const {defaultStatus, defaultSmoking} = defineProps({
    roomTypes: Object,
    statuses: Object,
    smoking: Object,
    defaultSmoking: String,
    defaultStatus: String,
})

const form = useForm({
    room_number: '',
    room_type_id: '',
    floor_number: '',
    status: defaultStatus,
    smoking_preference: defaultSmoking,
});

const closeModal = inject('closeModal');
const submitCreate = () => {
    form.post(route('admin.rooms.store'), {
        onSuccess: () => closeModal()
    })
}

</script>
