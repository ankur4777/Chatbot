import os

from services.knowledge.embedding_service import EmbeddingService
from services.knowledge.vector_store import vector_store
from services.knowledge.reranker_service import RerankerService


class RetrievalService:

    def __init__(self):

        self.embedding_service = EmbeddingService()

        self.vector_store = vector_store

        self.reranker_service = RerankerService()

        self.top_k = int(
            os.getenv("RAG_TOP_K", "4")
        )

        self.rerank_top_k = int(
            os.getenv("RERANK_TOP_K", "2")
        )

        self.reranker_threshold = float(
            os.getenv("RERANKER_THRESHOLD", "0.55")
        )

    def retrieve(
        self,
        website_id: int,
        question: str,
        k: int = None,
    ):

        self.vector_store.load_website(
            website_id
        )

        embedding = self.embedding_service.embed(
            question
        )

        results = self.vector_store.search(
            embedding,
            k=k or self.top_k,
        )

        reranked_results = self.reranker_service.rerank(
            question=question,
            chunks=results,
            top_k=self.rerank_top_k,
        )

        filtered_results = [
            item["chunk"]
            for item in reranked_results
            if item["score"] >= self.reranker_threshold
        ]

        return filtered_results
