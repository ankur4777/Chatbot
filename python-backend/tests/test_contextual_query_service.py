import unittest

from services.contextual_query_service import ContextualQueryService


class ContextualQueryServiceTest(unittest.TestCase):
    def setUp(self):
        self.service = ContextualQueryService()

    def test_it_uses_the_latest_concrete_topic(self):
        history = [
            {"role": "user", "content": "tell me about dubai package"},
            {"role": "assistant", "content": "Dubai package details"},
            {"role": "user", "content": "tell me package price of manali"},
            {"role": "assistant", "content": "The Manali price is INR 12,999"},
            {"role": "user", "content": "tell me package include in it"},
        ]

        query = self.service.build_search_query(
            "tell me package include in it",
            history,
        )

        self.assertIn("manali", query.lower())
        self.assertNotIn("dubai", query.lower())
        self.assertNotRegex(query.lower(), r"\bit\b")

    def test_conversational_messages_between_questions_are_ignored(self):
        history = [
            {"role": "user", "content": "tell me about bali package"},
            {"role": "assistant", "content": "Bali package details"},
            {"role": "user", "content": "thank you"},
            {"role": "assistant", "content": "You're welcome"},
            {"role": "user", "content": "what is included in it"},
        ]

        query = self.service.build_search_query(
            "what is included in it",
            history,
        )

        self.assertIn("bali", query.lower())

    def test_generic_follow_up_walks_back_to_recent_concrete_topic(self):
        history = [
            {"role": "user", "content": "tell me about singapore premium"},
            {"role": "assistant", "content": "Singapore details"},
            {"role": "user", "content": "package price"},
            {"role": "assistant", "content": "Package price details"},
            {"role": "user", "content": "what does it include"},
        ]

        query = self.service.build_search_query(
            "what does it include",
            history,
        )

        self.assertIn("singapore premium", query.lower())

    def test_explicit_current_topic_does_not_use_old_context(self):
        history = [
            {"role": "user", "content": "tell me about dubai package"},
            {"role": "assistant", "content": "Dubai details"},
            {"role": "user", "content": "what is the manali package price"},
        ]

        query = self.service.build_search_query(
            "what is the manali package price",
            history,
        )

        self.assertEqual("what is the manali package price", query)

    def test_common_reference_words_use_the_latest_topic(self):
        history = [
            {"role": "user", "content": "tell me about kerala package"},
            {"role": "assistant", "content": "Kerala package details"},
        ]
        follow_ups = (
            "what is included in this",
            "tell me more about that",
            "what do they include",
            "give me their details",
            "show the same price",
            "what is included in those",
        )

        for follow_up in follow_ups:
            with self.subTest(follow_up=follow_up):
                query = self.service.build_search_query(
                    follow_up,
                    history + [{"role": "user", "content": follow_up}],
                )

                self.assertIn("kerala", query.lower())


if __name__ == "__main__":
    unittest.main()
