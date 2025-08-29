<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Subscription sayfasını göster
     */
    public function index()
    {
        $user = Auth::user();
        $plans = Plan::where('is_active', true)->orderBy('price', 'asc')->get();
        $currentSubscription = $user->activeSubscription;
        
        return view('dashboard.subscription.index', compact('plans', 'currentSubscription', 'user'));
    }

    /**
     * Plan seçimi ve subscription oluşturma
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id'
        ]);

        $user = Auth::user();
        $plan = Plan::findOrFail($request->plan_id);

        // Mevcut subscription'ı iptal et
        if ($user->activeSubscription) {
            $user->activeSubscription->update(['status' => 'cancelled']);
        }

        // Yeni subscription oluştur
        $startDate = now();
        $endDate = $startDate->copy();

        // Plan türüne göre süre belirle
        if ($plan->name === 'Freemium') {
            $endDate->addDays(7); // 1 hafta
        } elseif ($plan->billing_cycle === 'monthly') {
            $endDate->addMonth();
        } else {
            $endDate->addYear();
        }

        // Freemium plan için trial süresi
        $trialEndsAt = null;
        if ($plan->name === 'Freemium') {
            $trialEndsAt = $endDate;
        }

        Subscription::create([
            'tenant_id' => $user->id,
            'plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'trial_ends_at' => $trialEndsAt
        ]);

        return redirect()->route('dashboard.subscription.index')
            ->with('success', 'Plan başarıyla seçildi! Artık tüm özellikleri kullanabilirsiniz.');
    }

    /**
     * Subscription'ı iptal et
     */
    public function cancel()
    {
        $user = Auth::user();
        
        if ($user->activeSubscription) {
            $user->activeSubscription->update(['status' => 'cancelled']);
            
            return redirect()->route('dashboard.subscription.index')
                ->with('success', 'Subscription başarıyla iptal edildi.');
        }

        return redirect()->route('dashboard.subscription.index')
            ->with('error', 'Aktif subscription bulunamadı.');
    }

    /**
     * Subscription'ı yenile
     */
    public function renew()
    {
        $user = Auth::user();
        
        if (!$user->activeSubscription) {
            return redirect()->route('dashboard.subscription.index')
                ->with('error', 'Yenilenecek subscription bulunamadı.');
        }

        $subscription = $user->activeSubscription;
        $plan = $subscription->plan;

        // Yeni bitiş tarihi hesapla
        $newEndDate = $subscription->end_date->copy();
        
        if ($plan->name === 'Freemium') {
            $newEndDate->addDays(7);
        } elseif ($plan->billing_cycle === 'monthly') {
            $newEndDate->addMonth();
        } else {
            $newEndDate->addYear();
        }

        $subscription->update([
            'end_date' => $newEndDate,
            'status' => 'active'
        ]);

        return redirect()->route('dashboard.subscription.index')
            ->with('success', 'Subscription başarıyla yenilendi.');
    }

    /**
     * Expired subscription sayfasını göster
     */
    public function expired()
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()->latest()->first();
        
        return view('subscription.expired', compact('subscription'));
    }
}
