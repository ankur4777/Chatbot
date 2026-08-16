import re

class ChunkService:

    def chunk(
        self,
        text: str,
        source: str,
        max_words: int = 250,
        overlap_sentences: int = 2,
    ):

        text = re.sub(r"\s+", " ", text).strip()

        if not text:
            return []

        sentences = re.split(
    r'(?<=[.!?])\s+|\n{2,}',
    text
)

        chunks = []
        current_chunk = []
        current_words = 0
        chunk_index = 1

        for sentence in sentences:

            sentence = sentence.strip()

            if not sentence:
                continue

            sentence_words = len(sentence.split())

            if (
                current_words + sentence_words > max_words
                and current_chunk
            ):

                chunks.append({
                    "chunk_index": chunk_index,
                    "source": source,
                    "text": " ".join(current_chunk),
                })

                chunk_index += 1

                current_chunk = current_chunk[-overlap_sentences:]

                current_words = sum(
                    len(s.split())
                    for s in current_chunk
                )

            current_chunk.append(sentence)
            current_words += sentence_words

        if current_chunk:

            chunks.append({
                "chunk_index": chunk_index,
                "source": source,
                "text": " ".join(current_chunk),
            })

        return chunks