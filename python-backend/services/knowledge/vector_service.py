import faiss
import numpy as np
import os
import json


class VectorService:

    def __init__(self):

        self.dimension = 384

        self.index = faiss.IndexFlatIP(self.dimension)
        self.chunks = []

    def add(self, embedding, metadata):

        vector = np.array([embedding], dtype="float32")

        self.index.add(vector)

        self.chunks.append(metadata)

    def search(self, embedding, k=5):

        vector = np.array([embedding], dtype="float32")

        distances, indices = self.index.search(vector, k)

        results = []
        for index in indices[0]:

            if index == -1:
                continue

            if index < len(self.chunks):
                results.append(self.chunks[index])

        return results

    def save_index(self, path):

        os.makedirs(
            os.path.dirname(path),
            exist_ok=True
        )

        faiss.write_index(
            self.index,
            path
        )

    def load_index(self, path):

        if os.path.exists(path):
            self.index = faiss.read_index(path)

    def save_metadata(self, path):

        os.makedirs(
            os.path.dirname(path),
            exist_ok=True
        )

        with open(
            path,
            "w",
            encoding="utf-8"
        ) as file:

            json.dump(
                self.chunks,
                file,
                ensure_ascii=False,
                indent=4
            )

    def load_metadata(self, path):

        if not os.path.exists(path):
            return

        with open(
            path,
            "r",
            encoding="utf-8"
        ) as file:

            self.chunks = json.load(file)

    def reset(self):

        self.index = faiss.IndexFlatIP(self.dimension)

        self.chunks = []

    def get_storage_paths(self, website_id):

        base_path = f"storage/faiss/website_{website_id}"

        return {
            "index": f"{base_path}/index.faiss",
            "metadata": f"{base_path}/chunks.json"
        }
    
    def load_website(self, website_id):

        self.reset()

        paths = self.get_storage_paths(website_id)

        self.load_index(paths["index"])

        self.load_metadata(paths["metadata"])

    def save_website(self, website_id):

        paths = self.get_storage_paths(website_id)

        self.save_index(paths["index"])

        self.save_metadata(paths["metadata"])

    def clear_website(self, website_id):

        paths = self.get_storage_paths(website_id)

        if os.path.exists(paths["index"]):
            os.remove(paths["index"])

        if os.path.exists(paths["metadata"]):
            os.remove(paths["metadata"])

        self.reset()