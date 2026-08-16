import os
import ollama

from services.context_builder import ContextBuilder
from services.prompt_service import PromptService
from services.knowledge.retrieval_service import RetrievalService


class AIService:

    def __init__(self):

        self.prompt_service = PromptService()

        self.context_builder = ContextBuilder()

        self.retrieval_service = RetrievalService()

    def generate_summary(self, history: list) -> str:

        if not history:
            return ""

        model = os.getenv("OLLAMA_MODEL")

        summary_prompt = [
            {
                "role": "system",
                "content": (
                   "Summarize the conversation briefly. "
                   "Keep only important user preferences, requirements, "
                   "destinations, decisions, and context needed for future "
                   "questions. Do not invent information. "
                    "Return plain text only."
                ),
            },
            {
                "role": "user",
                "content": "\n".join(
                    f"{chat.role}: {chat.content}"
                    for chat in history
),
        },
    ]

        response = ollama.chat(
            model=model,
           messages=summary_prompt,
    )

        return response.message.content.strip()
    
    def generate_response(
        self,
        message: str,
        history: list,
        website_id: int,
        summary: str = None
    ):

        model = os.getenv("OLLAMA_MODEL")

        print("MESSAGE:", message)
        print("SUMMARY:", summary)

        results = self.retrieval_service.retrieve(
            website_id=website_id,
            question=message,
        )

        print("RESULTS:", results)
        print("TOTAL RESULTS:", len(results))

        # --------------------------------
        # KNOWLEDGE BASE RESPONSE
        # --------------------------------

        if results:

            context = self.context_builder.build(
                results
            )

            messages = self.prompt_service.build_prompt(
                message,
                context,
                history,
                summary
            )

        # --------------------------------
        # CONVERSATION HISTORY RESPONSE
        # --------------------------------

        else:

            messages = self.prompt_service.build_conversation_prompt(
                message,
                history,
                summary
            )

        response = ollama.chat(
            model=model,
            messages=messages,
        )

        print("RAW RESPONSE:", response)
        print("CONTENT:", response.message.content)

        return {
            "response": response.message.content
        }