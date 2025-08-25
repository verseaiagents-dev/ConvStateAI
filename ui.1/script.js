document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('messageInput');
    const chatContent = document.getElementById('chatContent');
    const soundToggle = document.getElementById('soundToggle');
    const voiceButton = document.getElementById('voiceButton');
    const chatContainer = document.querySelector('.chat-container');
    const scrollIndicator = document.getElementById('scrollIndicator');
    
    let isSoundEnabled = true;
    let isRecording = false;
    let recognition = null;
    let silenceTimer = null;
    let currentSpeech = null; // Text-to-speech için


    // Speech Recognition Setup
    function initializeSpeechRecognition() {
        // Check if browser supports speech recognition
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.warn('Speech recognition not supported in this browser');
            voiceButton.style.display = 'none';
            return false;
        }

        // Initialize speech recognition
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        
        recognition.continuous = false;
        recognition.interimResults = true;
        recognition.lang = 'tr-TR'; // Türkçe için, değiştirilebilir

        // Speech recognition events
        recognition.onstart = function() {
            isRecording = true;
            voiceButton.classList.add('recording');
        };

        recognition.onresult = function(event) {
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

            // Show interim results
            if (interimTranscript) {
                messageInput.value = interimTranscript;
                
                // Reset silence timer when user is speaking
                if (silenceTimer) {
                    clearTimeout(silenceTimer);
                }
            }

            // Process final result
            if (finalTranscript) {
                messageInput.value = finalTranscript;
                
                // Start silence timer - wait 1.5 seconds before stopping
                if (silenceTimer) {
                    clearTimeout(silenceTimer);
                }
                
                silenceTimer = setTimeout(() => {
                    if (isRecording) {
                        recognition.stop();
                    }
                }, 1500); // 1.5 saniye bekle
            }
        };

        recognition.onerror = function(event) {
            if (event.error === 'aborted') {
                // Kullanıcı manuel olarak durdurdu, hiçbir şey gösterme
                return;
            }
            console.error('Speech recognition error:', event.error);
        };

        recognition.onend = function() {
            isRecording = false;
            voiceButton.classList.remove('recording');
            
            if (silenceTimer) {
                clearTimeout(silenceTimer);
            }
        };

        return true;
    }

    // Voice button click handler
    voiceButton.addEventListener('click', function() {
        if (!recognition) {
            if (!initializeSpeechRecognition()) {
                alert('Bu tarayıcıda ses tanıma desteklenmiyor.');
                return;
            }
        }

        if (isRecording) {
            recognition.stop();
        } else {
            recognition.start();
        }
    });

    // Sound toggle functionality
    soundToggle.addEventListener('click', function() {
        isSoundEnabled = !isSoundEnabled;
        this.style.opacity = isSoundEnabled ? '1' : '0.5';
    });

    // Scroll indicator functionality
    function updateScrollIndicator() {
        if (!scrollIndicator) return;
        
        const scrollTop = chatContent.scrollTop;
        const scrollHeight = chatContent.scrollHeight;
        const clientHeight = chatContent.clientHeight;
        
        // Show indicator when not at bottom and there's scrollable content
        if (scrollHeight > clientHeight && scrollTop < scrollHeight - clientHeight - 10) {
            scrollIndicator.classList.add('show');
        } else {
            scrollIndicator.classList.remove('show');
        }
    }
    
    // Add scroll event listener to chat content
    chatContent.addEventListener('scroll', updateScrollIndicator);
    
    // Initial check for scroll indicator
    setTimeout(updateScrollIndicator, 100);

    // Message sending functionality
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim()) {
            e.preventDefault(); // Prevent default form submission
            const messageText = this.value.trim();
            addMessage(messageText, 'user');
            this.value = '';
            
            // Simulate AI response after a short delay
            setTimeout(() => {
                addMessage('Thank you for your message. I\'m processing your request.', 'agent');
            }, 1000);
        }
    });

    // Send button functionality
    const sendButton = document.getElementById('sendButton');
    if (sendButton) {
        sendButton.addEventListener('click', function() {
            const messageText = messageInput.value.trim();
            if (messageText) {
                addMessage(messageText, 'user');
                messageInput.value = '';
                
                // Simulate AI response after a short delay
                setTimeout(() => {
                    addMessage('Thank you for your message. I\'m processing your request.', 'agent');
                }, 1000);
            }
        });
    }

    function addMessage(text, type) {
        console.log('Adding message:', text, type); // Debug log
        
        const messageGroup = document.createElement('div');
        messageGroup.className = `message-group ${type}`;

        if (type === 'agent') {
            // Add agent avatar for agent messages
            const avatar = document.createElement('div');
            avatar.className = 'agent-avatar';
            avatar.innerHTML = '<img src="https://via.placeholder.com/40" alt="Agent Avatar">';
            messageGroup.appendChild(avatar);
        }

        const message = document.createElement('div');
        message.className = `message ${type}-message`;
        
        // Add text content
        const textContent = document.createElement('div');
        textContent.innerHTML = `<p>${text}</p>`;
        message.appendChild(textContent);
        
        // Add TTS button for agent messages
        if (type === 'agent') {
            const ttsButton = createTTSButton(text);
            message.appendChild(ttsButton);
        }
        
        messageGroup.appendChild(message);
        chatContent.appendChild(messageGroup);
        
        // Scroll to bottom
        chatContent.scrollTop = chatContent.scrollHeight;
        
        // Update scroll indicator after adding message
        setTimeout(updateScrollIndicator, 100);

        // Play sound if enabled
        if (isSoundEnabled) {
            playMessageSound(type);
        }
        
        console.log('Message added successfully'); // Debug log
    }

    // Text-to-Speech Button Creation
    function createTTSButton(text) {
        const ttsButton = document.createElement('button');
        ttsButton.className = 'tts-button';
        ttsButton.title = 'Metni seslendir';
        ttsButton.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 5L8 9H4V15H8L12 19V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15.54 8.46C16.4774 9.39764 17.0039 10.6692 17.0039 12C17.0039 13.3308 16.4774 14.6024 15.54 15.54" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        `;
        
        ttsButton.addEventListener('click', function(e) {
            e.stopPropagation();
            speakText(text, ttsButton);
        });
        
        return ttsButton;
    }

    // Text-to-Speech Function
    function speakText(text, button) {
        // Stop any current speech
        if (currentSpeech) {
            currentSpeech.cancel();
        }
        
        // Check if speech synthesis is supported
        if (!('speechSynthesis' in window)) {
            alert('Bu tarayıcıda text-to-speech desteklenmiyor.');
            return;
        }
        
        // Create speech utterance
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'tr-TR'; // Türkçe
        utterance.rate = 0.9; // Biraz yavaş
        utterance.pitch = 1;
        utterance.volume = 1;
        
        // Add button visual feedback
        button.classList.add('playing');
        
        // Speech events
        utterance.onstart = function() {
            console.log('Speech started');
        };
        
        utterance.onend = function() {
            button.classList.remove('playing');
            currentSpeech = null;
        };
        
        utterance.onerror = function(event) {
            console.error('Speech error:', event.error);
            button.classList.remove('playing');
            currentSpeech = null;
        };
        
        // Start speaking
        currentSpeech = utterance;
        speechSynthesis.speak(utterance);
    }

    function playMessageSound(type) {
        // Add sound implementation here
        console.log(`Playing ${type} message sound`);
    }



    // Initialize speech recognition
    initializeSpeechRecognition();

    // Initialize TTS buttons
    initializeTTSButtons();

    // Initialize secondary buttons
    initializeSecondaryButtons();

    // Initialize perfect scrollbar or any other enhancements
    initializeEnhancements();
    
    // Initialize product slider functionality
    initializeProductSlider();
});

function initializeEnhancements() {
    // Add any additional UI enhancements here
    console.log('UI enhancements initialized');
}

function initializeProductSlider() {
    const productsContainer = document.querySelector('.products-scroll-container');
    const productsWrapper = document.querySelector('.products-wrapper');
    
    if (!productsContainer || !productsWrapper) return;
    
    let isDown = false;
    let startX;
    let scrollLeft;
    
    // Mouse events for drag scrolling
    productsContainer.addEventListener('mousedown', (e) => {
        isDown = true;
        productsContainer.style.cursor = 'grabbing';
        startX = e.pageX - productsContainer.offsetLeft;
        scrollLeft = productsContainer.scrollLeft;
    });
    
    productsContainer.addEventListener('mouseleave', () => {
        isDown = false;
        productsContainer.style.cursor = 'grab';
    });
    
    productsContainer.addEventListener('mouseup', () => {
        isDown = false;
        productsContainer.style.cursor = 'grab';
    });
    
    productsContainer.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - productsContainer.offsetLeft;
        const walk = (x - startX) * 2;
        productsContainer.scrollLeft = scrollLeft - walk;
    });
    
    // Touch events for mobile
    productsContainer.addEventListener('touchstart', (e) => {
        startX = e.touches[0].pageX - productsContainer.offsetLeft;
        scrollLeft = productsContainer.scrollLeft;
    });
    
    productsContainer.addEventListener('touchmove', (e) => {
        if (!startX) return;
        e.preventDefault();
        const x = e.touches[0].pageX - productsContainer.offsetLeft;
        const walk = (x - startX) * 2;
        productsContainer.scrollLeft = scrollLeft - walk;
    });
    
    productsContainer.addEventListener('touchend', () => {
        startX = null;
    });
    
    // Add scroll indicators
    addScrollIndicators();
    
    console.log('Product slider initialized');
}

function addScrollIndicators() {
    const productsContainer = document.querySelector('.products-scroll-container');
    const productsWrapper = document.querySelector('.products-wrapper');
    
    if (!productsContainer || !productsWrapper) return;
    
    // Scroll functionality is handled by mouse/touch events in initializeProductSlider()
    console.log('Scroll functionality initialized (mouse/touch only)');
}

// Initialize TTS buttons
function initializeTTSButtons() {
    const ttsButtons = document.querySelectorAll('.tts-button');
    
    ttsButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Check if this button is currently playing
            if (this.classList.contains('playing')) {
                // Stop current speech
                if (window.currentSpeech) {
                    window.currentSpeech.cancel();
                    window.currentSpeech = null;
                }
                this.classList.remove('playing');
                return;
            }
            
            // Get the message text from the parent message element
            const messageElement = this.closest('.message');
            const textElements = messageElement.querySelectorAll('p, h3, h4');
            let text = '';
            
            // Collect all text from the message
            textElements.forEach(element => {
                text += element.textContent + ' ';
            });
            
            // Clean up the text
            text = text.trim();
            
            if (text) {
                speakText(text, this);
            }
        });
    });
}

// Initialize secondary buttons
function initializeSecondaryButtons() {
    const secondaryButtons = document.querySelectorAll('.secondary-button');
    
    secondaryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const buttonText = this.textContent.trim();
            
            if (buttonText && messageInput) {
                // Write button text to message input
                messageInput.value = buttonText;
                
                // Focus on input field
                messageInput.focus();
                
                // Optional: Auto-send after a short delay
                // setTimeout(() => {
                //     if (messageInput.value === buttonText) {
                //         addMessage(buttonText, 'user');
                //         messageInput.value = '';
                //         
                //         setTimeout(() => {
                //             addMessage(`I understand you want to "${buttonText}". How can I help you with that?`, 'agent');
                //         }, 1000);
                //     }
                // }, 2000);
            }
        });
    });
}

// Global speakText function for HTML onclick handlers
window.speakText = function(text, button) {
    // Stop any current speech from other buttons
    if (window.currentSpeech) {
        window.currentSpeech.cancel();
        // Remove playing class from all buttons
        document.querySelectorAll('.tts-button.playing').forEach(btn => {
            btn.classList.remove('playing');
        });
    }
    
    // Check if speech synthesis is supported
    if (!('speechSynthesis' in window)) {
        alert('Bu tarayıcıda text-to-speech desteklenmiyor.');
        return;
    }
    
    // Create speech utterance
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'tr-TR'; // Türkçe
    utterance.rate = 0.9; // Biraz yavaş
    utterance.pitch = 1;
    utterance.volume = 1;
    
    // Add button visual feedback
    button.classList.add('playing');
    
    // Speech events
    utterance.onstart = function() {
        console.log('Speech started');
    };
    
    utterance.onend = function() {
        button.classList.remove('playing');
        window.currentSpeech = null;
    };
    
    utterance.onerror = function(event) {
        console.error('Speech error:', event.error);
        button.classList.remove('playing');
        window.currentSpeech = null;
    };
    
    // Start speaking
    window.currentSpeech = utterance;
    speechSynthesis.speak(utterance);
};
