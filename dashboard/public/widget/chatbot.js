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

    <div id="chatbot-live-chat-actions">
        <button type="button" id="chatbot-talk-agent">
            <span class="chatbot-talk-agent-icon" aria-hidden="true">
                <i class="bi bi-person-fill"></i>
            </span>
            <span class="chatbot-talk-agent-content">
                <span class="chatbot-talk-agent-heading">
                    Want to talk to a human?
                </span>
                <span id="chatbot-talk-agent-label" class="chatbot-talk-agent-cta">
                    Connect with our support team.
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </span>
            </span>
        </button>
    </div>

    <div id="chatbot-input-area">

        <button type="button" id="chatbot-attach" title="Attach file">
            <i class="bi bi-paperclip"></i>
        </button>

        <input
            type="file"
            id="chatbot-attachment"
            accept="image/jpeg,image/png,image/webp,application/pdf,video/mp4,video/webm,video/quicktime,video/ogg"
            hidden
        >

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

</div>
            `
        );
    }

    constructor() {

        this.createWidget();

        this.apiUrl = window.ChatbotConfig?.apiUrl || "/api/widget";
        this.domain = window.ChatbotConfig?.domain || window.location.hostname;

        this.widgetKey =
            window.ChatbotConfig?.widgetKey || null;

        this.showToggleOnError =
            window.ChatbotConfig?.showToggleOnError || false;

        this.visitorId =
            this.getVisitorId();

        this.sessionId =
            this.getSessionId();

        this.conversationId = null;

        this.conversationStatus = "active";

        this.conversationMode = "ai";

        this.liveChatRequested = false;

        this.conversationEnded = false;

        this.pendingLiveChatRating = null;

        this.pendingNewChatAfterRating = false;

        this.realtimeConfig = null;

        this.echo = null;

        this.liveChannel = null;

        this.liveChannelName = null;

        this.agentTypingTimeout = null;

        this.visitorTypingThrottle = null;

        this.visitorTypingIdleTimer = null;

        this.visitorTypingActive = false;

        this.selectedAttachment = null;

        this.renderedMessageIds = new Set();

        this.renderedHistoryIds = new Set();

        this.toggle =
            document.getElementById("chatbot-toggle");

        this.box =
            document.getElementById("chatbot-box");

        this.close =
            document.getElementById("chatbot-close");

        this.messages =
            document.getElementById("chatbot-messages");

        this.input =
            document.getElementById("chatbot-input");

        this.send =
            document.getElementById("chatbot-send");

        this.attachButton =
            document.getElementById("chatbot-attach");

        this.attachmentInput =
            document.getElementById("chatbot-attachment");

        this.talkAgentButton =
            document.getElementById("chatbot-talk-agent");

        this.talkAgentButtonLabel =
            document.getElementById("chatbot-talk-agent-label");

        this.liveChatActions =
            document.getElementById("chatbot-live-chat-actions");

        this.initialized = false;

        // Hide widget until website is verified
        this.toggle.style.display = "none";
        this.box.style.display = "none";

        this.bindEvents();

        // Check website immediately
        this.init();
    }

    bindEvents() {

        this.toggle.addEventListener(
            "click",
            () => this.open()
        );

        this.close.addEventListener(
            "click",
            () => this.closeWidget()
        );

        this.newChatButton =
            document.getElementById(
                "chatbot-new-chat"
            );

        this.newChatButton.addEventListener(
            "click",
            () => this.newChat()
        );

        this.send.addEventListener(
            "click",
            (e) => {

                e.preventDefault();

                this.sendMessage();
            }
        );

        this.input.addEventListener(
            "keypress",
            (e) => {

                if (e.key === "Enter") {

                    e.preventDefault();

                    this.sendMessage();
                }
            }
        );

        this.input.addEventListener(
            "input",
            () => this.handleVisitorInputTyping()
        );

        this.attachButton.addEventListener(
            "click",
            () => this.attachmentInput.click()
        );

        this.attachmentInput.addEventListener(
            "change",
            () => this.handleAttachmentSelection()
        );

        this.talkAgentButton.addEventListener(
            "click",
            (e) => {
                e.preventDefault();

                this.requestLiveChat();
            }
        );
    }

    getVisitorId() {

        if (!this.widgetKey) {

            console.error(
                "Chatbot widget key is missing."
            );

            return null;
        }

        const storageKey =
            `chatbot_visitor_uuid_${this.widgetKey}`;

        let visitorId =
            localStorage.getItem(storageKey);

        if (!visitorId) {

            visitorId =
                crypto.randomUUID();

            localStorage.setItem(
                storageKey,
                visitorId
            );
        }

        return visitorId;
    }

    getSessionId() {

        const storageKey =
            `chatbot_session_${this.widgetKey}`;

        let sessionId =
            localStorage.getItem(storageKey);

        if (!sessionId) {

            sessionId =
                crypto.randomUUID();

            localStorage.setItem(
                storageKey,
                sessionId
            );
        }

        return sessionId;
    }

    resetSessionId() {

        const storageKey =
            `chatbot_session_${this.widgetKey}`;

        const sessionId =
            crypto.randomUUID();

        localStorage.setItem(
            storageKey,
            sessionId
        );

        return sessionId;
    }

    ensureSessionId() {

        if (!this.sessionId) {

            this.sessionId =
                this.getSessionId();
        }

        return this.sessionId;
    }

    getConversationStorageKey() {

        return `chatbot_conversation_${this.widgetKey}`;
    }

    getStoredConversationId() {

        return localStorage.getItem(
            this.getConversationStorageKey()
        ) || localStorage.getItem(
            "chatbot_conversation"
        );
    }

    storeConversationId(conversationId) {

        if (!conversationId) {
            return;
        }

        localStorage.setItem(
            this.getConversationStorageKey(),
            conversationId
        );

        localStorage.setItem(
            "chatbot_conversation",
            conversationId
        );
    }

    clearStoredConversationId() {

        localStorage.removeItem(
            this.getConversationStorageKey()
        );

        localStorage.removeItem(
            "chatbot_conversation"
        );
    }

    getNewChatAfterRatingStorageKey() {

        return `chatbot_new_chat_after_rating_${this.widgetKey}`;
    }

    shouldStartNewChatAfterRating() {

        return localStorage.getItem(
            this.getNewChatAfterRatingStorageKey()
        ) === "1";
    }

    storeNewChatAfterRating() {

        localStorage.setItem(
            this.getNewChatAfterRatingStorageKey(),
            "1"
        );
    }

    clearNewChatAfterRating() {

        localStorage.removeItem(
            this.getNewChatAfterRatingStorageKey()
        );
    }

    renderWelcomeHero() {

        this.messages.innerHTML = `
            <div id="chatbot-hero">

                <div class="chatbot-hero-icon">
                    <i class="bi bi-robot"></i>
                </div>

                <h2 id="chatbot-hero-title"></h2>

                <div id="chatbot-hero-message"></div>

            </div>
        `;

        this.applyWelcomeHeroText();
    }

    applyWelcomeHeroText() {

        const settings =
            this.settings ?? {};

        const heroTitle =
            document.getElementById(
                "chatbot-hero-title"
            );

        const heroMessage =
            document.getElementById(
                "chatbot-hero-message"
            );

        if (heroTitle) {
            heroTitle.textContent =
                settings.chatbot_name ||
                "AI Assistant";
        }

        if (heroMessage) {
            heroMessage.textContent =
                settings.welcome_message ||
                "Hey! How may I help you?";
        }
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

    async endCurrentConversation() {

        if (
            !this.conversationId ||
            this.conversationEnded
        ) {
            return;
        }

        try {

            await fetch(
                `${this.apiUrl}/end-chat`,
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/json",

                        "Accept":
                            "application/json",
                    },

                    body: JSON.stringify({

                        widget_key:
                            this.widgetKey,

                        domain:
                            this.domain,

                        visitor_uuid:
                            this.visitorId,

                        session_id:
                            this.sessionId,

                        conversation_id:
                            this.conversationId,
                    }),
                }
            );

            this.conversationEnded = true;

            this.clearStoredConversationId();

        } catch (error) {

            console.error(
                "End chat error:",
                error
            );
        }
    }

    async newChat() {

        try {
            const isActiveHumanLiveChat =
                this.conversationStatus === "live_active" &&
                this.conversationMode === "live";

            if (this.conversationId) {
                const pendingBefore =
                    await this.fetchPendingLiveChatRating();

                if (
                    pendingBefore &&
                    this.conversationStatus !== "live_active"
                ) {
                    this.showLiveChatRatingCard(
                        pendingBefore,
                        () => this.completeNewChatAfterRating(true),
                        {
                            requireRating:
                                this.shouldStartNewChatAfterRating(),
                        }
                    );
                    return;
                }
            }

            let pendingAfterClose = null;
            let closeSucceeded = false;
            if (
                this.conversationId &&
                !this.conversationEnded
            ) {

                const response = await fetch(
                    `${this.apiUrl}/end-chat`,
                    {
                        method: "POST",

                        headers: {
                            "Content-Type":
                                "application/json",

                            "Accept":
                                "application/json",
                        },

                        body: JSON.stringify({

                            widget_key:
                                this.widgetKey,

                            domain:
                                this.domain,

                            session_id:
                                this.sessionId,

                            visitor_uuid:
                                this.visitorId,

                            conversation_id:
                                this.conversationId,
                        }),
                    }
                );

                const data = await response.json();

                if (response.ok && data.success) {
                    closeSucceeded = true;
                    pendingAfterClose = data.pending_rating || null;
                }
            }

            if (pendingAfterClose) {
                if (isActiveHumanLiveChat) {
                    this.storeNewChatAfterRating();
                }

                this.showLiveChatRatingCard(
                    pendingAfterClose,
                    () => this.completeNewChatAfterRating(false),
                    {
                        requireRating: isActiveHumanLiveChat,
                    }
                );
                return;
            }

            if (isActiveHumanLiveChat && !closeSucceeded) {
                this.addStatusMessage(
                    "Unable to close the live chat. Please try again."
                );

                return;
            }

            if (isActiveHumanLiveChat && !pendingAfterClose) {
                this.addStatusMessage(
                    "Unable to load the feedback form. Please try again."
                );

                return;
            }

            this.clearNewChatAfterRating();

            await this.startNewChat();

        } catch (error) {

            console.error(
                "New chat error:",
                error
            );
        }
    }

    async startNewChat() {

        try {
            this.clearNewChatAfterRating();

            this.conversationId = null;

            this.conversationStatus = "active";

            this.conversationMode = "ai";

            this.liveChatRequested = false;

            this.conversationEnded = false;

            this.clearSelectedAttachment();

            this.unsubscribeRealtime();

            this.renderedMessageIds.clear();
            this.renderedHistoryIds.clear();

            this.sessionId =
                this.resetSessionId();

            this.clearStoredConversationId();

            this.messages.innerHTML = `
                <div id="chatbot-hero">

                    <div class="chatbot-hero-icon">
                        <i class="bi bi-robot"></i>
                    </div>

                    <h2 id="chatbot-hero-title"></h2>

                    <div id="chatbot-hero-message"></div>

                </div>
            `;

            const settings =
                this.settings ?? {};

            document
                .getElementById(
                    "chatbot-hero-title"
                )
                .textContent =
                    settings.chatbot_name ||
                    "AI Assistant";

            document
                .getElementById(
                    "chatbot-hero-message"
                )
                .textContent =
                    settings.welcome_message ||
                    "Hey! 👋, how may I help you?";

            this.currentStep = 0;

            await this.loadFlow();

            this.input.value = "";

            this.updateLiveChatControls();

        } catch (error) {

            console.error(
                "New chat error:",
                error
            );
        }
    }

    async completeNewChatAfterRating(shouldEndConversation) {
        this.clearNewChatAfterRating();

        if (
            shouldEndConversation &&
            this.conversationId &&
            !this.conversationEnded
        ) {
            await this.endCurrentConversation();
        }

        await this.startNewChat();
    }

    applyPosition(position) {

        if (!position) {
            return;
        }

        const horizontal =
            position.horizontal || "right";

        const horizontalValue =
            Number(
                position.horizontal_value ?? 25
            );

        const vertical =
            position.vertical || "bottom";

        const verticalValue =
            Number(
                position.vertical_value ?? 25
            );

        this.toggle.style.left = "";
        this.toggle.style.right = "";
        this.toggle.style.top = "";
        this.toggle.style.bottom = "";

        this.box.style.left = "";
        this.box.style.right = "";
        this.box.style.top = "";
        this.box.style.bottom = "";

        this.toggle.style[horizontal] =
            `${horizontalValue}px`;

        this.box.style[horizontal] =
            `${horizontalValue + 10}px`;

        this.toggle.style[vertical] =
            `${verticalValue}px`;

        this.box.style[vertical] =
            `${verticalValue + 10}px`;
    }

    async init() {

        try {

            const response = await fetch(
                `${this.apiUrl}/init` +
                `?domain=${encodeURIComponent(this.domain)}` +
                `&widget_key=${encodeURIComponent(this.widgetKey)}` +
                `&visitor_uuid=${encodeURIComponent(this.visitorId)}` +
                `&session_id=${encodeURIComponent(this.sessionId)}`
            );

            const data =
                await response.json();

            if (!data.success) {

                this.toggle.style.display =
                    this.showToggleOnError ? "flex" : "none";

                this.box.style.display =
                    "none";

                return;
            }

            this.initialized = true;

            this.toggle.style.display =
                "flex";

            this.settings =
                data.data.settings ?? {};

            this.realtimeConfig =
                data.data.realtime ?? null;

            const settings =
                this.settings;

            this.applyPosition(
                settings.position
            );

            document
                .getElementById(
                    "chatbot-title"
                )
                .textContent =
                    settings.chatbot_name ||
                    "AI Assistant";

            document
                .getElementById(
                    "chatbot-hero-title"
                )
                .textContent =
                    settings.chatbot_name ||
                    "AI Assistant";

            document
                .getElementById(
                    "chatbot-hero-message"
                )
                .textContent =
                    settings.welcome_message ||
                    "Hey! 👋, how may I help you?";

            document
                .getElementById(
                    "chatbot-input"
                )
                .placeholder =
                    settings.placeholder ||
                    "Type your message...";

            if (settings.primary_color) {

                document.documentElement
                    .style
                    .setProperty(
                        "--primary-color",
                        settings.primary_color
                    );

                document.documentElement
                    .style
                    .setProperty(
                        "--user-bg",
                        settings.primary_color
                    );
            }

            this.updateLiveChatControls();

            const restoredConversation =
                await this.restoreStoredConversationState();

            if (!restoredConversation) {
                await this.loadFlow();
            }

        } catch (error) {

            console.error(error);

            this.toggle.style.display =
                this.showToggleOnError ? "flex" : "none";

            this.box.style.display =
                "none";
        }
    }

    async loadFlow(answeredStepIds = []) {

        try {

            const response = await fetch(
                `${this.apiUrl}/flow` +
                `?domain=${encodeURIComponent(this.domain)}` +
                `&widget_key=${encodeURIComponent(this.widgetKey)}`
            );

            const data =
                await response.json();

            if (!data.success) {
                return;
            }

            this.flow =
                data.flow;

            const answered =
                new Set(
                    answeredStepIds.map(id => String(id))
                );

            this.currentStep = Array.isArray(this.flow?.steps)
                ? this.flow.steps.findIndex(step =>
                    !answered.has(String(step.id))
                )
                : 0;

            if (this.currentStep < 0) {
                return;
            }

            this.showCurrentStep();

        } catch (error) {

            console.error(error);
        }
    }

    async sendMessage() {

        const message =
            this.input.value.trim();
        const attachment =
            this.selectedAttachment;

        if (!message && !attachment) {
            return;
        }

        if (!attachment && await this.submitMatchingFlowTextAnswer(message)) {
            this.input.value = "";
            return;
        }

        if (
            attachment &&
            (
                this.conversationStatus !== "live_active" ||
                this.conversationMode !== "live"
            )
        ) {
            this.addStatusMessage(
                "Attachments are only available during live chat."
            );
            return;
        }

        this.ensureSessionId();

        const wasConversationEnded =
            this.conversationEnded;

        const oldConversationId =
            this.conversationId;

        this.input.value = "";

        this.stopVisitorTyping(true);

        const isLiveChatSend =
            ["waiting_agent", "live_active"].includes(
                this.conversationStatus
            ) || this.conversationMode === "live";

        let sentMessageBubble = null;

        if (!wasConversationEnded && !attachment) {

            sentMessageBubble =
                this.addUserMessage(
                    message,
                    null,
                    null,
                    isLiveChatSend
                        ? { receipt: "sent" }
                        : {}
                );
        }

        this.showTyping();

        try {

            const payload = new FormData();
            payload.append("widget_key", this.widgetKey);
            payload.append("domain", this.domain);
            payload.append("visitor_uuid", this.visitorId);
            payload.append("session_id", this.sessionId);
            payload.append("message", message);

            if (this.conversationId) {
                payload.append("conversation_id", this.conversationId);
            }

            if (attachment) {
                payload.append("attachment", attachment);
            }

            const response = await fetch(
                `${this.apiUrl}/send-message`,
                {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                    },
                    body: payload,
                }
            );

            const data =
                await response.json();

            this.hideTyping();

            if (!data.success) {

                this.addBotMessage(
                    data.message || "Something went wrong."
                );

                return;
            }

            const newConversationId =
                data.conversation_id;

            if (data.attachment?.view_url === null && data.message_id) {
                data.attachment.view_url =
                    this.attachmentViewUrl(data.message_id);
            }

            if (
                attachment ||
                (
                    wasConversationEnded &&
                    newConversationId !== oldConversationId
                )
            ) {

                sentMessageBubble =
                    this.addUserMessage(
                        message,
                        null,
                        data.attachment || null,
                        isLiveChatSend
                            ? { receipt: "sent" }
                            : {}
                    );

                this.conversationEnded =
                    false;
            }

            this.clearSelectedAttachment();

            this.conversationId =
                newConversationId;

            this.applyConversationState(data);

            if (data.message_id) {
                this.renderedMessageIds.add(
                    String(data.message_id)
                );
            }

            if (sentMessageBubble && isLiveChatSend) {
                sentMessageBubble.dataset.messageId =
                    data.message_id || "";

                this.updateMessageReceipt(
                    sentMessageBubble,
                    "delivered"
                );
            }

            this.storeConversationId(
                this.conversationId
            );

            const aiResponse =
                data.response ?? data.ai_response;

            if (aiResponse) {
                this.addBotMessage(
                    aiResponse
                );
            } else if (data.message_saved && !isLiveChatSend) {
                this.addStatusMessage(
                    data.message ||
                    this.getConversationStatusText()
                );
            }

        } catch (error) {

            this.hideTyping();

            console.error(error);

            this.addBotMessage(
                "Unable to connect to server."
            );
        }
    }

    applyConversationState(data) {

        if (data.status) {
            this.conversationStatus = data.status;
        }

        if (data.mode) {
            this.conversationMode = data.mode;
        }

        if (
            this.conversationStatus !== "live_active" ||
            this.conversationMode !== "live"
        ) {
            this.stopVisitorTyping(true);
        }

        this.liveChatRequested = [
            "waiting_agent",
            "live_active",
        ].includes(this.conversationStatus);

        if (this.conversationStatus === "closed") {
            this.conversationEnded = true;
        }

        this.updateLiveChatControls();

        this.syncRealtimeSubscription();
    }

    async loadRealtimeScripts() {

        if (window.Pusher && window.Echo) {
            return true;
        }

        const loadScript = (src) =>
            new Promise((resolve, reject) => {
                const existing =
                    document.querySelector(
                        `script[src="${src}"]`
                    );

                if (existing) {
                    existing.addEventListener(
                        "load",
                        () => resolve()
                    );
                    existing.addEventListener(
                        "error",
                        () => reject()
                    );
                    return;
                }

                const script =
                    document.createElement("script");

                script.src = src;
                script.async = true;
                script.onload = () => resolve();
                script.onerror = () => reject();

                document.head.appendChild(script);
            });

        try {
            if (!window.Pusher) {
                await loadScript(
                    "https://js.pusher.com/8.4.0/pusher.min.js"
                );
            }

            if (!window.Echo) {
                await loadScript(
                    "https://cdn.jsdelivr.net/npm/laravel-echo@2.2.4/dist/echo.iife.js"
                );
            }

            return !!(window.Pusher && window.Echo);
        } catch (error) {
            console.warn(
                "Realtime scripts unavailable; HTTP chat remains active.",
                error
            );

            return false;
        }
    }

    async restoreStoredConversationState() {

        const storedConversationId =
            this.getStoredConversationId();

        if (!storedConversationId) {
            return false;
        }

        try {
            const response = await fetch(
                `${this.apiUrl}/conversation-state`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type":
                            "application/json",
                        "Accept":
                            "application/json",
                    },
                    body: JSON.stringify({
                        widget_key:
                            this.widgetKey,
                        domain:
                            this.domain,
                        visitor_uuid:
                            this.visitorId,
                        session_id:
                            this.sessionId,
                        conversation_id:
                            storedConversationId,
                    }),
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                this.clearStoredConversationId();
                return false;
            }

            const shouldRestoreConversation =
                data.status === "waiting_agent" ||
                (
                    data.status === "live_active" &&
                    data.mode === "live"
                );

            if (data.pending_rating) {
                const shouldStartNewChatAfterRating =
                    this.shouldStartNewChatAfterRating();

                this.conversationId =
                    data.conversation_id;

                this.renderConversationMessages(
                    data.messages
                );

                this.applyConversationState(data);

                this.showLiveChatRatingCard(
                    data.pending_rating,
                    shouldStartNewChatAfterRating
                        ? () => this.completeNewChatAfterRating(false)
                        : null,
                    {
                        requireRating: shouldStartNewChatAfterRating,
                    }
                );

                return true;
            }

            if (!shouldRestoreConversation) {
                this.conversationId = null;
                this.conversationStatus = "active";
                this.conversationMode = "ai";
                this.liveChatRequested = false;
                this.conversationEnded = false;
                this.clearSelectedAttachment();
                this.unsubscribeRealtime();
                this.clearStoredConversationId();
                this.renderWelcomeHero();
                await this.loadFlow();

                return true;
            }

            this.conversationId =
                data.conversation_id;

            this.renderConversationMessages(
                data.messages
            );

            this.applyConversationState(data);

            if (data.status === "closed") {
                this.clearStoredConversationId();
            }

            return true;
        } catch (error) {
            console.warn(
                "Unable to restore realtime conversation.",
                error
            );

            return false;
        }
    }

    renderConversationMessages(messages) {

        if (!Array.isArray(messages)) {
            return;
        }

        this.renderWelcomeHero();
        this.renderedMessageIds.clear();
        this.renderedHistoryIds.clear();

        if (messages.length === 0) {
            return;
        }

        messages.forEach(message => {
            this.addStoredMessage(message);
        });

        this.scrollBottom();
    }

    addStoredMessage(message) {

        const historyKey =
            message.history_key ||
            (
                message.id
                    ? `chat-message-${message.id}`
                    : null
            );

        if (
            historyKey &&
            this.renderedHistoryIds.has(historyKey)
        ) {
            return;
        }

        if (historyKey) {
            this.renderedHistoryIds.add(historyKey);
        }

        if (message.type === "flow_question") {
            this.addHistoricalFlowQuestion(message);
            return;
        }

        if (message.type === "flow_answer") {
            this.addUserMessage(
                message.message,
                message.created_at
            );
            return;
        }

        const senderType =
            message.sender_type || "bot";

        const div =
            document.createElement("div");

        div.dataset.messageId =
            message.id;

        if (message.id) {
            this.renderedMessageIds.add(
                String(message.id)
            );
        }

        if (
            ["visitor", "user"].includes(senderType)
        ) {
            div.className = "user-message";
        } else if (senderType === "system") {
            div.className = "chatbot-status-message";
        } else {
            div.className = "bot-message";
        }

        if (message.message) {
            const text = document.createElement("div");
            text.textContent =
                message.message;
            div.appendChild(text);
        }

        if (message.attachment) {
            div.appendChild(
                this.renderAttachment(message.attachment)
            );
        }

        const browserTimestamp =
            this.formatBrowserTimestamp(
                message.created_at
            );

        if (browserTimestamp) {
            div.title = browserTimestamp;
        }

        this.messages.appendChild(div);
    }

    addHistoricalFlowQuestion(message) {

        this.addBotMessage(
            message.message,
            message.created_at
        );

        if (
            !Array.isArray(message.options) ||
            message.options.length === 0
        ) {
            return;
        }

        const container =
            document.createElement("div");

        container.className =
            "chatbot-options chatbot-options-history";

        message.options.forEach(option => {
            const button =
                document.createElement("button");

            button.type =
                "button";

            button.disabled =
                true;

            button.className =
                "chatbot-option chatbot-option-history";

            button.textContent =
                option.text || option.value || option.label || "";

            if (option.selected) {
                button.classList.add("selected");
                button.setAttribute("aria-pressed", "true");
            }

            container.appendChild(button);
        });

        this.messages.appendChild(container);
    }

    async syncRealtimeSubscription() {

        if (
            !this.realtimeConfig?.enabled ||
            !this.conversationId ||
            ![
                "waiting_agent",
                "live_active",
            ].includes(this.conversationStatus)
        ) {
            this.unsubscribeRealtime();
            return;
        }

        const channelName =
            `live-chat.${this.conversationId}`;

        if (this.liveChannelName === channelName) {
            return;
        }

        this.unsubscribeRealtime();

        if (!await this.loadRealtimeScripts()) {
            return;
        }

        const EchoCtor =
            window.Echo.default || window.Echo;

        if (!this.echo) {
            this.echo = new EchoCtor({
                broadcaster: "reverb",
                key: this.realtimeConfig.key,
                cluster: "mt1",
                wsHost: this.realtimeConfig.host,
                wsPort: this.realtimeConfig.port,
                wssPort: this.realtimeConfig.port,
                forceTLS:
                    this.realtimeConfig.scheme === "https",
                enabledTransports:
                    this.realtimeConfig.scheme === "https"
                        ? ["wss"]
                        : ["ws"],
                disableStats: true,
                authorizer: (channel) => ({
                    authorize: (socketId, callback) => {
                        fetch(
                            this.realtimeConfig.auth_endpoint,
                            {
                                method: "POST",
                                headers: {
                                    "Content-Type":
                                        "application/json",
                                    "Accept":
                                        "application/json",
                                },
                                body: JSON.stringify({
                                    widget_key:
                                        this.widgetKey,
                                    domain:
                                        this.domain,
                                    visitor_uuid:
                                        this.visitorId,
                                    session_id:
                                        this.sessionId,
                                    conversation_id:
                                        this.conversationId,
                                    socket_id:
                                        socketId,
                                    channel_name:
                                        channel.name,
                                }),
                            }
                        )
                            .then(response => response.json())
                            .then(data => callback(null, data))
                            .catch(error => callback(error));
                    },
                }),
            });
        }

        this.liveChannelName = channelName;

        this.liveChannel =
            this.echo.private(channelName)
                .listen(
                    ".LiveChatMessageSent",
                    (event) => this.handleRealtimeMessage(event)
                )
                .listen(
                    ".AgentJoinedConversation",
                    (event) => this.handleAgentJoined(event)
                )
                .listen(
                    ".LiveChatClosed",
                    (event) => this.handleLiveChatClosed(event)
                )
                .listen(
                    ".LiveChatMessagesRead",
                    (event) => this.handleLiveChatMessagesRead(event)
                )
                .listenForWhisper(
                    "agent_typing",
                    (event) => this.handleAgentTyping(event)
                )
                .listenForWhisper(
                    "messages_read",
                    (event) => this.handleLiveChatMessagesRead(event)
                );
    }

    unsubscribeRealtime() {

        this.stopVisitorTyping(true);

        if (this.echo && this.liveChannelName) {
            this.echo.leave(
                `private-${this.liveChannelName}`
            );
        }

        this.liveChannel = null;
        this.liveChannelName = null;
    }

    handleRealtimeMessage(event) {

        if (
            String(event.conversation_id) !==
            String(this.conversationId)
        ) {
            return;
        }

        if (
            event.message_id &&
            this.renderedMessageIds.has(
                String(event.message_id)
            )
        ) {
            return;
        }

        if (event.message_id) {
            this.renderedMessageIds.add(
                String(event.message_id)
            );
        }

        if (event.sender_type === "agent") {
            this.addBotMessage(
                event.message,
                event.created_at,
                event.attachment || null
            );
        }
    }

    handleVisitorInputTyping() {

        if (!this.input.value.trim()) {
            this.stopVisitorTyping(true);
            return;
        }

        this.sendVisitorTyping();
    }

    sendVisitorTyping() {

        if (
            !this.liveChannel ||
            this.conversationStatus !== "live_active" ||
            this.conversationMode !== "live"
        ) {
            this.stopVisitorTyping(false);
            return;
        }

        if (!this.visitorTypingActive || !this.visitorTypingThrottle) {
            this.emitVisitorTyping(true);
            this.visitorTypingActive = true;

            clearTimeout(this.visitorTypingThrottle);
            this.visitorTypingThrottle = setTimeout(
                () => {
                    this.visitorTypingThrottle = null;
                },
                1000
            );
        }

        clearTimeout(this.visitorTypingIdleTimer);

        this.visitorTypingIdleTimer = setTimeout(
            () => this.stopVisitorTyping(true),
            2000
        );
    }

    stopVisitorTyping(sendStop = false) {

        clearTimeout(this.visitorTypingIdleTimer);
        this.visitorTypingIdleTimer = null;
        clearTimeout(this.visitorTypingThrottle);
        this.visitorTypingThrottle = null;

        if (sendStop && this.visitorTypingActive) {
            this.emitVisitorTyping(false);
        }

        this.visitorTypingActive = false;
    }

    emitVisitorTyping(isTyping) {

        if (!this.liveChannel || !this.conversationId) {
            return;
        }

        try {
            this.liveChannel.whisper(
                "visitor_typing",
                {
                    conversation_id: this.conversationId,
                    typing: isTyping,
                }
            );
        } catch (error) {
            console.warn(
                "Unable to send typing indicator.",
                error
            );
        }
    }

    handleAgentTyping(event) {

        if (
            event?.conversation_id &&
            String(event.conversation_id) !==
                String(this.conversationId)
        ) {
            return;
        }

        if (this.conversationStatus !== "live_active") {
            return;
        }

        this.showLiveTyping();
    }

    handleAgentJoined(event) {

        if (
            String(event.conversation_id) !==
            String(this.conversationId)
        ) {
            return;
        }

        this.applyConversationState({
            status: event.status || "live_active",
            mode: event.mode || "live",
        });

        this.addStatusMessage(
            event.agent_name
                ? `Connected to ${event.agent_name}.`
                : "Connected to support team."
        );
    }

    handleLiveChatClosed(event) {

        if (
            String(event.conversation_id) !==
            String(this.conversationId)
        ) {
            return;
        }

        this.hideLiveTyping();

        if (event.live_chat_ended || event.status === "active") {
            this.applyConversationState({
                status: event.status || "active",
                mode: event.mode || "ai",
            });

            this.conversationEnded = false;

            this.addStatusMessage(
                event.message ||
                "Live chat ended. You can continue chatting or connect to support team again."
            );

            if (event.pending_rating) {
                this.showLiveChatRatingCard(
                    event.pending_rating
                );
            }

            this.storeConversationId(
                this.conversationId
            );

            this.unsubscribeRealtime();
            this.updateLiveChatControls();

            return;
        }

        this.applyConversationState({
            status: event.status || "closed",
            mode: event.mode || this.conversationMode,
        });

        this.addStatusMessage(
            event.message || "Conversation closed."
        );

        if (event.pending_rating) {
            this.showLiveChatRatingCard(
                event.pending_rating
            );
        }

        this.unsubscribeRealtime();
    }

    async fetchPendingLiveChatRating() {

        if (!this.conversationId) {
            return null;
        }

        try {
            const response = await fetch(
                `${this.apiUrl}/live-chat-rating/pending`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },
                    body: JSON.stringify(
                        this.conversationPayload()
                    ),
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                return null;
            }

            return data.pending_rating || null;
        } catch (error) {
            console.warn(
                "Unable to load live chat rating state.",
                error
            );

            return null;
        }
    }

    conversationPayload(extra = {}) {
        return {
            widget_key: this.widgetKey,
            domain: this.domain,
            visitor_uuid: this.visitorId,
            session_id: this.sessionId,
            conversation_id: this.conversationId,
            ...extra,
        };
    }

    showLiveChatRatingCard(
        pendingRating,
        afterComplete = null,
        options = {}
    ) {

        if (!pendingRating || !pendingRating.session_id) {
            if (afterComplete) {
                afterComplete();
            }
            return;
        }

        const existing = this.messages.querySelector(
            `[data-live-chat-rating-id="${pendingRating.session_id}"]`
        );

        if (existing) {
            existing.scrollIntoView({
                behavior: "smooth",
                block: "nearest",
            });
            return;
        }

        this.pendingLiveChatRating = pendingRating;

        const requireRating =
            options.requireRating === true;

        const card = document.createElement("div");
        card.className = "chatbot-rating-card";
        card.dataset.liveChatRatingId = pendingRating.session_id;
        card.innerHTML = `
            <div class="chatbot-rating-title">
                How was your support experience?
            </div>
            <div class="chatbot-rating-stars" role="radiogroup" aria-label="Support rating">
                ${[1, 2, 3, 4, 5].map((value) => `
                    <button type="button" class="chatbot-rating-star" data-rating="${value}" aria-label="${value} star">
                        <i class="bi bi-star"></i>
                    </button>
                `).join("")}
            </div>
            <textarea
                class="chatbot-rating-feedback"
                maxlength="2000"
                placeholder="Tell us more about your experience (optional)"
            ></textarea>
            <div class="chatbot-rating-actions">
                <button type="button" class="chatbot-rating-submit" disabled>
                    Submit Feedback
                </button>
                ${requireRating
                    ? ""
                    : `<button type="button" class="chatbot-rating-skip">
                        Skip
                    </button>`}
            </div>
            <div class="chatbot-rating-error" hidden></div>
        `;

        this.messages.appendChild(card);
        this.scrollBottom();

        let selectedRating = null;
        const stars = card.querySelectorAll(
            ".chatbot-rating-star"
        );
        const submitButton = card.querySelector(
            ".chatbot-rating-submit"
        );
        const feedbackInput = card.querySelector(
            ".chatbot-rating-feedback"
        );

        const updateStars = () => {
            stars.forEach((star) => {
                const active =
                    Number(star.dataset.rating) <= selectedRating;
                star.classList.toggle("selected", active);
                const icon = star.querySelector("i");
                if (icon) {
                    icon.className = active
                        ? "bi bi-star-fill"
                        : "bi bi-star";
                }
            });
            submitButton.disabled = !selectedRating;
        };

        stars.forEach((star) => {
            star.addEventListener("click", () => {
                selectedRating = Number(star.dataset.rating);
                updateStars();
            });
        });

        submitButton.addEventListener("click", async () => {
            await this.submitLiveChatRating(
                card,
                pendingRating.session_id,
                selectedRating,
                feedbackInput.value,
                afterComplete
            );
        });

        const skipButton =
            card.querySelector(".chatbot-rating-skip");

        if (skipButton) {
            skipButton.addEventListener("click", async () => {
                await this.skipLiveChatRating(
                    card,
                    pendingRating.session_id,
                    afterComplete
                );
            });
        }
    }

    async submitLiveChatRating(
        card,
        sessionId,
        rating,
        feedback,
        afterComplete
    ) {
        await this.completeLiveChatRating(
            card,
            "submit",
            {
                live_chat_session_id: sessionId,
                rating,
                feedback,
            },
            afterComplete
        );
    }

    async skipLiveChatRating(card, sessionId, afterComplete) {
        await this.completeLiveChatRating(
            card,
            "skip",
            {
                live_chat_session_id: sessionId,
            },
            afterComplete
        );
    }

    async completeLiveChatRating(
        card,
        action,
        payload,
        afterComplete
    ) {
        const error = card.querySelector(".chatbot-rating-error");

        try {
            const response = await fetch(
                `${this.apiUrl}/live-chat-rating/${action}`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },
                    body: JSON.stringify(
                        this.conversationPayload(payload)
                    ),
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || "Unable to save feedback."
                );
            }

            card.innerHTML = `
                <div class="chatbot-rating-thanks">
                    ${action === "submit"
                        ? `Thank you for your feedback!
                            <div class="chatbot-rating-summary">
                                <div>
                                    <span>Your Rating</span>
                                    <strong>${"★".repeat(payload.rating)} ${payload.rating}/5</strong>
                                </div>
                                <div>
                                    <span>Your Feedback</span>
                                    <strong>${payload.feedback && payload.feedback.trim()
                                        ? this.escapeHtml(payload.feedback.trim())
                                        : "No feedback added"}</strong>
                                </div>
                            </div>`
                        : "Feedback skipped."}
                </div>
            `;

            this.pendingLiveChatRating = null;

            const shouldStartNewChatAfterRating =
                this.shouldStartNewChatAfterRating();

            if (afterComplete || shouldStartNewChatAfterRating) {
                window.setTimeout(() => {
                    if (afterComplete) {
                        afterComplete();
                        return;
                    }

                    this.completeNewChatAfterRating(false);
                }, 650);
            }
        } catch (exception) {
            if (error) {
                error.hidden = false;
                error.textContent = exception.message;
            }
        }
    }

    getConversationStatusText() {

        if (this.conversationStatus === "waiting_agent") {
            return "Waiting for an agent...";
        }

        if (this.conversationStatus === "live_active") {
            return "A support agent is connected.";
        }

        if (this.conversationStatus === "closed") {
            return "Conversation closed.";
        }

        return "Message saved.";
    }

    updateLiveChatControls() {

        if (!this.liveChatActions || !this.talkAgentButton) {
            return;
        }

        const shouldShow =
            this.settings?.show_live_chat_entry === true ||
            this.settings?.can_request_live_chat === true ||
            (
                this.settings?.enable_live_chat === true &&
                (
                    this.settings?.live_chat_available === true ||
                    this.settings?.offline_behavior !== "hide_button"
                )
            );

        this.liveChatActions.style.display =
            shouldShow ? "flex" : "none";

        const status =
            document.getElementById("chatbot-status");

        if (status) {
            status.textContent =
                this.conversationStatus === "waiting_agent"
                    ? "Waiting for agent"
                    : this.conversationStatus === "live_active"
                        ? "Connected to support team"
                        : "Online";
        }

        this.talkAgentButton.disabled =
            this.liveChatRequested ||
            this.conversationStatus === "closed";

        if (this.talkAgentButtonLabel) {
            this.talkAgentButtonLabel.innerHTML =
                'Connect with our support team. <i class="bi bi-arrow-right" aria-hidden="true"></i>';
        }

        if (this.attachButton) {
            const canAttach =
                this.conversationStatus === "live_active" &&
                this.conversationMode === "live";

            this.attachButton.style.display =
                canAttach ? "inline-flex" : "none";

            this.attachButton.disabled = !canAttach;

            if (!canAttach) {
                this.clearSelectedAttachment();
            }
        }
    }

    async requestLiveChat() {

        if (this.liveChatRequested) {
            return;
        }

        if (!this.conversationId) {
            this.addStatusMessage(
                "Please send a message first to connect."
            );

            return;
        }

        this.ensureSessionId();

        this.talkAgentButton.disabled = true;

        try {

            const response = await fetch(
                `${this.apiUrl}/request-live-chat`,
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
                        conversation_id: this.conversationId,
                    }),
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                this.talkAgentButton.disabled = false;

                this.addStatusMessage(
                    data.message ||
                    "Unable to connect to an agent right now."
                );

                return;
            }

            if (data.offline_required) {
                this.addStatusMessage(
                    data.message ||
                    this.settings?.offline_message ||
                    "Our support team is currently offline."
                );

                this.showOfflineLiveChatForm();
                this.talkAgentButton.disabled = false;

                return;
            }

            this.applyConversationState(data);

            this.addStatusMessage(
                data.message ||
                "Connecting you to our support team..."
            );

        } catch (error) {

            console.error("Live chat request error:", error);

            this.talkAgentButton.disabled = false;

            this.addStatusMessage(
                "Unable to connect to an agent right now."
            );
        }
    }

    showOfflineLiveChatForm() {

        if (document.querySelector(".chatbot-offline-form")) {
            return;
        }

        const container =
            document.createElement("div");

        container.className =
            "chatbot-lead-form chatbot-offline-form";

        container.innerHTML = `
            <div class="lead-form-title">
                Leave a message for our support team.
            </div>

            <input type="text" id="offline-name" placeholder="Your name">
            <input type="email" id="offline-email" placeholder="Your email">
            <input type="tel" id="offline-phone" placeholder="Phone Number *" required>
            <textarea id="offline-message" rows="4" placeholder="How can we help? (optional)"></textarea>

            <button type="button" id="offline-submit">
                Send Message
            </button>

            <div id="offline-error"></div>
        `;

        this.messages.appendChild(container);
        this.scrollBottom();

        document
            .getElementById("offline-submit")
            .addEventListener("click", () => {
                this.submitOfflineLiveChatRequest();
            });
    }

    async submitOfflineLiveChatRequest() {

        const name =
            document.getElementById("offline-name").value.trim();
        const email =
            document.getElementById("offline-email").value.trim();
        const phone =
            document.getElementById("offline-phone").value.trim();
        const message =
            document.getElementById("offline-message").value.trim();
        const error =
            document.getElementById("offline-error");
        const submitButton =
            document.getElementById("offline-submit");
        const form =
            document.querySelector(".chatbot-offline-form");

        error.textContent = "";

        if (!name) {
            error.textContent = "Please enter your name.";
            return;
        }

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            error.textContent = "Please enter a valid email.";
            return;
        }

        if (!phone) {
            error.textContent = "Please enter your phone number.";
            return;
        }

        if (!/^\+?[0-9]{7,15}$/.test(phone)) {
            error.textContent =
                "Please enter a valid phone number (7 to 15 digits).";
            return;
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = "Submitting...";
        }

        try {
            const response = await fetch(
                `${this.apiUrl}/offline-live-chat-request`,
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
                        conversation_id: this.conversationId,
                        name,
                        email,
                        phone,
                        message,
                    }),
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                error.textContent =
                    data.message ||
                    "Unable to save your message.";

                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = "Send Message";
                }

                return;
            }

            this.showOfflineLiveChatConfirmation(
                form,
                data.message,
                data.lead || {
                    name,
                    email,
                    phone,
                    message,
                }
            );
        } catch (exception) {
            console.error(exception);
            error.textContent = "Unable to connect to server.";

            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = "Send Message";
            }
        }
    }

    showOfflineLiveChatConfirmation(container, message, lead) {

        if (!container) {
            return;
        }

        container.innerHTML = `
            <div class="lead-confirmation-header">
                <div class="lead-confirmation-icon">
                    <i class="bi bi-person-fill"></i>
                </div>

                <strong>My Details</strong>
            </div>

            <div class="lead-confirmation-divider"></div>
        `;
        container.className =
            "lead-confirmation chatbot-offline-confirmation";

        const fields =
            document.createElement("div");
        fields.className =
            "offline-confirmation-fields";

        const offlineDetails = [
            ["bi bi-person", lead?.name || ""],
            ["bi bi-envelope-fill", lead?.email || ""],
            ["bi bi-telephone-fill", lead?.phone || ""],
        ];

        if (lead?.message) {
            offlineDetails.push([
                "bi bi-chat-left-text-fill",
                lead.message,
            ]);
        }

        offlineDetails.forEach(([icon, value]) => {
            const row =
                document.createElement("div");
            row.className =
                "lead-detail offline-confirmation-row";

            const fieldIcon =
                document.createElement("i");
            fieldIcon.className =
                icon;

            const fieldValue =
                document.createElement("span");
            fieldValue.className =
                "offline-confirmation-value";
            fieldValue.textContent =
                value;

            row.appendChild(fieldIcon);
            row.appendChild(fieldValue);
            fields.appendChild(row);
        });

        container.appendChild(fields);

        this.addBotMessage(
            message || "Thanks! Your details have been saved."
        );

        this.scrollBottom();
    }

    showCurrentStep() {

        if (!this.flow) {
            return;
        }

        const step =
            this.flow.steps[
                this.currentStep
            ];

        if (!step) {

            this.addBotMessage(
                "Great! I have all the trip details I need."
            );

            this.showLeadForm();

            return;
        }

        this.addBotMessage(
            step.question
        );

        this.addOptionButtons(
            step.options
        );
    }

    formatBrowserTimestamp(timestamp) {

        if (!timestamp) {
            return "";
        }

        const date =
            new Date(timestamp);

        if (Number.isNaN(date.getTime())) {
            return "";
        }

        return date.toLocaleString();
    }

    handleAttachmentSelection() {

        const file =
            this.attachmentInput.files?.[0] || null;

        if (!file) {
            this.clearSelectedAttachment();
            return;
        }

        const allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/webp",
            "application/pdf",
            "video/mp4",
            "video/webm",
            "video/quicktime",
            "video/ogg",
        ];

        if (!allowedTypes.includes(file.type) || file.size > 10 * 1024 * 1024) {
            this.attachmentInput.value = "";
            this.addStatusMessage(
                "Upload an image, PDF, or video up to 10 MB."
            );
            return;
        }

        this.selectedAttachment = file;
        this.renderSelectedAttachment();
    }

    renderSelectedAttachment() {

        let preview =
            document.getElementById("chatbot-selected-attachment");

        if (!preview) {
            preview = document.createElement("div");
            preview.id = "chatbot-selected-attachment";

            const inputArea =
                document.getElementById("chatbot-input-area");

            inputArea.parentNode.insertBefore(preview, inputArea);
        }

        preview.innerHTML = "";

        const name =
            document.createElement("span");
        name.textContent =
            this.selectedAttachment?.name || "";

        const remove =
            document.createElement("button");
        remove.type = "button";
        remove.setAttribute("aria-label", "Remove attachment");
        remove.textContent = "x";
        remove.addEventListener(
            "click",
            () => this.clearSelectedAttachment()
        );

        preview.appendChild(name);
        preview.appendChild(remove);
    }

    clearSelectedAttachment() {

        this.selectedAttachment = null;

        if (this.attachmentInput) {
            this.attachmentInput.value = "";
        }

        const preview =
            document.getElementById("chatbot-selected-attachment");

        if (preview) {
            preview.remove();
        }
    }

    attachmentViewUrl(messageId) {

        const params =
            new URLSearchParams({
                widget_key: this.widgetKey,
                domain: this.domain,
                visitor_uuid: this.visitorId,
                session_id: this.sessionId,
                conversation_id: this.conversationId,
            });

        return `${this.apiUrl}/attachments/${messageId}?${params}`;
    }

    renderAttachment(attachment) {

        const wrapper =
            document.createElement("div");
        wrapper.className =
            "chatbot-message-attachment";

        const url =
            attachment.view_url || "#";

        if (attachment.type === "image") {
            const link =
                document.createElement("a");
            link.href = url;
            link.target = "_blank";
            link.rel = "noopener";

            const image =
                document.createElement("img");
            image.src = url;
            image.alt =
                attachment.name || "Attachment";

            link.appendChild(image);
            wrapper.appendChild(link);

            return wrapper;
        }

        if (attachment.type === "video") {
            const video =
                document.createElement("video");
            video.src = url;
            video.controls = true;
            video.preload = "metadata";

            wrapper.appendChild(video);

            return wrapper;
        }

        const link =
            document.createElement("a");
        link.className =
            "chatbot-attachment-card";
        link.href = url;
        link.target = "_blank";
        link.rel = "noopener";

        const icon =
            document.createElement("span");
        icon.className =
            "chatbot-attachment-icon";
        icon.textContent =
            "PDF";

        const name =
            document.createElement("span");
        name.textContent =
            attachment.name || "Attachment";

        link.appendChild(icon);
        link.appendChild(name);
        wrapper.appendChild(link);

        return wrapper;
    }

    addUserMessage(
        message,
        timestamp = null,
        attachment = null,
        options = {}
    ) {

        const div =
            document.createElement("div");

        div.className =
            "user-message";

        if (message) {
            const text =
                document.createElement("div");
            text.className =
                "chatbot-user-message-text";
            text.textContent =
                message;
            div.appendChild(text);
        }

        if (attachment) {
            div.appendChild(
                this.renderAttachment(attachment)
            );
        }

        if (options.receipt) {
            this.appendMessageReceipt(
                div,
                options.receipt
            );
        }

        const browserTimestamp =
            this.formatBrowserTimestamp(timestamp);

        if (browserTimestamp) {
            div.title = browserTimestamp;
        }

        this.messages.appendChild(
            div
        );

        this.scrollBottom();

        return div;
    }

    appendMessageReceipt(messageElement, state) {

        const receipt =
            document.createElement("span");

        receipt.className =
            "chatbot-message-receipt";

        messageElement.appendChild(receipt);

        this.updateMessageReceipt(
            messageElement,
            state
        );
    }

    updateMessageReceipt(messageElement, state) {

        const receipt =
            messageElement?.querySelector(
                ".chatbot-message-receipt"
            );

        if (!receipt) {
            return;
        }

        receipt.classList.remove(
            "is-sent",
            "is-delivered",
            "is-read"
        );

        const normalizedState =
            state === "read"
                ? "read"
                : state === "delivered"
                    ? "delivered"
                    : "sent";

        receipt.classList.add(
            `is-${normalizedState}`
        );

        receipt.textContent =
            normalizedState === "sent"
                ? "\u2713"
                : "\u2713\u2713";

        receipt.setAttribute(
            "aria-label",
            normalizedState === "sent"
                ? "Sent"
                : normalizedState === "delivered"
                    ? "Delivered"
                    : "Read"
        );
    }

    markLiveMessageReceiptsRead() {

        this.messages
            .querySelectorAll(
                ".user-message .chatbot-message-receipt.is-delivered"
            )
            .forEach(receipt => {
                this.updateMessageReceipt(
                    receipt.closest(".user-message"),
                    "read"
                );
            });
    }

    handleLiveChatMessagesRead(event) {

        if (
            String(event.conversation_id) !==
            String(this.conversationId)
        ) {
            return;
        }

        const messageIds =
            Array.isArray(event.message_ids)
                ? event.message_ids.map(id => String(id))
                : [];

        if (messageIds.length === 0) {
            this.markLiveMessageReceiptsRead();
            return;
        }

        messageIds.forEach(messageId => {
            const messageElement =
                this.messages.querySelector(
                    `.user-message[data-message-id="${CSS.escape(messageId)}"]`
                );

            if (messageElement) {
                this.updateMessageReceipt(
                    messageElement,
                    "read"
                );
            }
        });
    }

    addBotMessage(message, timestamp = null, attachment = null) {

        const div =
            document.createElement("div");

        div.className =
            "bot-message";

        if (message) {
            const text =
                document.createElement("div");
            text.textContent =
                message;
            div.appendChild(text);
        }

        if (attachment) {
            div.appendChild(
                this.renderAttachment(attachment)
            );
        }

        const browserTimestamp =
            this.formatBrowserTimestamp(timestamp);

        if (browserTimestamp) {
            div.title = browserTimestamp;
        }

        this.messages.appendChild(
            div
        );

        this.scrollBottom();
    }

    addStatusMessage(message, timestamp = null) {

        const div =
            document.createElement("div");

        div.className =
            "chatbot-status-message";

        div.textContent =
            message;

        const browserTimestamp =
            this.formatBrowserTimestamp(timestamp);

        if (browserTimestamp) {
            div.title = browserTimestamp;
        }

        this.messages.appendChild(
            div
        );

        this.scrollBottom();
    }

    showLiveTyping() {

        let typing =
            document.getElementById(
                "live-typing-indicator"
            );

        if (!typing) {
            typing =
                document.createElement("div");

            typing.id =
                "live-typing-indicator";

            typing.className =
                "bot-message";

            typing.innerHTML = `
                <div class="typing-bubble" title="Agent is typing">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            `;

            this.messages.appendChild(
                typing
            );
        }

        clearTimeout(this.agentTypingTimeout);

        this.agentTypingTimeout = setTimeout(
            () => this.hideLiveTyping(),
            1800
        );

        this.scrollBottom();
    }

    hideLiveTyping() {

        const typing =
            document.getElementById(
                "live-typing-indicator"
            );

        if (typing) {
            typing.remove();
        }

        clearTimeout(this.agentTypingTimeout);
        this.agentTypingTimeout = null;
    }

    showTyping() {

        if (
            document.getElementById(
                "typing-indicator"
            )
        ) {
            return;
        }

        const typing =
            document.createElement("div");

        typing.id =
            "typing-indicator";

        typing.className =
            "bot-message";

        typing.innerHTML = `
            <div class="typing-bubble">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;

        this.messages.appendChild(
            typing
        );

        this.scrollBottom();
    }

    hideTyping() {

        const typing =
            document.getElementById(
                "typing-indicator"
            );

        if (typing) {

            typing.remove();
        }
    }

    scrollBottom() {

        this.messages.scrollTop =
            this.messages.scrollHeight;
    }

    async addOptionButtons(options) {

        if (
            !options ||
            options.length === 0
        ) {
            return;
        }

        const container =
            document.createElement("div");

        container.className =
            "chatbot-options";

        const currentStep =
            this.flow?.steps?.[
                this.currentStep
            ];

        if (!currentStep) {
            return;
        }

        options.forEach(option => {

            const button =
                document.createElement(
                    "button"
                );

            button.type =
                "button";

            button.className =
                "chatbot-option";

            const answer =
                option.value ??
                option.label;

            button.textContent =
                answer;

            button.addEventListener(
                "click",
                async () => {
                    await this.submitFlowAnswer(
                        answer,
                        currentStep,
                        container
                    );
                }
            );

            container.appendChild(
                button
            );
        });

        this.messages.appendChild(
            container
        );

        this.scrollBottom();
    }

    async submitMatchingFlowTextAnswer(message) {

        const currentStep =
            this.flow?.steps?.[
                this.currentStep
            ];

        if (!currentStep?.options?.length) {
            return false;
        }

        const normalizedMessage =
            message.trim().toLowerCase();

        const matchedOption =
            currentStep.options.find(option => {
                const answer =
                    String(option.value ?? option.label ?? "")
                        .trim()
                        .toLowerCase();

                return answer === normalizedMessage;
            });

        if (!matchedOption) {
            return false;
        }

        await this.submitFlowAnswer(
            matchedOption.value ?? matchedOption.label,
            currentStep,
            document.querySelector(".chatbot-options")
        );

        return true;
    }

    async submitFlowAnswer(answer, currentStep, optionsContainer = null) {

        this.ensureSessionId();

        this.addUserMessage(
            answer
        );

        if (optionsContainer) {
            optionsContainer.remove();
        }

        try {

            const response =
                await fetch(
                    `${this.apiUrl}/flow-answer`,
                    {
                        method:
                            "POST",

                        headers: {

                            "Content-Type":
                                "application/json",

                            "Accept":
                                "application/json",
                        },

                        body:
                            JSON.stringify({

                                widget_key:
                                    this.widgetKey,

                                domain:
                                    this.domain,

                                session_id:
                                    this.sessionId,

                                visitor_uuid:
                                    this.visitorId,

                                conversation_id:
                                    this.conversationId,

                                chatbot_flow_step_id:
                                    currentStep.id,

                                answer:
                                    answer,
                            }),
                    }
                );

            const data =
                await response.json();

            if (!data.success) {

                console.error(
                    "Flow answer failed:",
                    data
                );

                return;
            }

            this.conversationId =
                data.conversation_id;

            this.conversationEnded =
                false;

            this.storeConversationId(
                this.conversationId
            );

            if (data.flow_completed) {
                this.currentStep =
                    Array.isArray(this.flow?.steps)
                        ? this.flow.steps.length
                        : this.currentStep + 1;

                this.showCurrentStep();

                return;
            }

            this.currentStep++;

            this.showCurrentStep();

        } catch (error) {

            console.error(
                "Flow answer error:",
                error
            );
        }
    }

    showLeadForm() {

        if (document.querySelector(".chatbot-lead-form:not(.chatbot-offline-form)")) {
            return;
        }

        const container =
            document.createElement("div");

        container.className =
            "chatbot-lead-form";

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

            <button
                type="button"
                id="lead-submit"
            >
                Submit
            </button>

            <div id="lead-error"></div>
        `;

        this.messages.appendChild(
            container
        );

        this.scrollBottom();

        document
            .getElementById(
                "lead-submit"
            )
            .addEventListener(
                "click",
                () => {
                    this.submitLead();
                }
            );
    }

    async submitLead() {

        const name =
            document
                .getElementById(
                    "lead-name"
                )
                .value
                .trim();

        const email =
            document
                .getElementById(
                    "lead-email"
                )
                .value
                .trim();

        const phone =
            document
                .getElementById(
                    "lead-phone"
                )
                .value
                .trim();

        const error =
            document.getElementById(
                "lead-error"
            );

        error.textContent = "";

        if (!name) {

            error.textContent =
                "Please enter your name.";

            return;
        }

        if (!email) {

            error.textContent =
                "Please enter your email.";

            return;
        }

        if (
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/
                .test(email)
        ) {

            error.textContent =
                "Please enter a valid email.";

            return;
        }

        if (!phone) {

            error.textContent =
                "Please enter your phone number.";

            return;
        }
        if (!/^\+?[0-9]{7,15}$/.test(phone)) {
    error.textContent =
        "Please enter a valid phone number (7 to 15 digits).";

    return;
}

        this.ensureSessionId();

        try {

            const response = await fetch(
                `${this.apiUrl}/save-lead`,
                {
                    method: "POST",

                    headers: {

                        "Content-Type":
                            "application/json",

                        "Accept":
                            "application/json",
                    },

                    body: JSON.stringify({

                        name:
                            name,

                        email:
                            email,

                        phone:
                            phone,

                        widget_key:
                            this.widgetKey,

                        domain:
                            this.domain,

                        session_id:
                            this.sessionId,

                        visitor_uuid:
                            this.visitorId,

                        conversation_id:
                            this.conversationId,

                        notes:
                            null,
                    }),
                }
            );

            const data =
                await response.json();

            if (!data.success) {

                error.textContent =
                    data.message ||
                    "Unable to save your details.";

                return;
            }

            this.addLeadConfirmation(
                name,
                email,
                phone
            );

            const form =
                document.querySelector(
                    ".chatbot-lead-form"
                );

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

    addLeadConfirmation(
        name,
        email,
        phone
    ) {

        const div =
            document.createElement("div");

        div.className =
            "lead-confirmation";

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

                <span>
                    ${this.escapeHtml(name)}
                </span>

            </div>

            <div class="lead-detail">

                <i class="bi bi-envelope-fill"></i>

                <span>
                    ${this.escapeHtml(email)}
                </span>

            </div>

            <div class="lead-detail">

                <i class="bi bi-telephone-fill"></i>

                <span>
                    ${this.escapeHtml(phone)}
                </span>

            </div>
        `;

        this.messages.appendChild(
            div
        );

        this.scrollBottom();
    }

    escapeHtml(value) {

        const div =
            document.createElement("div");

        div.textContent =
            value;

        return div.innerHTML;
    }

    showEndChatQuestion() {

        const div =
            document.createElement("div");

        div.className =
            "end-chat-question";

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

        this.messages.appendChild(
            div
        );

        this.scrollBottom();

        const buttons =
            div.querySelectorAll(
                ".end-chat-option"
            );

        buttons.forEach(button => {

            button.addEventListener(
                "click",
                () => {

                    const action =
                        button.dataset.action;

                    const selectedText =
                        button
                            .textContent
                            .trim();

                    const options =
                        div.querySelector(
                            ".end-chat-options"
                        );

                    if (options) {

                        options.remove();
                    }

                    this.addUserMessage(
                        selectedText
                    );

                    if (action === "yes") {

                        this.confirmEndChat();

                    } else {

                        this.continueChat();
                    }
                }
            );
        });
    }

    continueChat() {

        this.addBotMessage(
            "Sure! 😊 What else can I help you with?"
        );

        this.conversationEnded =
            false;

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

                        "Content-Type":
                            "application/json",

                        "Accept":
                            "application/json",
                    },

                    body: JSON.stringify({

                        widget_key:
                            this.widgetKey,

                        domain:
                            this.domain,

                        session_id:
                            this.sessionId,

                        visitor_uuid:
                            this.visitorId,

                        conversation_id:
                            this.conversationId,
                    }),
                }
            );

            const data =
                await response.json();

            if (
                !response.ok ||
                !data.success
            ) {

                this.addBotMessage(
                    "Sorry, I couldn't end the conversation."
                );

                return;
            }

            this.conversationEnded =
                true;

            this.unsubscribeRealtime();

            if (data.pending_rating) {

                this.addBotMessage(
                    "Your conversation has ended. Please share your feedback."
                );

                this.showLiveChatRatingCard(
                    data.pending_rating,
                    () => {
                        this.addConversationDivider();
                        this.clearStoredConversationId();
                        this.updateLiveChatControls();
                    }
                );

                return;
            }

            this.addBotMessage(
                "Your conversation has ended. Thank you for chatting with us! 😊"
            );

            this.clearStoredConversationId();

            this.updateLiveChatControls();

            this.addConversationDivider();

        } catch (error) {

            console.error(
                "End chat error:",
                error
            );

            this.addBotMessage(
                "Unable to end the conversation."
            );
        }
    }

    addConversationDivider() {

        const divider =
            document.createElement("div");

        divider.className =
            "conversation-divider";

        divider.innerHTML = `
            <span>
                Conversation ended
            </span>
        `;

        this.messages.appendChild(
            divider
        );

        this.scrollBottom();
    }
}

const bootChatbot = () => {
    new Chatbot();
};

if (document.readyState === "loading") {
    document.addEventListener(
        "DOMContentLoaded",
        bootChatbot,
        { once: true }
    );
} else {
    bootChatbot();
}
