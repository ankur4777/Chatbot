import os
from pyexpat.errors import messages
from urllib import response
from xml.parsers.expat import model
import ollama

from services.prompt_service import PromptService
from services.knowledge.embedding_service import EmbeddingService
from services.knowledge.vector_store import vector_store
class AIService:

    def __init__(self):

        self.prompt_service = PromptService()

        self.embedding_service = EmbeddingService()

        self.vector_service = vector_store

    def generate_response(
    self,
    message: str,
    history: list
):
        model = os.getenv("OLLAMA_MODEL")
        print("MESSAGE:", message)
        embedding = self.embedding_service.embed(message)

        results = self.vector_service.search(
            embedding,
            k=5
        )
        print("RESULTS:", results)
        print("TOTAL RESULTS:", len(results))
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
        messages = self.prompt_service.build_prompt(
            message,
            context,
            history
        )

        print("\n========== PROMPT ==========\n")
        print(messages)
        print("\n============================\n")
        
        response = ollama.chat(
            model=model,
            messages=messages,
        )

        print("RAW RESPONSE:", response)
        print("CONTENT:", response.message.content)

        return {
            "response": response.message.content
        }