<template>
    <Modal title="Editar Rol" formId="editRolesForm">
        <form @submit.prevent="submitEdit" method="post" id="editRolesForm">
            <div class="row">
                <BaseInput
                    label="Nombre"
                    v-model="form.name"
                    :error="form.errors.name"
                    placeholder="nombre"
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

const {role} = defineProps({
    role: Object
})

const form = useForm({
    name: role.name,
});

const closeModal = inject('closeModal');
const submitEdit = () => {
    form.put(route('admin.roles.update', role.id), {
        onSuccess: () => closeModal()
    })
}

</script>
