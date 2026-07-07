import sys
import os

sys.path.append(
    os.path.dirname(
        os.path.dirname(
            os.path.abspath(__file__)
        )
    )
)

from services.knowledge.pdf_parser import PDFParser

parser = PDFParser()

text = parser.parse("knowledge/sample.pdf")

print(text[:1000])