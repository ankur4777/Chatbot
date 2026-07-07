class PromptService:

    def build_prompt(self, message: str, context: str = ""):

        system_prompt = f"""
You are a helpful AI assistant.

Answer ONLY from the provided knowledge.

If the answer is not present in the knowledge, reply:
"I don't have enough information to answer that."

Knowledge:

{context}
"""

        return [
            {
                "role": "system",
                "content": system_prompt,
            },
            {
                "role": "user",
                "content": message,
            },
        ]