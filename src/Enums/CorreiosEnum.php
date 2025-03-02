<?php

namespace FalconERP\Skeleton\Enums;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class CorreiosEnum extends BaseEnum
{
    /**
     * Formatos caixa ou pacote.
     */
    public const PACKAGE_BOX = 1;

    /**
     * Formatos rolo ou prisma.
     */
    public const PACKAGE_ROLL = 2;

    /**
     * Formato envelope.
     */
    public const PACKAGE_ENVELOPE = 3;

    /**
     * PAC.
     */
    public const SERVICE_PAC = '4510';

    /**
     * PAC com contrato.
     */
    public const SERVICE_PAC_CONTRATO = '4669';

    /**
     * Sedex.
     */
    public const SERVICE_SEDEX = '4014';

    /**
     * Sedex com contrato.
     */
    public const SERVICE_SEDEX_CONTRATO = '4162';

    /**
     * Sedex a Cobrar.
     */
    public const SERVICE_SEDEX_A_COBRAR = '40045';

    /**
     * Sedex 10.
     */
    public const SERVICE_SEDEX_10 = '40215';

    /**
     * Sedex Hoje.
     */
    public const SERVICE_SEDEX_HOJE = '40290';

    /**
     * Sedex Contrato 04316.
     */
    public const SERVICE_SEDEX_CONTRATO_04316 = '4316';

    /**
     * Sedex Contrato 40096.
     */
    public const SERVICE_SEDEX_CONTRATO_40096 = '40096';

    /**
     * Sedex Contrato 40436.
     */
    public const SERVICE_SEDEX_CONTRATO_40436 = '40436';

    /**
     * Sedex Contrato 40444.
     */
    public const SERVICE_SEDEX_CONTRATO_40444 = '40444';

    /**
     * Sedex Contrato 40568.
     */
    public const SERVICE_SEDEX_CONTRATO_40568 = '40568';

    /**
     * PAC Contrato 04812.
     */
    public const SERVICE_PAC_CONTRATO_04812 = '4812';

    /**
     * PAC Contrato 41068.
     */
    public const SERVICE_PAC_CONTRATO_41068 = '41068';

    /**
     * PAC Contrato 41211.
     */
    public const SERVICE_PAC_CONTRATO_41211 = '41211';
}
