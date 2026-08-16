
class Chatbot {

    createWidget() {

        if (document.getElementById("chatbot-box")) {
        return;
    }

    document.body.insertAdjacentHTML(
        "beforeend",
        `
<div id="chatbot-toggle">
    <i class="bi bi-chat-dots-fill"></i>
</div>

<div id="chatbot-box">

    <div id="chatbot-header">

    <div class="chatbot-header-info">

        <div class="chatbot-avatar">
            <i class="bi bi-robot"></i>
        </div>

        <div>
            <h5 id="chatbot-title">
                AI Assistant
            </h5>

            <small id="chatbot-status">
                Online
            </small>
        </div>

    </div>

    <div class="chatbot-header-actions">

        <button type="button" id="chatbot-new-chat">
            <i class="bi bi-plus-lg"></i>
            New Chat
        </button>

        <button type="button" id="chatbot-close">
            <i class="bi bi-x"></i>
        </button>

    </div>

</div>

    <div id="chatbot-messages">

        <div id="chatbot-hero">

            <div class="chatbot-hero-icon">
                <i class="bi bi-robot"></i>
            </div>

            <h2 id="chatbot-hero-title"></h2>

            <div id="chatbot-hero-message"></div>

        </div>

    </div>

    <div id="chatbot-input-area">

        <input
            type="text"
            id="chatbot-input"
            placeholder="Type your message..."
            autocomplete="off"
        >

        <button type="button" id="chatbot-send">

    <i class="bi bi-send-fill"></i>

</button>

</div>
        `
    );

}

    constructor() {
       this.createWidget();

    this.apiUrl = "http://127.0.0.1:8000/api/widget";
        this.domain = window.location.hostname;
        this.widgetKey = window.ChatbotConfig?.widgetKey || null;

        this.sessionId = this.getSessionId();
        this.conversationId = null;

        this.toggle = document.getElementById("chatbot-toggle");
        this.box = document.getElementById("chatbot-box");
        this.close = document.getElementById("chatbot-close");

        this.messages = document.getElementById("chatbot-messages");

        this.input = document.getElementById("chatbot-input");

        this.send = document.getElementById("chatbot-send");

        this.initialized = false;

        this.bindEvents();

    }

    bindEvents() {

    this.toggle.addEventListener("click", () => this.open());

    this.close.addEventListener("click", () => this.closeWidget());

    this.send.addEventListener("click", (e) => {
    e.preventDefault();
    this.sendMessage();
});

    this.input.addEventListener("keypress", (e) => {

        if (e.key === "Enter") {
    e.preventDefault();
    this.sendMessage();
}

    });

}

    getSessionId() {

    let sessionId = localStorage.getItem("chatbot_session");

    if (!sessionId) {

        sessionId = crypto.randomUUID();

        localStorage.setItem("chatbot_session", sessionId);

    }

    return sessionId;

}

    open() {

    this.toggle.style.display = "none";

    this.box.style.display = "flex";

    if (!this.initialized) {
        this.init();
    }

}

    closeWidget() {

    this.box.style.display = "none";

    this.toggle.style.display = "flex";

}
async init() {
    try {
    const response = await fetch(
    `${this.apiUrl}/init?domain=${encodeURIComponent(this.domain)}&widget_key=${encodeURIComponent(this.widgetKey)}`
);

        const data = await response.json();

        if (!data.success) {

            this.addBotMessage("Unable to initialize chatbot.");
            return;

        }

        this.initialized = true;

        const settings = data.data.settings ?? {};

        // Hero Section
        document.getElementById("chatbot-title").textContent =
    settings.chatbot_name || "AI Assistant";

document.getElementById("chatbot-hero-title").textContent =
    settings.chatbot_name || "AI Assistant";

document.getElementById("chatbot-hero-message").textContent =
    settings.welcome_message || "Hey! 👋, how may I help you?";

document.getElementById("chatbot-input").placeholder =
    settings.placeholder || "Type your message...";

    if (settings.primary_color) {
    document.getElementById("chatbot-header").style.background = settings.primary_color;
    document.getElementById("chatbot-send").style.background = settings.primary_color;
    document.getElementById("chatbot-toggle").style.background = settings.primary_color;
}

        await this.loadFlow();

    } catch (error) {

        console.error(error);

        this.addBotMessage("Unable to connect to server.");

    }

}
async loadFlow() {

    try {

        const response = await fetch(
            `${this.apiUrl}/flow?domain=${encodeURIComponent(this.domain)}&widget_key=${encodeURIComponent(this.widgetKey)}`
        );

        const data = await response.json();

        if (!data.success) {
            return;
        }

        this.flow = data.flow;
        this.currentStep = 0;

        this.showCurrentStep();

    } catch (error) {

        console.error(error);

    }

}

    async sendMessage() {

    const message = this.input.value.trim();

    if (!message) {
        return;
    }

    this.addUserMessage(message);

    this.input.value = "";
    

    this.showTyping();

    try {

        const response = await fetch(`${this.apiUrl}/send-message`, {

            method: "POST",

            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
            },

            body: JSON.stringify({
    widget_key: this.widgetKey,

    domain: this.domain,

    session_id: this.sessionId,

    conversation_id: this.conversationId,

    message: message,

    name: "Ananya",
    email: "ananya@test.com",
    phone: "9876543210",
    notes: "Testing Lead",
}),
        });

        const data = await response.json();

this.hideTyping();

if (!data.success) {
    this.addBotMessage("Something went wrong.");
    return;
}
this.conversationId = data.conversation_id;
this.addBotMessage(data.response);

    } catch (error) {

        this.hideTyping();

        console.error(error);

        this.addBotMessage("Unable to connect to server.");

    }

}
showCurrentStep() {

    if (!this.flow) return;

    const step = this.flow.steps[this.currentStep];

    if (!step) return;

    this.addBotMessage(step.question);
    this.addOptionButtons(step.options);

}
   addUserMessage(message) {

    const div = document.createElement("div");

    div.className = "user-message";

    div.textContent = message;

    this.messages.appendChild(div);

    this.scrollBottom();

}

    addBotMessage(message) {

    const div = document.createElement("div");

    div.className = "bot-message";

    div.textContent = message;

    this.messages.appendChild(div);

    this.scrollBottom();

}

    showTyping() {

    if (document.getElementById("typing-indicator")) {
        return;
    }

    const typing = document.createElement("div");

    typing.id = "typing-indicator";

    typing.className = "bot-message";

    typing.innerHTML = `
        <div class="typing-bubble">
            <span></span>
            <span></span>
            <span></span>
        </div>
    `;

    this.messages.appendChild(typing);

    this.scrollBottom();

}

hideTyping() {

    const typing = document.getElementById("typing-indicator");

    if (typing) {
        typing.remove();
    }

}

    scrollBottom() {

    this.messages.scrollTop = this.messages.scrollHeight;

}
addOptionButtons(options) {

    if (!options || options.length === 0) {
        return;
    }

    const container = document.createElement("div");
    container.className = "chatbot-options";

    options.forEach(option => {
        const button = document.createElement("button");

        button.type = "button";
        button.className = "chatbot-option";
        button.textContent = option.value ?? option.label;

         button.addEventListener("click", () => {
            this.addUserMessage(option.value ?? option.label);
            container.remove();
        });

        container.appendChild(button);

    });

    this.messages.appendChild(container);

    this.scrollBottom();

}

}

new Chatbot();