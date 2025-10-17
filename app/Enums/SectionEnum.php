<?php

namespace App\Enums;


enum SectionEnum: string
{
    case HERO = 'hero';
    case UPCOMING_EVENT = 'upcoming-event';
    case POPULAR_VANUE = 'popular-vanue';
    case APP_DOWNLOAD = 'app-download';
    case NEWSLETTER = 'newsletter';
    case CARD = 'card';
    case TEXT = 'text';
}
