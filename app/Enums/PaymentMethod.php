<?php
namespace App\Enums;

use App\Attributes\Display;
use App\Traits\BaseEnum;

enum PaymentMethod: string
{
    use BaseEnum;
    #[Display('Credit Card', 'bg-info-lt')]
    case CREDIT_CARD ='credit_card';

    #[Display('Cash', 'bg-success-lt')]
    case CASH = 'cash';

    #[Display('Bank Transfer', 'bg-warning-lt')]
    case BANK_TRANSFER = 'bank_transfer';

    #[Display('Online', 'bg-warning-lt')]
    case ONLINE = 'online';

    #[Display('QR Code', 'bg-primary-lt')]
    case QR_CODE = 'qr_code';
}
