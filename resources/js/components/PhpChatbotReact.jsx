import React, { useState, useRef } from 'react';

export default function PhpChatbotReact() {
  const [open, setOpen] = useState(false);
  const [input, setInput] = useState('');
  const [messages, setMessages] = useState([]);
  const bodyRef = useRef(null);

  const send = async (e) => {
    e.preventDefault();
    if (!input.trim()) return;
    setMessages(msgs => [...msgs, { role: 'user', content: input }]);
    const userMsg = input;
    setInput('');
    // Replace with your backend endpoint
    const res = await fetch('/php-chatbot/message', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: userMsg })
    });
    const data = await res.json();
    setMessages(msgs => [...msgs, { role: 'bot', content: data.reply }]);
    setTimeout(() => {
      if (bodyRef.current) bodyRef.current.scrollTop = bodyRef.current.scrollHeight;
    }, 0);
  };

  return (
    <div id="php-chatbot-react" style={{position:'fixed',bottom:24,right:24,width:340,maxWidth:'90vw',zIndex:9999,fontFamily:'sans-serif',boxShadow:'0 4px 24px rgba(0,0,0,0.12)'}}>
      <div style={{background:'#222',color:'#fff',padding:'14px 16px',borderRadius:'8px 8px 0 0',cursor:'pointer',fontWeight:600,display:'flex',alignItems:'center',gap:8}} onClick={() => setOpen(o => !o)}>
        <span style={{fontSize:'1.3em'}}>💬</span> <span>Chat with us!</span>
        <span style={{marginLeft:'auto',fontSize:'1.1em',opacity:0.7}}>{open ? '▼' : '▲'}</span>
      </div>
      <div ref={bodyRef} style={{display:open?'block':'none',background:'#fff',border:'1px solid #ddd',borderTop:'none',padding:'14px 12px 12px 12px',borderRadius:'0 0 8px 8px',maxHeight:400,overflowY:'auto',fontSize:'1em'}}>
        {messages.map((msg, i) => (
          <div key={i} style={{textAlign:msg.role==='user'?'right':'left',margin:'8px 0'}}>
            <span style={{background:msg.role==='user'?'#e0e0e0':'#f0f0ff',padding:'6px 12px',borderRadius:16,display:'inline-block'}}>{msg.content}</span>
          </div>
        ))}
      </div>
      <form style={{display:open?'flex':'none',background:'#f9f9f9',padding:'10px 8px 8px 8px',borderRadius:'0 0 8px 8px',borderTop:'1px solid #eee',gap:8,alignItems:'center'}} onSubmit={send}>
        <input value={input} onChange={e=>setInput(e.target.value)} type="text" placeholder="Type your message..." style={{flex:1,padding:'8px 12px',border:'1px solid #ccc',borderRadius:4,fontSize:'1em'}} autoComplete="off" />
        <button type="submit" style={{padding:'8px 16px',border:'none',background:'#222',color:'#fff',borderRadius:4,fontWeight:600}}>Send</button>
      </form>
    </div>
  );
}
