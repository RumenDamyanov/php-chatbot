import React, { useState } from 'react';

interface ChatMessage {
  sender: 'user' | 'bot';
  text: string;
}

const PhpChatbotTs: React.FC = () => {
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);

  const sendMessage = async () => {
    if (!input.trim()) return;
    const userMsg: ChatMessage = { sender: 'user', text: input };
    setMessages((msgs) => [...msgs, userMsg]);
    setInput('');
    setLoading(true);
    try {
      const res = await fetch('/php-chatbot/message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: input }),
      });
      const data = await res.json();
      setMessages((msgs) => [...msgs, { sender: 'bot', text: data.reply }]);
    } catch (e) {
      setMessages((msgs) => [...msgs, { sender: 'bot', text: 'Error contacting chatbot.' }]);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div id="php-chatbot-popup" style={{ width: 340, position: 'fixed', bottom: 24, right: 24, zIndex: 9999 }}>
      <div id="php-chatbot-header" style={{ background: '#222', color: '#fff', padding: 14, borderRadius: '8px 8px 0 0', cursor: 'pointer', fontWeight: 600 }}>PHP Chatbot</div>
      <div id="php-chatbot-body" style={{ background: '#fff', border: '1px solid #ddd', borderTop: 'none', padding: 14, borderRadius: '0 0 8px 8px', maxHeight: 400, overflowY: 'auto', fontSize: '1em' }}>
        {messages.map((msg, i) => (
          <div key={i} className={msg.sender} style={{ margin: '8px 0', textAlign: msg.sender === 'user' ? 'right' : 'left' }}>
            <span style={{ background: msg.sender === 'user' ? '#e0e0e0' : '#f0f0ff', padding: '6px 12px', borderRadius: 16, display: 'inline-block' }}>{msg.text}</span>
          </div>
        ))}
        {loading && <div className="bot"><span>...</span></div>}
      </div>
      <div id="php-chatbot-form" style={{ background: '#f9f9f9', padding: 10, borderRadius: '0 0 8px 8px', borderTop: '1px solid #eee', display: 'flex', gap: 8, alignItems: 'center' }}>
        <input
          id="php-chatbot-input"
          style={{ flex: 1, padding: '8px 12px', border: '1px solid #ccc', borderRadius: 4, fontSize: '1em' }}
          value={input}
          onChange={e => setInput(e.target.value)}
          onKeyDown={e => { if (e.key === 'Enter') sendMessage(); }}
          disabled={loading}
          placeholder="Type your message..."
        />
        <button onClick={sendMessage} disabled={loading || !input.trim()} style={{ padding: '8px 16px', border: 'none', background: '#222', color: '#fff', borderRadius: 4, fontWeight: 600 }}>Send</button>
      </div>
    </div>
  );
};

export default PhpChatbotTs;
