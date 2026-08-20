import os
from sentence_transformers import SentenceTransformer


class EmbeddingService:

    def __init__(self):

        self.model = SentenceTransformer(
            os.getenv(
                "EMBEDDING_MODEL",
                "BAAI/bge-small-en-v1.5"
            )
        )

    def embed(self, text: str):

        embedding = self.model.encode(text, normalize_embeddings=True)

        return embedding.tolist()

    def embed_batch(self, texts: list[str]):

        embeddings = self.model.encode(
            texts,
            normalize_embeddings=True,
            batch_size=32,
            show_progress_bar=False
        )

        return embeddings.tolist()