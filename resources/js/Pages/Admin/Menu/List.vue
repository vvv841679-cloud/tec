<template>
    <Head title="menús" />
    <div class="row g-2 align-items-center mb-4">
        <div class="col">
            <h2 class="page-title">Menús</h2>
        </div>
        <!-- Page title actions -->
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <button v-if="access.createMenu" class="btn btn-primary btn-5 d-none d-sm-inline-block"
                        @click="openModal = !openModal">
                    <IconPlus class="icon icon-2"/>
                    Nuevo Menú
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-table">
            <div class="card-header">
                <div class="row w-full">
                    <div class="col">
                        <h3 class="card-title mb-0">Menús</h3>
                        <p class="text-secondary m-0">Lista de Menús del Sistema.</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                    <thead>
                    <tr>
                        <th class="w-1"></th>
                        <th>Nombre</th>
                        <th>Ruta</th>
                        <th>Icono</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Padre</th>
                        <th>Roles</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody class="table-tbody">
                        <tr v-for="menu in menus" :key="menu.id">
                            <td>
                                <input class="form-check-input m-0 align-middle table-selectable-check" type="checkbox"
                                       aria-label="Select menu" value="true">
                            </td>
                            <td>
                                <span v-if="menu.parent_id" class="text-muted ms-3">└─</span>
                                {{ menu.name }}
                            </td>
                            <td><code>{{ menu.route_name }}</code></td>
                            <td>
                                <span class="badge bg-blue-lt">{{ menu.icon || 'N/A' }}</span>
                            </td>
                            <td>{{ menu.order }}</td>
                            <td>
                                <span class="badge" :class="menu.active ? 'bg-success' : 'bg-danger'">
                                    {{ menu.active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>{{ menu.parent?.name || '-' }}</td>
                            <td>
                                <span v-if="menu.roles && menu.roles.length > 0" 
                                      class="badge bg-purple-lt me-1"
                                      v-for="role in menu.roles" :key="role.id">
                                    {{ role.name }}
                                </span>
                                <span v-else class="text-muted">Todos</span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                            data-bs-toggle="dropdown" aria-expanded="true">
                                        Acciones
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" data-popper-placement="bottom-end">
                                        <button class="dropdown-item align-middle"
                                                @click="openEditModal(menu)">
                                            <IconEdit class="icon icon1"/>
                                            Editar
                                        </button>
                                        <button class="dropdown-item"
                                                @click="() => confirmDelete(route('admin.menus.destroy', menu.id))">
                                            <IconTrash class="icon icon1"/>
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <Create v-if="openModal && !editingMenu"/>
    <Update v-if="openModal && editingMenu" :menu="editingMenu"/>
</template>

<script setup>
import {provide, ref} from "vue";
import Create from "./Create.vue";
import {IconEdit, IconTrash, IconPlus} from '@tabler/icons-vue';
import Update from "./Update.vue";
import {useConfirm} from "../../../Composables/useConfirm.js";

const confirmDelete = useConfirm();

defineProps({
    menus: Object,
    access: Object,
});

let editingMenu = ref(null);
let openModal = ref(false);

provide("closeModal", () => {
    openModal.value = false
    if(editingMenu) editingMenu.value = null
});

const openEditModal = (menu) => {
    editingMenu.value = menu;
    openModal.value = true
}

</script>
