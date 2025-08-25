import React, { useState } from 'react';
import { WheelSpinTemplateProps, Prize } from '../../types';

const WheelSpinTemplate: React.FC<WheelSpinTemplateProps> = ({ prizes, onSpin, isSpinning }) => {
  const [rotation, setRotation] = useState(0);
  const [selectedPrize, setSelectedPrize] = useState<Prize | null>(null);

  const handleSpin = () => {
    if (isSpinning) return;
    
    // Random rotation between 720 and 1440 degrees (2-4 full spins)
    const newRotation = rotation + 720 + Math.random() * 720;
    setRotation(newRotation);
    
    // Call the parent spin handler
    onSpin();
    
    // Simulate prize selection after spin
    setTimeout(() => {
      const randomPrize = prizes[Math.floor(Math.random() * prizes.length)];
      setSelectedPrize(randomPrize);
    }, 3000);
  };

  const wheelStyle = {
    transform: `rotate(${rotation}deg)`,
    transition: isSpinning ? 'transform 3s cubic-bezier(0.25, 0.46, 0.45, 0.94)' : 'none'
  };

  return (
    <div className="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg border border-purple-200 p-6 text-center">
      {/* Header */}
      <div className="mb-6">
        <div className="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg className="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 className="text-xl font-bold text-gray-900 mb-2">
          🎯 Şans Çarkı
        </h3>
        <p className="text-sm text-gray-600">
          Çarkı çevir ve harika ödüller kazan!
        </p>
      </div>

      {/* Prize Wheel */}
      <div className="relative mb-6">
        <div 
          className="w-48 h-48 mx-auto relative"
          style={wheelStyle}
        >
          {/* Wheel Segments */}
          {prizes.map((prize, index) => {
            const angle = (360 / prizes.length) * index;
            const segmentStyle = {
              transform: `rotate(${angle}deg)`,
              transformOrigin: '50% 50%'
            };
            
            return (
              <div
                key={prize.id}
                className="absolute w-24 h-24 origin-bottom-right"
                style={segmentStyle}
              >
                <div className="w-full h-full bg-gradient-to-br from-purple-400 to-pink-400 rounded-full border-4 border-white flex items-center justify-center text-white text-xs font-bold">
                  {prize.name}
                </div>
              </div>
            );
          })}
        </div>
        
        {/* Center Pointer */}
        <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
          <div className="w-0 h-0 border-l-[12px] border-r-[12px] border-b-[24px] border-l-transparent border-r-transparent border-b-purple-600"></div>
        </div>
      </div>

      {/* Spin Button */}
      <button
        onClick={handleSpin}
        disabled={isSpinning}
        className={`w-full py-3 px-6 rounded-lg font-bold text-lg transition-all ${
          isSpinning
            ? 'bg-gray-400 cursor-not-allowed'
            : 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-lg hover:shadow-xl'
        }`}
      >
        {isSpinning ? '🔄 Çevriliyor...' : '🎰 Çarkı Çevir!'}
      </button>

      {/* Selected Prize Display */}
      {selectedPrize && (
        <div className="mt-6 p-4 bg-green-100 border border-green-200 rounded-lg">
          <p className="text-sm text-green-800 mb-2">
            🎉 Tebrikler! Kazandığınız ödül:
          </p>
          <div className="bg-white border border-green-300 rounded px-4 py-2 inline-block">
            <span className="text-lg font-bold text-green-700">
              {selectedPrize.name}
            </span>
          </div>
          <p className="text-xs text-green-600 mt-2">
            Bu ödülü kullanmak için kupon kodunuzu kaydedin
          </p>
        </div>
      )}

      {/* Prize List */}
      <div className="mt-6">
        <h4 className="text-sm font-semibold text-gray-700 mb-3">
          Mevcut Ödüller:
        </h4>
        <div className="grid grid-cols-2 gap-2">
          {prizes.map((prize) => (
            <div
              key={prize.id}
              className="bg-white border border-gray-200 rounded-lg p-2 text-center"
            >
              <p className="text-xs font-medium text-gray-900">
                {prize.name}
              </p>
              <p className="text-xs text-gray-500">
                {prize.value}
              </p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default WheelSpinTemplate;
