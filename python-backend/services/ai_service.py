import os
import ollama

from services.prompt_service import PromptService
from services.knowledge.embedding_service import EmbeddingService
from services.knowledge.vector_store import vector_store
class AIService:

    def __init__(self):

        self.prompt_service = PromptService()

        self.embedding_service = EmbeddingService()

        self.vector_service = vector_store

    def generate_response(self, message: str):

        model = os.getenv("OLLAMA_MODEL")

        embedding = self.embedding_service.embed(message)

        results = self.vector_service.search(
            embedding,
            k=5
        )
        print("\n========== SEARCH RESULTS ==========\n")

        for i, chunk in enumerate(results):
            print(f"\nChunk {i+1}")
            print(chunk["text"][:300])

        print("\n====================================\n")

        if not results:
            context = "No knowledge available."
        else:
            context = "\n\n".join(
                chunk["text"]
                for chunk in results
            )


        response = ollama.chat(
            model=model,
            messages=self.prompt_service.build_prompt(
                message,
                context
            ),
        )

        return {
            "response": response["message"]["content"]
        }