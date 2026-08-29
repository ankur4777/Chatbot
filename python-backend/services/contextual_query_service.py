import re

from services.conversational_intent_service import ConversationalIntentService


class ContextualQueryService:
    MAX_RECENT_USER_MESSAGES = 4

    REFERENCE_TERMS = {
        "it",
        "its",
        "this",
        "that",
        "these",
        "those",
        "them",
        "they",
        "their",
        "there",
        "same",
        "one",
        "ones",
    }

    FOLLOW_UP_TERMS = {
        "package",
        "plan",
        "trip",
        "tour",
        "price",
        "cost",
        "include",
        "includes",
        "included",
        "inclusion",
        "inclusions",
        "detail",
        "details",
        "info",
        "information",
    }

    TOPIC_STOP_WORDS = REFERENCE_TERMS | FOLLOW_UP_TERMS | {
        "a",
        "an",
        "and",
        "are",
        "about",
        "at",
        "be",
        "can",
        "could",
        "did",
        "do",
        "does",
        "explain",
        "for",
        "from",
        "get",
        "give",
        "had",
        "has",
        "have",
        "how",
        "i",
        "in",
        "is",
        "know",
        "me",
        "more",
        "my",
        "of",
        "on",
        "our",
        "please",
        "provide",
        "show",
        "tell",
        "the",
        "to",
        "us",
        "was",
        "were",
        "what",
        "when",
        "where",
        "which",
        "who",
        "why",
        "with",
        "would",
        "you",
        "your",
    }

    def __init__(self):
        self.conversational_intent_service = ConversationalIntentService()

    def build_search_query(
        self,
        message: str,
        history: list = None,
        summary: str = None,
    ) -> str:
        current = self.normalize(message)

        if not current or not self.needs_context(current):
            return message

        recent_messages = self.find_previous_user_messages(
            current_message=message,
            history=history,
        )

        for previous_message in recent_messages:
            topic = self.extract_topic(previous_message)

            if topic:
                return self.resolve_message(message, topic)

        if recent_messages:
            return f"{recent_messages[0]}. Follow-up: {message}"

        summary_topic = self.extract_topic(summary or "")

        if summary_topic:
            return self.resolve_message(message, summary_topic)

        return message

    def normalize(self, message: str) -> str:
        text = (message or "").strip().lower()
        text = re.sub(r"[^\w\s]", " ", text)
        text = re.sub(r"\s+", " ", text)

        return text.strip()

    def extract_topic(self, message: str) -> str:
        normalized = self.normalize(message)
        topic_words = [
            word
            for word in normalized.split()
            if word not in self.TOPIC_STOP_WORDS
        ]

        return " ".join(topic_words)

    def needs_context(self, normalized: str) -> bool:
        words = normalized.split()

        if not words:
            return False

        # A concrete subject in the current message makes it self-contained.
        if self.extract_topic(normalized):
            return False

        if any(word in self.REFERENCE_TERMS for word in words):
            return True

        return (
            len(words) <= 8
            and any(word in self.FOLLOW_UP_TERMS for word in words)
        )

    def resolve_message(self, message: str, topic: str) -> str:
        reference_pattern = re.compile(
            r"\b(" + "|".join(map(re.escape, self.REFERENCE_TERMS)) + r")\b",
            flags=re.IGNORECASE,
        )
        resolved_message = reference_pattern.sub(topic, message)

        # Repeating the resolved topic keeps retrieval and reranking focused on
        # the latest subject instead of an older, semantically similar chunk.
        return f"{topic}. {resolved_message}"

    def find_previous_user_messages(
        self,
        current_message: str,
        history: list = None,
    ) -> list[str]:
        if not history:
            return []

        current = self.normalize(current_message)
        user_messages = []

        for chat in history:
            if isinstance(chat, dict):
                role = chat.get("role")
                content = chat.get("content")
            else:
                role = getattr(chat, "role", None)
                content = getattr(chat, "content", None)

            if role == "user" and content:
                user_messages.append(content)

        # Laravel includes the just-saved current message as the final history item.
        if user_messages and self.normalize(user_messages[-1]) == current:
            user_messages.pop()

        recent_messages = []

        for content in reversed(user_messages):
            if self.conversational_intent_service.get_response(content):
                continue

            recent_messages.append(content)

            if len(recent_messages) >= self.MAX_RECENT_USER_MESSAGES:
                break

        return recent_messages
