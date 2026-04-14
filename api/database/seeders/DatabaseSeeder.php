<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ShippingSeeder::class,
            DemoDataSeeder::class,
            FullDemoSeeder::class,
        ]);

        User::create([
            'name' => 'Marko Marković',
            'email' => 'marko@webshop.test',
            'password' => 'password',
            'phone' => '+381641234567',
        ]);

        TaxRate::firstOrCreate(
            ['country' => 'RS', 'is_default' => true],
            ['name' => 'PDV 20%', 'rate' => 20.00, 'is_active' => true],
        );

        // Kuriri
        $carriers = [
            ['code' => 'post_srbije', 'name' => 'Pošta Srbije', 'tracking_url_pattern' => 'https://www.posta.rs/cir/alati/pracenje-posiljke.aspx?barcode={tracking_number}', 'sort_order' => 1],
            ['code' => 'dexpress', 'name' => 'D Express', 'tracking_url_pattern' => 'https://www.dexpress.rs/rs/pracenje-posiljke/{tracking_number}', 'sort_order' => 2],
            ['code' => 'bex', 'name' => 'BEX Express', 'tracking_url_pattern' => 'https://bfracking.bfrack.com/{tracking_number}', 'sort_order' => 3],
            ['code' => 'aks', 'name' => 'AKS Express', 'tracking_url_pattern' => 'https://www.aksexpress.rs/pracenje/{tracking_number}', 'sort_order' => 4],
            ['code' => 'custom', 'name' => 'Ostalo', 'tracking_url_pattern' => null, 'sort_order' => 99],
        ];
        foreach ($carriers as $carrier) {
            \App\Models\Carrier::firstOrCreate(['code' => $carrier['code']], $carrier);
        }

        // Newsletter podešavanja
        Setting::setValue('newsletter', 'newsletter_popup_delay', '10');
        Setting::setValue('newsletter', 'newsletter_popup_enabled', 'true');

        // Social proof podešavanja
        Setting::setValue('storefront', 'social_proof_viewing_enabled', 'true');
        Setting::setValue('storefront', 'social_proof_purchase_count_enabled', 'true');
        Setting::setValue('storefront', 'social_proof_popup_enabled', 'true');

        // Email podešavanja
        Setting::setValue('email', 'email_sender_name', 'eLokal');
        Setting::setValue('email', 'email_sender_address', 'noreply@elokal.rs');
        Setting::setValue('email', 'email_primary_color', '#4F46E5');
        Setting::setValue('email', 'email_logo_url', '');
        Setting::setValue('email', 'email_footer_text', '© eLokal. Sva prava zadržana.');
        Setting::setValue('email', 'email_welcome_enabled', 'true');
        Setting::setValue('email', 'email_order_confirmation_enabled', 'true');
        Setting::setValue('email', 'email_order_shipped_enabled', 'true');
        Setting::setValue('email', 'email_order_delivered_enabled', 'true');
        Setting::setValue('email', 'email_order_cancelled_enabled', 'true');
        Setting::setValue('email', 'email_refund_enabled', 'true');

        // Notifikacije podešavanja
        Setting::setValue('notifications', 'notifications_admin_email', 'admin@elokal.rs');
        Setting::setValue('notifications', 'notifications_low_stock_threshold', '5');
        Setting::setValue('notifications', 'notifications_on_new_order', 'true');
        Setting::setValue('notifications', 'notifications_on_new_review', 'true');
        Setting::setValue('notifications', 'notifications_on_low_stock', 'true');
        Setting::setValue('notifications', 'notifications_on_contact_form', 'true');

        // Admin IP whitelist (prazno = sve dozvoljeno)
        Setting::setValue('admin', 'admin_ip_whitelist', '');

        // Jezici podešavanja
        Setting::setValue('languages', 'languages_available', 'sr');
        Setting::setValue('languages', 'languages_default', 'sr');
        Setting::setValue('languages', 'languages_picker_position', 'header');
    }
}
