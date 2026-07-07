from fastapi import APIRouter
from pydantic import BaseModel

from services.knowledge.knowledge_importer import KnowledgeImporter

router = APIRouter(
    prefix="/knowledge",
    tags=["Knowledge"]
)

importer = KnowledgeImporter()


class ImportRequest(BaseModel):
    type: str
    source: str


@router.post("/import")
def import_knowledge(request: ImportRequest):

    if request.type == "website":
        return importer.import_website(
            request.source
        )

    elif request.type == "pdf":
        return importer.import_pdf(
            request.source
        )

    return {
        "success": False,
        "message": "Unsupported knowledge source."
    }