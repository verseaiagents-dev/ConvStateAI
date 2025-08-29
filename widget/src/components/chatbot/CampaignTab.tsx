import React, { useState, useEffect } from 'react';
import './chatbot.css';

interface CampaignTabProps {
  isVisible: boolean;
  onClose: () => void;
}

interface Campaign {
  id: number;
  title: string;
  description: string;
  category: string;
  discount: string;
  valid_until: string;
  start_date: string;
  end_date: string;
  discount_type: string;
  discount_value: number;
  minimum_order_amount: number;
  is_active: boolean;
  image_url?: string;
}

const CampaignTab: React.FC<CampaignTabProps> = ({ isVisible, onClose }) => {
  const [campaigns, setCampaigns] = useState<Campaign[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (isVisible) {
      loadCampaigns();
    }
  }, [isVisible]);

  const loadCampaigns = async () => {
    try {
      setLoading(true);
      setError(null);
      
      // API endpoint'i - production'da gerçek URL kullanın
      const apiUrl = process.env.REACT_APP_API_URL || 'http://localhost:8000';
      
      const response = await fetch(`${apiUrl}/api/campaigns?site_id=1`, {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });
      
      if (!response.ok) {
        if (response.status === 404) {
          // Site bulunamadı veya kampanya yok
          setCampaigns([]);
          setError(null);
          return;
        }
        const errorText = await response.text();
        console.error('Error response text:', errorText);
        throw new Error(`Kampanyalar yüklenirken hata oluştu: ${response.status}`);
      }
      
      const responseText = await response.text();
      
      let result;
      try {
        result = JSON.parse(responseText);
      } catch (parseError) {
        console.error('JSON parse error:', parseError);
        throw new Error('API response JSON formatında değil');
      }
      
      if (result.success) {
        setCampaigns(result.data || []);
      } else {
        setError(result.message || 'Kampanyalar yüklenemedi');
      }
    } catch (err) {
      console.error('Campaign loading error:', err);
      setError(err instanceof Error ? err.message : 'Bilinmeyen hata oluştu');
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Sürekli';
    
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString('tr-TR');
    } catch {
      return 'Geçersiz tarih';
    }
  };

  const getDiscountDisplay = (campaign: Campaign) => {
    switch (campaign.discount_type) {
      case 'percentage':
        return `%${campaign.discount_value} İndirim`;
      case 'fixed':
        return `${campaign.discount_value} TL İndirim`;
      case 'free_shipping':
        return 'Ücretsiz Kargo';
      case 'buy_x_get_y':
      default:
        return campaign.discount;
    }
  };

  if (loading) {
    return (
      <div className="campaign-tab-content">
        <div className="campaign-content">
          <div className="campaign-item shimmer">
            <div className="campaign-item-header">
              <div className="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
              <div className="h-3 bg-gray-200 rounded w-1/4"></div>
            </div>
            <div className="h-3 bg-gray-200 rounded w-full mb-2"></div>
            <div className="h-3 bg-gray-200 rounded w-2/3"></div>
          </div>
          <div className="campaign-item shimmer">
            <div className="campaign-item-header">
              <div className="h-4 bg-gray-200 rounded w-2/3 mb-2"></div>
              <div className="h-3 bg-gray-200 rounded w-1/3"></div>
            </div>
            <div className="h-3 bg-gray-200 rounded w-full mb-2"></div>
            <div className="h-3 bg-gray-200 rounded w-3/4"></div>
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="campaign-tab-content">
        {/* Campaign List with Error Item */}
        <div className="campaign-content">
          <div className="campaign-item error-state">
            <div className="campaign-item-header">
              <h4 className="text-red-600">⚠️ Hata Oluştu</h4>
              <span className="campaign-category error-category">Sistem Hatası</span>
            </div>
            <p className="campaign-description text-red-500">{error}</p>
            <div className="campaign-item-footer">
              <span className="campaign-discount error-discount">Yüklenemedi</span>
              <span className="campaign-validity">Şimdi</span>
            </div>
            
            {/* Retry Button */}
            <div className="campaign-details">
              <button 
                onClick={loadCampaigns}
                className="campaign-retry-btn"
              >
                🔄 Tekrar Dene
              </button>
            </div>
          </div>
        </div>

        {/* Footer */}
        <div className="campaign-footer">
          <button className="campaign-action-btn" onClick={onClose}>
            💬 Chat'e Dön
          </button>
        </div>
      </div>
    );
  }

  // Debug: campaigns state'ini kontrol et
  console.log('Campaigns state:', campaigns);
  console.log('Campaigns length:', campaigns?.length);
  console.log('Campaigns type:', typeof campaigns);
  
  if (!campaigns || campaigns.length === 0) {
    return (
      <div className="campaign-tab-content">
        <div className="campaign-content">
          <div className="campaign-item empty-state">
            <div className="text-center py-8">
              <div className="text-gray-400 text-6xl mb-4">📢</div>
              <h3 className="text-lg font-medium text-gray-900 mb-2">Henüz Kampanya Yok</h3>
              <p className="text-gray-500">Şu anda aktif kampanya bulunmuyor. Yeni kampanyalar eklendiğinde burada görünecek.</p>
            </div>
          </div>
        </div>
        
        {/* Footer */}
        <div className="campaign-footer">
          <button className="campaign-action-btn" onClick={onClose}>
            💬 Chat'e Dön
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="campaign-tab-content">
      {/* Campaign List */}
      <div className="campaign-content">
        {campaigns.map((campaign) => (
          <div key={campaign.id} className="campaign-item">
            <div className="campaign-item-header">
              <h4>{campaign.title}</h4>
              <span className="campaign-category">{campaign.category}</span>
            </div>
            <p className="campaign-description">{campaign.description}</p>
            <div className="campaign-item-footer">
              <span className="campaign-discount">{getDiscountDisplay(campaign)}</span>
              <span className="campaign-validity">
                Geçerli: {formatDate(campaign.end_date)}
              </span>
            </div>
            
            {/* Additional campaign details */}
            {campaign.minimum_order_amount && (
              <div className="campaign-details">
                <small className="text-gray-500">
                  Min. Sipariş: {campaign.minimum_order_amount} TL
                </small>
              </div>
            )}
          </div>
        ))}
      </div>

      {/* Footer */}
      <div className="campaign-footer">
        <button className="campaign-action-btn" onClick={onClose}>
          💬 Chat'e Dön
        </button>
      </div>
    </div>
  );
};

export default CampaignTab;
