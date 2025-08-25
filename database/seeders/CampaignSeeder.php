<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\Site;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have a site
        $site = Site::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Test Site',
                'domain' => 'test.com',
                'is_active' => true
            ]
        );

        $campaigns = [
            [
                'title' => '🎉 2 Al 1 Bedava (Moda)',
                'description' => 'Seçili moda ürünlerinde 2 al 1 bedava kampanyası. Stoklarla sınırlı, kaçırma!',
                'category' => 'Moda',
                'discount' => '2 Al 1 Bedava',
                'discount_type' => 'buy_x_get_y',
                'start_date' => now(),
                'end_date' => now()->addMonths(2),
                'is_active' => true,
                'site_id' => $site->id,
                'minimum_order_amount' => 100.00,
                'max_usage' => 1000,
                'current_usage' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400',
                'terms_conditions' => 'Kampanya stoklarla sınırlıdır. Değişiklik yapma hakkı saklıdır.'
            ],
            [
                'title' => '🚚 500 TL Üzeri Ücretsiz Kargo',
                'description' => '500 TL ve üzeri alışverişlerde ücretsiz kargo. Tüm kategorilerde geçerli!',
                'category' => 'Genel',
                'discount' => 'Ücretsiz Kargo',
                'discount_type' => 'free_shipping',
                'start_date' => now(),
                'end_date' => null, // Sürekli
                'is_active' => true,
                'site_id' => $site->id,
                'minimum_order_amount' => 500.00,
                'max_usage' => null,
                'current_usage' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=400',
                'terms_conditions' => '500 TL altındaki siparişlerde kargo ücreti 29.90 TL\'dir.'
            ],
            [
                'title' => '🛒 Yeni Üyelere %10 İndirim',
                'description' => 'Yeni üye olanlara özel %10 indirim fırsatı. Hemen üye ol, indirimini kap!',
                'category' => 'Üyelik',
                'discount' => '%10 İndirim',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'is_active' => true,
                'site_id' => $site->id,
                'minimum_order_amount' => 50.00,
                'max_usage' => 500,
                'current_usage' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=400',
                'terms_conditions' => 'Sadece yeni üyeler için geçerlidir. Bir kez kullanılabilir.'
            ],
            [
                'title' => '🔥 Seçili Elektronik Ürünlerde %20 İndirim',
                'description' => 'Elektronik kategorisinde seçili ürünlerde %20 indirim. Teknoloji tutkunları için!',
                'category' => 'Elektronik',
                'discount' => '%20 İndirim',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'is_active' => true,
                'site_id' => $site->id,
                'minimum_order_amount' => 200.00,
                'max_usage' => 200,
                'current_usage' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=400',
                'terms_conditions' => 'Seçili ürünlerde geçerlidir. Stoklarla sınırlıdır.'
            ],
            [
                'title' => '💳 Peşin Fiyatına 3 Taksit',
                'description' => 'Tüm ürünlerde peşin fiyatına 3 taksit imkanı. Ekstra ücret yok!',
                'category' => 'Ödeme',
                'discount' => '3 Taksit',
                'discount_type' => 'buy_x_get_y',
                'start_date' => now(),
                'end_date' => null, // Sürekli
                'is_active' => true,
                'site_id' => $site->id,
                'minimum_order_amount' => 100.00,
                'max_usage' => null,
                'current_usage' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=400',
                'terms_conditions' => 'Tüm kredi kartlarında geçerlidir. Ekstra ücret alınmaz.'
            ],
            [
                'title' => '🌟 VIP Üyelere Özel %15 İndirim',
                'description' => 'VIP üye statüsündeki müşterilere özel %15 indirim. VIP avantajlarını keşfet!',
                'category' => 'VIP',
                'discount' => '%15 İndirim',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'start_date' => now(),
                'end_date' => now()->addMonths(2),
                'is_active' => true,
                'site_id' => $site->id,
                'minimum_order_amount' => 150.00,
                'max_usage' => null,
                'current_usage' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=400',
                'terms_conditions' => 'Sadece VIP üyeler için geçerlidir. Diğer üyeler için geçerli değildir.'
            ],
            [
                'title' => '🎁 İlk Siparişe Özel Hediye',
                'description' => 'İlk siparişini veren müşterilere özel hediye paketi. Sürpriz hediyeler seni bekliyor!',
                'category' => 'Hediye',
                'discount' => 'Özel Hediye',
                'discount_type' => 'buy_x_get_y',
                'start_date' => now(),
                'end_date' => now()->addMonths(6),
                'is_active' => true,
                'site_id' => $site->id,
                'minimum_order_amount' => 75.00,
                'max_usage' => 1000,
                'current_usage' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=400',
                'terms_conditions' => 'Sadece ilk sipariş için geçerlidir. Hediye türü değişebilir.'
            ],
            [
                'title' => '⚡ Flash İndirim - %30',
                'description' => 'Sadece bugün geçerli flash indirim fırsatı. Hızlı ol, kaçırma!',
                'category' => 'Flash',
                'discount' => '%30 İndirim',
                'discount_type' => 'percentage',
                'discount_value' => 30.00,
                'start_date' => now(),
                'end_date' => now()->endOfDay(),
                'is_active' => true,
                'site_id' => $site->id,
                'minimum_order_amount' => 100.00,
                'max_usage' => 100,
                'current_usage' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=400',
                'terms_conditions' => 'Sadece bugün geçerlidir. Stoklarla sınırlıdır.'
            ]
        ];

        foreach ($campaigns as $campaignData) {
            Campaign::create($campaignData);
        }

        $this->command->info('Campaigns seeded successfully!');
    }
}
