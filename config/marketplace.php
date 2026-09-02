<?php

return [

    /*
    | When true, public /marketplace routes show a coming-soon page instead of listings.
    */
    'public_coming_soon' => env('MARKETPLACE_COMING_SOON', true),

    /*
    | When true, dashboard marketplace routes (browse, listings, watchlist, etc.)
    | show a coming-soon page instead of marketplace features.
    */
    'dashboard_coming_soon' => env('MARKETPLACE_DASHBOARD_COMING_SOON', true),

];
