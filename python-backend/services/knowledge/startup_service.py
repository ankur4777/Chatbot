import os
from services.knowledge.vector_store import vector_store
from services.knowledge.metadata_service import MetadataService


class StartupService:

    def __init__(self):

        self.metadata_service = MetadataService()

    def load(self):

        base_dir = os.path.dirname(
            os.path.dirname(
                os.path.dirname(__file__)
            )
        )

        faiss_path = os.path.join(
            base_dir,
            "data",
            "faiss",
            "index.faiss"
        )

        metadata_path = os.path.join(
            base_dir,
            "data",
            "metadata",
            "metadata.json"
        )

        if os.path.exists(faiss_path):
            vector_store.load_index(faiss_path)

        vector_store.chunks = self.metadata_service.load(
            metadata_path
        )

        print("Knowledge Base Loaded Successfully")