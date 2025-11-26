<template>
    <Modal title="Nuevo Menú" formId="createMenuForm">
        <form @submit.prevent="submitCreate" method="post" id="createMenuForm">
            <div class="row">
                <BaseInput
                    label="Nombre"
                    v-model="form.name"
                    :error="form.errors.name"
                    placeholder="Nombre del menú"
                    required
                />
                
                <SelectBox
                    label="Menú Padre"
                    v-model="form.parent_id"
                    :error="form.errors.parent_id"
                    :options="parentMenuOptionsFormatted"
                    placeholder="Sin padre (menú principal)"
                />

                <BaseInput
                    label="Nombre de Ruta"
                    v-model="form.route_name"
                    :error="form.errors.route_name"
                    placeholder="admin.dashboard"
                    required
                />

                <BaseInput
                    label="Icono"
                    v-model="form.icon"
                    :error="form.errors.icon"
                    placeholder="IconHome"
                />

                <BaseInput
                    label="Orden"
                    type="number"
                    v-model="form.order"
                    :error="form.errors.order"
                    placeholder="1"
                    required
                />

                <div class="col-12 mb-3">
                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" v-model="form.active">
                        <span class="form-check-label">Activo</span>
                    </label>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Roles (dejar vacío para todos)</label>
                    <div v-for="role in roles" :key="role.id" class="form-check">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            :value="role.id"
                            v-model="form.roles"
                            :id="`role-${role.id}`">
                        <label class="form-check-label" :for="`role-${role.id}`">
                            {{ role.name }}
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </Modal>
</template>
<script setup>
import Modal from "../../../Components/Modal.vue";
import {useForm} from "@inertiajs/vue3";
import BaseInput from "../../../Components/BaseInput.vue";
import SelectBox from "../../../Components/SelectBox.vue";
import {inject, computed} from "vue";

const props = defineProps({
    parentMenus: Array,
    roles: Array,
});

const form = useForm({
    name: '',
    parent_id: null,
    route_name: '',
    icon: '',
    order: 1,
    active: true,
    roles: [],
});

// Formato correcto para SelectBox: {id: label}
const parentMenuOptionsFormatted = computed(() => {
    const options = {};
    props.parentMenus.forEach(menu => {
        options[menu.id] = menu.name;
    });
    return options;
});

const closeModal = inject('closeModal');
const submitCreate = () => {
    form.post(route('admin.menus.store'), {
        onSuccess: () => closeModal()
    })
}

</script>
