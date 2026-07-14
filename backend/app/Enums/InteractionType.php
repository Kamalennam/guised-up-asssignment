<?php

namespace App\Enums;

enum InteractionType: string
{
    case View = 'view';
    case Reply = 'reply';
    case Reaction = 'reaction';
    case Share = 'share';
}
