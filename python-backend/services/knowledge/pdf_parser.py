import fitz


class PDFParser:

    def parse(self, file_path: str):

        doc = fitz.open(file_path)

        text = ""

        for page in doc:

            text += page.get_text()

        doc.close()

        return text