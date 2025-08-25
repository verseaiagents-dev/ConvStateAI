import { useState, useRef, useCallback } from 'react';

interface STTOptions {
  lang?: string;
  continuous?: boolean;
  interimResults?: boolean;
}

export const useSpeechToText = (options: STTOptions = {}) => {
  const [isListening, setIsListening] = useState(false);
  const [transcript, setTranscript] = useState('');
  const [error, setError] = useState<string | null>(null);
  
  const recognitionRef = useRef<SpeechRecognition | null>(null);

  const defaultOptions = {
    lang: 'tr-TR',
    continuous: false,
    interimResults: false,
    ...options
  };

  const startListening = useCallback((onResult?: (text: string) => void) => {
    // Check if SpeechRecognition is supported
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
      setError('Bu tarayıcıda speech-to-text desteklenmiyor.');
      return;
    }

    try {
      // Create speech recognition instance
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      const recognition = new SpeechRecognition();
      
      recognition.lang = defaultOptions.lang;
      recognition.continuous = defaultOptions.continuous;
      recognition.interimResults = defaultOptions.interimResults;
      
      recognitionRef.current = recognition;

      // Set up event handlers
      recognition.onstart = () => {
        setIsListening(true);
        setError(null);
        setTranscript('');
      };

      recognition.onresult = (event) => {
        let finalTranscript = '';
        let interimTranscript = '';

        for (let i = event.resultIndex; i < event.results.length; i++) {
          const transcript = event.results[i][0].transcript;
          if (event.results[i].isFinal) {
            finalTranscript += transcript;
          } else {
            interimTranscript += transcript;
          }
        }

        const result = finalTranscript || interimTranscript;
        setTranscript(result);
        
        // Call callback with result
        if (onResult && finalTranscript) {
          onResult(finalTranscript);
        }
      };

      recognition.onend = () => {
        setIsListening(false);
        recognitionRef.current = null;
      };

      recognition.onerror = (event) => {
        setError(`Ses tanıma hatası: ${event.error}`);
        setIsListening(false);
        recognitionRef.current = null;
      };

      // Start recognition
      recognition.start();
      
    } catch (err) {
      setError('Ses tanıma başlatılamadı.');
    }
  }, [defaultOptions]);

  const stopListening = useCallback(() => {
    if (recognitionRef.current) {
      recognitionRef.current.stop();
      recognitionRef.current = null;
    }
    setIsListening(false);
  }, []);

  const reset = useCallback(() => {
    setTranscript('');
    setError(null);
    setIsListening(false);
    if (recognitionRef.current) {
      recognitionRef.current.stop();
      recognitionRef.current = null;
    }
  }, []);

  return {
    isListening,
    transcript,
    error,
    startListening,
    stopListening,
    reset
  };
};
