from fastapi import APIRouter
from pydantic import BaseModel

from services.ai_service import AIService

router = APIRouter()

ai_service = AIService()


class ChatRequest(BaseModel):
    message: str


@router.post("/chat")
def chat(request: ChatRequest):

    response = ai_service.generate_response(request.message)

    return response