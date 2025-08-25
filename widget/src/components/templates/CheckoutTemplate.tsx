import React from 'react';
import { CheckoutTemplateProps, Product } from '../../types';

const CheckoutTemplate: React.FC<CheckoutTemplateProps> = ({ 
  abandonedProducts, 
  discountCode, 
  onContinueCheckout, 
  onViewCart 
}) => {
  return (
    <div className="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 p-4">
      {/* Header */}
      <div className="text-center mb-4">
        <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
        </div>
        <h3 className="text-lg font-semibold text-gray-900 mb-2">
          Sepetinizde Ürünler Bekliyor!
        </h3>
        <p className="text-sm text-gray-600">
          Sepetinizde {abandonedProducts.length} ürün var. Özel indirim fırsatını kaçırmayın!
        </p>
      </div>

      {/* Discount Code */}
      {discountCode && (
        <div className="bg-green-100 border border-green-200 rounded-lg p-3 mb-4 text-center">
          <p className="text-sm text-green-800 mb-2">
            🎉 Özel İndirim Kodu
          </p>
          <div className="bg-white border border-green-300 rounded px-3 py-2 inline-block">
            <code className="text-lg font-mono font-bold text-green-700">
              {discountCode}
            </code>
          </div>
          <p className="text-xs text-green-600 mt-1">
            Bu kodu kullanarak %20 indirim kazanın!
          </p>
        </div>
      )}

      {/* Abandoned Products */}
      <div className="space-y-3 mb-4">
        {abandonedProducts.slice(0, 3).map((product) => (
          <AbandonedProductCard key={product.id} product={product} />
        ))}
      </div>

      {/* Action Buttons */}
      <div className="space-y-3">
        <button
          onClick={onContinueCheckout}
          className="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium"
        >
          🛒 Sepete Devam Et
        </button>
        <button
          onClick={onViewCart}
          className="w-full bg-white text-blue-600 border border-blue-300 py-2 px-4 rounded-lg hover:bg-blue-50 transition-colors font-medium"
        >
          Sepeti Görüntüle
        </button>
      </div>

      {/* Additional Info */}
      <div className="mt-4 text-center">
        <p className="text-xs text-gray-500">
          Bu ürünler 24 saat sonra sepetinizden kaldırılacak
        </p>
      </div>
    </div>
  );
};

// Abandoned Product Card Component
interface AbandonedProductCardProps {
  product: Product;
}

const AbandonedProductCard: React.FC<AbandonedProductCardProps> = ({ product }) => {
  return (
    <div className="bg-white rounded-lg border border-gray-200 p-3 flex items-center gap-3">
      {/* Product Image */}
      <div className="w-16 h-16 bg-gray-100 rounded-md flex-shrink-0">
        {product.image ? (
          <img
            src={product.image}
            alt={product.name}
            className="w-full h-full object-cover rounded-md"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center">
            <div className="text-gray-400 text-xs text-center">Resim</div>
          </div>
        )}
      </div>

      {/* Product Info */}
      <div className="flex-1 min-w-0">
        <h4 className="font-medium text-sm text-gray-900 truncate">
          {product.name}
        </h4>
        <p className="text-xs text-gray-500">{product.brand}</p>
        <p className="text-sm font-semibold text-green-600">
          {product.price} TL
        </p>
      </div>

      {/* Status */}
      <div className="flex-shrink-0">
        <div className="w-3 h-3 bg-green-500 rounded-full"></div>
      </div>
    </div>
  );
};

export default CheckoutTemplate;
