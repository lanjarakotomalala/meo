"""Create the static preview catalogue from WooCommerce Store API snapshots.

Usage: python scripts/build_catalog.py products-page1.json products-page2.json categories.json
The script writes data/catalog.json relative to the preview root.
"""

from __future__ import annotations

import html
import json
import re
import sys
from datetime import date
from html.parser import HTMLParser
from pathlib import Path


ROOT = Path(__file__).resolve().parent.parent


class TextOnly(HTMLParser):
    def __init__(self) -> None:
        super().__init__()
        self.parts: list[str] = []
        self.suppressed = 0

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        if tag in {"style", "script"}:
            self.suppressed += 1
        elif tag in {"p", "div", "br", "li", "tr", "td", "th", "h1", "h2", "h3", "h4"}:
            self.parts.append(" ")

    def handle_endtag(self, tag: str) -> None:
        if tag in {"style", "script"} and self.suppressed:
            self.suppressed -= 1
        elif tag in {"p", "div", "li", "tr", "td", "th", "h1", "h2", "h3", "h4"}:
            self.parts.append(" ")

    def handle_data(self, data: str) -> None:
        if not self.suppressed:
            self.parts.append(data)


def plain(value: str | None) -> str:
    value = re.sub(r"<p[^>]*>\s*table\s*\{.*?</p>", "", value or "", flags=re.IGNORECASE | re.DOTALL)
    parser = TextOnly()
    parser.feed(value)
    return re.sub(r"\s+", " ", html.unescape("".join(parser.parts))).strip()


def price(value: str | None, minor_unit: int) -> float | None:
    if value in (None, ""):
        return None
    return int(value) / 10**minor_unit


def main() -> None:
    if len(sys.argv) < 3:
        raise SystemExit("Expected product snapshots followed by a category snapshot")
    *product_files, category_file = map(Path, sys.argv[1:])
    raw_products = [item for path in product_files for item in json.loads(path.read_text(encoding="utf-8"))]
    raw_categories = json.loads(category_file.read_text(encoding="utf-8"))
    products = []
    for item in raw_products:
        prices = item.get("prices") or {}
        unit = prices.get("currency_minor_unit", 2)
        products.append(
            {
                "id": str(item["id"]),
                "name": plain(item.get("name")),
                "slug": item["slug"],
                "path": item["permalink"].replace("https://www.meo.fr", ""),
                "type": item.get("type", "simple"),
                "sku": item.get("sku") or "",
                "short": plain(item.get("short_description")),
                "description": plain(item.get("description")),
                "price": price(prices.get("price"), unit),
                "regularPrice": price(prices.get("regular_price"), unit),
                "onSale": bool(item.get("on_sale")),
                "rating": float(item.get("average_rating") or 0),
                "reviewCount": int(item.get("review_count") or 0),
                "images": [
                    {"src": image["src"], "alt": plain(image.get("alt"))}
                    for image in item.get("images", [])
                    if image.get("src")
                ],
                "categoryIds": [category["id"] for category in item.get("categories", [])],
                "attributes": [
                    {
                        "name": plain(attribute.get("name")),
                        "options": [plain(term.get("name")) for term in attribute.get("terms", [])],
                        "hasVariations": bool(attribute.get("has_variations")),
                    }
                    for attribute in item.get("attributes", [])
                ],
                "variations": item.get("variations", []),
                "purchasable": bool(item.get("is_purchasable")),
                "inStock": bool(item.get("is_in_stock")),
            }
        )

    categories = [
        {
            "id": category["id"],
            "name": plain(category.get("name")),
            "slug": category["slug"],
            "path": category["permalink"].replace("https://www.meo.fr", ""),
            "description": plain(category.get("description")),
            "parent": category.get("parent") or 0,
            "count": category.get("count") or 0,
            "image": (category.get("image") or {}).get("src") or "",
        }
        for category in raw_categories
    ]
    output = {
        "snapshot": date.today().isoformat(),
        "source": "Public WooCommerce Store API, meo.fr",
        "products": products,
        "categories": categories,
    }
    destination = ROOT / "data" / "catalog.json"
    destination.parent.mkdir(parents=True, exist_ok=True)
    destination.write_text(json.dumps(output, ensure_ascii=False, separators=(",", ":")), encoding="utf-8")
    print(f"Wrote {len(products)} products and {len(categories)} categories to {destination}")


if __name__ == "__main__":
    main()
