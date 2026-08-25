
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

        this.visitorId = this.getVisitorId();
        this.sessionId = null;

        this.conversationId = null;
        this.conversationEnded = false;

        this.toggle = document.getElementById("chatbot-toggle");
        this.box = document.getElementById("chatbot-box");
        this.close = document.getElementById("chatbot-close");

        this.messages = document.getElementById("chatbot-messages");

        this.input = document.getElementById("chatbot-input");

        this.send = document.getElementById("chatbot-send");

        this.initialized = false;

        // Hide widget until website is verified
        this.toggle.style.display = "none";
        this.box.style.display = "none";

        this.bindEvents();

        // Check website status immediately
        this.init();

        window.addEventListener("beforeunload", () => {
            if (!this.conversationId) {
                return;
            }

            const data = JSON.stringify({
                widget_key: this.widgetKey,
                domain: this.domain,
                visitor_uuid: this.visitorId,
                session_id: this.sessionId,
                conversation_id: this.conversationId,
            });

            navigator.sendBeacon(
                `${this.apiUrl}/end-chat`,
                new Blob([data], {
                    type: "application/json"
                })
            );
        });

    }

    bindEvents() {

        this.toggle.addEventListener("click", () => this.open());

        this.close.addEventListener("click", () => this.closeWidget());

        this.newChatButton =
            document.getElementById("chatbot-new-chat");

        this.newChatButton.addEventListener(
            "click",
            () => this.newChat()
        );

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
    getVisitorId() {

        if (!this.widgetKey) {
            console.error("Chatbot widget key is missing.");
            return null;
        }

        const storageKey = `chatbot_visitor_uuid_${this.widgetKey}`;

        let visitorId = localStorage.getItem(storageKey);

        if (!visitorId) {
            visitorId = crypto.randomUUID();

            localStorage.setItem(
                storageKey,
                visitorId
            );
        }

        return visitorId;
    }

    getSessionId() {
        return crypto.randomUUID();
    }

    ensureSessionId() {
        if (!this.sessionId) {
            this.sessionId = this.getSessionId();
        }

        return this.sessionId;
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
    async newChat() {

        try {

            if (this.conversationId) {

                await fetch(`${this.apiUrl}/end-chat`, {

                    method: "POST",

                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },

                    body: JSON.stringify({

                        widget_key: this.widgetKey,

                        domain: this.domain,

                        session_id: this.sessionId,

                        visitor_uuid: this.visitorId,

                        conversation_id: this.conversationId,

                    }),

                });

            }

            // Clear current conversation
            this.conversationId = null;
            this.conversationEnded = false;

            localStorage.removeItem("chatbot_conversation");

            // Clear messages
            this.messages.innerHTML = `
            <div id="chatbot-hero">

                <div class="chatbot-hero-icon">
                    <i class="bi bi-robot"></i>
                </div>

                <h2 id="chatbot-hero-title"></h2>

                <div id="chatbot-hero-message"></div>

            </div>
        `;
            const settings = this.settings ?? {};

            document.getElementById("chatbot-hero-title").textContent =
                settings.chatbot_name || "AI Assistant";

            document.getElementById("chatbot-hero-message").textContent =
                settings.welcome_message || "Hey! 👋, how may I help you?";

            // Reset flow
            this.currentStep = 0;

            if (this.flow) {
                this.showCurrentStep();
            }

            this.input.value = "";

        } catch (error) {

            console.error("New chat error:", error);

        }
    }

    applyPosition(position) {

        if (!position) {
            return;
        }

        const horizontal =
            position.horizontal || "right";

        const horizontalValue =
            Number(position.horizontal_value ?? 25);

        const vertical =
            position.vertical || "bottom";

        const verticalValue =
            Number(position.vertical_value ?? 25);


        // Reset all positions
        this.toggle.style.left = "";
        this.toggle.style.right = "";
        this.toggle.style.top = "";
        this.toggle.style.bottom = "";

        this.box.style.left = "";
        this.box.style.right = "";
        this.box.style.top = "";
        this.box.style.bottom = "";


        // Horizontal position
        this.toggle.style[horizontal] =
            `${horizontalValue}px`;

        this.box.style[horizontal] =
            `${horizontalValue + 10}px`;


        // Vertical position
        this.toggle.style[vertical] =
            `${verticalValue}px`;

        this.box.style[vertical] =
            `${verticalValue + 10}px`;
    }

    async init() {
        try {
            const response = await fetch(
                `${this.apiUrl}/init?domain=${encodeURIComponent(this.domain)}&widget_key=${encodeURIComponent(this.widgetKey)}&visitor_uuid=${encodeURIComponent(this.visitorId)}&session_id=${encodeURIComponent(this.sessionId)}`
            );

            const data = await response.json();

            if (!data.success) {

                // Website inactive / unavailable
                this.toggle.style.display = "none";
                this.box.style.display = "none";

                return;
            }

            this.initialized = true;
            this.toggle.style.display = "flex";

            this.settings = data.data.settings ?? {};

            const settings = this.settings;
            this.applyPosition(settings.position);

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

                document.documentElement.style.setProperty(
                    "--primary-color",
                    settings.primary_color
                );

                document.documentElement.style.setProperty(
                    "--user-bg",
                    settings.primary_color
                );

            }

            await this.loadFlow();

        } catch (error) {

            console.error(error);

            this.toggle.style.display = "none";
            this.box.style.display = "none";

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
        this.ensureSessionId();

        const wasConversationEnded =
            this.conversationEnded;

        const oldConversationId =
            this.conversationId;

        this.input.value = "";

        /*
         * If current conversation is still active,
         * show visitor message immediately.
         *
         * If conversation was ended, wait until backend
         * creates the new conversation before displaying
         * the message.
         */
        if (!wasConversationEnded) {
            this.addUserMessage(message);
        }

        this.showTyping();

        try {

            const response = await fetch(
                `${this.apiUrl}/send-message`,
                {
                    method: "POST",

                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },

                    body: JSON.stringify({
                   
                        widget_key: this.widgetKey,

                        domain: this.domain,

                        visitor_uuid: this.visitorId,

                        session_id: this.sessionId,

                        conversation_id:
                            this.conversationId,

                        message: message,

                    }),
                }
            );

            const data = await response.json();

            this.hideTyping();

            if (!data.success) {

                this.addBotMessage(
                    "Something went wrong."
                );

                return;
            }

            /*
             * Backend creates a NEW conversation
             * when old conversation is already ended.
             */
            const newConversationId =
                data.conversation_id;

            if (
                wasConversationEnded &&
                newConversationId !== oldConversationId
            ) {


                this.addUserMessage(message);

                this.conversationEnded = false;
            }

            this.conversationId =
                newConversationId;

            localStorage.setItem(
                "chatbot_conversation",
                this.conversationId
            );

            this.addBotMessage(
                data.response
            );

        } catch (error) {

            this.hideTyping();

            console.error(error);

            this.addBotMessage(
                "Unable to connect to server."
            );
        }

    }
    showCurrentStep() {

        if (!this.flow) return;

        const step = this.flow.steps[this.currentStep];

        if (!step) {

            // Flow finished
            this.addBotMessage(
                "Great! I have all the trip details I need."
            );

            this.showLeadForm();

            return;
        }

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
    async addOptionButtons(options) {

        if (!options || options.length === 0) {
            return;
        }

        const container = document.createElement("div");
        container.className = "chatbot-options";

        const currentStep =
            this.flow?.steps?.[this.currentStep];

        if (!currentStep) {
            return;
        }

        options.forEach(option => {

            const button = document.createElement("button");

            button.type = "button";
            button.className = "chatbot-option";

            const answer =
                option.value ?? option.label;

            button.textContent = answer;

            button.addEventListener("click", async () => {

                this.ensureSessionId();

                // Show selected answer in chat
                this.addUserMessage(answer);

                // Remove option buttons
                container.remove();

                try {

                    const response = await fetch(
                        `${this.apiUrl}/flow-answer`,
                        {
                            method: "POST",

                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                            },

                            body: JSON.stringify({

                                widget_key: this.widgetKey,

                                domain: this.domain,

                                session_id: this.sessionId,

                                visitor_uuid: this.visitorId,

                                conversation_id:
                                    this.conversationId,

                                chatbot_flow_step_id:
                                    currentStep.id,

                                answer: answer,

                            }),
                        }
                    );

                    const data = await response.json();

                    if (!data.success) {

                        console.error(
                            "Flow answer failed:",
                            data
                        );

                        return;
                    }

                    // Save conversation ID
                    this.conversationId =
                        data.conversation_id;

                    localStorage.setItem(
                        "chatbot_conversation",
                        this.conversationId
                    );

                    // Move to next flow step
                    this.currentStep++;

                    this.showCurrentStep();

                } catch (error) {

                    console.error(
                        "Flow answer error:",
                        error
                    );

                }

            });

            container.appendChild(button);

        });

        this.messages.appendChild(container);

        this.scrollBottom();
    }

    showLeadForm() {

        const container = document.createElement("div");

        container.className = "chatbot-lead-form";

        container.innerHTML = `
        <div class="lead-form-title">
            Before we finish, please share your details.
        </div>

        <input
            type="text"
            id="lead-name"
            placeholder="Your name"
        >

        <input
            type="email"
            id="lead-email"
            placeholder="Your email"
        >

        <input
            type="tel"
            id="lead-phone"
            placeholder="Your phone number"
        >

        <button type="button" id="lead-submit">
            Submit
        </button>

        <div id="lead-error"></div>
    `;

        this.messages.appendChild(container);

        this.scrollBottom();

        document
            .getElementById("lead-submit")
            .addEventListener("click", () => {
                this.submitLead();
            });
    }

    async submitLead() {

        const name =
            document.getElementById("lead-name").value.trim();

        const email =
            document.getElementById("lead-email").value.trim();

        const phone =
            document.getElementById("lead-phone").value.trim();

        const error =
            document.getElementById("lead-error");

        error.textContent = "";

        if (!name) {
            error.textContent = "Please enter your name.";
            return;
        }

        if (!email) {
            error.textContent = "Please enter your email.";
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            error.textContent = "Please enter a valid email.";
            return;
        }

        if (!phone) {
            error.textContent = "Please enter your phone number.";
            return;
        }

        this.ensureSessionId();

        try {

            const response = await fetch(
                `${this.apiUrl}/save-lead`,
                {
                    method: "POST",

                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },

                    body: JSON.stringify({
                         name: name,

    email: email,

    phone: phone,


                        widget_key: this.widgetKey,

                        domain: this.domain,

                        session_id: this.sessionId,

                        visitor_uuid: this.visitorId,

                        conversation_id: this.conversationId,


                        notes: null,

                    }),
                }
            );

            const data = await response.json();

            if (!data.success) {
                error.textContent =
                    data.message || "Unable to save your details.";
                return;
            }

            this.addLeadConfirmation(
                name,
                email,
                phone
            );

            const form =
                document.querySelector(".chatbot-lead-form");

            if (form) {
                form.remove();
            }

            this.addBotMessage(
                "Thanks! Your details have been saved. 😊"
            );
            this.showEndChatQuestion();

        } catch (e) {

            console.error(e);

            error.textContent =
                "Unable to connect to server.";

        }
    }

    addLeadConfirmation(name, email, phone) {

        const div = document.createElement("div");

        div.className = "lead-confirmation";

        div.innerHTML = `
        <div class="lead-confirmation-header">
            <div class="lead-confirmation-icon">
                <i class="bi bi-person-fill"></i>
            </div>

            <strong>My Details</strong>
        </div>

        <div class="lead-confirmation-divider"></div>

        <div class="lead-detail">
            <i class="bi bi-person"></i>
            <span>${this.escapeHtml(name)}</span>
        </div>

        <div class="lead-detail">
            <i class="bi bi-envelope-fill"></i>
            <span>${this.escapeHtml(email)}</span>
        </div>

        <div class="lead-detail">
            <i class="bi bi-telephone-fill"></i>
            <span>${this.escapeHtml(phone)}</span>
        </div>
    `;

        this.messages.appendChild(div);

        this.scrollBottom();
    }
    escapeHtml(value) {

        const div = document.createElement("div");

        div.textContent = value;

        return div.innerHTML;
    }

    showEndChatQuestion() {

        const div = document.createElement("div");

        div.className = "end-chat-question";

        div.innerHTML = `
        <div class="bot-message">
            Would you like to end this chat?
        </div>

        <div class="end-chat-options">

            <button
                type="button"
                class="end-chat-option"
                data-action="yes"
            >
                Yes, end chat
            </button>

            <button
                type="button"
                class="end-chat-option"
                data-action="no"
            >
                No, continue
            </button>

        </div>
    `;

        this.messages.appendChild(div);

        this.scrollBottom();

        const buttons =
            div.querySelectorAll(".end-chat-option");

        buttons.forEach(button => {

            button.addEventListener("click", () => {

                const action =
                    button.dataset.action;

                const selectedText = button.textContent.trim();

                // Don't remove the question.
                // Just remove the option buttons.
                const options =
                    div.querySelector(".end-chat-options");

                if (options) {
                    options.remove();
                }

                // Show selected option as visitor message
                this.addUserMessage(selectedText);

                if (action === "yes") {
                    this.confirmEndChat();
                } else {
                    this.continueChat();
                }

            });

        });
    }

    continueChat() {

        this.addBotMessage(
            "Sure! 😊 What else can I help you with?"
        );

        this.conversationEnded = false;

        this.scrollBottom();
    }

    async confirmEndChat() {

        if (!this.conversationId) {
            return;
        }
        this.ensureSessionId();
        try {

            const response = await fetch(
                `${this.apiUrl}/end-chat`,
                {
                    method: "POST",

                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },

                    body: JSON.stringify({
    
                        widget_key: this.widgetKey,

                        domain: this.domain,

                        session_id: this.sessionId,
                        visitor_uuid: this.visitorId,

                        conversation_id: this.conversationId,

                    }),
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {

                this.addBotMessage(
                    "Sorry, I couldn't end the conversation."
                );

                return;
            }

            this.conversationEnded = true;

            this.addBotMessage(
                "Your conversation has ended. Thank you for chatting with us! 😊"
            );

            this.addConversationDivider();

        } catch (error) {

            console.error("End chat error:", error);

            this.addBotMessage(
                "Unable to end the conversation."
            );
        }
    }

    addConversationDivider() {

        const divider = document.createElement("div");

        divider.className = "conversation-divider";

        divider.innerHTML = `
        <span>Conversation ended</span>
    `;

        this.messages.appendChild(divider);

        this.scrollBottom();
    }

}

new Chatbot();