<template>
    <Head title="Crear Reserva"/>
    <div class="row g-2 align-items-center mb-4">
        <div class="col">
            <h2 class="page-title text-capitalize">Crear Reserva</h2>
        </div>
        <div class="col-auto ms-auto">
            <Link class="btn btn-1" :href="route('admin.bookings.index')">
                <IconArrowLeft class="icon"/>
                Volver
            </Link>
        </div>
    </div>
    <div class="row">
        <div class="card">
            <div class="card-header">Crear Reserva</div>
            <div class="card-body">
                <form id="createRoomTypes" method="post" @submit.prevent="handleCreateRoomType"
                      class="gap-inputs">
                    <div class="row">
                        <div class="col-6">
                            <base-input
                                type="number"
                                min="1"
                                label="Adultos"
                                v-model="form.adults"
                                :error="form.errors.adult"
                                placeholder="2"
                                required
                            />
                        </div>
                        <div class="col-6">
                            <base-input
                                type="number"
                                label="Niños"
                                min="0"
                                max="10"
                                v-model="form.children"
                                :error="form.errors.children"
                                placeholder="0"
                            />
                        </div>
                    </div>
                    <div class="row col-8" v-if="form.children_age.length">
                        <Repeater
                            label="Edades de los Niños"
                            v-model="form.children_age"
                            :errors="form.errors"
                            name="children_age"
                            :with-actions="false"
                            :heads="['Edad']">
                            <template #default="{ item, index }" :key="index">
                                <td>
                                    <select-box
                                        placeholder="Elija la Edad del Niño"
                                        :options="ages"
                                        v-model="item.age"
                                        :error="item.errors?.age"
                                        required>
                                    </select-box>
                                </td>
                            </template>
                        </Repeater>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <base-input
                                type="date"
                                label="Fecha de Entrada"
                                :min="currentDate()"
                                v-model="form.check_in"
                                :error="form.errors.check_in"
                                required
                            />
                        </div>
                        <div class="col-6">
                            <base-input
                                type="date"
                                label="Fecha de Salida"
                                :min="addDays(Date.now(), 1)"
                                v-model="form.check_out"
                                :error="form.errors.check_out"
                                :onchange="changeCheckOut"
                                required
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <select-box
                                label="Preferencia de Fumador"
                                v-model="form.smoking_preference"
                                :options="smoking"
                                required
                                :error="form.errors.smoking_preference"/>
                        </div>
                    </div>
                    <div class="row">
                        <Repeater
                            label="Habitaciones"
                            v-model="form.rooms"
                            :errors="form.errors"
                            name="rooms"
                            :heads="['Tipo de Habitación', 'Cantidad']"
                            :with-actions="!!roomTypes"
                            :default-row="{type_id: '', quantity: 1}">
                            <template #default="{ item, index }" :key="index" v-if="roomTypes">
                                <td>
                                    <select-box
                                        placeholder="Elija el Tipo de Habitación"
                                        :options="roomTypes"
                                        v-model="item.type_id"
                                        :error="item.errors?.type_id">
                                    </select-box>
                                </td>
                                <td>
                                    <base-input
                                        type="number"
                                        min="1"
                                        v-model="item.quantity"
                                        :error="item.errors?.quantity"
                                        placeholder="Cantidad"/>
                                </td>
                            </template>
                        </Repeater>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <select-box
                                label="Cliente"
                                placeholder="Elija Su Cliente"
                                :options="customers"
                                v-model="form.customer_id"
                                :error="form.errors.customer_id"
                                required>
                            </select-box>
                        </div>
                        <div class="col-6">
                            <select-box
                                label="Plan de Comidas"
                                placeholder="Elija Su Plan de Comidas"
                                :options="mealPlans"
                                v-model="form.meal_plan_id"
                                :error="form.errors.meal_plan_id"
                                required>
                            </select-box>
                        </div>
                    </div>
                    <div class="row">
                        <base-textarea
                            label="Solicitudes Especiales"
                            placeholder="Ej. Añadir almohada extra"
                            v-model="form.special_requests"
                            :error="form.errors.special_requests">
                        </base-textarea>
                    </div>
                    <div class="row">
                        <base-switch
                            label="Registrar Entrada Ahora"
                            v-model="form.check_in_now"/>
                    </div>
                </form>
                <div class="card mt-4 bg-primary-lt" v-if="prices">
                    <div class="card-body">
                        <div class="card-title">
                            Precios por {{ prices.nights }} Noche{{ prices.nights === 1 ? '' : 's' }} / {{ prices.nights + 1 }} Día{{ prices.nights === 0 ? '' : 's' }}
                        </div>
                        <div class="row mt-4">
                            <div class="col d-flex flex-column gap-4">
                                <div>Precio Total de Habitaciones: <span class="text-green bold">{{
                                        money_format(prices.totalRooms)
                                        }}</span>
                                </div>
                                <div :title="`$${roomType.price} / por noche`" v-for="roomType in prices.roomTypes">
                                    Precio de Habitación {{ roomType.name }} (x{{ roomType.rooms }}):
                                    <span class="text-green bold">{{ money_format(roomType.totalPrice) }}</span>
                                </div>
                            </div>
                            <div class="col d-flex flex-column gap-4">
                                <div>Precio Total del Plan de Comidas: <span class="text-green bold">{{
                                        money_format(prices.mealPlan)
                                        }}</span>
                                </div>
                                <div v-for="mealPlan in prices.mealPlanAges" :title="`$${mealPlan.price} / por noche`">
                                    Precio de Plan de Comidas {{ capitalize(mealPlan.name) }} (x{{ mealPlan.count }}):
                                    <span class="text-green bold">{{ money_format(mealPlan.totalPrice) }}</span>
                                </div>
                            </div>
                            <div class="col d-flex flex-column gap-4">
                                <div>Impuestos: <span class="text-green bold">{{ money_format(prices.tax) }}</span></div>
                                <div>Precio Total: <span class="text-green bold">{{ money_format(prices.total) }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
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
import {defineProps, ref, watch} from "vue"
import {IconDeviceFloppy, IconArrowLeft} from "@tabler/icons-vue";
import BaseInput from "../../../Components/BaseInput.vue";
import {useForm} from "@inertiajs/vue3";
import SelectBox from "../../../Components/SelectBox.vue";
import {useEnum} from "../../../Composables/useEnum.js";
import Repeater from "../../../Components/Repeater.vue";
import BaseTextarea from "../../../Components/BaseTextarea.vue";
import BaseSwitch from "../../../Components/BaseSwitch.vue";
import {addDays, capitalize, currentDate, diffDays, money_format} from "../../../Utils/helper.js";

const props = defineProps({
    customers: Object,
    mealPlans: Object,
    smokingPreferences: Array,
});

const roomTypes = ref(null);
const prices = ref(null);

const {
    select: smoking,
    default: defaultSmoking,
} = useEnum(props.smokingPreferences)

const form = useForm({
    customer_id: '',
    adults: '',
    children: 0,
    check_in: '',
    check_out: '',
    smoking_preference: defaultSmoking,
    rooms: [{
        type_id: '',
        quantity: 1,
    }],
    children_age: [],
    meal_plan_id: '',
    special_requests: '',
    check_in_now: false,
});

const ages = Object.fromEntries(
    Array.from({length: 13}, (_, i) => [i, `${i} años`]) // Traducido: 'years old' -> 'años'
);

function changeCheckOut() {
    form.setError('check_out', '');
    if (!form.check_in) {
        form.check_out = '';
        // Traducido: 'You first must select check-in'
        form.setError('check_out', 'Primero debe seleccionar la fecha de entrada');
    }

    const diffDay = diffDays(form.check_in, form.check_out, false);

    if (diffDay < 1) {
        form.check_out = '';
        // Traducido: 'The check-out date must be after the check-in date'
        form.setError('check_out', 'La fecha de salida debe ser posterior a la fecha de entrada');
    }
}

watch(
    () => [form.adults, form.children, form.check_in, form.check_out, form.smoking_preference],
    (inputs) => {
        if (inputs.some(val => val === "")) {
            if (roomTypes.value) roomTypes.value = null;
            return;
        }

        const [adults, children, check_in, check_out, smoking_preference] = inputs;

        axios.post(route('admin.bookings.roomTypes'), {adults, children, check_in, check_out, smoking_preference})
            .then(res => {
                    roomTypes.value = res.data.roomTypes;
                }
            );
    })

watch(
    () => [form.adults, form.children, form.rooms, form.meal_plan_id, form.children_age, form.check_in, form.check_out],
    (inputs) => {
        if (inputs.some(val => val === "")) return null;

        const [adults, children, rooms, meal_plan_id, children_age, check_in, check_out] = inputs;
        const nights = diffDays(check_in, check_out);

        axios.post(route('admin.bookings.prices'), {adults, children, rooms, meal_plan_id, children_age, nights})
            .then(res => {
                    prices.value =  {...res.data, nights};
                }
            )
    }, {
        deep: true
    });

watch(() => form.children, (ch) => {

    let children = ch > 10 ? 10 : ch;

    if (!children) {
        form.children_age = [];
        return
    }

    const diff = children - form.children_age.length;

    if (diff > 0) {
        const ages = Array.from({length: diff}, () => ({age: ''}));
        form.children_age.push(...ages);
    } else {
        for (let i = 0; i < Math.abs(diff); i++) {
            form.children_age.pop();
        }
    }
})

const handleCreateRoomType = () => {
    form.post(route('admin.bookings.store'));
}

</script>
