@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto text-center">
        <div class="glass-effect rounded-xl p-8 border border-green-500/50">
            <div class="text-green-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-4">Ödeme Başarılı!</h1>
            <p class="text-gray-300 mb-6">Aboneliğiniz başarıyla aktifleştirildi.</p>
            <a href="{{ route('dashboard.subscription.index') }}" 
               class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                Aboneliklerime Git
            </a>
        </div>
    </div>
</div>
@endsection