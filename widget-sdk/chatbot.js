class Chatbot {

    constructor() {

        this.apiUrl = "http://127.0.0.1:8000/api/widget";

       this.domain = "localhost";

        this.sessionId = this.getSessionId();

        console.log(this.sessionId);
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

    this.send.addEventListener("click", () => this.sendMessage());

    this.input.addEventListener("keypress", (e) => {

        if (e.key === "Enter") {

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

    this.box.style.display = "flex";

    if (!this.initialized) {

        this.init();

    }

}

    closeWidget() {

    this.box.style.display = "none";

}
async init() {

    try {

        const response = await fetch(

            `${this.apiUrl}/init?domain=${this.domain}`

        );

        const data = await response.json();

        if (!data.success) {

            this.addBotMessage("Unable to initialize chatbot.");

            return;

        }

        this.initialized = true;

        const welcomeMessage =
            data.data.settings?.welcome_message ??
            "Hello! How can I help you today?";

        this.addBotMessage(welcomeMessage);

    } catch (error) {

        console.error(error);

        this.addBotMessage("Unable to connect to server.");

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

                domain: this.domain,

                session_id: this.sessionId,

                message: message,

            }),

        });

        const data = await response.json();

        this.hideTyping();

        if (!data.success) {

            this.addBotMessage("Something went wrong.");

            return;

        }

        this.addBotMessage(data.response);

    } catch (error) {

        this.hideTyping();

        console.error(error);

        this.addBotMessage("Unable to connect to server.");

    }

}

   addUserMessage(message) {

    const emptyState = document.getElementById("chatbot-empty-state");

    if (emptyState) {
        emptyState.style.display = "none";
    }

    const div = document.createElement("div");

    div.className = "user-message";

    div.textContent = message;

    this.messages.appendChild(div);

    this.scrollBottom();

}

    addBotMessage(message) {

    const emptyState = document.getElementById("chatbot-empty-state");

    if (emptyState) {
        emptyState.style.display = "none";
    }

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

    hideTyping() {

    }

    scrollBottom() {

    this.messages.scrollTop = this.messages.scrollHeight;

}

}

document.addEventListener("DOMContentLoaded", () => {

    new Chatbot();

});