@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-4">{{ $plan->name }} Planı</h1>
            <p class="text-xl text-gray-300">Ödeme Tutarı: ${{ number_format($plan->price, 2) }}</p>
        </div>

        <div class="glass-effect rounded-xl p-6 border border-gray-700">
            <div id="payment-form">
                <div class="text-center mb-6">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500 mx-auto"></div>
                    <p class="text-gray-400 mt-4">Ödeme formu yükleniyor...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // PayTR token al
    fetch('{{ route("payment.get-token") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(@json($paymentData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            // PayTR iframe'i yükle
            const paymentForm = document.getElementById('payment-form');
            paymentForm.innerHTML = `
                <iframe src="https://www.paytr.com/odeme/guvenli/${data.token}" 
                        id="paytriframe" 
                        frameborder="0" 
                        scrolling="no" 
                        style="width: 100%; height: 600px;">
                </iframe>
            `;
            
            // iframe boyutlandırma
            iFrameResize({}, '#paytriframe');
        } else {
            document.getElementById('payment-form').innerHTML = `
                <div class="text-center text-red-500">
                    <p>Ödeme formu yüklenemedi: ${data.error}</p>
                    <a href="{{ route('dashboard.subscription.index') }}" 
                       class="mt-4 inline-block px-6 py-2 bg-purple-600 text-white rounded-lg">
                        Geri Dön
                    </a>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('payment-form').innerHTML = `
            <div class="text-center text-red-500">
                <p>Bir hata oluştu. Lütfen tekrar deneyin.</p>
                <a href="{{ route('dashboard.subscription.index') }}" 
                   class="mt-4 inline-block px-6 py-2 bg-purple-600 text-white rounded-lg">
                    Geri Dön
                </a>
            </div>
        `;
    });
});
</script>
@endsection