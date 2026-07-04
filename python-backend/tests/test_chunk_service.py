import sys
import os

sys.path.append(
    os.path.dirname(
        os.path.dirname(
            os.path.abspath(__file__)
        )
    )
)

from services.knowledge.chunk_service import ChunkService

service = ChunkService()

text = "Hello World " * 300

chunks = service.chunk(
    text=text,
    source="https://example.com"
)

print("Total Chunks:", len(chunks))

print(chunks[0])