class ChunkService:

    def chunk(
        self,
        text: str,
        source: str,
        chunk_size: int = 500,
        overlap: int = 50,
    ):

        chunks = []

        start = 0
        index = 1

        while start < len(text):

            end = start + chunk_size

            chunk_text = text[start:end]

            chunks.append({
                "chunk_index": index,
                "source": source,
                "text": chunk_text,
            })

            start += chunk_size - overlap
            index += 1

        return chunks