from fastapi import APIRouter
from pydantic import BaseModel
from typing import List
from services.ai_service import AIService

router = APIRouter()

ai_service = AIService()

class ChatMessage(BaseModel):
    role: str
    content: str


class ChatRequest(BaseModel):
    message: str
    history: List[ChatMessage] = []


@router.post("/chat")

def chat(request: ChatRequest):

    response = ai_service.generate_response(
    request.message,
    request.history
)
    return response