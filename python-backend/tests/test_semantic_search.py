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

embedding_service = EmbeddingService()
vector_service = VectorService()

documents = [
    {
        "text": "Bali is famous for beaches and honeymoon trips.",
        "source": "bali"
    },
    {
        "text": "Dubai is famous for luxury shopping and Burj Khalifa.",
        "source": "dubai"
    },
    {
        "text": "Manali is popular for snow and mountains.",
        "source": "manali"
    }
]

for doc in documents:

    embedding = embedding_service.embed(doc["text"])

    vector_service.add(
        embedding,
        doc
    )

query = "I want to visit a snowy place."

query_embedding = embedding_service.embed(query)

results = vector_service.search(query_embedding)

print(results)