<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index()
    {
        return view('payment.index');
    }

    /**
     * Plan seçimi sonrası ödeme sayfasına yönlendirme
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id'
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $user = auth()->user();

        // Mevcut abonelik kontrolü - tenant_id kullan
        $currentSubscription = Subscription::where('tenant_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($currentSubscription && $currentSubscription->plan_id == $plan->id) {
            return redirect()->route('dashboard.subscription.index')
                ->with('error', 'Bu plan zaten aktif aboneliğiniz.');
        }

        // PayTR entegrasyonu için gerekli verileri hazırla
        $paymentData = $this->preparePaymentData($plan, $user);

        return view('payment.paytr', compact('paymentData', 'plan'));
    }

    /**
     * PayTR için ödeme verilerini hazırla
     */
    private function preparePaymentData($plan, $user)
    {
        // PayTR API bilgileri - direkt .env'den al
        $merchant_id = env('PAYTR_MERCHANT_ID');
        $merchant_key = env('PAYTR_MERCHANT_KEY');
        $merchant_salt = env('PAYTR_MERCHANT_SALT');

        // Benzersiz sipariş numarası - sadece alfanumerik karakterler
        $merchant_oid = 'ORDER' . time() . 'U' . $user->id . 'P' . $plan->id;

        // Ödeme tutarı (100 ile çarpılmış)
        $payment_amount = (int)($plan->price * 100);

        // Kullanıcı IP adresi
        $user_ip = request()->ip();

        // Sepet içeriği - PayTR formatı: [ürün_adı, fiyat, adet]
        // Fiyat string olarak gönderilmeli ama number_format kullanılmamalı
        $user_basket = base64_encode(json_encode([
            [$plan->name, (string)$plan->price, 1]
        ]));

        // Hash oluştur - PayTR'ın beklediği sıralama
        // Format: merchant_id + user_ip + merchant_oid + email + payment_amount + user_basket + no_installment + max_installment + currency + test_mode
        $test_mode_value = env('PAYTR_TEST_MODE', 1);
        $hash_str = $merchant_id . $user_ip . $merchant_oid . $user->email . 
                   $payment_amount . $user_basket . '0' . '0' . 'TL' . $test_mode_value;
        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));

        return [
            'merchant_id' => $merchant_id,
            'user_ip' => $user_ip,
            'merchant_oid' => $merchant_oid,
            'email' => $user->email,
            'payment_amount' => $payment_amount,
            'paytr_token' => $paytr_token,
            'user_basket' => $user_basket,
            'user_name' => $user->name,
            'user_address' => $user->address ?? 'Adres bilgisi girilmemiş',
            'user_phone' => $user->phone ?? 'Telefon bilgisi girilmemiş',
            'merchant_ok_url' => route('payment.success'),
            'merchant_fail_url' => route('payment.fail'),
            'currency' => 'TL',
            'test_mode' => $test_mode_value, // Test modu varsayılan olarak açık (hash hesaplamasında kullanılıyor)
            'debug_on' => env('PAYTR_DEBUG_ON', 1),
            'no_installment' => 0,
            'max_installment' => 0,
            'timeout_limit' => 30
        ];
    }

    /**
     * PayTR iframe token alma
     */
    public function getPaytrToken(Request $request)
    {
        $paymentData = $request->all();
        
        // Debug için log ekle
        Log::info('PayTR Token Request Data:', $paymentData);
        
        // Hash doğrulaması yap
        $merchant_id = $paymentData['merchant_id'];
        $user_ip = $paymentData['user_ip'];
        $merchant_oid = $paymentData['merchant_oid'];
        $email = $paymentData['email'];
        $payment_amount = $paymentData['payment_amount'];
        $user_basket = $paymentData['user_basket'];
        $test_mode = $paymentData['test_mode'];
        
        // Hash string'i yeniden oluştur
        $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . 
                   $payment_amount . $user_basket . '0' . '0' . 'TL' . $test_mode;
        
        $merchant_key = env('PAYTR_MERCHANT_KEY');
        $merchant_salt = env('PAYTR_MERCHANT_SALT');
        
        $calculated_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));
        
        Log::info('Hash calculation debug:', [
            'hash_string' => $hash_str,
            'calculated_token' => $calculated_token,
            'received_token' => $paymentData['paytr_token'],
            'tokens_match' => $calculated_token === $paymentData['paytr_token']
        ]);
        
        // PayTR'ın beklediği tüm gerekli alanları kontrol et
        $requiredFields = [
            'merchant_id', 'user_ip', 'merchant_oid', 'email', 'payment_amount',
            'paytr_token', 'user_basket', 'user_name', 'user_address', 'user_phone',
            'merchant_ok_url', 'merchant_fail_url', 'currency', 'test_mode'
        ];
        
        foreach ($requiredFields as $field) {
            if (!isset($paymentData[$field]) || empty($paymentData[$field])) {
                Log::error('Missing required field: ' . $field);
                return response()->json(['error' => 'Eksik alan: ' . $field], 400);
            }
        }
        
        // PayTR'a gönderilecek veriyi hazırla
        $postData = [
            'merchant_id' => $paymentData['merchant_id'],
            'user_ip' => $paymentData['user_ip'],
            'merchant_oid' => $paymentData['merchant_oid'],
            'email' => $paymentData['email'],
            'payment_amount' => $paymentData['payment_amount'],
            'paytr_token' => $paymentData['paytr_token'],
            'user_basket' => $paymentData['user_basket'],
            'user_name' => $paymentData['user_name'],
            'user_address' => $paymentData['user_address'],
            'user_phone' => $paymentData['user_phone'],
            'merchant_ok_url' => $paymentData['merchant_ok_url'],
            'merchant_fail_url' => $paymentData['merchant_fail_url'],
            'currency' => $paymentData['currency'],
            'test_mode' => $paymentData['test_mode'],
            'debug_on' => $paymentData['debug_on'] ?? 1,
            'no_installment' => $paymentData['no_installment'] ?? 0,
            'max_installment' => $paymentData['max_installment'] ?? 0,
            'timeout_limit' => $paymentData['timeout_limit'] ?? 30
        ];
        
        // PayTR'a gönderilecek veriyi logla
        Log::info('PayTR API Request Data:', $postData);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Test için, production'da kaldırın
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
            Log::error('PayTR connection error: ' . curl_error($ch));
            return response()->json(['error' => 'PayTR bağlantı hatası'], 500);
        }
        
        curl_close($ch);
        
        // Debug için response log ekle
        Log::info('PayTR API Response:', ['response' => $result]);
        
        $result = json_decode($result, true);
        
        if ($result && isset($result['status']) && $result['status'] == 'success') {
            return response()->json(['token' => $result['token']]);
        } else {
            $errorMessage = isset($result['reason']) ? $result['reason'] : 'Bilinmeyen hata';
            Log::error('PayTR token error: ' . $errorMessage, ['response' => $result]);
            return response()->json(['error' => $errorMessage], 400);
        }
    }

    /**
     * Başarılı ödeme sonrası yönlendirme
     */
    public function success()
    {
        return view('payment.success');
    }

    /**
     * Başarısız ödeme sonrası yönlendirme
     */
    public function fail()
    {
        return view('payment.fail');
    }

    /**
     * PayTR bildirim URL'i - Ödeme sonuçlarını alır
     */
    public function notification(Request $request)
    {
        $post = $request->all();
        
        // PayTR API bilgileri - direkt .env'den al
        $merchant_key = env('PAYTR_MERCHANT_KEY');
        $merchant_salt = env('PAYTR_MERCHANT_SALT');
        
        // Hash doğrulama
        $hash = base64_encode(hash_hmac('sha256', 
            $post['merchant_oid'] . $merchant_salt . $post['status'] . $post['total_amount'], 
            $merchant_key, true));
        
        if ($hash != $post['hash']) {
            Log::error('PayTR notification failed: bad hash');
            return response('PAYTR notification failed: bad hash', 400);
        }
        
        // Sipariş numarasından plan ve kullanıcı bilgilerini çıkar
        // Format: ORDER{timestamp}U{userId}P{planId}
        $merchantOid = $post['merchant_oid'];
        preg_match('/ORDER(\d+)U(\d+)P(\d+)/', $merchantOid, $matches);
        
        if (count($matches) !== 4) {
            Log::error('Invalid merchant_oid format', ['merchant_oid' => $merchantOid]);
            return response('Invalid merchant_oid format', 400);
        }
        
        $userId = $matches[2];
        $planId = $matches[3];
        
        if ($post['status'] == 'success') {
            // Ödeme başarılı - Aboneliği aktifleştir
            $this->activateSubscription($userId, $planId, $post);
        } else {
            // Ödeme başarısız - Log kaydı
            Log::info('Payment failed', [
                'merchant_oid' => $post['merchant_oid'],
                'failed_reason' => $post['failed_reason_msg'] ?? 'Bilinmeyen hata'
            ]);
        }
        
        // PayTR'a OK yanıtı
        return response('OK');
    }
    
    /**
     * Aboneliği aktifleştir
     */
    private function activateSubscription($userId, $planId, $paymentData)
    {
        $plan = Plan::find($planId);
        $user = \App\Models\User::find($userId);
        
        if (!$plan || !$user) {
            Log::error('Plan or user not found', ['user_id' => $userId, 'plan_id' => $planId]);
            return;
        }
        
        // Mevcut aktif aboneliği pasifleştir - tenant_id kullan
        Subscription::where('tenant_id', $userId)
            ->where('status', 'active')
            ->update(['status' => 'inactive']);
        
        // Yeni abonelik oluştur - tenant_id kullan
        Subscription::create([
            'tenant_id' => $userId,
            'plan_id' => $planId,
            'start_date' => now(),
            'end_date' => $plan->name === 'Freemium' 
                ? now()->addWeek() 
                : now()->addMonth(),
            'status' => 'active'
        ]);
        
        Log::info('Subscription activated', [
            'user_id' => $userId,
            'plan_id' => $planId,
            'amount' => $paymentData['total_amount']
        ]);
    }
}