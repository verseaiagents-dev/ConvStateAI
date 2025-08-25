import React, { useState } from 'react';
import { Message, FeedbackData } from '../../../types';

interface OrderMessageProps {
  message: Message;
  onFeedback: (feedback: FeedbackData) => void;
}

interface OrderItem {
  product_id: number;
  name: string;
  quantity: number;
  price: number;
}

interface ShippingInfo {
  courier: string;
  tracking_number: string;
  last_update: string;
  location: string;
  estimated_delivery: string;
}

interface OrderTrackingData {
  order_id: string;
  status: string;
  order_date: string;
  items: OrderItem[];
  shipping: ShippingInfo;
  message: string;
}

interface CargoTrackingData {
  intent: string;
  phase: string;
  order_id: string;
  status: string;
  courier: string;
  tracking_number: string;
  last_update: string;
  estimated_delivery: string;
}

const OrderMessage: React.FC<OrderMessageProps> = ({ message, onFeedback }) => {
  const [orderNumber, setOrderNumber] = useState('');
  const [showOrderDetails, setShowOrderDetails] = useState(false);
  const [showCargoTracking, setShowCargoTracking] = useState(false);
  const [orderData, setOrderData] = useState<OrderTrackingData | null>(null);
  const [cargoData, setCargoData] = useState<CargoTrackingData | null>(null);
  
  const handleFeedback = (isHelpful: boolean) => {
    onFeedback({
      messageId: message.id,
      isHelpful,
      comment: ''
    });
  };

  const handleOrderNumberSubmit = async () => {
    if (orderNumber.trim()) {
      try {
        // Call the new order tracking API endpoint
        const response = await fetch('/api/order-tracking', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            order_number: orderNumber
          })
        });

        if (response.ok) {
          const result = await response.json();
          if (result.success && result.data) {
            // Set the cargo tracking data in the new simplified format
            setCargoData(result.data);
            setShowCargoTracking(true);
          }
        } else {
          // Fallback to mock data if API fails
          const mockCargoData: CargoTrackingData = {
            intent: "order_tracking",
            phase: "cargo",
            order_id: "ORD-998877",
            status: "in_transit",
            courier: "Yurtiçi Kargo",
            tracking_number: "YT123456789TR",
            last_update: "2025-08-18T14:30:00Z",
            estimated_delivery: "2025-08-20"
          };
          
          setCargoData(mockCargoData);
          setShowCargoTracking(true);
        }
      } catch (error) {
        // Fallback to mock data on error
        const mockCargoData: CargoTrackingData = {
          intent: "order_tracking",
          phase: "cargo",
          order_id: "ORD-998877",
          status: "in_transit",
          courier: "Yurtiçi Kargo",
          tracking_number: "YT123456789TR",
          last_update: "2025-08-18T14:30:00Z",
          estimated_delivery: "2025-08-20"
        };
        
        setCargoData(mockCargoData);
        setShowCargoTracking(true);
      }
    }
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR');
  };

  const formatDateTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('tr-TR');
  };

  // If this is an order tracking request, show input field
  if (message.intent === 'order_tracking' && !showOrderDetails && !showCargoTracking) {
    return (
      <div className="message agent-message">
        <div className="message-content-wrapper">
          <p>Kargo takip numaranızı veya sipariş numaranızı girin:</p>
          
          <div className="order-input-container">
            <input
              type="text"
              placeholder="Sipariş/Kargo numarası girin..."
              value={orderNumber}
              onChange={(e) => setOrderNumber(e.target.value)}
              className="order-number-input"
            />
            <button 
              onClick={handleOrderNumberSubmit}
              className="track-order-button"
              disabled={!orderNumber.trim()}
            >
              Takip Et
            </button>
          </div>
        </div>
        
        <div className="feedback-container">
          <button 
            className="feedback-button"
            onClick={() => handleFeedback(true)}
          >
            👍 Yararlı
          </button>
          <button 
            className="feedback-button negative"
            onClick={() => handleFeedback(false)}
          >
            👎 Yararsız
          </button>
        </div>
      </div>
    );
  }

  // Show simplified cargo tracking if available
  if (showCargoTracking && cargoData) {
    const statusText = cargoData.status === 'in_transit' ? 'Yolda' : 
                      cargoData.status === 'delivered' ? 'Teslim Edildi' : 
                      cargoData.status === 'pending' ? 'Hazırlanıyor' : cargoData.status;

    return (
      <div className="message agent-message">
        <div className="message-content-wrapper">
          <div className="cargo-tracking-simple">
            <h3>Kargo Takip Bilgileri</h3>
            
            <div className="cargo-info-grid">
              <div className="cargo-info-item">
                <strong>Sipariş No:</strong>
                <span>{cargoData.order_id}</span>
              </div>
              
              <div className="cargo-info-item">
                <strong>Durum:</strong>
                <span className={`status-badge ${cargoData.status}`}>{statusText}</span>
              </div>
              
              <div className="cargo-info-item">
                <strong>Kargo Firması:</strong>
                <span>{cargoData.courier}</span>
              </div>
              
              <div className="cargo-info-item">
                <strong>Takip Numarası:</strong>
                <span className="tracking-number">{cargoData.tracking_number}</span>
              </div>
              
              <div className="cargo-info-item">
                <strong>Son Güncelleme:</strong>
                <span>{formatDateTime(cargoData.last_update)}</span>
              </div>
              
              <div className="cargo-info-item">
                <strong>Tahmini Teslim:</strong>
                <span>{formatDate(cargoData.estimated_delivery)}</span>
              </div>
            </div>
          </div>
        </div>
        
        <div className="feedback-container">
          <button 
            className="feedback-button"
            onClick={() => handleFeedback(true)}
          >
            👍 Yararlı
          </button>
          <button 
            className="feedback-button negative"
            onClick={() => handleFeedback(false)}
          >
            👎 Yararsız
          </button>
        </div>
      </div>
    );
  }

  // Show order details if available
  if (showOrderDetails && orderData) {
    return (
      <div className="message agent-message">
        <div className="message-content-wrapper">
          <div className="order-tracking-details">
            <h3>Sipariş Takip Bilgileri</h3>
            
            <div className="order-header">
              <div className="order-id">Sipariş No: {orderData.order_id}</div>
              <div className="order-status">Durum: {orderData.status === 'shipped' ? 'Kargoya Verildi' : orderData.status}</div>
              <div className="order-date">Sipariş Tarihi: {formatDate(orderData.order_date)}</div>
            </div>

            <div className="order-items">
              <h4>Sipariş Edilen Ürünler:</h4>
              {orderData.items.map((item, index) => (
                <div key={index} className="order-item">
                  <span className="item-name">{item.name}</span>
                  <span className="item-quantity">x{item.quantity}</span>
                  <span className="item-price">₺{item.price}</span>
                </div>
              ))}
            </div>

            <div className="shipping-info">
              <h4>Kargo Bilgileri:</h4>
              <div className="shipping-detail">
                <strong>Kargo Firması:</strong> {orderData.shipping.courier}
              </div>
              <div className="shipping-detail">
                <strong>Takip Numarası:</strong> {orderData.shipping.tracking_number}
              </div>
              <div className="shipping-detail">
                <strong>Son Güncelleme:</strong> {formatDateTime(orderData.shipping.last_update)}
              </div>
              <div className="shipping-detail">
                <strong>Konum:</strong> {orderData.shipping.location}
              </div>
              <div className="shipping-detail">
                <strong>Tahmini Teslim:</strong> {formatDate(orderData.shipping.estimated_delivery)}
              </div>
            </div>

            <div className="order-message">
              <p>{orderData.message}</p>
            </div>
          </div>
        </div>
        
        <div className="feedback-container">
          <button 
            className="feedback-button"
            onClick={() => handleFeedback(true)}
          >
            👍 Yararlı
          </button>
          <button 
            className="feedback-button negative"
            onClick={() => handleFeedback(false)}
          >
            👎 Yararsız
          </button>
        </div>
      </div>
    );
  }

  // Default order inquiry display
  return (
    <div className="message agent-message">
      <div className="message-content-wrapper">
        <p>{message.content || message.message || 'Sipariş mesajı bulunamadı.'}</p>
        
        <div className="action-buttons">
          <div className="action-buttons-scroll-container">
            <div className="action-buttons-wrapper">
              {message.suggestions && message.suggestions.map((suggestion, index) => (
                <button key={index} className="secondary-button">
                  {suggestion}
                </button>
              ))}
            </div>
          </div>
        </div>
      </div>
      
      <div className="feedback-container">
        <button 
          className="feedback-button"
          onClick={() => handleFeedback(true)}
        >
          👍 Yararlı
        </button>
        <button 
          className="feedback-button negative"
          onClick={() => handleFeedback(false)}
        >
          👎 Yararsız
        </button>
      </div>
    </div>
  );
};

export default OrderMessage;
