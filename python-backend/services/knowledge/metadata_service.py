import json
import os


class MetadataService:

    def save(self, metadata, path):

        os.makedirs(
            os.path.dirname(path),
            exist_ok=True
        )

        with open(
            path,
            "w",
            encoding="utf-8"
        ) as file:

            json.dump(
                metadata,
                file,
                ensure_ascii=False,
                indent=4
            )

    def load(self, path):

        if not os.path.exists(path):
            return []

        with open(
            path,
            "r",
            encoding="utf-8"
        ) as file:

            return json.load(file)