from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
from dotenv import load_dotenv
from routes.upload import router as upload_router
import uvicorn
from services.knowledge.startup_service import StartupService
from routes.knowledge import router as knowledge_router
import os
import secrets

from routes.health import router as health_router
from routes.chat import router as chat_router

load_dotenv()

app = FastAPI(
    title="AI Chatbot API",
    version="1.0.0"
)

@app.middleware("http")
async def authenticate_request(request: Request, call_next):

    # Public endpoints
    if request.url.path in ["/", "/health"]:
        return await call_next(request)

    expected_key = os.getenv("PYTHON_API_KEY")

    # API key configuration check
    if not expected_key:
        return JSONResponse(
            status_code=500,
            content={
                "success": False,
                "message": "AI API authentication is not configured."
            }
        )

    # Read Authorization header
    authorization = request.headers.get("Authorization")

    if not authorization:
        return JSONResponse(
            status_code=401,
            content={
                "success": False,
                "message": "Missing authentication credentials."
            }
        )

    # Expected format:
    # Authorization: Bearer YOUR_API_KEY
    if not authorization.startswith("Bearer "):
        return JSONResponse(
            status_code=401,
            content={
                "success": False,
                "message": "Invalid authentication format."
            }
        )

    provided_key = authorization.replace("Bearer ", "", 1).strip()

    # Secure comparison
    if not secrets.compare_digest(provided_key, expected_key):
        return JSONResponse(
            status_code=401,
            content={
                "success": False,
                "message": "Invalid API key."
            }
        )

    return await call_next(request)

@app.get("/")
def home():
    return {
        "status": "running",
        "message": "AI Chatbot API is running"
    }


app.include_router(health_router)
app.include_router(chat_router)
app.include_router(upload_router)
app.include_router(knowledge_router)

if __name__ == "__main__":
    uvicorn.run(
        "app:app",
        host=os.getenv("HOST", "127.0.0.1"),
        port=int(os.getenv("PORT", 8001)),
        reload=True
    )