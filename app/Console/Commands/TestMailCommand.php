<?php

namespace App\Console\Commands;

use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : Test mail gönderilecek e-posta adresi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mail sistemi test komutu';

    /**
     * Execute the console command.
     */
    public function handle(MailService $mailService)
    {
        $email = $this->argument('email');

        $this->info('Mail sistemi test ediliyor...');
        $this->info('Test maili gönderiliyor: ' . $email);

        try {
            // Mail bağlantısını test et
            if ($mailService->testMailConnection()) {
                $this->info('✓ Mail bağlantısı başarılı');
            } else {
                $this->error('✗ Mail bağlantısı başarısız');
                return 1;
            }

            // Test maili gönder
            Mail::raw('Bu bir test mailidir. Mail sistemi çalışıyor!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('ConvState AI - Mail Sistemi Test')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->info('✓ Test maili başarıyla gönderildi');
            $this->info('Lütfen ' . $email . ' adresini kontrol edin');

        } catch (\Exception $e) {
            $this->error('✗ Mail gönderimi başarısız: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
