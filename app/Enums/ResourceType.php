<?php

namespace App\Enums;

enum ResourceType: string
{
    case Synchronization = 'synchronization';
    case Import = 'import';
}
