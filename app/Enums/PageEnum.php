<?php


namespace App\Enums;

enum PageEnum: string
{
    case HOME_PAGE  = 'home-page';
    case EVENT_PAGE  = 'event-page';
    case EVENT_DETAILS_PAGE  = 'event-details-page';
    case FEATURES_PAGE  = 'features-page';
    case NEWSLETTER_PAGE  = 'newsletter-page';
}
