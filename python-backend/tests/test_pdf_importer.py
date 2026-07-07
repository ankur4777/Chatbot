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

result = importer.import_pdf(
    "knowledge/sample.pdf"
)

print(result)