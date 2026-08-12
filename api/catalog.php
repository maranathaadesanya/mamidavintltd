<?php
// Server-side source of truth for item names/prices, keyed by the same ids
// used in farm-order.html, rentals.html and bakery.html. Cart endpoints look
// prices up here rather than trusting whatever the browser sends, so a
// visitor can't tamper with prices via devtools/curl.
//
// Keep this in sync with the product cards in those three pages.

return [
    // Farm
    'piglet'   => ['name' => 'Live Piglet', 'price' => 45000, 'category' => 'Farm'],
    'pig'      => ['name' => 'Matured Pig', 'price' => 120000, 'category' => 'Farm'],
    'maize'    => ['name' => 'Maize (50kg bag)', 'price' => 35000, 'category' => 'Farm'],
    'cassava'  => ['name' => 'Cassava Tubers (bag)', 'price' => 20000, 'category' => 'Farm'],
    'garri'    => ['name' => 'Garri (25kg bag)', 'price' => 18000, 'category' => 'Farm'],
    'chicken'  => ['name' => 'Frozen Chicken (crate)', 'price' => 40000, 'category' => 'Farm'],
    'fish'     => ['name' => 'Frozen Fish (carton)', 'price' => 30000, 'category' => 'Farm'],
    // Rentals
    'tent'     => ['name' => 'Canopy Tent (10x10)', 'price' => 25000, 'category' => 'Rentals'],
    'chairs'   => ['name' => 'Chiavari Chairs (set of 10)', 'price' => 15000, 'category' => 'Rentals'],
    'table'    => ['name' => 'Round Table (seats 8)', 'price' => 8000, 'category' => 'Rentals'],
    'sound'    => ['name' => 'Sound System (PA + Mics)', 'price' => 60000, 'category' => 'Rentals'],
    'gen'      => ['name' => 'Generator (10KVA)', 'price' => 45000, 'category' => 'Rentals'],
    'backdrop' => ['name' => 'Decoration Backdrop', 'price' => 30000, 'category' => 'Rentals'],
    // Bakery
    'cake-s'   => ['name' => 'Celebration Cake (Small)', 'price' => 20000, 'category' => 'Bakery'],
    'cake-l'   => ['name' => 'Celebration Cake (Large)', 'price' => 55000, 'category' => 'Bakery'],
    'chops'    => ['name' => 'Small Chops (Tray of 100)', 'price' => 25000, 'category' => 'Bakery'],
    'pie'      => ['name' => 'Meat Pies (Dozen)', 'price' => 9000, 'category' => 'Bakery'],
    'cupcake'  => ['name' => 'Cupcakes (Dozen)', 'price' => 12000, 'category' => 'Bakery'],
    'catering' => ['name' => 'Small Chops Package (100 Guests)', 'price' => 180000, 'category' => 'Bakery'],
];
