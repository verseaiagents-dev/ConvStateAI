<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FAQ;
use App\Models\Site;

class FAQSeeder extends Seeder
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

        $faqs = [
            [
                'question' => '📦 Siparişimi nasıl takip edebilirim?',
                'answer' => 'Siparişinizi takip etmek için sipariş numaranızı kullanarak "Sipariş Takip" sayfasından veya size gönderilen SMS/email linklerinden takip edebilirsiniz. Ayrıca mobil uygulamamız üzerinden de sipariş durumunuzu anlık olarak görebilirsiniz. Sipariş durumu güncellemeleri otomatik olarak size bildirilir.',
                'short_answer' => 'Sipariş Takip',
                'is_active' => true,
                'site_id' => $site->id,
                'sort_order' => 1,
                'tags' => ['sipariş', 'takip', 'durum'],
                'view_count' => 1250,
                'helpful_count' => 89,
                'not_helpful_count' => 12,
                'keywords' => null,
                'is_featured' => true,
                'meta_title' => 'Sipariş Takip Sistemi',
                'meta_description' => 'Siparişinizi nasıl takip edeceğinizi öğrenin',
                'seo_url' => 'siparis-takip'
            ],
            [
                'question' => '🚚 Kargo ücreti ne kadar?',
                'answer' => '500 TL ve üzeri alışverişlerde kargo ücretsizdir. 500 TL altındaki siparişlerde kargo ücreti 29.90 TL\'dir. VIP üyelerimiz için tüm siparişlerde kargo ücretsizdir. Ayrıca belirli kampanyalar döneminde kargo ücreti indirimi yapılabilir.',
                'short_answer' => 'Ücretsiz Kargo',
                'is_active' => true,
                'site_id' => $site->id,
                'sort_order' => 2,
                'tags' => ['kargo', 'ücret', 'ücretsiz'],
                'view_count' => 980,
                'helpful_count' => 76,
                'not_helpful_count' => 8,
                'keywords' => null,
                'is_featured' => true,
                'meta_title' => 'Kargo Ücreti ve Ücretsiz Kargo',
                'meta_description' => 'Kargo ücretleri ve ücretsiz kargo koşulları',
                'seo_url' => 'kargo-ucreti'
            ],
            [
                'question' => '🔄 İade ve değişim nasıl yapılır?',
                'answer' => 'Ürünlerinizi teslim aldıktan sonra 14 gün içinde iade veya değişim talebinde bulunabilirsiniz. Ürün orijinal ambalajında ve kullanılmamış olmalıdır. İade kargo ücreti müşteri tarafından karşılanır. İade işlemi için müşteri hizmetlerimizle iletişime geçebilir veya online iade formunu doldurabilirsiniz.',
                'short_answer' => '14 Gün İade',
                'is_active' => true,
                'site_id' => $site->id,
                'sort_order' => 3,
                'tags' => ['iade', 'değişim', '14 gün'],
                'view_count' => 756,
                'helpful_count' => 65,
                'not_helpful_count' => 15,
                'keywords' => null,
                'is_featured' => true,
                'meta_title' => 'İade ve Değişim Koşulları',
                'meta_description' => '14 gün içinde iade ve değişim hakkı',
                'seo_url' => 'iade-degisim'
            ],
            [
                'question' => '💳 Hangi ödeme yöntemleri kabul ediliyor?',
                'answer' => 'Kredi kartı, banka kartı, havale/EFT, kapıda ödeme ve taksit seçeneklerini kabul ediyoruz. Tüm kartlarda peşin fiyatına 3 taksit imkanı sunuyoruz. Güvenli ödeme altyapısı ile işlemleriniz korunmaktadır. Ayrıca Apple Pay ve Google Pay gibi mobil ödeme yöntemleri de desteklenmektedir.',
                'short_answer' => '3 Taksit',
                'is_active' => true,
                'site_id' => $site->id,
                'sort_order' => 4,
                'tags' => ['ödeme', 'taksit', 'kredi kartı'],
                'view_count' => 1120,
                'helpful_count' => 92,
                'not_helpful_count' => 6,
                'keywords' => null,
                'is_featured' => true,
                'meta_title' => 'Ödeme Yöntemleri ve Taksit',
                'meta_description' => 'Kabul edilen ödeme yöntemleri ve taksit seçenekleri',
                'seo_url' => 'odeme-yontemleri'
            ],
            [
                'question' => '⭐ Üyelik avantajları nelerdir?',
                'answer' => 'Üyelerimiz özel indirimler, erken erişim fırsatları, puan kazanma, özel kampanyalar ve müşteri hizmetleri önceliği gibi avantajlardan yararlanır. VIP üyelerimiz için ekstra %15 indirim ve ücretsiz kargo hizmeti sunuyoruz. Ayrıca doğum günü indirimleri ve özel etkinlik davetleri de sunulmaktadır.',
                'short_answer' => 'VIP Avantajlar',
                'is_active' => true,
                'site_id' => $site->id,
                'sort_order' => 5,
                'tags' => ['üyelik', 'vip', 'avantaj'],
                'view_count' => 890,
                'helpful_count' => 78,
                'not_helpful_count' => 9,
                'keywords' => null,
                'is_featured' => true,
                'meta_title' => 'Üyelik Avantajları ve VIP',
                'meta_description' => 'Üyelik avantajları ve VIP üyelik özellikleri',
                'seo_url' => 'uyelik-avantajlari'
            ]
        ];

        foreach ($faqs as $faqData) {
            FAQ::create($faqData);
        }

        $this->command->info('FAQs seeded successfully!');
    }
}
