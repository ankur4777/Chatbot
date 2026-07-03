import os
import ollama
from services.prompt_service import PromptService

class AIService:

    def __init__(self):

        self.prompt_service = PromptService()

    def generate_response(self, message: str):

        model = os.getenv("OLLAMA_MODEL")

        response = ollama.chat(
            model=model,
            messages=self.prompt_service.build_prompt(message),
        )

        return {
            "response": response["message"]["content"]
        }