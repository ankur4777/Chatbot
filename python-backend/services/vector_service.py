import faiss
import numpy as np


class VectorService:

    def create_index(self, embeddings):

        embeddings = np.array(
            embeddings,
            dtype="float32"
        )

        index = faiss.IndexFlatL2(
            embeddings.shape[1]
        )

        index.add(embeddings)

        return index