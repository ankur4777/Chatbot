import re


class ConversationalIntentService:
    GREETING_RESPONSE = "Hi! 👋 How can I help you today?"
    THANKS_RESPONSE = "You're welcome! 😊"
    ACKNOWLEDGEMENT_RESPONSE = "Sure! Let me know if you need anything else."
    FAREWELL_RESPONSE = "Goodbye! Have a great day! 👋"
    WELLBEING_RESPONSE = "I'm doing well! How can I help you today?"
    COURTESY_RESPONSE = "Nice to meet you too! How can I help you today?"
    CAPABILITY_RESPONSE = (
        "I can help answer questions using the uploaded knowledge base."
    )

    GREETINGS = {
        "hi",
        "hii",
        "hello",
        "hey",
        "good morning",
        "good afternoon",
        "good evening",
    }

    GRATITUDE = {
        "thanks",
        "thank you",
        "thank u",
        "thanks a lot",
        "thnx",
        "ty",
    }

    ACKNOWLEDGEMENTS = {
        "ok",
        "okay",
        "alright",
        "got it",
        "understood",
        "fine",
        "sure",
    }

    FAREWELLS = {
        "bye",
        "goodbye",
        "see you",
        "talk to you later",
        "good night",
    }

    WELLBEING = {
        "how are you",
        "how are you doing",
        "how r you",
        "how are u",
    }

    COURTESY = {
        "nice to meet you",
        "have a nice day",
    }

    CAPABILITY = {
        "who are you",
        "what can you do",
        "can you help me",
    }

    def get_response(self, message: str) -> str | None:
        normalized = self.normalize(message)

        if not normalized:
            return None

        if self.is_greeting(normalized):
            return self.GREETING_RESPONSE

        if normalized in self.GRATITUDE:
            return self.THANKS_RESPONSE

        if normalized in self.ACKNOWLEDGEMENTS:
            return self.ACKNOWLEDGEMENT_RESPONSE

        if normalized in self.FAREWELLS:
            return self.FAREWELL_RESPONSE

        if normalized in self.WELLBEING:
            return self.WELLBEING_RESPONSE

        if normalized in self.COURTESY:
            return self.COURTESY_RESPONSE

        if normalized in self.CAPABILITY:
            return self.CAPABILITY_RESPONSE

        return None

    def normalize(self, message: str) -> str:
        text = (message or "").strip().lower()
        text = re.sub(r"[^\w\s]", " ", text)
        text = re.sub(r"\s+", " ", text)

        return text.strip()

    def is_greeting(self, normalized: str) -> bool:
        if normalized in self.GREETINGS:
            return True

        if re.fullmatch(r"h+i+", normalized):
            return True

        return False
