import faiss
import numpy as np
import os
import json


class VectorService:

    def __init__(self):

        self.dimension = 384

        self.index = faiss.IndexFlatL2(self.dimension)

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