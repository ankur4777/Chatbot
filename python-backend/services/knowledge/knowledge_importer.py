from services.knowledge.website_crawler import WebsiteCrawler
from services.knowledge.chunk_service import ChunkService
from services.knowledge.embedding_service import EmbeddingService
from services.knowledge.vector_store import vector_store
import os
import requests
from services.knowledge.pdf_parser import PDFParser

class KnowledgeImporter:

    def __init__(self):

        self.crawler = WebsiteCrawler()
        self.chunk_service = ChunkService()
        self.embedding_service = EmbeddingService()
        self.vector_service = vector_store
        self.pdf_parser = PDFParser()

    def _save_knowledge(self, website_id):

       self.vector_service.save_website(website_id)

    def import_website(self, url: str, website_id: int):
        self.vector_service.reset()

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
        self._save_knowledge(website_id)

        return {
            "success": True,
            "pages": len(pages),
            "chunks": total_chunks,
        }

    def import_json(self, file_path: str):
        pass

    def import_pdf(self, file_path: str, website_id: int):
        try:
            # Step 1: Extract PDF text
            text = self.pdf_parser.parse(file_path)

            # Step 2: Create chunks
            chunks = self.chunk_service.chunk(
                text=text,
                source=file_path
            )

            if not chunks:
                raise ValueError(
                    "No readable content found in this PDF."
                )

            total_chunks = len(chunks)

            return {
                "success": True,
                "pages": 1,
                "chunks": total_chunks,
                "content": text,
                "chunk_data": chunks,
            }

        except ValueError as e:
            return {
                "success": False,
                "message": str(e),
            }

        except Exception as e:
            return {
                "success": False,
                "message": f"Knowledge processing failed: {str(e)}",
            }
    
    def import_docx(self, file_path: str):
        pass

    def import_txt(self, file_path: str):
        pass

    def import_faq(self, data):
        pass
    def rebuild_website(self, website_id: int):
        pass