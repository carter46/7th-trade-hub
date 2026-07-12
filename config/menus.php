<?php

return [
    'user' => [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'dashboard.wallet', 'label' => 'Wallet', 'icon' => 'credit_card'],
        ['route' => 'dashboard.exchange', 'label' => 'Crypto Exchange', 'icon' => 'swap_horiz'],
        ['route' => 'dashboard.social', 'label' => 'Social Services', 'icon' => 'groups'],
        ['route' => 'dashboard.documents', 'label' => 'Document Templates', 'icon' => 'description'],
        ['route' => 'dashboard.listings', 'label' => 'Website Listings', 'icon' => 'public'],
        ['route' => 'dashboard.orders', 'label' => 'Orders', 'icon' => 'shopping_bag'],
        ['route' => 'dashboard.messages', 'label' => 'Messages', 'icon' => 'chat_bubble'],
    ],

    'admin' => [
        ['route' => 'admin', 'label' => 'Overview', 'icon' => 'grid_view'],
        ['route' => 'admin.users', 'label' => 'User Management', 'icon' => 'group'],
        ['route' => 'admin.transactions', 'label' => 'Transactions', 'icon' => 'paid'],
        ['route' => 'admin.listings', 'label' => 'Site Listings', 'icon' => 'inventory_2'],
        ['route' => 'admin.social', 'label' => 'Social Media Services', 'icon' => 'campaign'],
        ['route' => 'admin.tickets', 'label' => 'Support Tickets', 'icon' => 'support_agent'],
        ['route' => 'admin.analytics', 'label' => 'Analytics', 'icon' => 'monitoring'],
    ],
];
