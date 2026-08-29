import os
import ollama

from services.context_builder import ContextBuilder
from services.conversational_intent_service import ConversationalIntentService
from services.contextual_query_service import ContextualQueryService
from services.prompt_service import PromptService
from services.knowledge.retrieval_service import RetrievalService


class AIService:
    KNOWLEDGE_FALLBACK = (
        "I couldn't find this information in the uploaded knowledge base."
    )

    def __init__(self):

        self.prompt_service = PromptService()

        self.context_builder = ContextBuilder()

        self.retrieval_service = RetrievalService()

        self.conversational_intent_service = ConversationalIntentService()

        self.contextual_query_service = ContextualQueryService()

    def generate_summary(
        self,
        history: list,
        previous_summary: str = None
    ) -> str:

        if not history:
            return previous_summary or ""

        model = os.getenv("OLLAMA_MODEL")

        user_messages = []

        for chat in history:
            if chat.role == "user":
                user_messages.append(chat.content)

        if not user_messages:
            return previous_summary or ""

        summary_prompt = [
            {
            "role": "system",
            "content": """
You are a conversation memory manager.

Your job is to maintain a SHORT running summary of the user's
important information.

ONLY store information explicitly provided by the USER.

Store things such as:
- destination
- travel dates or month
- number of travelers
- adults and children
- budget
- travel preferences
- hotel preferences
- departure city
- flight status
- requirements
- decisions

DO NOT store:
- assistant responses
- assistant recommendations
- general knowledge
- assumptions
- invented information
- explanations
- unnecessary conversation

If new information updates an older value, replace the old value.

Keep the summary concise and factual.

Return plain text only.
Do not use Markdown.
Do not use bullets or numbering.
"""
                },
            {
            "role": "user",
            "content": f"""
Previous summary:
{previous_summary or "None"}

New information explicitly provided by the user:
{chr(10).join(user_messages)}

Create the updated running summary.
"""
        }
    ]

        response = ollama.chat(
            model=model,
            messages=summary_prompt,
            options={
                "temperature": 0,
                "num_predict": 80,
            },
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
        print("WEBSITE ID:", website_id)
        print("SUMMARY:", summary)

        conversational_response = (
            self.conversational_intent_service.get_response(message)
        )

        if conversational_response:
            return {
                "response": conversational_response,
                "is_conversational": True,
                "skip_summary": True,
                "knowledge_found": None,
            }

        search_query = self.contextual_query_service.build_search_query(
            message=message,
            history=history,
            summary=summary,
        )

        print("SEARCH QUERY:", search_query)

        results = self.retrieval_service.retrieve(
            website_id=website_id,
            question=search_query,
        )

        print("RESULTS:", results)
        print("TOTAL RESULTS:", len(results))

        if not results:
            return {
                "response": self.KNOWLEDGE_FALLBACK,
                "knowledge_found": False,
            }

        # --------------------------------
        # KNOWLEDGE BASE RESPONSE
        # --------------------------------

        context = self.context_builder.build(
            results
        )

        messages = self.prompt_service.build_prompt(
            search_query,
            context,
            history,
            summary
        )

        response = ollama.chat(
            model=model,
            messages=messages,
            options={
                "temperature": 0,
                "top_p": 0.7,
                "num_predict": 160,
                "num_ctx": 2048,
            },
        )

        print("RAW RESPONSE:", response)
        print("CONTENT:", response.message.content)

        return {
            "response": response.message.content.strip(),
            "knowledge_found": True,
        }
        
