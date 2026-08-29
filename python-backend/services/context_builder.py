class ContextBuilder:
    MAX_CHARS_PER_CHUNK = 1200

    def build(self, chunks: list) -> str:

        if not chunks:
            return ""

        context = []

        for index, chunk in enumerate(chunks, start=1):

            title = chunk.get("title") or "Knowledge Base"

            source = chunk.get("source") or "Unknown"

            text = chunk.get("text", "").strip()

            if len(text) > self.MAX_CHARS_PER_CHUNK:
                text = text[:self.MAX_CHARS_PER_CHUNK].rsplit(" ", 1)[0]

            context.append(
                f"""
[{index}]
Title: {title}
Source: {source}

{text}
"""
            )

        return "\n\n".join(context)
