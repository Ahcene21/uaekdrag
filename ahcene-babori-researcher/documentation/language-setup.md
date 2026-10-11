# Language setup – English, French, Arabic (Polylang)

The website does **not** use machine translation. Every public interface string (buttons, labels, forms, filters, breadcrumbs, messages) is already written in English, French and Arabic inside the plugin (`includes/strings.php`). Page content is translated by you (or a translator) with Polylang.

## 1. Add the languages
*Languages → Languages → Add new language*

| Language | Locale | Language code (slug) | Text direction | Flag |
|---|---|---|---|---|
| English | en_US | `en` | Left to right | any |
| Français | fr_FR | `fr` | Left to right | any |
| العربية | ar | `ar` | **Right to left** | any |

Use the exact codes `en`, `fr`, `ar` (the theme’s switcher and the content installer rely on them).

## 2. Settings (*Languages → Settings → URL modifications*)
- *The language is set from the directory name in pretty permalinks* → URLs `/en/`, `/fr/`, `/ar/`.
- Untick “Hide URL language information for default language” if you want `/en/` visible.
- *Detect browser language*: optional.
- Tick **Custom post types and Taxonomies** support in *Languages → Settings → Custom post types and Taxonomies*: the plugin already declares Publications, Books, Projects, Presentations, Academic Service, Teaching, Blog, About Timeline and their taxonomies; they should be pre-ticked.

## 3. Create the content
Fastest: *Researcher → Sample content → Install structure / sample entries* – creates every page, category, research area, menu and sample entry in the three languages and links them as translations.
Manually: in any list, click the **+** icon of a language in the Polylang column to create its translation (language-neutral fields such as year, DOI, URLs and files are copied from the source).

## 4. Menus
*Appearance → Menus* + *Languages → Settings → Menus*: assign a menu to *Primary menu* and *Footer menu* for each language.

## 5. Language switcher
The header shows **EN | FR | AR** (links to the translation of the current page). Nothing to configure.

## 6. Right-to-left (Arabic)
Automatic: `<html lang="ar" dir="rtl">`, mirrored navigation, cards, grids, forms, breadcrumbs, timeline and pagination; Noto Sans Arabic typeface; Arabic month names; date and number formats suited to the Maghreb (Western digits).

## 7. Per-language profile texts
*Researcher → Profile & Photo*: professional title, positioning statement, affiliation and short biography each have three fields (EN / FR / AR). *Researcher → Statistics*: labels in three languages. *Researcher → CV files*: one PDF per language.

## 8. Terminology used
Digital libraries = bibliothèques numériques = المكتبات الرقمية · Institutional repository = dépôt institutionnel = المستودع المؤسسي · Open access = accès ouvert = الوصول الحر · Open science = science ouverte = العلم المفتوح · Scholarly communication = communication scientifique = الاتصال العلمي · Metadata = métadonnées = البيانات الوصفية · Research data management = gestion des données de la recherche = إدارة بيانات البحث. Edit `includes/strings.php` if your institution prefers other terms.

## 9. SEO per language
Polylang adds `hreflang` links. Yoast/Rank Math: write meta descriptions per language page.
