from fastapi import APIRouter
from httpx import request
from pydantic import BaseModel
from typing import List
from services.ai_service import AIService
from services.knowledge.retrieval_service import RetrievalService

router = APIRouter()

ai_service = AIService()

retrieval_service = RetrievalService()

class ChatMessage(BaseModel):
    role: str
    content: str


class ChatRequest(BaseModel):
    message: str
    website_id: int
    history: List[ChatMessage] = []
    summary: str | None = None
    summary_messages: List[ChatMessage] = []

class SearchRequest(BaseModel):
    website_id: int
    question: str
    k: int = 5

@router.post("/chat")

def chat(request: ChatRequest):
    print("HISTORY COUNT BEFORE AI:", len(request.history))
    print("HISTORY:", request.history)
    response = ai_service.generate_response(
    website_id=request.website_id,
    message=request.message,
    history=request.history,
    summary=request.summary,
)
    if request.summary_messages:

        print("SUMMARY GENERATION STARTED")
        print("SUMMARY MESSAGES COUNT:", len(request.summary_messages))

        new_summary = ai_service.generate_summary(
        request.summary_messages,
        request.summary
    )

        print("NEW SUMMARY:", new_summary)

        response["summary"] = new_summary
    return response
@router.post("/search")
def search(request: SearchRequest):

    results = retrieval_service.retrieve(
        website_id=request.website_id,
        question=request.question,
        k=request.k,
    )

    return {
        "success": True,
        "count": len(results),
        "results": results,
    }
