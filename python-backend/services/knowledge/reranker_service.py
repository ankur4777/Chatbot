import os

from sentence_transformers import CrossEncoder


class RerankerService:

    def __init__(self):

        self.model = CrossEncoder(
            os.getenv(
                "RERANKER_MODEL",
                "BAAI/bge-reranker-base"
            )
        )

    def rerank(
        self,
        question: str,
        chunks: list,
        top_k: int = 3,
    ):

        if not chunks:
            return []

        pairs = []

        for chunk in chunks:

            text = chunk.get("text", "").strip()

            pairs.append([
                question,
                text,
            ])

        scores = self.model.predict(pairs)

        ranked = []

        for chunk, score in zip(chunks, scores):

            ranked.append({
                "chunk": chunk,
                "score": float(score),
            })

        ranked.sort(
            key=lambda item: item["score"],
            reverse=True
        )

        return ranked[:top_k]