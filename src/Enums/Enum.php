<?php

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class Enum extends BaseEnum
{
    public const Sunday    = 0;
    public const Monday    = 1;
    public const Tuesday   = 2;
    public const Wednesday = 3;
    public const Thursday  = 4;
    public const Friday    = 5;
    public const Saturday  = 6;
}

Enum::isValidName('Humpday');                  // false
Enum::isValidName('Monday');                   // true
Enum::isValidName('monday');                   // true
Enum::isValidName('monday', $strict = true);   // false
Enum::isValidName(0);                          // false

Enum::isValidValue(0);                         // true
Enum::isValidValue(5);                         // true
Enum::isValidValue(7);                         // false
Enum::isValidValue('Friday');                  // false
