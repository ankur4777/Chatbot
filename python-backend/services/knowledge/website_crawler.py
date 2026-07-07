import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse

from services.knowledge.website_parser import WebsiteParser

class WebsiteCrawler:

    def __init__(self):
        self.parser = WebsiteParser()

    def crawl(self, url: str):

        response = requests.get(url, timeout=15)
        response.raise_for_status()

        soup = BeautifulSoup(response.text, "html.parser")

        links = set()

        base_domain = urlparse(url).netloc

        for link in soup.find_all("a", href=True):

            absolute_url = urljoin(url, link["href"])

            parsed = urlparse(absolute_url)

            if parsed.netloc == base_domain:
                links.add(absolute_url)

        pages = []

        for link in links:

            try:

                text = self.parser.parse(link)


                pages.append({
                    "url": link,
                    "text": text
                })

            except Exception as e:

                print(f"Failed to parse {link}: {e}")
        return pages