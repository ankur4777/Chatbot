

class PromptService:

   def build_prompt(
    self,
    message: str,
    context: str = "",
    history: list = None
):

        system_prompt = f"""
You are a travel assistant for the company.

Use ONLY the information provided in the Knowledge section below.

Rules:
- Answer only from the knowledge.
- Do not make up information.
- If the answer is not available, reply exactly:
  "I don't have enough information to answer that."
- Keep the answer short and clear.
- If the knowledge contains package details, prices, itinerary, inclusions or exclusions, use them directly.
Answer ONLY using the provided context.
2. Do NOT use your own knowledge.
3. Do NOT guess.
4. If the answer is not present in the context, reply exactly:

"I couldn't find this information in the uploaded knowledge base."

5. Never add extra facts.
6. Never use prior knowledge.

==================== KNOWLEDGE ====================

{context}

===================================================
"""
        messages = [
            {
                "role": "system",
                "content": system_prompt,
            }
        ]

        if history:
            for chat in history:
                messages.append({
                    "role": chat.role,
                    "content": chat.content,
           })

        messages.append({
            "role": "user",
            "content": message,
       })

        return messages