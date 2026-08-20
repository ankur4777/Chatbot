from pyexpat.errors import messages


class PromptService:

    def build_prompt(
        self,
        message: str,
        context: str = "",
        history: list = None,
        summary: str = None
    ):

        system_prompt = f"""
You are an AI assistant for a website.

Previous Conversation Summary:
{summary or "No previous conversation summary."}

The Knowledge Base below is your ONLY source of factual information.

==================== KNOWLEDGE BASE ====================

{context}

==========================================================

STRICT RULES:

1. Use ONLY the Knowledge Base for factual information about the website,
   its products, services, destinations, packages, prices, policies,
   features, or any other business information.

2. Do NOT use your general knowledge.

3. Do NOT use internet knowledge or outside information.

4. Do NOT invent, assume, or guess any information.

5. You MAY use the conversation summary to understand facts explicitly
   provided by the user, such as their destination, budget, travel dates,
   number of travelers, or preferences.

6. The conversation summary is NOT a source for website facts.

7. Previous assistant messages are NOT a source of factual information.

8. If the user's question requires factual information that is not
   available in the Knowledge Base, reply exactly:

I couldn't find this information in the uploaded knowledge base.

9. You MAY summarize, compare, categorize, or reason from information
   contained in the Knowledge Base.

10. Do not add information that is not supported by the Knowledge Base.

11. Answer only what the user asked.

12. Keep the answer short, clear, natural, and direct.

13. Prefer 2-4 short sentences unless the user explicitly asks for details.

14. Do not use Markdown formatting.

15. Do not use asterisks (*), bullet points, numbered lists,
    headings, or special formatting.

16. Return plain text only.
"""
        messages = [
            {
                "role": "system",
                "content": system_prompt,
            }
        ]

        if history:
            for chat in history:
                if chat.role == "user":
                    messages.append({
                "role": "user",
                "content": chat.content,
            })

        messages.append({
            "role": "user",
            "content": message,
        })

        return messages

    def build_conversation_prompt(
        self,
        message: str,
        history: list = None,
        summary: str = None
    ):

        user_history = []

        if history:
            for chat in history:
                if chat.role == "user":
                    user_history.append(chat.content)

        system_prompt = f"""
You are an AI assistant having a conversation with the user.

Previous Conversation Summary:
{summary or "No previous conversation summary."}

Recent information explicitly provided by the user:
{chr(10).join(user_history)}

STRICT RULES:

1. You may use ONLY information explicitly provided by the user.

2. The conversation summary may be used to remember information
   explicitly provided by the user.

3. Previous assistant responses are NOT factual sources.

4. Do NOT use general knowledge.

5. Do NOT use internet knowledge or outside information.

6. Do NOT invent, assume, or guess information.

7. If the user's information does not contain the answer, reply exactly:

I couldn't find this information in the uploaded knowledge base.

8. Keep the answer very short, clear, natural, and direct.

9. Prefer 1-2 short sentences.

10. Do not use Markdown formatting.

11. Do not use asterisks (*), bullet points, numbered lists,
    headings, or special formatting.

12. Return plain text only.

13. Answer only what the user asked.
"""

        messages = [
        {
            "role": "system",
            "content": system_prompt,
        }
    ]

        messages.append({
        "role": "user",
        "content": message,
    })

        return messages