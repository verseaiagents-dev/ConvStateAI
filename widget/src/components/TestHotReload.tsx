import React, { useState } from 'react';

const TestHotReload: React.FC = () => {
  const [count, setCount] = useState(0);
  const [timestamp] = useState(new Date().toLocaleTimeString());

  return (
    <div className="p-4 border rounded-lg bg-purple-50">
      <h3 className="text-lg font-semibold mb-2">🚀 Hot Reload Test Component - WORKING!</h3>
      <p className="text-sm text-gray-600 mb-2">
        Component created at: {timestamp}
      </p>
      <div className="flex items-center gap-4">
        <button
          onClick={() => setCount(count + 1)}
          className="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600"
        >
          Count: {count}
        </button>
        <span className="text-sm text-gray-500">
          Click to test state preservation during hot reload
        </span>
      </div>
      <p className="text-xs text-gray-400 mt-2">
        🎉 Hot Reload çalışıyor! Bu component mor tema ile güncellendi.
        Sayfa yenilenmeden değişiklikler otomatik olarak yansıyor!
      </p>
    </div>
  );
};

export default TestHotReload;
