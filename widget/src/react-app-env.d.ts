/// <reference types="react-scripts" />

// Fast Refresh için gerekli type tanımları
declare module 'react-refresh/runtime' {
  export function performReactRefresh(): void;
}

// Environment variables için type tanımları
declare namespace NodeJS {
  interface ProcessEnv {
    NODE_ENV: 'development' | 'production' | 'test';
    FAST_REFRESH: 'true' | 'false';
    CHOKIDAR_USEPOLLING: 'true' | 'false';
    WATCHPACK_POLLING: 'true' | 'false';
    PORT?: string;
  }
}
