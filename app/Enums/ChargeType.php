<?php
namespace App\Enums;

use App\Attributes\Display;
use App\Traits\BaseEnum;

enum ChargeType: string
{
    use BaseEnum;

    #[Display('Habitación', 'bi-house')]
    case ROOM ='room';

    #[Display('Plan de Comidas', 'bi-egg-fried')]
    case MEAL_PLAN = 'meal_plan';

    #[Display('Servicio Adicional', 'bi-plus-circle')]
    case SERVICE = 'service';

    #[Display('Tarifa de Cancelación', 'bi-x-circle')]
    case CANCELLATION_FEE = 'cancellation_fee';

    #[Display('Impuesto', 'bi-bank')]
    case TAX = 'tax';
}
