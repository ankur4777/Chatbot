from email import message
import os
from urllib import response
from xml.parsers.expat import model
import ollama

from services.context_builder import ContextBuilder
from services.prompt_service import PromptService
from services.knowledge.retrieval_service import RetrievalService


class AIService:

    def __init__(self):

        self.prompt_service = PromptService()

        self.context_builder = ContextBuilder()

        self.retrieval_service = RetrievalService()

    def generate_summary(
    self,
    history: list,
    previous_summary: str = None
) -> str:

        if not history and not previous_summary:
            return ""

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
You are a strict conversation memory manager.

Your ONLY job is to maintain factual memory about the USER.

IMPORTANT:
- Store ONLY information explicitly stated by the user.
- NEVER guess.
- NEVER infer.
- NEVER use information from assistant messages.
- NEVER change a user's number, date, destination, budget, or preference.
- If a new user message changes an old value, replace the old value.
- If a value is not mentioned, preserve the previous value.
- Ignore greetings, small talk, and casual messages.

Maintain these fields when information is available:

Destination:
Travel date:
Number of travelers:
Adults:
Children:
Budget:
Travel style:
Preferences:
Departure city:
Flight booked:
Flight date:
Hotel preference:
Other requirements:

If a field is unknown, write "Not specified".

Return ONLY the memory.
Do not add explanations.
Do not add recommendations.
Do not invent information.
"""
        },
        {
            "role": "user",
            "content": f"""
CURRENT MEMORY:
{previous_summary or "No previous memory."}

NEW USER MESSAGES:
{chr(10).join(user_messages)}

Update the memory using ONLY the information explicitly provided
by the user.

Preserve all correct information from CURRENT MEMORY.
Only change a field when the NEW USER MESSAGES explicitly provide
new information for that field.
"""
        }
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

        if results:

            context = self.context_builder.build(results)

            messages = self.prompt_service.build_prompt(
            message,
            context,
            history,
            summary
        )

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