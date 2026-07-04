import sys
import os

sys.path.append(
    os.path.dirname(
        os.path.dirname(
            os.path.abspath(__file__)
        )
    )
)

from services.knowledge.knowledge_importer import KnowledgeImporter

importer = KnowledgeImporter()

result = importer.import_website(
    "https://example.com"
)

print(result)