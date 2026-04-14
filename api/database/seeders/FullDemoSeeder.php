<?php

namespace Database\Seeders;

use App\Models\AbandonedCart;
use App\Models\ActivityLog;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\BlogCategory;
use App\Models\CallbackRequest;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\CouponCondition;
use App\Models\Currency;
use App\Models\DownloadableFile;
use App\Models\DownloadLog;
use App\Models\GiftCard;
use App\Models\GiftCardTransaction;
use App\Models\Look;
use App\Models\LookHotspot;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use App\Models\MediaFolder;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Refund;
use App\Models\SearchLog;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\StockMovement;
use App\Models\StockNotification;
use App\Models\StoreCreditAccount;
use App\Models\StoreCreditTransaction;
use App\Models\StoreLocation;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use App\Models\Webhook;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FullDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdminUsers();
        $this->seedSettings();
        $this->seedCurrencies();
        $this->seedAttributesAndVariants();
        $this->seedAddresses();
        $this->seedWishlists();
        $this->seedProductRelations();
        $this->seedCoupons();
        $this->seedGiftCards();
        $this->seedLoyalty();
        $this->seedStoreCredits();
        $this->seedBlog();
        $this->seedNewsletterSubscribers();
        $this->seedAbandonedCarts();
        $this->seedStoreLocations();
        $this->seedLooks();
        $this->seedContactMessages();
        $this->seedCallbackRequests();
        $this->seedSearchLogs();
        $this->seedStockMovements();
        $this->seedDownloadableFiles();
        $this->seedShipmentsAndRefunds();
        $this->seedWebhooks();
        $this->seedMediaFolders();
        $this->seedStockNotifications();
        $this->seedActivityLog();
        $this->seedTranslations();
    }

    // ------------------------------------------------------------------
    // Admin korisnici (editor + admin pored super_admin)
    // ------------------------------------------------------------------
    private function seedAdminUsers(): void
    {
        Admin::firstOrCreate(['email' => 'editor@webshop.test'], [
            'name' => 'Jelena Urednik',
            'password' => 'password',
            'role' => 'editor',
            'permissions' => ['products', 'categories', 'blog', 'media', 'reviews'],
            'is_active' => true,
        ]);

        Admin::firstOrCreate(['email' => 'admin@test.rs'], [
            'name' => 'Milan Admin',
            'password' => 'password',
            'role' => 'admin',
            'permissions' => ['products', 'categories', 'orders', 'customers', 'reviews', 'blog', 'media', 'settings', 'coupons', 'reports'],
            'is_active' => true,
        ]);

        // Neaktivan admin za testiranje
        Admin::firstOrCreate(['email' => 'neaktivan@webshop.test'], [
            'name' => 'Neaktivan Admin',
            'password' => 'password',
            'role' => 'admin',
            'permissions' => [],
            'is_active' => false,
        ]);
    }

    // ------------------------------------------------------------------
    // Sva podešavanja (general, storefront, trust, cart, badges, SEO, GDPR)
    // ------------------------------------------------------------------
    private function seedSettings(): void
    {
        // General
        $general = [
            'site_name' => 'eLokal',
            'site_logo' => '',
            'site_favicon' => '',
            'site_address' => 'Knez Mihailova 1, 11000 Beograd',
            'site_phone' => '+381 11 123 4567',
            'site_email' => 'info@elokal.rs',
            'social_facebook' => 'https://facebook.com/elokal',
            'social_instagram' => 'https://instagram.com/elokal',
            'social_tiktok' => '',
        ];
        foreach ($general as $key => $value) {
            Setting::setValue('general', $key, $value);
        }

        // Storefront layout
        $storefront = [
            'header_variant' => 'A',
            'plp_variant' => 'A',
            'pdp_variant' => 'A',
            'cart_variant' => 'A',
            'products_per_page' => '12',
            'variant_display_mode' => 'swatch',
        ];
        foreach ($storefront as $key => $value) {
            Setting::setValue('storefront', $key, $value);
        }

        // Top bar
        Setting::setValue('storefront', 'top_bar_enabled', 'true');
        Setting::setValue('storefront', 'top_bar_text', 'Besplatna dostava za narudžbine iznad 5.000 RSD!');
        Setting::setValue('storefront', 'top_bar_bg_color', '#283618');
        Setting::setValue('storefront', 'top_bar_text_color', '#FFFFFF');

        // Trust & Conversion
        $trust = [
            'stock_status_display' => 'true',
            'urgency_bar_enabled' => 'true',
            'countdown_timer_enabled' => 'true',
            'shipping_text' => 'Besplatna dostava iznad 5.000 RSD',
            'return_text' => 'Povrat u roku od 14 dana',
            'dispatch_text' => 'Isporuka za 2-4 radna dana',
            'trust_badges_enabled' => 'true',
        ];
        foreach ($trust as $key => $value) {
            Setting::setValue('trust', $key, $value);
        }

        // Cart & Checkout
        $cart = [
            'add_to_cart_feedback' => 'drawer',
            'free_shipping_threshold' => '5000',
            'guest_checkout_enabled' => 'true',
            'cart_feature_abandoned_cart' => 'true',
        ];
        foreach ($cart as $key => $value) {
            Setting::setValue('cart', $key, $value);
        }

        // Badges
        $badges = [
            'new_badge_days' => '14',
            'new_badge_color' => '#22C55E',
            'sale_badge_color' => '#EF4444',
        ];
        foreach ($badges as $key => $value) {
            Setting::setValue('badges', $key, $value);
        }

        // SEO
        $seo = [
            'meta_title_pattern' => '{name} | eLokal',
            'meta_description_pattern' => 'Kupite {name} po najboljoj ceni. Besplatna dostava iznad 5.000 RSD.',
            'google_analytics_id' => 'G-DEMO123456',
            'facebook_pixel_id' => '1234567890',
        ];
        foreach ($seo as $key => $value) {
            Setting::setValue('seo', $key, $value);
        }

        // GDPR
        $gdpr = [
            'cookie_consent_text' => 'Koristimo kolačiće za poboljšanje korisničkog iskustva. Nastavkom korišćenja sajta prihvatate našu politiku privatnosti.',
            'privacy_policy_url' => '/politika-privatnosti',
            'terms_url' => '/uslovi-koriscenja',
        ];
        foreach ($gdpr as $key => $value) {
            Setting::setValue('gdpr', $key, $value);
        }

        // Feature flags — sve ukljuceno za demo
        $features = [
            'feature_wishlist' => 'true',
            'feature_newsletter' => 'true',
            'feature_compare' => 'true',
            'feature_social_proof' => 'true',
            'feature_store_credits' => 'true',
            'feature_multi_currency' => 'true',
            'feature_gift_cards' => 'true',
            'feature_loyalty' => 'true',
            'feature_webhooks' => 'true',
            'feature_abandoned_cart' => 'true',
            'feature_shop_the_look' => 'true',
            'feature_store_locator' => 'true',
            'feature_downloads' => 'true',
            'feature_multi_language' => 'true',
        ];
        foreach ($features as $key => $value) {
            Setting::setValue('features', $key, $value);
        }
    }

    // ------------------------------------------------------------------
    // Valute
    // ------------------------------------------------------------------
    private function seedCurrencies(): void
    {
        if (Currency::count() > 0) return;

        Currency::create([
            'code' => 'RSD',
            'name' => 'Srpski dinar',
            'symbol' => 'RSD',
            'exchange_rate' => 1.000000,
            'is_default' => true,
            'is_active' => true,
            'decimal_places' => 2,
        ]);

        Currency::create([
            'code' => 'EUR',
            'name' => 'Evro',
            'symbol' => '€',
            'exchange_rate' => 0.008500,
            'is_default' => false,
            'is_active' => true,
            'decimal_places' => 2,
        ]);

        Currency::create([
            'code' => 'USD',
            'name' => 'Američki dolar',
            'symbol' => '$',
            'exchange_rate' => 0.009300,
            'is_default' => false,
            'is_active' => true,
            'decimal_places' => 2,
        ]);
    }

    // ------------------------------------------------------------------
    // Atributi + varijante
    // ------------------------------------------------------------------
    private function seedAttributesAndVariants(): void
    {
        if (Attribute::count() > 0) return;

        // Pozivamo postojeci seeder
        $this->call(AttributeVariantSeeder::class);
    }

    // ------------------------------------------------------------------
    // Adrese za korisnike
    // ------------------------------------------------------------------
    private function seedAddresses(): void
    {
        if (Address::count() > 0) return;

        $users = User::all();
        $cities = [
            ['city' => 'Beograd', 'postal' => '11000'],
            ['city' => 'Novi Sad', 'postal' => '21000'],
            ['city' => 'Niš', 'postal' => '18000'],
            ['city' => 'Kragujevac', 'postal' => '34000'],
            ['city' => 'Subotica', 'postal' => '24000'],
        ];

        foreach ($users as $i => $user) {
            $city = $cities[$i % count($cities)];
            Address::create([
                'user_id' => $user->id,
                'label' => 'Kuća',
                'first_name' => explode(' ', $user->name)[0],
                'last_name' => explode(' ', $user->name)[1] ?? '',
                'address_line_1' => 'Ulica ' . fake()->numberBetween(1, 200) . ' br. ' . fake()->numberBetween(1, 50),
                'city' => $city['city'],
                'postal_code' => $city['postal'],
                'country' => 'RS',
                'phone' => $user->phone ?? '+381641234567',
                'is_default' => true,
            ]);

            // Drugi user dobija i poslovnu adresu
            if ($i % 3 === 0) {
                $city2 = $cities[($i + 2) % count($cities)];
                Address::create([
                    'user_id' => $user->id,
                    'label' => 'Posao',
                    'first_name' => explode(' ', $user->name)[0],
                    'last_name' => explode(' ', $user->name)[1] ?? '',
                    'address_line_1' => 'Bulevar ' . fake()->numberBetween(1, 50),
                    'city' => $city2['city'],
                    'postal_code' => $city2['postal'],
                    'country' => 'RS',
                    'phone' => $user->phone ?? '+381641234567',
                    'is_default' => false,
                ]);
            }
        }
    }

    // ------------------------------------------------------------------
    // Wishlists
    // ------------------------------------------------------------------
    private function seedWishlists(): void
    {
        if (Wishlist::count() > 0) return;

        $users = User::limit(5)->get();
        $products = Product::where('is_active', true)->inRandomOrder()->limit(20)->get();

        foreach ($users as $user) {
            $wishlistProducts = $products->random(rand(2, 5));
            foreach ($wishlistProducts as $product) {
                Wishlist::firstOrCreate([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }

    // ------------------------------------------------------------------
    // Product relations (related, cross-sell, up-sell)
    // ------------------------------------------------------------------
    private function seedProductRelations(): void
    {
        if (DB::table('product_relations')->count() > 0) return;

        $products = Product::where('is_active', true)->get();
        if ($products->count() < 10) return;

        // Prvih 10 proizvoda dobija relacije
        foreach ($products->take(10) as $product) {
            $others = $products->where('id', '!=', $product->id);

            // Related (3-4 proizvoda)
            $related = $others->random(min(rand(3, 4), $others->count()));
            foreach ($related as $i => $rel) {
                DB::table('product_relations')->insert([
                    'product_id' => $product->id,
                    'related_product_id' => $rel->id,
                    'type' => 'related',
                    'sort_order' => $i,
                ]);
            }

            // Cross-sell (2 proizvoda)
            $crossSell = $others->diff($related)->shuffle()->take(2);
            foreach ($crossSell as $i => $cs) {
                DB::table('product_relations')->insert([
                    'product_id' => $product->id,
                    'related_product_id' => $cs->id,
                    'type' => 'cross_sell',
                    'sort_order' => $i,
                ]);
            }

            // Up-sell (2 proizvoda — skuplji)
            $upSell = $others->where('price', '>', $product->price)->shuffle()->take(2);
            foreach ($upSell as $i => $us) {
                DB::table('product_relations')->insert([
                    'product_id' => $product->id,
                    'related_product_id' => $us->id,
                    'type' => 'up_sell',
                    'sort_order' => $i,
                ]);
            }
        }
    }

    // ------------------------------------------------------------------
    // Kuponi
    // ------------------------------------------------------------------
    private function seedCoupons(): void
    {
        if (Coupon::count() > 0) return;

        // Procentualni kupon
        $c1 = Coupon::create([
            'code' => 'POPUST10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_order_amount' => 2000.00,
            'max_discount_amount' => 3000.00,
            'max_uses' => 100,
            'times_used' => 7,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->addDays(60),
            'is_active' => true,
            'description' => '10% popusta na sve narudžbine iznad 2.000 RSD',
        ]);

        // Fiksni popust
        $c2 = Coupon::create([
            'code' => 'USTEДИ500',
            'type' => 'fixed_amount',
            'value' => 500.00,
            'min_order_amount' => 3000.00,
            'max_uses' => 50,
            'times_used' => 3,
            'starts_at' => now()->subDays(5),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
            'description' => '500 RSD popusta na narudžbine iznad 3.000 RSD',
        ]);

        // Besplatna dostava
        Coupon::create([
            'code' => 'FRIŠIP',
            'type' => 'free_shipping',
            'value' => 0,
            'min_order_amount' => 0,
            'max_uses' => 200,
            'times_used' => 15,
            'starts_at' => now()->subDays(20),
            'expires_at' => now()->addDays(90),
            'is_active' => true,
            'description' => 'Besplatna dostava bez minimalne narudžbine',
        ]);

        // Istekli kupon
        Coupon::create([
            'code' => 'PROŠAO20',
            'type' => 'percentage',
            'value' => 20.00,
            'min_order_amount' => 1000.00,
            'max_uses' => 50,
            'times_used' => 50,
            'starts_at' => now()->subDays(60),
            'expires_at' => now()->subDays(10),
            'is_active' => false,
            'description' => 'Istekao kupon za testiranje',
        ]);

        // Kupon sa uslovima
        $c5 = Coupon::create([
            'code' => 'NOVO15',
            'type' => 'percentage',
            'value' => 15.00,
            'min_order_amount' => 5000.00,
            'max_discount_amount' => 5000.00,
            'max_uses' => 30,
            'times_used' => 0,
            'starts_at' => now(),
            'expires_at' => now()->addDays(45),
            'is_active' => true,
            'description' => '15% za nove korisnike, min 3 artikla',
        ]);

        CouponCondition::create([
            'coupon_id' => $c5->id,
            'type' => 'first_order',
            'operator' => 'eq',
            'value' => 'true',
        ]);

        CouponCondition::create([
            'coupon_id' => $c5->id,
            'type' => 'min_items',
            'operator' => 'gte',
            'value' => '3',
        ]);
    }

    // ------------------------------------------------------------------
    // Poklon kartice
    // ------------------------------------------------------------------
    private function seedGiftCards(): void
    {
        if (GiftCard::count() > 0) return;

        $user = User::first();

        // Aktivna puna
        $gc1 = GiftCard::create([
            'code' => GiftCard::generateCode(),
            'initial_amount' => 5000.00,
            'balance' => 5000.00,
            'purchased_by' => $user?->id,
            'recipient_email' => 'prijatelj@test.rs',
            'recipient_name' => 'Marko Prijatelj',
            'message' => 'Srećan rođendan! Izaberi nešto lepo.',
            'is_active' => true,
            'expires_at' => now()->addYear(),
        ]);

        // Delimicno iskoriscena
        $gc2 = GiftCard::create([
            'code' => GiftCard::generateCode(),
            'initial_amount' => 3000.00,
            'balance' => 1200.00,
            'purchased_by' => $user?->id,
            'recipient_email' => 'ana@test.rs',
            'recipient_name' => 'Ana Jovanović',
            'message' => 'Uživaj!',
            'is_active' => true,
            'expires_at' => now()->addMonths(6),
        ]);

        GiftCardTransaction::create([
            'gift_card_id' => $gc2->id,
            'amount' => 1800.00,
            'type' => 'redeem',
            'balance_after' => 1200.00,
            'note' => 'Iskorišćeno pri kupovini',
        ]);

        // Potpuno iskoriscena
        $gc3 = GiftCard::create([
            'code' => GiftCard::generateCode(),
            'initial_amount' => 2000.00,
            'balance' => 0.00,
            'purchased_by' => User::skip(1)->first()?->id,
            'recipient_email' => 'stefan@test.rs',
            'recipient_name' => 'Stefan Đorđević',
            'is_active' => true,
            'expires_at' => now()->addMonths(3),
        ]);

        GiftCardTransaction::create([
            'gift_card_id' => $gc3->id,
            'amount' => 2000.00,
            'type' => 'redeem',
            'balance_after' => 0.00,
            'note' => 'Iskorišćeno u celosti',
        ]);

        // Istekla
        GiftCard::create([
            'code' => GiftCard::generateCode(),
            'initial_amount' => 1000.00,
            'balance' => 1000.00,
            'recipient_email' => 'istekla@test.rs',
            'recipient_name' => 'Test Istekla',
            'is_active' => false,
            'expires_at' => now()->subMonth(),
        ]);
    }

    // ------------------------------------------------------------------
    // Loyalty program
    // ------------------------------------------------------------------
    private function seedLoyalty(): void
    {
        if (LoyaltyAccount::count() > 0) return;

        $users = User::limit(6)->get();
        $tiers = [
            ['points' => 450, 'earned' => 450, 'tier' => 'bronze'],
            ['points' => 1800, 'earned' => 2200, 'tier' => 'silver'],
            ['points' => 6200, 'earned' => 7500, 'tier' => 'gold'],
            ['points' => 16500, 'earned' => 20000, 'tier' => 'platinum'],
            ['points' => 50, 'earned' => 50, 'tier' => 'bronze'],
            ['points' => 3200, 'earned' => 4100, 'tier' => 'silver'],
        ];

        foreach ($users as $i => $user) {
            if (!isset($tiers[$i])) break;
            $t = $tiers[$i];

            $account = LoyaltyAccount::create([
                'user_id' => $user->id,
                'points_balance' => $t['points'],
                'points_earned_total' => $t['earned'],
                'points_spent_total' => $t['earned'] - $t['points'],
                'tier' => $t['tier'],
            ]);

            // Transakcije
            LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'points' => $t['earned'],
                'type' => 'earned',
                'description' => 'Bodovi od kupovina',
                'expires_at' => now()->addYear(),
            ]);

            if ($t['earned'] - $t['points'] > 0) {
                LoyaltyTransaction::create([
                    'loyalty_account_id' => $account->id,
                    'points' => -($t['earned'] - $t['points']),
                    'type' => 'spent',
                    'description' => 'Iskorišćeno pri kupovini',
                ]);
            }
        }
    }

    // ------------------------------------------------------------------
    // Store krediti
    // ------------------------------------------------------------------
    private function seedStoreCredits(): void
    {
        if (StoreCreditAccount::count() > 0) return;

        $users = User::limit(4)->get();
        $amounts = [1500.00, 3000.00, 500.00, 0.00];

        foreach ($users as $i => $user) {
            if (!isset($amounts[$i])) break;

            $account = StoreCreditAccount::create([
                'user_id' => $user->id,
                'balance' => $amounts[$i],
            ]);

            if ($amounts[$i] > 0) {
                StoreCreditTransaction::create([
                    'store_credit_account_id' => $account->id,
                    'amount' => $amounts[$i] + 500,
                    'type' => 'credit',
                    'balance_after' => $amounts[$i] + 500,
                    'reason' => 'Refund na store credit',
                ]);

                StoreCreditTransaction::create([
                    'store_credit_account_id' => $account->id,
                    'amount' => 500,
                    'type' => 'debit',
                    'balance_after' => $amounts[$i],
                    'reason' => 'Iskorišćeno pri kupovini',
                ]);
            }
        }
    }

    // ------------------------------------------------------------------
    // Blog (kategorije, tagovi, postovi)
    // ------------------------------------------------------------------
    private function seedBlog(): void
    {
        if (Post::count() > 0) return;

        $admin = Admin::first();

        // Blog kategorije
        $cats = [];
        $catData = [
            ['name' => 'Saveti za roditelje', 'slug' => 'saveti-za-roditelje', 'description' => 'Korisni saveti za uređenje dečije sobe'],
            ['name' => 'Trendovi', 'slug' => 'trendovi', 'description' => 'Najnoviji trendovi u dizajnu enterijera'],
            ['name' => 'Održavanje', 'slug' => 'odrzavanje', 'description' => 'Kako održavati nameštaj i dekoraciju'],
            ['name' => 'Inspiracija', 'slug' => 'inspiracija', 'description' => 'Inspirativni enterijeri i ideje'],
        ];
        foreach ($catData as $c) {
            $cats[] = BlogCategory::firstOrCreate(['slug' => $c['slug']], $c);
        }

        // Tagovi
        $tags = [];
        $tagData = [
            ['name' => 'Dečija soba', 'slug' => 'decija-soba'],
            ['name' => 'DIY', 'slug' => 'diy'],
            ['name' => 'Organizacija', 'slug' => 'organizacija'],
            ['name' => 'Boje', 'slug' => 'boje'],
            ['name' => 'Sigurnost', 'slug' => 'sigurnost'],
            ['name' => 'Prirodni materijali', 'slug' => 'prirodni-materijali'],
            ['name' => 'Mali prostor', 'slug' => 'mali-prostor'],
        ];
        foreach ($tagData as $t) {
            $tags[] = Tag::firstOrCreate(['slug' => $t['slug']], $t);
        }

        // Objavljeni postovi
        $posts = [
            [
                'title' => 'Kako urediti dečiju sobu: kompletni vodič',
                'slug' => 'kako-urediti-deciju-sobu',
                'excerpt' => 'Praktični saveti za uređenje funkcionalne i lepe dečije sobe.',
                'content' => '<p>Uređenje dečije sobe je uzbudljiv ali i zahtjevan zadatak. U ovom vodiču ćemo vam pomoći da stvorite prostor koji je siguran, funkcionalan i inspirativan za vaše dete.</p><h2>Izbor nameštaja</h2><p>Kvalitetan nameštaj je osnova svake dečije sobe. Birajte komade koji su prilagođeni uzrastu deteta, sa zaobljenim ivicama i netoksičnim materijalima.</p><h2>Organizacija prostora</h2><p>Koristite vertikalni prostor — police, viseće organizere i kutije za igračke pomažu da soba bude uredna.</p><h2>Boje i dekoracija</h2><p>Pastelne boje stvaraju smirujuću atmosferu, dok življe boje podstiču kreativnost. Kombinujte ih prema temperamentu deteta.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(15),
                'cats' => [0, 3],
                'tags' => [0, 2, 3],
            ],
            [
                'title' => '5 trendova u dečijem nameštaju za 2026.',
                'slug' => '5-trendova-deciji-namestaj-2026',
                'excerpt' => 'Otkrijte šta je popularno ove sezone u svetu dečijeg nameštaja.',
                'content' => '<p>Svake godine donosi nove trendove u dizajnu enterijera, a dečije sobe nisu izuzetak.</p><h2>1. Prirodni materijali</h2><p>Drvo, bambus i pamuk dominiraju. Roditelji sve više biraju ekološke materijale.</p><h2>2. Montessori pristup</h2><p>Nameštaj prilagođen visini deteta podstiče samostalnost od najranijeg uzrasta.</p><h2>3. Multifunkcionalni komadi</h2><p>Kreveti sa prostorom za odlaganje, stolovi koji rastu sa detetom.</p><h2>4. Prigušene boje</h2><p>Tonovi karamele, masline i prljavo roze zamenjuju jarke boje.</p><h2>5. Personalizacija</h2><p>Proizvodi koji se mogu prilagoditi — ime na polici, inicijali na jastuku.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(8),
                'cats' => [1],
                'tags' => [0, 5],
            ],
            [
                'title' => 'Održavanje drvenog nameštaja: sve što treba da znate',
                'slug' => 'odrzavanje-drvenog-namestaja',
                'excerpt' => 'Kako da vaš drveni nameštaj traje godinama.',
                'content' => '<p>Drveni nameštaj je investicija koja uz pravilno održavanje može trajati generacijama.</p><h2>Svakodnevno čišćenje</h2><p>Koristite mekanu, suvu krpu. Izbegavajte mokre krpe jer vlaga može oštetiti drvo.</p><h2>Zaštita od sunca</h2><p>Direktna sunčeva svetlost može izbledeti boju drveta. Koristite zavese ili UV zaštitne folije.</p><h2>Periodično negovanje</h2><p>Svakih 6 meseci nanesite specijalno ulje ili vosak za drvo. To čuva sjaj i štiti od vlage.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'cats' => [2],
                'tags' => [5],
            ],
            [
                'title' => 'Sigurnost u dečijoj sobi: na šta obratiti pažnju',
                'slug' => 'sigurnost-u-decijoj-sobi',
                'excerpt' => 'Ključni saveti za sigurno okruženje u dečijoj sobi.',
                'content' => '<p>Bezbednost dece u njihovom životnom prostoru je prioritet svakog roditelja.</p><h2>Učvršćivanje nameštaja</h2><p>Svaki viši komad nameštaja mora biti pričvršćen za zid. Komode i police mogu pasti ako se dete popne na njih.</p><h2>Zaštita uglova</h2><p>Zaoblite oštre uglove zaštitnim gumicama, naročito na visini glave deteta.</p><h2>Električne utičnice</h2><p>Koristite zaštitne poklopce za sve utičnice dostupne detetu.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(20),
                'cats' => [0],
                'tags' => [0, 4],
            ],
            [
                'title' => 'Kako iskoristiti mali prostor za dečiju sobu',
                'slug' => 'mali-prostor-decija-soba',
                'excerpt' => 'Kreativna rešenja za male dečije sobe.',
                'content' => '<p>Nemaju sva deca sreću da imaju veliku sobu, ali to ne znači da prostor ne može biti funkcionalan i lep.</p><h2>Krevet na sprat</h2><p>Oslobađa ceo pod za igru ili radni sto.</p><h2>Zidne police</h2><p>Iskoristite vertikalni prostor za knjige i igračke.</p><h2>Sklopivi nameštaj</h2><p>Sto koji se sklapa kad nije u upotrebi je idealan za male sobe.</p>',
                'status' => 'published',
                'published_at' => now()->subDay(),
                'cats' => [0, 3],
                'tags' => [0, 2, 6],
            ],
            // Draft post
            [
                'title' => 'Najbolji materijali za dečiji nameštaj (DRAFT)',
                'slug' => 'najbolji-materijali-deciji-namestaj',
                'excerpt' => 'Pregled materijala i njihovih prednosti.',
                'content' => '<p>Rad u toku...</p>',
                'status' => 'draft',
                'published_at' => null,
                'cats' => [0, 2],
                'tags' => [5],
            ],
            // Scheduled post
            [
                'title' => 'Prolećno uređenje dečije sobe',
                'slug' => 'prolecno-uredjenje-decije-sobe',
                'excerpt' => 'Ideje za osvežavanje prostora kada stigne proleće.',
                'content' => '<p>Sa dolaskom proleća, vreme je da osvežite dečiju sobu. Menjajte posteljinu u svetlije tonove, dodajte biljke na prozor i organizujte generalku zajedno sa detetom — to je odlična prilika za učenje odgovornosti.</p>',
                'status' => 'scheduled',
                'published_at' => now()->addDays(7),
                'cats' => [1, 3],
                'tags' => [0, 3],
            ],
        ];

        foreach ($posts as $postData) {
            $catIds = $postData['cats'];
            $tagIds = $postData['tags'];
            unset($postData['cats'], $postData['tags']);

            $post = Post::create(array_merge($postData, [
                'admin_id' => $admin->id,
            ]));

            $post->categories()->attach(array_map(fn($i) => $cats[$i]->id, $catIds));
            $post->tags()->attach(array_map(fn($i) => $tags[$i]->id, $tagIds));
        }
    }

    // ------------------------------------------------------------------
    // Newsletter pretplatnici
    // ------------------------------------------------------------------
    private function seedNewsletterSubscribers(): void
    {
        if (NewsletterSubscriber::count() > 0) return;

        $subscribers = [
            ['email' => 'ana@test.rs', 'status' => 'confirmed', 'source' => 'registration', 'confirmed_at' => now()->subDays(30)],
            ['email' => 'petar@test.rs', 'status' => 'confirmed', 'source' => 'footer', 'confirmed_at' => now()->subDays(20)],
            ['email' => 'milica@test.rs', 'status' => 'confirmed', 'source' => 'popup', 'confirmed_at' => now()->subDays(10)],
            ['email' => 'newsletter1@test.rs', 'status' => 'confirmed', 'source' => 'popup', 'confirmed_at' => now()->subDays(5)],
            ['email' => 'newsletter2@test.rs', 'status' => 'confirmed', 'source' => 'checkout', 'confirmed_at' => now()->subDays(3)],
            ['email' => 'pending@test.rs', 'status' => 'pending', 'source' => 'popup', 'confirmed_at' => null],
            ['email' => 'pending2@test.rs', 'status' => 'pending', 'source' => 'footer', 'confirmed_at' => null],
            ['email' => 'odjava@test.rs', 'status' => 'unsubscribed', 'source' => 'registration', 'confirmed_at' => now()->subDays(60), 'unsubscribed_at' => now()->subDays(5)],
        ];

        foreach ($subscribers as $s) {
            NewsletterSubscriber::create(array_merge($s, [
                'token' => NewsletterSubscriber::generateToken(),
            ]));
        }
    }

    // ------------------------------------------------------------------
    // Napuštene korpe
    // ------------------------------------------------------------------
    private function seedAbandonedCarts(): void
    {
        if (AbandonedCart::count() > 0) return;

        $products = Product::where('is_active', true)->inRandomOrder()->limit(10)->get();

        $carts = [
            [
                'email' => 'napustena1@test.rs',
                'status' => 'abandoned',
                'emails_sent' => 2,
                'total' => 8500.00,
                'created_at' => now()->subHours(36),
            ],
            [
                'email' => 'napustena2@test.rs',
                'status' => 'abandoned',
                'emails_sent' => 1,
                'total' => 3200.00,
                'created_at' => now()->subHours(6),
            ],
            [
                'email' => 'napustena3@test.rs',
                'status' => 'abandoned',
                'emails_sent' => 0,
                'total' => 12400.00,
                'created_at' => now()->subMinutes(45),
            ],
            [
                'email' => 'ana@test.rs',
                'user_id' => User::where('email', 'ana@test.rs')->first()?->id,
                'status' => 'recovered',
                'emails_sent' => 1,
                'total' => 6700.00,
                'recovered_at' => now()->subDays(2),
                'created_at' => now()->subDays(3),
            ],
            [
                'email' => 'istekla@test.rs',
                'status' => 'expired',
                'emails_sent' => 3,
                'total' => 4100.00,
                'created_at' => now()->subDays(35),
            ],
        ];

        foreach ($carts as $cartData) {
            $itemCount = rand(1, 3);
            $cartProducts = $products->random($itemCount);
            $items = $cartProducts->map(fn($p) => [
                'product_id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->effective_price,
                'quantity' => rand(1, 2),
            ])->toArray();

            AbandonedCart::create(array_merge($cartData, [
                'items' => $items,
                'token' => AbandonedCart::generateToken(),
            ]));
        }
    }

    // ------------------------------------------------------------------
    // Fizicke prodavnice
    // ------------------------------------------------------------------
    private function seedStoreLocations(): void
    {
        if (StoreLocation::count() > 0) return;

        $locations = [
            [
                'name' => 'eLokal — Beograd Centar',
                'address' => 'Knez Mihailova 22',
                'city' => 'Beograd',
                'postal_code' => '11000',
                'country' => 'RS',
                'phone' => '+381 11 123 4567',
                'email' => 'beograd@elokal.rs',
                'latitude' => 44.8176,
                'longitude' => 20.4569,
                'working_hours' => ['pon-pet' => '09:00 - 20:00', 'sub' => '09:00 - 15:00', 'ned' => 'Zatvoreno'],
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'name' => 'eLokal — Novi Sad',
                'address' => 'Bulevar oslobođenja 30',
                'city' => 'Novi Sad',
                'postal_code' => '21000',
                'country' => 'RS',
                'phone' => '+381 21 456 7890',
                'email' => 'novisad@elokal.rs',
                'latitude' => 45.2671,
                'longitude' => 19.8335,
                'working_hours' => ['pon-pet' => '09:00 - 20:00', 'sub' => '10:00 - 14:00', 'ned' => 'Zatvoreno'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'eLokal — Niš',
                'address' => 'Obrenovićeva 15',
                'city' => 'Niš',
                'postal_code' => '18000',
                'country' => 'RS',
                'phone' => '+381 18 234 5678',
                'email' => 'nis@elokal.rs',
                'latitude' => 43.3209,
                'longitude' => 21.8954,
                'working_hours' => ['pon-pet' => '09:00 - 19:00', 'sub' => '09:00 - 14:00', 'ned' => 'Zatvoreno'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'eLokal — Kragujevac (uskoro)',
                'address' => 'Kralja Petra I 55',
                'city' => 'Kragujevac',
                'postal_code' => '34000',
                'country' => 'RS',
                'phone' => '+381 34 345 6789',
                'email' => 'kragujevac@elokal.rs',
                'latitude' => 44.0128,
                'longitude' => 20.9114,
                'working_hours' => [],
                'is_active' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($locations as $loc) {
            StoreLocation::create($loc);
        }
    }

    // ------------------------------------------------------------------
    // Shop the Look
    // ------------------------------------------------------------------
    private function seedLooks(): void
    {
        if (Look::count() > 0) return;

        $products = Product::where('is_active', true)->inRandomOrder()->limit(8)->get();
        if ($products->count() < 6) return;

        $look1 = Look::create([
            'title' => 'Moderna dečija soba u pastelnim tonovima',
            'image_path' => '', // Nema demo sliku, ali kreirana struktura
            'is_active' => true,
            'sort_order' => 0,
        ]);

        foreach ($products->take(3) as $i => $product) {
            LookHotspot::create([
                'look_id' => $look1->id,
                'product_id' => $product->id,
                'x_position' => 20.00 + ($i * 30),
                'y_position' => 30.00 + ($i * 15),
                'label' => $product->name,
            ]);
        }

        $look2 = Look::create([
            'title' => 'Skandinavski stil za mališane',
            'image_path' => '',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        foreach ($products->skip(3)->take(3) as $i => $product) {
            LookHotspot::create([
                'look_id' => $look2->id,
                'product_id' => $product->id,
                'x_position' => 15.00 + ($i * 25),
                'y_position' => 25.00 + ($i * 20),
                'label' => $product->name,
            ]);
        }

        // Neaktivan look
        $look3 = Look::create([
            'title' => 'Zimska čarolija (priprema)',
            'image_path' => '',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        foreach ($products->skip(6)->take(2) as $i => $product) {
            LookHotspot::create([
                'look_id' => $look3->id,
                'product_id' => $product->id,
                'x_position' => 40.00 + ($i * 20),
                'y_position' => 50.00,
                'label' => $product->name,
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Kontakt poruke
    // ------------------------------------------------------------------
    private function seedContactMessages(): void
    {
        if (ContactMessage::count() > 0) return;

        $messages = [
            ['name' => 'Dragan Milić', 'email' => 'dragan@gmail.com', 'subject' => 'Pitanje o dostavi', 'message' => 'Poštovani, zanima me da li vršite dostavu u Vranje i koliko bi trajala?', 'status' => 'new', 'created_at' => now()->subHours(2)],
            ['name' => 'Ivana Ristić', 'email' => 'ivana.r@yahoo.com', 'phone' => '+381601234567', 'subject' => 'Reklamacija', 'message' => 'Primila sam oštećen proizvod (narudžbina EL-240401-XXXX). Molim vas za zamenu.', 'status' => 'read', 'created_at' => now()->subDays(1)],
            ['name' => 'Mirko Tomić', 'email' => 'mirko.t@hotmail.com', 'subject' => 'Saradnja', 'message' => 'Zdravo, imam prodavnicu dečije opreme u Novom Sadu i zainteresovan sam za veleprodajnu saradnju. Možete li mi poslati cenovnik?', 'status' => 'replied', 'created_at' => now()->subDays(5)],
            ['name' => 'Jelena Popović', 'email' => 'jelena.p@gmail.com', 'subject' => 'Raspitivanje o proizvodu', 'message' => 'Da li je krevetac od punog drveta? Koji su tačni materijali?', 'status' => 'new', 'created_at' => now()->subHours(5)],
        ];

        foreach ($messages as $m) {
            ContactMessage::create($m);
        }
    }

    // ------------------------------------------------------------------
    // Zahtevi za povratni poziv
    // ------------------------------------------------------------------
    private function seedCallbackRequests(): void
    {
        if (CallbackRequest::count() > 0) return;

        $products = Product::where('is_active', true)->inRandomOrder()->limit(3)->get();

        $requests = [
            ['name' => 'Marko Nikolić', 'phone' => '+381641112233', 'channel' => 'call', 'message' => 'Zanima me da li imate ovaj proizvod na stanju u beloj boji.', 'status' => 'pending'],
            ['name' => 'Jovana Marić', 'phone' => '+381652223344', 'channel' => 'whatsapp', 'message' => 'Mogu li dobiti dodatne slike?', 'status' => 'contacted', 'admin_notes' => 'Poslate slike preko WhatsApp-a 08.04.'],
            ['name' => 'Aleksandar Vuković', 'phone' => '+381603334455', 'channel' => 'sms', 'message' => null, 'status' => 'closed', 'admin_notes' => 'Kupac je poručio online.'],
        ];

        foreach ($requests as $i => $r) {
            CallbackRequest::create(array_merge($r, [
                'product_id' => $products[$i % $products->count()]->id,
            ]));
        }
    }

    // ------------------------------------------------------------------
    // Search logovi
    // ------------------------------------------------------------------
    private function seedSearchLogs(): void
    {
        if (SearchLog::count() > 0) return;

        $queries = [
            ['query' => 'tepih', 'results_count' => 5],
            ['query' => 'krevetac', 'results_count' => 3],
            ['query' => 'lampa', 'results_count' => 8],
            ['query' => 'police', 'results_count' => 4],
            ['query' => 'roze zavesa', 'results_count' => 0],
            ['query' => 'drvena igračka', 'results_count' => 0],
            ['query' => 'fotelja', 'results_count' => 2],
            ['query' => 'tepih oblak', 'results_count' => 1],
            ['query' => 'dekoracija', 'results_count' => 12],
            ['query' => 'poklon', 'results_count' => 0],
            ['query' => 'lampa', 'results_count' => 8],
            ['query' => 'tepih', 'results_count' => 5],
            ['query' => 'krevetac', 'results_count' => 3],
            ['query' => 'zvezdice', 'results_count' => 2],
            ['query' => 'nameštaj', 'results_count' => 15],
        ];

        $users = User::all();

        foreach ($queries as $i => $q) {
            DB::table('search_logs')->insert([
                'query' => $q['query'],
                'results_count' => $q['results_count'],
                'user_id' => $i % 3 === 0 ? $users->random()->id : null,
                'ip' => '192.168.1.' . rand(1, 254),
                'created_at' => now()->subDays(rand(0, 14))->subHours(rand(0, 23)),
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Stock movements
    // ------------------------------------------------------------------
    private function seedStockMovements(): void
    {
        if (StockMovement::count() > 0) return;

        $products = Product::where('is_active', true)->limit(10)->get();

        foreach ($products as $product) {
            // Inicijalni restock
            DB::table('stock_movements')->insert([
                'product_id' => $product->id,
                'quantity' => $product->stock_quantity + rand(5, 20),
                'type' => 'restock',
                'note' => 'Inicijalno punjenje zaliha',
                'stock_after' => $product->stock_quantity + rand(5, 20),
                'created_at' => now()->subDays(30),
            ]);

            // Prodaje
            for ($i = 0; $i < rand(2, 5); $i++) {
                $qty = rand(1, 3);
                DB::table('stock_movements')->insert([
                    'product_id' => $product->id,
                    'quantity' => -$qty,
                    'type' => 'sale',
                    'reference_type' => 'App\\Models\\Order',
                    'reference_id' => Order::inRandomOrder()->first()?->id,
                    'note' => null,
                    'stock_after' => $product->stock_quantity,
                    'created_at' => now()->subDays(rand(1, 25)),
                ]);
            }

            // Poneki adjustment
            if (rand(0, 1)) {
                DB::table('stock_movements')->insert([
                    'product_id' => $product->id,
                    'quantity' => rand(-2, 5),
                    'type' => 'adjustment',
                    'note' => 'Korekcija nakon inventure',
                    'stock_after' => $product->stock_quantity,
                    'created_at' => now()->subDays(rand(1, 10)),
                ]);
            }
        }
    }

    // ------------------------------------------------------------------
    // Downloadable fajlovi (digitalni proizvodi)
    // ------------------------------------------------------------------
    private function seedDownloadableFiles(): void
    {
        if (DownloadableFile::count() > 0) return;

        $products = Product::where('is_active', true)->inRandomOrder()->limit(3)->get();
        $users = User::limit(2)->get();

        foreach ($products as $i => $product) {
            $file = DownloadableFile::create([
                'product_id' => $product->id,
                'name' => 'Uputstvo za montažu - ' . $product->name . '.pdf',
                'file_path' => 'downloads/uputstvo-' . Str::slug($product->name) . '.pdf',
                'file_size' => rand(500000, 5000000), // 500KB - 5MB
                'max_downloads' => 5,
                'expires_days' => 30,
                'sort_order' => 0,
            ]);

            // Download log za prvog korisnika
            if ($users->isNotEmpty() && $i < 2) {
                DownloadLog::create([
                    'downloadable_file_id' => $file->id,
                    'order_id' => Order::where('user_id', $users[$i]->id)->first()?->id,
                    'user_id' => $users[$i]->id,
                    'download_count' => rand(1, 3),
                    'expires_at' => now()->addDays(30),
                    'token' => DownloadLog::generateToken(),
                ]);
            }
        }
    }

    // ------------------------------------------------------------------
    // Shipments i Refunds za postojeće narudžbine
    // ------------------------------------------------------------------
    private function seedShipmentsAndRefunds(): void
    {
        if (Shipment::count() > 0) return;

        // Shipments za shipped/delivered/completed narudžbine
        $shippedOrders = Order::whereIn('status', ['shipped', 'delivered', 'completed'])->get();

        foreach ($shippedOrders as $order) {
            $carrier = \App\Models\Carrier::where('is_active', true)->inRandomOrder()->first();
            $trackingNum = strtoupper(Str::random(2)) . rand(100000000, 999999999);

            $status = match ($order->status) {
                'delivered', 'completed' => 'delivered',
                default => 'in_transit',
            };

            Shipment::create([
                'order_id' => $order->id,
                'tracking_number' => $trackingNum,
                'carrier' => $carrier?->name ?? 'D Express',
                'carrier_url' => $carrier?->getTrackingUrl($trackingNum) ?? null,
                'status' => $status,
                'weight' => rand(5, 150) / 10,
                'shipped_at' => $order->created_at->addDays(rand(1, 3)),
                'delivered_at' => $status === 'delivered' ? $order->created_at->addDays(rand(3, 7)) : null,
            ]);

            // Ažuriraj tracking na orderu
            $order->update([
                'tracking_number' => $trackingNum,
                'tracking_carrier' => $carrier?->name ?? 'D Express',
                'tracking_url' => $carrier?->getTrackingUrl($trackingNum) ?? null,
            ]);
        }

        // Refunds — napravi 2-3 refund-a na random narudžbine
        $completedOrders = Order::whereIn('status', ['completed', 'delivered'])->inRandomOrder()->limit(3)->get();
        $admin = Admin::first();

        foreach ($completedOrders as $i => $order) {
            $isPartial = $i < 2;
            $amount = $isPartial
                ? round((float) $order->total * (rand(20, 50) / 100), 2)
                : (float) $order->total;

            Refund::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $i === 0 ? 'store_credit' : 'original',
                'reason' => $isPartial
                    ? 'Kupac vratio jedan artikal — oštećen pri transportu'
                    : 'Kupac odustao — potpuni povrat',
                'created_by' => $admin->id,
            ]);

            $order->update([
                'refunded_amount' => $amount,
                'refund_reason' => $isPartial ? 'Parcijalni refund' : 'Potpuni refund',
                'status' => $isPartial ? $order->status : 'refunded',
            ]);

            // Timeline entry za refund
            DB::table('order_timeline')->insert([
                'order_id' => $order->id,
                'status' => $isPartial ? $order->status : 'refunded',
                'old_status' => $order->getOriginal('status') ?? $order->status,
                'note' => $isPartial
                    ? 'Parcijalni refund: ' . number_format($amount, 2) . ' RSD'
                    : 'Potpuni refund: ' . number_format($amount, 2) . ' RSD',
                'actor_type' => 'admin',
                'actor_id' => $admin->id,
                'created_at' => now()->subDays(rand(1, 5)),
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Webhooks
    // ------------------------------------------------------------------
    private function seedWebhooks(): void
    {
        if (Webhook::count() > 0) return;

        Webhook::create([
            'url' => 'https://hooks.example.com/elokal/orders',
            'events' => ['order.created', 'order.status_changed', 'order.refunded'],
            'secret' => Webhook::generateSecret(),
            'is_active' => true,
        ]);

        Webhook::create([
            'url' => 'https://hooks.example.com/elokal/inventory',
            'events' => ['product.created', 'product.updated', 'product.stock_changed'],
            'secret' => Webhook::generateSecret(),
            'is_active' => true,
        ]);

        Webhook::create([
            'url' => 'https://old-system.example.com/webhook',
            'events' => ['order.created'],
            'secret' => Webhook::generateSecret(),
            'is_active' => false,
            'failures' => 5,
        ]);
    }

    // ------------------------------------------------------------------
    // Media folderi
    // ------------------------------------------------------------------
    private function seedMediaFolders(): void
    {
        if (MediaFolder::count() > 0) return;

        $products = MediaFolder::create(['name' => 'Proizvodi']);
        MediaFolder::create(['name' => 'Nameštaj', 'parent_id' => $products->id]);
        MediaFolder::create(['name' => 'Dekoracija', 'parent_id' => $products->id]);
        MediaFolder::create(['name' => 'Tepisi', 'parent_id' => $products->id]);

        MediaFolder::create(['name' => 'Blog']);
        MediaFolder::create(['name' => 'Baneri']);
        MediaFolder::create(['name' => 'Ostalo']);
    }

    // ------------------------------------------------------------------
    // Stock notifikacije (obavesti me kad bude na stanju)
    // ------------------------------------------------------------------
    private function seedStockNotifications(): void
    {
        if (StockNotification::count() > 0) return;

        // Nađi out-of-stock ili low stock proizvode, ili bilo koje ako nema
        $products = Product::where('stock_quantity', '<=', 5)->limit(3)->get();
        if ($products->isEmpty()) {
            $products = Product::inRandomOrder()->limit(3)->get();
        }

        $emails = ['zainteresovan1@test.rs', 'zainteresovan2@test.rs', 'ana@test.rs', 'petar@test.rs'];

        foreach ($products as $product) {
            foreach (array_slice($emails, 0, rand(1, 3)) as $email) {
                StockNotification::create([
                    'product_id' => $product->id,
                    'email' => $email,
                    'notified' => false,
                ]);
            }
        }

        // Jedna vec obaveštena
        if ($products->isNotEmpty()) {
            StockNotification::create([
                'product_id' => $products->first()->id,
                'email' => 'obavestena@test.rs',
                'notified' => true,
                'notified_at' => now()->subDays(3),
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Activity log
    // ------------------------------------------------------------------
    private function seedActivityLog(): void
    {
        if (ActivityLog::count() > 0) return;

        $admin = Admin::first();
        $actions = [
            ['action' => 'created', 'subject_type' => 'App\\Models\\Product', 'subject_id' => 1, 'changes' => ['name' => 'Novi proizvod']],
            ['action' => 'updated', 'subject_type' => 'App\\Models\\Product', 'subject_id' => 3, 'changes' => ['price' => ['old' => 5000, 'new' => 4500]]],
            ['action' => 'updated', 'subject_type' => 'App\\Models\\Order', 'subject_id' => 1, 'changes' => ['status' => ['old' => 'pending', 'new' => 'confirmed']]],
            ['action' => 'deleted', 'subject_type' => 'App\\Models\\Review', 'subject_id' => 5, 'changes' => null],
            ['action' => 'created', 'subject_type' => 'App\\Models\\Coupon', 'subject_id' => 1, 'changes' => ['code' => 'POPUST10']],
            ['action' => 'updated', 'subject_type' => 'App\\Models\\Setting', 'subject_id' => null, 'changes' => ['key' => 'site_name', 'value' => 'eLokal']],
            ['action' => 'approved', 'subject_type' => 'App\\Models\\Review', 'subject_id' => 2, 'changes' => ['status' => ['old' => 'pending', 'new' => 'approved']]],
            ['action' => 'created', 'subject_type' => 'App\\Models\\Post', 'subject_id' => 1, 'changes' => ['title' => 'Kako urediti dečiju sobu']],
            ['action' => 'refunded', 'subject_type' => 'App\\Models\\Order', 'subject_id' => 3, 'changes' => ['amount' => 2500]],
            ['action' => 'login', 'subject_type' => null, 'subject_id' => null, 'changes' => null],
        ];

        foreach ($actions as $i => $a) {
            DB::table('activity_logs')->insert([
                'admin_id' => $admin->id,
                'action' => $a['action'],
                'subject_type' => $a['subject_type'],
                'subject_id' => $a['subject_id'],
                'changes' => $a['changes'] ? json_encode($a['changes']) : null,
                'ip_address' => '192.168.1.100',
                'created_at' => now()->subDays(10 - $i)->subHours(rand(0, 12)),
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Prevodi (primer za par proizvoda i kategorija)
    // ------------------------------------------------------------------
    private function seedTranslations(): void
    {
        if (Translation::count() > 0) return;

        // Prevedi prvih 5 proizvoda na engleski
        $products = Product::limit(5)->get();
        foreach ($products as $product) {
            Translation::create([
                'translatable_type' => 'App\\Models\\Product',
                'translatable_id' => $product->id,
                'locale' => 'en',
                'field' => 'name',
                'value' => 'EN: ' . $product->name,
            ]);
            Translation::create([
                'translatable_type' => 'App\\Models\\Product',
                'translatable_id' => $product->id,
                'locale' => 'en',
                'field' => 'description',
                'value' => 'English description for ' . $product->name,
            ]);
        }

        // Prevedi kategorije
        $categories = \App\Models\Category::limit(3)->get();
        foreach ($categories as $cat) {
            Translation::create([
                'translatable_type' => 'App\\Models\\Category',
                'translatable_id' => $cat->id,
                'locale' => 'en',
                'field' => 'name',
                'value' => 'EN: ' . $cat->name,
            ]);
        }

        // Prevedi stranice
        $pages = \App\Models\Page::limit(2)->get();
        foreach ($pages as $page) {
            Translation::create([
                'translatable_type' => 'App\\Models\\Page',
                'translatable_id' => $page->id,
                'locale' => 'en',
                'field' => 'title',
                'value' => 'EN: ' . $page->title,
            ]);
            Translation::create([
                'translatable_type' => 'App\\Models\\Page',
                'translatable_id' => $page->id,
                'locale' => 'en',
                'field' => 'content',
                'value' => '<p>English version of this page.</p>',
            ]);
        }
    }
}
