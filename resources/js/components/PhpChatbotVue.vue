<template>
  <div id="php-chatbot-vue" class="php-chatbot-popup">
    <div class="php-chatbot-header" @click="toggle">
      💬 Chat with us!
      <span class="php-chatbot-arrow">{{ open ? '▼' : '▲' }}</span>
    </div>
    <div v-show="open" class="php-chatbot-body">
      <div v-for="(msg, i) in messages" :key="i" :class="msg.role">
        <span>{{ msg.content }}</span>
      </div>
    </div>
    <form v-show="open" class="php-chatbot-form" @submit.prevent="send">
      <input v-model="input" type="text" placeholder="Type your message..." autocomplete="off" />
      <button type="submit">Send</button>
    </form>
  </div>
</template>

<script>
export default {
  name: 'PhpChatbotVue',
  data() {
    return {
      open: false,
      input: '',
      messages: []
    };
  },
  methods: {
    toggle() {
      this.open = !this.open;
    },
    async send() {
      if (!this.input.trim()) return;
      this.messages.push({ role: 'user', content: this.input });
      const userMsg = this.input;
      this.input = '';
      // Replace with your backend endpoint
      const res = await fetch('/php-chatbot/message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: userMsg })
      });
      const data = await res.json();
      this.messages.push({ role: 'bot', content: data.reply });
      this.$nextTick(() => {
        const body = this.$el.querySelector('.php-chatbot-body');
        body.scrollTop = body.scrollHeight;
      });
    }
  }
};
</script>

<style scoped>
.php-chatbot-popup { position:fixed;bottom:24px;right:24px;width:340px;max-width:90vw;z-index:9999;font-family:sans-serif;box-shadow:0 4px 24px rgba(0,0,0,0.12); }
.php-chatbot-header { background:#222;color:#fff;padding:14px 16px;border-radius:8px 8px 0 0;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:8px; }
.php-chatbot-arrow { margin-left:auto;font-size:1.1em;opacity:0.7; }
.php-chatbot-body { background:#fff;border:1px solid #ddd;border-top:none;padding:14px 12px 12px 12px;border-radius:0 0 8px 8px;max-height:400px;overflow-y:auto;font-size:1em; }
.php-chatbot-form { background:#f9f9f9;padding:10px 8px 8px 8px;border-radius:0 0 8px 8px;border-top:1px solid #eee;display:flex;gap:8px;align-items:center; }
.user { text-align:right;margin:8px 0; }
.user span { background:#e0e0e0;padding:6px 12px;border-radius:16px;display:inline-block; }
.bot { text-align:left;margin:8px 0; }
.bot span { background:#f0f0ff;padding:6px 12px;border-radius:16px;display:inline-block; }
</style>
