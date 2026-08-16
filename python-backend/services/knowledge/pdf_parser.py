import fitz


class PDFParser:

    def parse(self, file_path: str):

        try:

            doc = fitz.open(file_path)

            text = ""

            for page in doc:
                text += page.get_text()

            doc.close()

            text = text.strip()

            if not text:
                raise ValueError(
                    "No readable content found in this PDF."
                )

            return text

        except ValueError:
            raise

        except Exception as e:

            raise RuntimeError(
                f"Failed to read PDF: {str(e)}"
            )