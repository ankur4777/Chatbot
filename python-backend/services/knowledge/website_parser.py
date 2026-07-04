import requests
from bs4 import BeautifulSoup


class WebsiteParser:

    def parse(self, url: str):

        response = requests.get(url, timeout=15)

        response.raise_for_status()

        soup = BeautifulSoup(response.text, "html.parser")

        # Remove unwanted tags
        for tag in soup(["script", "style", "noscript"]):
            tag.decompose()

        text = soup.get_text(separator=" ", strip=True)

        return text