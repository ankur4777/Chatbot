from pathlib import Path


class LoaderService:

    def load_text(self, file_path: str):

        path = Path(file_path)

        with open(path, "r", encoding="utf-8") as file:

            return file.read()