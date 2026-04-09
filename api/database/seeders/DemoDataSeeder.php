<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTimeline;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPaymentMethods();
        $this->seedUsers();
        $this->seedOrders();
        $this->seedReviews();
        $this->seedPages();
        $this->seedHomepageSections();
    }

    private function seedPaymentMethods(): void
    {
        PaymentMethod::firstOrCreate(['code' => 'cod'], [
            'name' => 'Pouzeće',
            'description' => 'Plaćanje kuriru pri preuzimanju pošiljke.',
            'instructions' => null,
            'additional_cost' => 150,
            'is_active' => true,
            'is_online' => false,
            'sort_order' => 0,
        ]);

        PaymentMethod::firstOrCreate(['code' => 'bank_transfer'], [
            'name' => 'Virman / Uplata na račun',
            'description' => 'Plaćanje bankovnim transferom pre isporuke.',
            'instructions' => "Primalac: eLokal d.o.o.\nBroj računa: 160-1234567-89\nPoziv na broj: Vaš broj narudžbine\n\nNarudžbina će biti obrađena nakon primljene uplate.",
            'additional_cost' => 0,
            'is_active' => true,
            'is_online' => false,
            'sort_order' => 1,
        ]);
    }

    private function seedUsers(): void
    {
        $users = [
            ['name' => 'Ana Jovanović', 'email' => 'ana@test.rs', 'phone' => '+381641111111'],
            ['name' => 'Petar Petrović', 'email' => 'petar@test.rs', 'phone' => '+381642222222'],
            ['name' => 'Milica Nikolić', 'email' => 'milica@test.rs', 'phone' => '+381643333333'],
            ['name' => 'Stefan Đorđević', 'email' => 'stefan@test.rs', 'phone' => '+381644444444'],
            ['name' => 'Jovana Stanković', 'email' => 'jovana@test.rs', 'phone' => '+381645555555'],
            ['name' => 'Nikola Ilić', 'email' => 'nikola@test.rs', 'phone' => '+381646666666'],
            ['name' => 'Marija Pavlović', 'email' => 'marija@test.rs', 'phone' => '+381647777777'],
            ['name' => 'Lazar Stojanović', 'email' => 'lazar@test.rs', 'phone' => '+381648888888'],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], array_merge($user, ['password' => 'password']));
        }
    }

    private function seedOrders(): void
    {
        if (Order::count() > 0) return;

        $users = User::all();
        $products = Product::where('is_active', true)->inRandomOrder()->limit(20)->get();
        $paymentMethod = PaymentMethod::first();

        if ($users->isEmpty() || $products->isEmpty()) return;

        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed'];
        $cities = ['Beograd', 'Novi Sad', 'Niš', 'Kragujevac', 'Subotica', 'Zrenjanin', 'Pančevo', 'Čačak'];

        for ($i = 0; $i < 25; $i++) {
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            $itemCount = rand(1, 4);
            $orderProducts = $products->random($itemCount);

            $subtotal = 0;
            $items = [];
            foreach ($orderProducts as $product) {
                $qty = rand(1, 3);
                $price = (float) $product->effective_price;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'price' => $price,
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                ];
            }

            $shipping = $subtotal >= 5000 ? 0 : 350;
            $tax = round($subtotal * 0.20, 2);
            $total = $subtotal + $shipping + $tax;
            $city = $cities[array_rand($cities)];
            $createdAt = now()->subDays(rand(1, 60))->subHours(rand(0, 23));

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'status' => $status,
                'email' => $user->email,
                'phone' => $user->phone,
                'shipping_first_name' => explode(' ', $user->name)[0],
                'shipping_last_name' => explode(' ', $user->name)[1] ?? '',
                'shipping_address_line_1' => 'Ulica ' . rand(1, 200) . ' br. ' . rand(1, 50),
                'shipping_city' => $city,
                'shipping_postal_code' => strval(rand(11000, 36000)),
                'shipping_country' => 'RS',
                'billing_first_name' => explode(' ', $user->name)[0],
                'billing_last_name' => explode(' ', $user->name)[1] ?? '',
                'billing_address_line_1' => 'Ulica ' . rand(1, 200) . ' br. ' . rand(1, 50),
                'billing_city' => $city,
                'billing_postal_code' => strval(rand(11000, 36000)),
                'billing_country' => 'RS',
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'tax' => $tax,
                'discount' => 0,
                'total' => $total,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($items as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }

            // Payment
            if ($paymentMethod) {
                $order->payments()->create([
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $total,
                    'status' => in_array($status, ['delivered', 'completed']) ? 'completed' : 'pending',
                ]);
            }

            // Timeline (nema updated_at kolonu — koristimo insert)
            \Illuminate\Support\Facades\DB::table('order_timeline')->insert([
                'order_id' => $order->id,
                'status' => 'pending',
                'note' => 'Narudžbina kreirana.',
                'actor_type' => 'system',
                'created_at' => $createdAt,
            ]);

            if (in_array($status, ['confirmed', 'processing', 'shipped', 'delivered', 'completed'])) {
                \Illuminate\Support\Facades\DB::table('order_timeline')->insert([
                    'order_id' => $order->id,
                    'status' => 'confirmed',
                    'old_status' => 'pending',
                    'note' => 'Narudžbina potvrđena.',
                    'actor_type' => 'admin',
                    'actor_id' => 1,
                    'created_at' => $createdAt->addHours(rand(1, 12)),
                ]);
            }
        }
    }

    private function seedReviews(): void
    {
        if (Review::count() > 0) return;

        $users = User::all();
        $products = Product::where('is_active', true)->inRandomOrder()->limit(15)->get();

        $comments = [
            5 => ['Odličan proizvod, potpuno zadovoljna!', 'Kvalitet je izvanredan, preporučujem.', 'Baš onako kako je opisano, sve pohvale.', 'Dete je oduševljeno, hvala!'],
            4 => ['Veoma dobar proizvod, sitna zamerka na pakovanje.', 'Dobro, ali dostava mogla brže.', 'Kvalitetno, koristimo svaki dan.'],
            3 => ['Solidno za ovu cenu.', 'OK proizvod, ništa specijalno.', 'Prosečan kvalitet, očekivala sam više.'],
            2 => ['Moglo je bolje, materijal je tanak.', 'Nije baš kao na slici.'],
            1 => ['Razočarana sam kvalitetom.'],
        ];

        foreach ($products as $product) {
            $reviewCount = rand(1, 4);
            $usedUsers = $users->random(min($reviewCount, $users->count()));

            foreach ($usedUsers as $user) {
                $rating = array_rand([5 => 40, 4 => 30, 3 => 15, 2 => 10, 1 => 5]);
                // Težinski random ka višim ocenama
                $rand = rand(1, 100);
                if ($rand <= 40) $rating = 5;
                elseif ($rand <= 70) $rating = 4;
                elseif ($rand <= 85) $rating = 3;
                elseif ($rand <= 95) $rating = 2;
                else $rating = 1;

                $pool = $comments[$rating];
                $comment = $pool[array_rand($pool)];

                Review::firstOrCreate([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                ], [
                    'rating' => $rating,
                    'content' => $comment,
                    'status' => 'approved',
                    'created_at' => now()->subDays(rand(1, 90)),
                ]);
            }
        }
    }

    private function seedPages(): void
    {
        $pages = [
            [
                'title' => 'O nama',
                'slug' => 'o-nama',
                'content' => '<p>eLokal je webshop posvećen kvalitetnim proizvodima za vaš dom. Nudimo širok izbor dečijeg nameštaja, dekoracije i igračaka od proverenih proizvođača.</p><p>Naša misija je da svakom domu donesemo radost kvalitetnim i pristupačnim proizvodima, uz brzu i pouzdanu dostavu širom Srbije.</p>',
            ],
            [
                'title' => 'Kontakt',
                'slug' => 'kontakt',
                'content' => '<p>Imate pitanje? Kontaktirajte nas!</p><p><strong>Email:</strong> info@webshop.rs<br><strong>Telefon:</strong> +381 11 123 4567<br><strong>Radno vreme:</strong> Pon-Pet 09:00 - 17:00</p><p><strong>Adresa:</strong><br>eLokal d.o.o.<br>Knez Mihailova 1<br>11000 Beograd, Srbija</p>',
            ],
            [
                'title' => 'Uslovi korišćenja',
                'slug' => 'uslovi-koriscenja',
                'content' => '<h2>Opšti uslovi</h2><p>Korišćenjem ovog sajta prihvatate ove uslove. Zadržavamo pravo izmene uslova bez prethodnog obaveštenja.</p><h2>Narudžbine</h2><p>Svaka narudžbina predstavlja ponudu za kupovinu. Ugovor se smatra zaključenim kada primite potvrdu narudžbine na email.</p><h2>Reklamacije</h2><p>Imate pravo na reklamaciju u roku od 14 dana od prijema robe. Kontaktirajte nas na info@webshop.rs.</p>',
            ],
            [
                'title' => 'Politika privatnosti',
                'slug' => 'politika-privatnosti',
                'content' => '<h2>Prikupljanje podataka</h2><p>Prikupljamo samo podatke neophodne za obradu narudžbina: ime, adresu, email, telefon.</p><h2>Zaštita podataka</h2><p>Vaši podaci su zaštićeni i neće biti deljeni sa trećim licima bez vaše saglasnosti.</p><h2>Kolačići</h2><p>Koristimo kolačiće za poboljšanje korisničkog iskustva. Možete ih isključiti u podešavanjima browsera.</p>',
            ],
            [
                'title' => 'Česta pitanja',
                'slug' => 'cesta-pitanja',
                'content' => '<h2>Koliko traje dostava?</h2><p>Standardna dostava traje 2-4 radna dana. Express dostava 1-2 radna dana.</p><h2>Da li mogu da vratim proizvod?</h2><p>Da, imate pravo na povrat u roku od 14 dana od prijema. Proizvod mora biti nekorišćen i u originalnom pakovanju.</p><h2>Kako mogu da pratim narudžbinu?</h2><p>Kada pošiljka bude poslata, dobićete email sa tracking brojem. Status možete pratiti na stranici /pracenje.</p><h2>Da li je dostava besplatna?</h2><p>Dostava je besplatna za narudžbine iznad 5.000 RSD.</p>',
            ],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(['slug' => $page['slug']], array_merge($page, [
                'is_active' => true,
                'sort_order' => 0,
            ]));
        }
    }

    private function seedHomepageSections(): void
    {
        if (\App\Models\PageSection::where('page_key', 'homepage')->exists()) return;

        $sections = [
            [
                'type' => 'hero_slideshow',
                'data' => [
                    'slides' => [
                        ['title' => 'Kvalitetni proizvodi za vaš dom', 'subtitle' => 'Otkrijte širok izbor dečijeg nameštaja i dekoracije po najboljim cenama.', 'cta_text' => 'Pogledaj ponudu', 'cta_link' => '/proizvodi', 'bg_color' => '#283618'],
                        ['title' => 'Novo u ponudi', 'subtitle' => 'Pogledajte najnovije dodatke našoj kolekciji.', 'cta_text' => 'Istraži novo', 'cta_link' => '/proizvodi?sort=created_at', 'bg_color' => '#606c38'],
                    ],
                ],
            ],
            ['type' => 'category_grid', 'data' => ['title' => 'Kupujte po kategorijama', 'columns' => 6]],
            ['type' => 'featured_products', 'data' => ['title' => 'Istaknuti proizvodi', 'limit' => 8]],
            ['type' => 'banner_split', 'data' => ['left_title' => 'Besplatna dostava', 'left_text' => 'Za narudžbine iznad 5.000 RSD', 'right_title' => 'Sigurna kupovina', 'right_text' => 'Pouzeće ili karticom']],
            ['type' => 'new_arrivals', 'data' => ['title' => 'Novo u ponudi', 'limit' => 8]],
            ['type' => 'trust_badges', 'data' => null],
            ['type' => 'newsletter', 'data' => null],
            ['type' => 'blog_preview', 'data' => ['title' => 'Iz našeg bloga', 'limit' => 3]],
            ['type' => 'recently_viewed', 'data' => ['title' => 'Nedavno pregledano']],
        ];

        foreach ($sections as $i => $section) {
            \App\Models\PageSection::create(array_merge($section, [
                'page_key' => 'homepage',
                'is_active' => true,
                'sort_order' => $i,
            ]));
        }
    }
}
