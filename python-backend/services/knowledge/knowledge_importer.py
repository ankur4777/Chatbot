from services.knowledge.website_crawler import WebsiteCrawler
from services.knowledge.chunk_service import ChunkService
from services.knowledge.embedding_service import EmbeddingService
from services.knowledge.vector_service import VectorService


class KnowledgeImporter:

    def __init__(self):

        self.crawler = WebsiteCrawler()
        self.chunk_service = ChunkService()
        self.embedding_service = EmbeddingService()
        self.vector_service = VectorService()

    def import_website(self, url: str):

        pages = self.crawler.crawl(url)

        total_chunks = 0

        for page in pages:

            chunks = self.chunk_service.chunk(
                text=page["text"],
                source=page["url"]
            )

            for chunk in chunks:

                embedding = self.embedding_service.embed(
                    chunk["text"]
                )

                self.vector_service.add(
                    embedding,
                    chunk
                )

                total_chunks += 1

        return {
            "success": True,
            "pages": len(pages),
            "chunks": total_chunks,
        }

    def import_json(self, file_path: str):
        pass

    def import_pdf(self, file_path: str):
        pass

    def import_docx(self, file_path: str):
        pass

    def import_txt(self, file_path: str):
        pass

    def import_faq(self, data):
        pass