import sys
import os

sys.path.append(
    os.path.dirname(
        os.path.dirname(
            os.path.abspath(__file__)
        )
    )
)


from services.knowledge.website_crawler import WebsiteCrawler

crawler = WebsiteCrawler()

pages = crawler.crawl("https://www.python.org")

print("Total Pages:", len(pages))

print()

for page in pages[:3]:

    print(page["url"])

    print("-" * 50)

    print(page["text"][:500])

    print("\n\n")