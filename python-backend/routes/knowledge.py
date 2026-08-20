from fastapi import APIRouter
from pydantic import BaseModel
from typing import List
from services.knowledge.knowledge_importer import KnowledgeImporter
from services.knowledge.embedding_service import EmbeddingService
from services.knowledge.vector_store import vector_store

router = APIRouter()

embedding_service = EmbeddingService()
knowledge_importer = KnowledgeImporter()

class ClearRequest(BaseModel):
    website_id: int

class Chunk(BaseModel):
    id: int
    website_id: int
    knowledge_base_id: int
    chunk_order: int
    text: str
    title: str | None = None
    source: str | None = None
    source_type: str | None = None


class SyncRequest(BaseModel):
    knowledge_base_id: int
    chunks: List[Chunk]
    website_id: int
class ImportRequest(BaseModel):
    type: str
    source: str
    website_id: int
    

@router.post("/knowledge/import")
def import_knowledge(request: ImportRequest):

    print("KNOWLEDGE IMPORT REQUEST:", request)

    if request.type == "pdf":
        result = knowledge_importer.import_pdf(
            file_path=request.source,
            website_id=request.website_id
        )

        print("KNOWLEDGE IMPORT RESULT:", result)

        return result

    return {
        "success": False,
        "message": f"Unsupported source type: {request.type}"
    }
@router.post("/knowledge/sync")
def sync(request: SyncRequest):

    vector_store.reset()

    texts = [
        chunk.text
        for chunk in request.chunks
    ]

    embeddings = embedding_service.embed_batch(texts)

    for chunk, embedding in zip(request.chunks, embeddings):

        vector_store.add(
            embedding,
            {
                "id": chunk.id,
                "website_id": chunk.website_id,
                "knowledge_base_id": chunk.knowledge_base_id,
                "chunk_order": chunk.chunk_order,
                "title": chunk.title,
                "source": chunk.source,
                "source_type": chunk.source_type,
                "text": chunk.text,
            }
        )

    vector_store.save_website(request.website_id)

    return {
        "success": True,
        "chunks": len(request.chunks)
    }
@router.post("/knowledge/clear")
def clear(request: ClearRequest):

    vector_store.clear_website(
        request.website_id
    )

    return {
        "success": True
    }