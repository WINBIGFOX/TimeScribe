<?php

declare(strict_types=1);

namespace App\Enums;

enum HolidayRegionEnum: string
{
    use BaseEnumTrait;

    // Australia
    case AU = 'AU';
    case AU_ACT = 'AU-ACT';
    case AU_NSW = 'AU-NSW';
    case AU_NT = 'AU-NT';
    case AU_QLD = 'AU-QLD';
    case AU_SA = 'AU-SA';
    case AU_TAS = 'AU-TAS';
    case AU_VIC = 'AU-VIC';
    case AU_WA = 'AU-WA';

    // Austria
    case AT = 'AT';
    case AT_1 = 'AT-1';
    case AT_2 = 'AT-2';
    case AT_3 = 'AT-3';
    case AT_4 = 'AT-4';
    case AT_5 = 'AT-5';
    case AT_6 = 'AT-6';
    case AT_7 = 'AT-7';
    case AT_8 = 'AT-8';
    case AT_9 = 'AT-9';

    // Belarus
    case BY = 'BY';

    // Belgium
    case BE = 'BE';

    // Brazil
    case BR = 'BR';

    // Bulgaria
    case BG = 'BG';

    // Canada
    case CA = 'CA';
    case CA_AB = 'CA-AB';
    case CA_BC = 'CA-BC';
    case CA_MB = 'CA-MB';
    case CA_NB = 'CA-NB';
    case CA_NL = 'CA-NL';
    case CA_NT = 'CA-NT';
    case CA_NS = 'CA-NS';
    case CA_NU = 'CA-NU';
    case CA_ON = 'CA-ON';
    case CA_PE = 'CA-PE';
    case CA_QC = 'CA-QC';
    case CA_SK = 'CA-SK';
    case CA_YT = 'CA-YT';

    // Czech Republic
    case CZ = 'CZ';

    // Denmark
    case DK = 'DK';

    // Estonia
    case EE = 'EE';

    // Finland
    case FI = 'FI';

    // France
    case FR = 'FR';
    case FR_57 = 'FR-57';
    case FR_67 = 'FR-67';
    case FR_68 = 'FR-68';
    case FR_GF = 'FR-GF';
    case FR_GUA = 'FR-GUA';
    case FR_LRE = 'FR-LRE';
    case FR_MQ = 'FR-MQ';

    // Germany
    case DE = 'DE';
    case DE_BB = 'DE-BB';
    case DE_BE = 'DE-BE';
    case DE_BW = 'DE-BW';
    case DE_BY = 'DE-BY';
    case DE_HB = 'DE-HB';
    case DE_HE = 'DE-HE';
    case DE_HH = 'DE-HH';
    case DE_MV = 'DE-MV';
    case DE_NI = 'DE-NI';
    case DE_NW = 'DE-NW';
    case DE_RP = 'DE-RP';
    case DE_SH = 'DE-SH';
    case DE_SL = 'DE-SL';
    case DE_SN = 'DE-SN';
    case DE_ST = 'DE-ST';
    case DE_TH = 'DE-TH';

    // Greenland
    case GL = 'GL';

    // Iceland
    case IS = 'IS';

    // Ireland
    case IE = 'IE';

    // Italy
    case IT = 'IT';
    case IT_32 = 'IT-32';

    // Latvia
    case LV = 'LV';

    // Liechtenstein
    case FL = 'FL';

    // Lithuania
    case LT = 'LT';

    // Luxembourg
    case LU = 'LU';

    // Mexico
    case MX = 'MX';

    // Netherlands
    case NL = 'NL';

    // Norway
    case NO = 'NO';

    // Poland
    case PL = 'PL';

    // Portugal
    case PT = 'PT';
    case PT_20 = 'PT-20';
    case PT_30 = 'PT-30';

    // Russia
    case RU = 'RU';

    // Spain
    case ES = 'ES';

    // Sweden
    case SE = 'SE';

    // Switzerland
    case CH = 'CH';
    case CH_AG = 'CH-AG';
    case CH_AI = 'CH-AI';
    case CH_AR = 'CH-AR';
    case CH_BE = 'CH-BE';
    case CH_BL = 'CH-BL';
    case CH_BS = 'CH-BS';
    case CH_FR = 'CH-FR';
    case CH_GE = 'CH-GE';
    case CH_GL = 'CH-GL';
    case CH_GR = 'CH-GR';
    case CH_JU = 'CH-JU';
    case CH_LU = 'CH-LU';
    case CH_NE = 'CH-NE';
    case CH_NW = 'CH-NW';
    case CH_OW = 'CH-OW';
    case CH_SG = 'CH-SG';
    case CH_SH = 'CH-SH';
    case CH_SO = 'CH-SO';
    case CH_SZ = 'CH-SZ';
    case CH_TG = 'CH-TG';
    case CH_TI = 'CH-TI';
    case CH_UR = 'CH-UR';
    case CH_VD = 'CH-VD';
    case CH_VS = 'CH-VS';
    case CH_ZG = 'CH-ZG';
    case CH_ZH = 'CH-ZH';

    // Turkey
    case TR = 'TR';

    // Ukraine
    case UA = 'UA';

    // United Kingdom
    case GB = 'GB';
    case GB_NIR = 'GB-NIR';
    case GB_SCT = 'GB-SCT';

    // USA
    case US = 'US';

    public function label(): string
    {
        return __('region.'.$this->value);
    }
}
