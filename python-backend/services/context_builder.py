class ContextBuilder:

    def build(self, chunks: list) -> str:

        if not chunks:
            return ""

        context = []

        for index, chunk in enumerate(chunks, start=1):

            title = chunk.get("title") or "Knowledge Base"

            source = chunk.get("source") or "Unknown"

            text = chunk.get("text", "").strip()

            context.append(
                f"""
[{index}]
Title: {title}
Source: {source}

{text}
"""
            )

        return "\n\n".join(context)