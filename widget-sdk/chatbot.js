document.addEventListener("DOMContentLoaded", () => {

    const toggle = document.getElementById("chatbot-toggle");
    const box = document.getElementById("chatbot-box");
    const close = document.getElementById("chatbot-close");
    const send = document.getElementById("chatbot-send");
    const input = document.getElementById("chatbot-input");
    const messages = document.getElementById("chatbot-messages");

    // Open Widget
    toggle.addEventListener("click", () => {

        box.style.display = "flex";

        if (!messages.dataset.loaded) {

            addBotMessage("How can I help you today?");

            messages.dataset.loaded = "true";
        }

        scrollBottom();

    });

    

    // Close Widget
    close.addEventListener("click", () => {

        box.style.display = "none";

    });

    // Send Button
    send.addEventListener("click", sendMessage);

    // Enter Key
    input.addEventListener("keypress", (e) => {

        if (e.key === "Enter") {

            sendMessage();

        }

    });

    

    function sendMessage() {

        const text = input.value.trim();

        if (text === "") return;

        addUserMessage(text);

        input.value = "";

        // Temporary reply
        setTimeout(() => {

            addBotMessage("Received: " + text);

        }, 500);

    }

    function addUserMessage(text) {

        const div = document.createElement("div");

        div.className = "user-message";

        div.textContent = text;

        messages.appendChild(div);

        scrollBottom();

    }

    function addBotMessage(text) {

        const div = document.createElement("div");

        div.className = "bot-message";

        div.textContent = text;

        messages.appendChild(div);

        scrollBottom();

    }

    function scrollBottom() {

        messages.scrollTop = messages.scrollHeight;

    }

    function showTyping() {

    document.getElementById("typing-indicator").style.display = "block";

    scrollBottom();

}

function hideTyping() {

    document.getElementById("typing-indicator").style.display = "none";

}

    class Chatbot {

    constructor() {

        this.toggle = document.getElementById("chatbot-toggle");
        this.box = document.getElementById("chatbot-box");
        this.close = document.getElementById("chatbot-close");
        this.messages = document.getElementById("chatbot-messages");
        this.input = document.getElementById("chatbot-input");
        this.send = document.getElementById("chatbot-send");

        this.initialized = false;

        this.events();
    }

    events() {

        this.toggle.addEventListener("click", () => this.open());

        this.close.addEventListener("click", () => this.closeWidget());

        this.send.addEventListener("click", () => this.sendMessage());

        this.input.addEventListener("keypress", (e) => {

            if (e.key === "Enter") {

                this.sendMessage();

            }

        });

    }

    open() {

        this.box.style.display = "flex";

        if (!this.initialized) {

            this.addBotMessage("Hello 👋");
            this.addBotMessage("How can I help you today?");

            this.initialized = true;
        }

    }

    closeWidget() {

        this.box.style.display = "none";

    }

    sendMessage() {

        const text = this.input.value.trim();

        if (!text) return;

        this.addUserMessage(text);

        this.input.value = "";

        showTyping();

setTimeout(() => {

    hideTyping();

    addBotMessage("How can I help you today?");

}, 1500);

    }

    addUserMessage(message) {

        const div = document.createElement("div");

        div.className = "user-message";

        div.textContent = message;

        this.messages.appendChild(div);

        this.scroll();

    }

    addBotMessage(message) {

        const div = document.createElement("div");

        div.className = "bot-message";

        div.textContent = message;

        this.messages.appendChild(div);

        this.scroll();

    }

    scroll() {

        this.messages.scrollTop = this.messages.scrollHeight;

    }

}

document.addEventListener("DOMContentLoaded", () => {

    new Chatbot();

});

});