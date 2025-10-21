<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Enums;

use Illuminate\Support\Collection;
use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class ArchiveEnum extends BaseEnum
{
    public const NAME_SHOP_LOGO     = 'shop_logo';
    public const NAME_PRODUCT_IMAGE = 'product_image';
    public const NAME_PEOPLE_IMAGE  = 'people_image';
    public const NAME_PEOPLE_FILE   = 'people_file';
    public const NAME_BILL_FILE     = 'bill_file';
    public const NAME_EMAIL_FILE    = 'email_file';
    public const CERTIFICATE_FILE   = 'certificate_file';
    public const XML_ASSIGN_FILE    = 'xml_assign_file';

    public const MIME_TYPE_IMAGE_JPG  = 'image/jpg';
    public const MIME_TYPE_IMAGE_JPEG = 'image/jpeg';
    public const MIME_TYPE_IMAGE_PNG  = 'image/png';
    public const MIME_TYPE_IMAGE_GIF  = 'image/gif';
    public const MIME_TYPE_IMAGE_BMP  = 'image/bmp';
    public const MIME_TYPE_IMAGE_TIFF = 'image/tiff';
    public const MIME_TYPE_IMAGE_TIF  = 'image/tif';
    public const MIME_TYPE_IMAGE_WEBP = 'image/webp';
    public const MIME_TYPE_IMAGE_HEIC = 'image/heic';
    public const MIME_TYPE_IMAGE_HEIF = 'image/heif';
    public const MIME_TYPE_IMAGE_AVIF = 'image/avif';

    public const MIME_TYPE_PDF  = 'application/pdf';
    public const MIME_TYPE_DOC  = 'application/msword';
    public const MIME_TYPE_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    public const MIME_TYPE_XLS  = 'application/vnd.ms-excel';
    public const MIME_TYPE_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    public const MIME_TYPE_PPT  = 'application/vnd.ms-powerpoint';
    public const MIME_TYPE_PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    public const MIME_TYPE_TXT  = 'text/plain';
    public const MIME_TYPE_CSV  = 'text/csv';
    public const MIME_TYPE_FILE = 'application/octet-stream';
    public const MIME_TYPE_PFX  = 'application/x-pkcs12';
    public const MIME_TYPE_XML  = 'application/xml';

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

    public static function mimeType(): Collection
    {
        return new Collection(static::filterConstants('MIME_TYPE'));
    }

    public static function mimeTypeFiles(): Collection
    {
        return new Collection(static::filterConstants('MIME_TYPE_FILE'));
    }
}
