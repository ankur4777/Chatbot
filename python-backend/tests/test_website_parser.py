import sys
import os

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from services.knowledge.website_parser import WebsiteParser

parser = WebsiteParser()

text = parser.parse("https://example.com")

print(text[:1000])