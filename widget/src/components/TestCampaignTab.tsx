import React, { useState } from 'react';

const TestCampaignTab: React.FC = () => {
  const [testMessage, setTestMessage] = useState('');

  const testMessages = [
    'kampanyalarda neler var',
    'indirim var mı',
    'fırsatları göster',
    'bedava ürün var mı',
    'ücretsiz kargo',
    'taksit imkanı'
  ];

  const handleTestMessage = (message: string) => {
    setTestMessage(message);

  };

  return (
    <div className="p-4 border rounded-lg bg-yellow-50">
      <h3 className="text-lg font-semibold mb-2">🧪 Kampanya Tab Test</h3>
      <p className="text-sm text-gray-600 mb-2">
        Bu mesajları chat'e yazarak kampanya tab'ının açılıp açılmadığını test edin:
      </p>
      
      <div className="grid grid-cols-2 gap-2 mb-3">
        {testMessages.map((msg, index) => (
          <button
            key={index}
            onClick={() => handleTestMessage(msg)}
            className="px-3 py-2 text-xs bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            {msg}
          </button>
        ))}
      </div>
      
      <div className="text-sm">
        <p><strong>Seçilen Test Mesajı:</strong></p>
        <p className="text-gray-700 bg-white p-2 rounded border">
          {testMessage || 'Henüz mesaj seçilmedi'}
        </p>
      </div>
      
      <div className="text-xs text-gray-500 mt-2">
        💡 Console'da kampanya tespit loglarını kontrol edin
      </div>
    </div>
  );
};

export default TestCampaignTab;
