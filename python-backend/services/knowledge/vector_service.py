import faiss
import numpy as np


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

            results.append(self.chunks[index])

        return results