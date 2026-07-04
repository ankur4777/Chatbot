from services.knowledge.website_parser import WebsiteParser
from services.knowledge.json_parser import JsonParser
from services.knowledge.pdf_parser import PdfParser
from services.knowledge.docx_parser import DocxParser
from services.knowledge.txt_parser import TxtParser
from services.knowledge.faq_parser import FaqParser

from services.knowledge.chunk_service import ChunkService
from services.knowledge.embedding_service import EmbeddingService
from services.knowledge.vector_service import VectorService


class KnowledgeService:

    def __init__(self):

        self.website = WebsiteParser()
        self.json = JsonParser()
        self.pdf = PdfParser()
        self.docx = DocxParser()
        self.txt = TxtParser()
        self.faq = FaqParser()

        self.chunk = ChunkService()

        self.embedding = EmbeddingService()

        self.vector = VectorService()

    def import_knowledge(self):

        pass