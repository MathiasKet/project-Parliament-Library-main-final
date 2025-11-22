import adminState from './shared-state.js';
import apiService from './api-service.js';

class Chat {
    constructor() {
        this.chatBtn = document.getElementById('chatBtn');
        this.chatWindow = document.getElementById('chatWindow');
        this.chatClose = document.getElementById('chatClose');
        this.chatMessages = document.getElementById('chatMessages');
        this.chatForm = document.getElementById('chatForm');
        this.chatInput = document.getElementById('chatInput');
        
        this.isOpen = false;
        this.isTyping = false;
        this.messageQueue = [];
        this.messageHistory = [];
        
        this.init();
        this.loadMessageHistory();
    }

    init() {
        // Initialize event listeners
        this.chatBtn.addEventListener('click', () => this.toggleChat());
        this.chatClose.addEventListener('click', () => this.closeChat());
        this.chatForm.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Add emoji picker button
        this.addEmojiPicker();
        
        // Add file attachment button
        this.addFileAttachment();
        
        // Close chat when clicking outside
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.chatWindow.contains(e.target) && !this.chatBtn.contains(e.target)) {
                this.closeChat();
            }
        });

        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeChat();
            }
        });
    }

    addEmojiPicker() {
        const emojiButton = document.createElement('button');
        emojiButton.type = 'button';
        emojiButton.className = 'emoji-picker-btn';
        emojiButton.innerHTML = '😊';
        emojiButton.title = 'Add emoji';
        
        const emojiPicker = document.createElement('div');
        emojiPicker.className = 'emoji-picker';
        emojiPicker.style.display = 'none';
        
        // Add some common emojis
        const emojis = ['😊', '👍', '❤️', '🎉', '👋', '📚', '🔍', '💡', '📝', '📅'];
        emojis.forEach(emoji => {
            const span = document.createElement('span');
            span.textContent = emoji;
            span.addEventListener('click', () => {
                this.chatInput.value += emoji;
                emojiPicker.style.display = 'none';
            });
            emojiPicker.appendChild(span);
        });
        
        emojiButton.addEventListener('click', () => {
            emojiPicker.style.display = emojiPicker.style.display === 'none' ? 'grid' : 'none';
        });
        
        this.chatInput.parentElement.insertBefore(emojiButton, this.chatInput);
        this.chatInput.parentElement.appendChild(emojiPicker);
    }

    addFileAttachment() {
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*,.pdf,.doc,.docx';
        fileInput.style.display = 'none';
        
        const fileButton = document.createElement('button');
        fileButton.type = 'button';
        fileButton.className = 'file-attachment-btn';
        fileButton.innerHTML = '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>';
        fileButton.title = 'Attach file';
        
        fileButton.addEventListener('click', () => fileInput.click());
        
        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (file) {
                try {
                    const formData = new FormData();
                    formData.append('file', file);
                    
                    // Show uploading indicator
                    this.addMessage('Uploading file...', 'system');
                    
                    // Upload file
                    const response = await apiService.post('/api/chat/upload', formData);
                    
                    // Remove uploading indicator
                    this.chatMessages.removeChild(this.chatMessages.lastChild);
                    
                    // Add file message
                    this.addMessage(`File uploaded: ${file.name}`, 'user');
                    
                    // Send file reference to chat
                    await this.handleSubmit(new Event('submit'), response.fileUrl);
                } catch (error) {
                    this.addMessage('Failed to upload file. Please try again.', 'system error');
                }
            }
        });
        
        this.chatInput.parentElement.insertBefore(fileButton, this.chatInput);
        this.chatInput.parentElement.insertBefore(fileInput, fileButton);
    }

    async loadMessageHistory() {
        try {
            const response = await apiService.get('/api/chat/history');
            this.messageHistory = response.messages;
            this.messageHistory.forEach(msg => {
                this.addMessage(msg.content, msg.type, msg.timestamp, msg.read);
            });
        } catch (error) {
            console.error('Failed to load message history:', error);
        }
    }

    toggleChat() {
        if (this.isOpen) {
            this.closeChat();
        } else {
            this.openChat();
        }
    }

    openChat() {
        this.isOpen = true;
        this.chatWindow.classList.add('active');
        this.chatBtn.classList.add('active');
        this.chatInput.focus();
        
        // Mark messages as read when opening chat
        this.markMessagesAsRead();
    }

    closeChat() {
        this.isOpen = false;
        this.chatWindow.classList.remove('active');
        this.chatBtn.classList.remove('active');
    }

    async handleSubmit(e, fileUrl = null) {
        e.preventDefault();
        
        const message = this.chatInput.value.trim();
        if (!message && !fileUrl) return;

        // Add user message to chat
        this.addMessage(message || 'Shared a file', 'user', new Date().toISOString());
        this.chatInput.value = '';

        // Show typing indicator
        this.showTypingIndicator();

        try {
            // Send message to API
            const response = await apiService.post('/api/chat', { 
                message: message || fileUrl,
                fileUrl: fileUrl
            });
            
            // Remove typing indicator
            this.removeTypingIndicator();
            
            // Add librarian response
            this.addMessage(response.message, 'librarian', new Date().toISOString());
            
            // Save to message history
            this.messageHistory.push({
                content: message || fileUrl,
                type: 'user',
                timestamp: new Date().toISOString(),
                read: true
            });
            
            this.messageHistory.push({
                content: response.message,
                type: 'librarian',
                timestamp: new Date().toISOString(),
                read: false
            });
            
            // Save message history
            localStorage.setItem('chatHistory', JSON.stringify(this.messageHistory));
        } catch (error) {
            // Remove typing indicator
            this.removeTypingIndicator();
            
            // Show error message
            this.addMessage('Sorry, I encountered an error. Please try again later.', 'librarian error');
            
            // Log error
            console.error('Chat error:', error);
        }
    }

    addMessage(message, type, timestamp = new Date().toISOString(), read = false) {
        const messageElement = document.createElement('div');
        messageElement.className = `message ${type}-message`;
        
        // Add message content
        const content = document.createElement('div');
        content.className = 'message-content';
        content.textContent = message;
        messageElement.appendChild(content);
        
        // Add timestamp
        const time = document.createElement('div');
        time.className = 'message-timestamp';
        time.textContent = new Date(timestamp).toLocaleTimeString();
        messageElement.appendChild(time);
        
        // Add read receipt for user messages
        if (type === 'user') {
            const receipt = document.createElement('div');
            receipt.className = 'message-receipt';
            receipt.innerHTML = read ? '✓✓' : '✓';
            messageElement.appendChild(receipt);
        }
        
        this.chatMessages.appendChild(messageElement);
        this.scrollToBottom();
    }

    showTypingIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'typing-indicator';
        
        // Add three dots
        for (let i = 0; i < 3; i++) {
            const dot = document.createElement('div');
            dot.className = 'typing-dot';
            indicator.appendChild(dot);
        }
        
        this.chatMessages.appendChild(indicator);
        this.scrollToBottom();
    }

    removeTypingIndicator() {
        const indicator = this.chatMessages.querySelector('.typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    async markMessagesAsRead() {
        const unreadMessages = this.messageHistory.filter(msg => !msg.read && msg.type === 'librarian');
        if (unreadMessages.length > 0) {
            try {
                await apiService.post('/api/chat/mark-read', {
                    messageIds: unreadMessages.map(msg => msg.id)
                });
                
                // Update local message history
                unreadMessages.forEach(msg => msg.read = true);
                localStorage.setItem('chatHistory', JSON.stringify(this.messageHistory));
                
                // Update UI
                this.chatMessages.querySelectorAll('.message.librarian-message').forEach(msg => {
                    const receipt = msg.querySelector('.message-receipt');
                    if (receipt) {
                        receipt.innerHTML = '✓✓';
                    }
                });
            } catch (error) {
                console.error('Failed to mark messages as read:', error);
            }
        }
    }

    scrollToBottom() {
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    }
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new Chat();
});

export default Chat; 