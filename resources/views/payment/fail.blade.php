@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto text-center">
        <div class="glass-effect rounded-xl p-8 border border-red-500/50">
            <div class="text-red-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-4">Ödeme Başarısız</h1>
            <p class="text-gray-300 mb-6">Ödeme işlemi sırasında bir hata oluştu. Lütfen tekrar deneyin.</p>
            <a href="{{ route('dashboard.subscription.index') }}" 
               class="mt-4 inline-block px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                Tekrar Dene
            </a>
        </div>
    </div>
</div>
@endsection