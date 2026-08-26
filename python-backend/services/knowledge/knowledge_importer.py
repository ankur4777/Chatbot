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
        try:
            self.vector_service.load_website(website_id)

            pages = self.crawler.crawl(url)

            all_chunks = []
            content_parts = []

            for page in pages:
                if page["text"]:
                    content_parts.append(page["text"])

                chunks = self.chunk_service.chunk(
                    text=page["text"],
                    source=page["url"]
                )

                for chunk in chunks:

                    embedding = self.embedding_service.embed(
                        chunk["text"]
                    )

                    if embedding is None:
                        raise RuntimeError(
                            "Failed to create embedding for website content."
                        )

                    self.vector_service.add(
                        embedding,
                        chunk
                    )

                    all_chunks.append(chunk)

            if not all_chunks:
                raise ValueError(
                    "No readable content found on this website."
                )

            self._save_knowledge(website_id)

            return {
                "success": True,
                "pages": len(pages),
                "chunks": len(all_chunks),
                "content": "\n\n".join(content_parts),
                "chunk_data": all_chunks,
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

    def import_json(self, file_path: str):
        pass

    def import_pdf(self, file_path: str, website_id: int):

        try:

            self.vector_service.load_website(website_id)

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

            total_chunks = 0

        # Step 3: Create embeddings and add to vector store
            for chunk in chunks:

                embedding = self.embedding_service.embed(
                    chunk["text"]
                )

                if embedding is None:
                    raise RuntimeError(
                        "Failed to create embedding for PDF content."
                    )

                self.vector_service.add(
                    embedding,
                    chunk
                )

                total_chunks += 1

        # Step 4: Save FAISS/vector data
            self._save_knowledge(website_id)

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
