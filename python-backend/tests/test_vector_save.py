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
from services.knowledge.vector_service import VectorService
from services.knowledge.vector_service import VectorService

print(VectorService)
print(dir(VectorService))
embedding_service = EmbeddingService()
vector = VectorService()

texts = [
    "Bali Tour",
    "Dubai Tour",
    "Manali Tour"
]

for text in texts:

    embedding = embedding_service.embed(text)

    vector.add(
        embedding,
        {
            "text": text
        }
    )

vector.save_index(
    "data/faiss/index.faiss"
)

print("Index Saved Successfully")