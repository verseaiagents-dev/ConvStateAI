<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MailTemplate;

class MailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Welcome Template
        MailTemplate::create([
            'name' => 'Hoşgeldin Maili',
            'subject' => 'Hoş Geldiniz! {{sitename}} ailesine katıldığınız için teşekkür ederiz',
            'content' => $this->getWelcomeTemplate(),
            'category' => 'welcome',
            'description' => 'Kullanıcı kaydı sonrası gönderilen hoşgeldin maili',
            'is_active' => true,
            'variables' => ['username', 'useremail', 'usercreated', 'sitename', 'siteurl', 'loginurl', 'dashboardurl', 'currentdate', 'companyname', 'companyaddress', 'companyphone']
        ]);

        // Subscription Welcome Template
        MailTemplate::create([
            'name' => 'Abonelik Hoşgeldin Maili',
            'subject' => 'Aboneliğiniz Aktif! {{sitename}} Premium Özelliklerine Erişim',
            'content' => $this->getSubscriptionTemplate(),
            'category' => 'subscription',
            'description' => 'Premium abonelik başlangıcında gönderilen mail',
            'is_active' => true,
            'variables' => ['username', 'useremail', 'userplan', 'userplanexpired', 'usercreated', 'sitename', 'siteurl', 'dashboardurl', 'currentdate', 'companyname', 'companyaddress', 'companyphone']
        ]);

        // Password Reset Template
        MailTemplate::create([
            'name' => 'Şifre Sıfırlama Maili',
            'subject' => 'Şifre Sıfırlama Talebi - {{sitename}}',
            'content' => $this->getPasswordResetTemplate(),
            'category' => 'security',
            'description' => 'Şifre sıfırlama talebi sonrası gönderilen mail',
            'is_active' => true,
            'variables' => ['username', 'useremail', 'reseturl', 'sitename', 'siteurl', 'currentdate', 'companyname', 'companyaddress', 'companyphone']
        ]);

        // Notification Template
        MailTemplate::create([
            'name' => 'Genel Bildirim Maili',
            'subject' => '{{title}} - {{sitename}}',
            'content' => $this->getNotificationTemplate(),
            'category' => 'notification',
            'description' => 'Genel bildirimler için kullanılan mail template',
            'is_active' => true,
            'variables' => ['username', 'useremail', 'title', 'message', 'sitename', 'siteurl', 'currentdate', 'companyname', 'companyaddress', 'companyphone']
        ]);
    }

    private function getWelcomeTemplate(): string
    {
        return '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoş Geldiniz - {{sitename}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e9ecef; }
        .welcome-title { color: #2c3e50; font-size: 28px; font-weight: 700; margin-bottom: 10px; }
        .content { margin-bottom: 30px; }
        .greeting { font-size: 18px; color: #2c3e50; margin-bottom: 20px; }
        .cta-button { display: inline-block; background: #3498db; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: 600; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="welcome-title">Hoş Geldiniz!</h1>
        <p>{{sitename}} ailesine katıldığınız için teşekkür ederiz</p>
    </div>
    <div class="content">
        <p class="greeting">Merhaba {{username}},</p>
        <p>{{sitename}} platformuna başarıyla kayıt oldunuz! Artık yapay zeka destekli müşteri hizmetleri ve chatbot çözümlerimizden yararlanabilirsiniz.</p>
        <p>Hemen başlamak için aşağıdaki butona tıklayarak dashboard\'a giriş yapabilirsiniz:</p>
        <div style="text-align: center;">
            <a href="{{dashboardurl}}" class="cta-button">Dashboard\'a Git</a>
        </div>
        <p>Herhangi bir sorunuz olursa, destek ekibimiz size yardımcı olmaktan mutluluk duyacaktır.</p>
    </div>
    <div class="footer">
        <p>&copy; {{currentdate}} {{companyname}}. Tüm hakları saklıdır.</p>
        <p>Bu e-posta {{useremail}} adresine gönderilmiştir.</p>
    </div>
</body>
</html>';
    }

    private function getSubscriptionTemplate(): string
    {
        return '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aboneliğiniz Aktif - {{sitename}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e9ecef; }
        .title { color: #27ae60; font-size: 28px; font-weight: 700; margin-bottom: 10px; }
        .plan-details { background: #27ae60; color: white; padding: 25px; border-radius: 10px; margin: 20px 0; text-align: center; }
        .cta-button { display: inline-block; background: #27ae60; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: 600; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Aboneliğiniz Aktif!</h1>
        <p>Premium özelliklere erişiminiz başladı</p>
    </div>
    <div class="content">
        <p class="greeting">Merhaba {{username}},</p>
        <p>Tebrikler! {{sitename}} premium aboneliğiniz başarıyla aktifleştirildi. Artık platformun tüm gelişmiş özelliklerinden yararlanabilirsiniz.</p>
        <div class="plan-details">
            <div style="font-size: 24px; font-weight: 700; margin-bottom: 10px;">{{userplan}}</div>
            <div style="font-size: 16px; opacity: 0.9;">Sonraki faturalama: {{userplanexpired}}</div>
        </div>
        <p>Hemen premium özellikleri keşfetmeye başlayın:</p>
        <div style="text-align: center;">
            <a href="{{dashboardurl}}" class="cta-button">Dashboard\'a Git</a>
        </div>
    </div>
    <div class="footer">
        <p>&copy; {{currentdate}} {{companyname}}. Tüm hakları saklıdır.</p>
        <p>Bu e-posta {{useremail}} adresine gönderilmiştir.</p>
    </div>
</body>
</html>';
    }

    private function getPasswordResetTemplate(): string
    {
        return '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırlama - {{sitename}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e9ecef; }
        .title { color: #e74c3c; font-size: 28px; font-weight: 700; margin-bottom: 10px; }
        .warning-box { background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .cta-button { display: inline-block; background: #e74c3c; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: 600; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Şifre Sıfırlama</h1>
        <p>Hesabınızın güvenliği için şifrenizi sıfırlayın</p>
    </div>
    <div class="content">
        <p class="greeting">Merhaba {{username}},</p>
        <p>{{sitename}} hesabınız için şifre sıfırlama talebinde bulundunuz. Bu işlemi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz.</p>
        <div class="warning-box">
            <strong>Güvenlik Uyarısı:</strong> Şifre sıfırlama linki 60 dakika içinde geçerliliğini yitirecektir.
        </div>
        <p>Şifrenizi sıfırlamak için aşağıdaki butona tıklayın:</p>
        <div style="text-align: center;">
            <a href="{{reseturl}}" class="cta-button">Şifremi Sıfırla</a>
        </div>
    </div>
    <div class="footer">
        <p>&copy; {{currentdate}} {{companyname}}. Tüm hakları saklıdır.</p>
        <p>Bu e-posta güvenlik amaçlı gönderilmiştir.</p>
    </div>
</body>
</html>';
    }

    private function getNotificationTemplate(): string
    {
        return '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{title}} - {{sitename}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e9ecef; }
        .title { color: #2c3e50; font-size: 28px; font-weight: 700; margin-bottom: 10px; }
        .notification-box { background-color: #f8f9fa; border-left: 4px solid #3498db; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">{{title}}</h1>
        <p>{{sitename}} Bildirimi</p>
    </div>
    <div class="content">
        <p class="greeting">Merhaba {{username}},</p>
        <div class="notification-box">
            <div style="color: #2c3e50; font-size: 20px; font-weight: 600; margin-bottom: 15px;">{{title}}</div>
            <div style="color: #555; font-size: 16px; line-height: 1.8;">{{message}}</div>
        </div>
        <p>Herhangi bir sorunuz olursa, destek ekibimiz size yardımcı olmaktan mutluluk duyacaktır.</p>
    </div>
    <div class="footer">
        <p>&copy; {{currentdate}} {{companyname}}. Tüm hakları saklıdır.</p>
        <p>Bu e-posta {{useremail}} adresine gönderilmiştir.</p>
    </div>
</body>
</html>';
    }
}
