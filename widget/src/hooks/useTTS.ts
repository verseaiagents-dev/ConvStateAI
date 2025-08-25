import { useState, useRef, useCallback, useMemo } from 'react';

interface TTSOptions {
  lang?: string;
  rate?: number;
  pitch?: number;
  volume?: number;
}

export const useTTS = (options: TTSOptions = {}) => {
  const [isPlaying, setIsPlaying] = useState(false);
  const [currentText, setCurrentText] = useState('');
  const currentSpeechRef = useRef<SpeechSynthesisUtterance | null>(null);

  const defaultOptions = useMemo(() => ({
    lang: 'tr-TR',
    rate: 0.9,
    pitch: 1,
    volume: 1,
    ...options
  }), [options]);

  const speak = useCallback((text: string) => {
    // Stop any current speech
    if (currentSpeechRef.current) {
      speechSynthesis.cancel();
      currentSpeechRef.current = null;
    }

    // Check if speech synthesis is supported
    if (!('speechSynthesis' in window)) {
      alert('Bu tarayıcıda text-to-speech desteklenmiyor.');
      return;
    }

    // Create speech utterance
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = defaultOptions.lang!;
    utterance.rate = defaultOptions.rate!;
    utterance.pitch = defaultOptions.pitch!;
    utterance.volume = defaultOptions.volume!;

    // Set current text and playing state
    setCurrentText(text);
    setIsPlaying(true);

    // Speech events
    utterance.onstart = () => {
      // TTS started
    };

    utterance.onend = () => {
      setIsPlaying(false);
      setCurrentText('');
      currentSpeechRef.current = null;
    };

    utterance.onerror = (event) => {
      setIsPlaying(false);
      setCurrentText('');
      currentSpeechRef.current = null;
    };

    // Start speaking
    currentSpeechRef.current = utterance;
    speechSynthesis.speak(utterance);
  }, [defaultOptions]);

  const stop = useCallback(() => {
    if (currentSpeechRef.current) {
      speechSynthesis.cancel();
      currentSpeechRef.current = null;
      setIsPlaying(false);
      setCurrentText('');
    }
  }, []);

  const isCurrentlyPlaying = useCallback((text: string) => {
    return isPlaying && currentText === text;
  }, [isPlaying, currentText]);

  return {
    speak,
    stop,
    isPlaying,
    currentText,
    isCurrentlyPlaying
  };
};
