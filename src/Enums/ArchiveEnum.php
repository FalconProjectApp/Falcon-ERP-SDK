<?php

namespace FalconERP\Skeleton\Enums;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;
use Illuminate\Support\Collection;

abstract class ArchiveEnum extends BaseEnum
{
    public const NAME_SHOP_LOGO     = 'shop_logo';
    public const NAME_PRODUCT_IMAGE = 'product_image';
    public const NAME_PEOPLE_IMAGE  = 'people_image';
    public const NAME_BILL_FILE     = 'bill_file';
    public const NAME_EMAIL_FILE    = 'email_file';

    public const MIME_TYPE_IMAGE_PNG  = 'image/png';
    public const MIME_TYPE_IMAGE_JPG  = 'image/jpeg';
    public const MIME_TYPE_IMAGE_JPEG = 'image/jpeg';

    public const MIME_TYPE_PDF = 'application/pdf';
    public const MIME_TYPE_FILE = 'application/octet-stream';

    public const RULE_PUBLIC  = 'public';
    public const RULE_PRIVATE = 'private';

    public static function name(): Collection
    {
        return new Collection(static::filterConstants('NAME'));
    }

    public static function mimeTypeImage(): Collection
    {
        return new Collection(static::filterConstants('MIME_TYPE_IMAGE'));
    }

    public static function mimeTypeFiles(): Collection
    {
        return new Collection(static::filterConstants('MIME_TYPE_FILE'));
    }
}
