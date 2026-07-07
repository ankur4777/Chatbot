from services.knowledge.website_crawler import WebsiteCrawler
from services.knowledge.chunk_service import ChunkService
from services.knowledge.embedding_service import EmbeddingService
from services.knowledge.vector_store import vector_store
import os
from services.knowledge.pdf_parser import PDFParser
from services.knowledge.metadata_service import MetadataService

class KnowledgeImporter:

    def __init__(self):

        self.crawler = WebsiteCrawler()
        self.chunk_service = ChunkService()
        self.embedding_service = EmbeddingService()
        self.vector_service = vector_store
        self.metadata_service = MetadataService()
        self.pdf_parser = PDFParser()

    def _save_knowledge(self):

        faiss_path = os.path.join(
            "data",
            "faiss",
            "index.faiss"
        )

        metadata_path = os.path.join(
            "data",
            "metadata",
            "metadata.json"
        )

        self.vector_service.save_index(
            faiss_path
        )

        self.metadata_service.save(
            self.vector_service.chunks,
            metadata_path
        )

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
        self._save_knowledge()

        return {
            "success": True,
            "pages": len(pages),
            "chunks": total_chunks,
        }

    def import_json(self, file_path: str):
        pass

    def import_pdf(self, file_path: str):

        text = self.pdf_parser.parse(file_path)

        chunks = self.chunk_service.chunk(
        text=text,
        source=file_path
        )

        total_chunks = 0

        for chunk in chunks:

            embedding = self.embedding_service.embed(
                chunk["text"]
            )

            self.vector_service.add(
                embedding,
                chunk
            )

            total_chunks += 1

        self._save_knowledge()

        return {
            "success": True,
            "pages": 1,
            "chunks": total_chunks,
        }

    def import_docx(self, file_path: str):
        pass

    def import_txt(self, file_path: str):
        pass

    def import_faq(self, data):
        pass