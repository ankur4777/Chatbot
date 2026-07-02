from fastapi import FastAPI
from dotenv import load_dotenv
import uvicorn
import os

from routes.health import router as health_router
from routes.chat import router as chat_router

load_dotenv()

app = FastAPI(
    title="AI Chatbot API",
    version="1.0.0"
)


@app.get("/")
def home():
    return {
        "status": "running",
        "message": "AI Chatbot API is running"
    }


app.include_router(health_router)
app.include_router(chat_router)


if __name__ == "__main__":
    uvicorn.run(
        "app:app",
        host=os.getenv("HOST", "127.0.0.1"),
        port=int(os.getenv("PORT", 8001)),
        reload=True
    )