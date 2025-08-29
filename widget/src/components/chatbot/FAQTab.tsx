import React, { useState, useEffect } from 'react';
import './chatbot.css';

interface FAQTabProps {
  isVisible: boolean;
  onClose: () => void;
}

interface FAQItem {
  id: number;
  title: string;
  description: string;
  category: string;
  answer: string;
  short_answer: string;
  is_active: boolean;
  sort_order: number;
  tags?: string[];
  view_count: number;
  helpful_count: number;
  not_helpful_count: number;
}

const FAQTab: React.FC<FAQTabProps> = ({ isVisible, onClose }) => {
  const [openFAQ, setOpenFAQ] = useState<number | null>(null);
  const [faqs, setFaqs] = useState<FAQItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (isVisible) {
      loadFAQs();
    }
  }, [isVisible]);

  const loadFAQs = async () => {
    try {
      setLoading(true);
      setError(null);
      
      // API endpoint'i - production'da gerçek URL kullanın
      const apiUrl = process.env.REACT_APP_API_URL || 'http://localhost:8000';
      
      const response = await fetch(`${apiUrl}/api/faqs?site_id=1`, {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });
      
      if (!response.ok) {
        if (response.status === 404) {
          // Site bulunamadı veya FAQ yok
          setFaqs([]);
          setError(null);
          return;
        }
        const errorText = await response.text();
        console.error('Error response text:', errorText);
        throw new Error(`SSS yüklenirken hata oluştu: ${response.status}`);
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
        setFaqs(result.data || []);
      } else {
        setError(result.message || 'SSS yüklenemedi');
      }
    } catch (err) {
      console.error('FAQ loading error:', err);
      setError(err instanceof Error ? err.message : 'Bilinmeyen hata oluştu');
    } finally {
      setLoading(false);
    }
  };

  const toggleFAQ = (id: number) => {
    setOpenFAQ(openFAQ === id ? null : id);
  };

  const markAsHelpful = async (id: number) => {
    try {
      const apiUrl = process.env.REACT_APP_API_URL || 'http://localhost:8000';
      await fetch(`${apiUrl}/api/faqs/${id}/helpful`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        }
      });
      
      // Update local state
      setFaqs(prevFaqs => 
        prevFaqs.map(faq => 
          faq.id === id 
            ? { ...faq, helpful_count: faq.helpful_count + 1 }
            : faq
        )
      );
          } catch (error) {
        // Helpful mark failed
      }
  };

  const markAsNotHelpful = async (id: number) => {
    try {
      const apiUrl = process.env.REACT_APP_API_URL || 'http://localhost:8000';
      await fetch(`${apiUrl}/api/faqs/${id}/not-helpful`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        }
      });
      
      // Update local state
      setFaqs(prevFaqs => 
        prevFaqs.map(faq => 
          faq.id === id 
            ? { ...faq, not_helpful_count: faq.not_helpful_count + 1 }
            : faq
        )
      );
          } catch (error) {
        // Not helpful mark failed
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
        {/* FAQ List with Error Item */}
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
                onClick={loadFAQs}
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

  // Debug: faqs state'ini kontrol et
  console.log('FAQs state:', faqs);
  console.log('FAQs length:', faqs?.length);
  console.log('FAQs type:', typeof faqs);
  
  if (!faqs || faqs.length === 0) {
    return (
      <div className="campaign-tab-content">
        <div className="campaign-content">
          <div className="campaign-item empty-state">
            <div className="text-center py-8">
              <div className="text-gray-400 text-6xl mb-4">❓</div>
              <h3 className="text-lg font-medium text-gray-900 mb-2">Henüz SSS Yok</h3>
              <p className="text-gray-500">Şu anda sık sorulan soru bulunmuyor. Yeni SSS'ler eklendiğinde burada görünecek.</p>
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
      {/* FAQ List - Using Campaign Style */}
      <div className="campaign-content">
        {faqs.map((item) => (
          <div 
            key={item.id} 
            className="campaign-item"
            onClick={() => toggleFAQ(item.id)}
          >
            <div className="campaign-item-header">
              <h4>{item.title}</h4>
              <span className="campaign-category">{item.category}</span>
            </div>
            <p className="campaign-description">{item.description}</p>
            <div className="campaign-item-footer">
              <span className="campaign-discount">{item.short_answer}</span>
              <span className="campaign-validity">
                {openFAQ === item.id ? 'Detaylar Açık' : 'Detaylar için tıklayın'}
              </span>
            </div>
            
            {/* FAQ Answer - Expandable */}
            {openFAQ === item.id && (
              <div className="faq-answer-expanded">
                <div className="faq-answer-content">
                  <p>{item.answer}</p>
                  
                  {/* Helpful buttons */}
                  <div className="faq-feedback mt-4 pt-4 border-t border-gray-200">
                    <div className="flex items-center justify-between">
                      <div className="text-sm text-gray-500">
                        Bu bilgi faydalı mıydı?
                      </div>
                      <div className="flex space-x-2">
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            markAsHelpful(item.id);
                          }}
                          className="flex items-center px-3 py-1 text-sm text-green-600 hover:text-green-700 border border-green-300 rounded-md hover:bg-green-50"
                        >
                          👍 Faydalı ({item.helpful_count})
                        </button>
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            markAsNotHelpful(item.id);
                          }}
                          className="flex items-center px-3 py-1 text-sm text-red-600 hover:text-red-700 border border-red-300 rounded-md hover:bg-red-50"
                        >
                          👎 Faydalı Değil ({item.not_helpful_count})
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
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

export default FAQTab;
