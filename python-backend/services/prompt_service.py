class PromptService:

    def build_prompt(self, message: str):

        system_prompt = """
You are a helpful AI assistant.

Answer professionally.

If you don't know the answer, say you don't know.
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