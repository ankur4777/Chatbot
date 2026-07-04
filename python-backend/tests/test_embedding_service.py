import sys
import os

sys.path.append(
    os.path.dirname(
        os.path.dirname(
            os.path.abspath(__file__)
        )
    )
)

from services.knowledge.embedding_service import EmbeddingService

service = EmbeddingService()

vector = service.embed("Hello, I want to visit Bali.")

print(type(vector))

print("Dimensions:", len(vector))

print(vector[:10])