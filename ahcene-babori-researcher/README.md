# Dr. Ahcene Babori – Academic Researcher Website (WordPress)

A real, installable **WordPress** website for *Dr. Ahcene Babori – Assistant Professor | Library & Information Science Researcher* (`https://ahcenebabori.com`): trilingual (English / French / Arabic with full RTL), accessible, SEO-ready, with no external runtime dependencies.

> *Researching the intersection of Digital Libraries, Open Science, Scholarly Communication and Artificial Intelligence.*

## Package contents

| Path | What it is |
|---|---|
| `dist/ahcene-babori-researcher-complete.zip` | **Everything** in one download (also published as `dist/ahcene-babori-researcher.zip`) |
| `dist/ahcene-babori-academic-theme.zip` | Theme – upload in *Appearance → Themes* |
| `dist/ahcene-babori-core-plugin.zip` | Plugin – upload in *Plugins → Add New* |
| `demo-content/demo-content.xml` | WordPress (WXR) starter content |
| `documentation/` | `installation-guide.md`, `language-setup.md`, `QA-REPORT.md` |
| `theme/ahcene-babori-academic/` | Theme source |
| `plugin/ahcene-babori-core/` | Plugin source |
| `tools/` | `build-wxr.php`, `build-package.sh` (regenerate XML and ZIPs) |
| `INSTALLATION.md`, `CHANGELOG.md` | Install guide, release notes |

## Architecture

* **Theme `ahcene-babori-academic`** – design only: header/footer, EN|FR|AR switcher, hero, templates, responsive CSS, RTL, fonts (bundled Inter + Noto Sans Arabic), print CV.
* **Plugin `ahcene-babori-core`** – all data and behaviour: 8 custom post types (Publications, Books, Research Projects, Presentations, Academic Service, Teaching, Research Resources, Research Notes), taxonomies (Publication Type, Research Area, Year, Keywords, Service Category, Teaching Section, Resource Type, Note Category/Tags), custom fields with admin meta boxes, researcher profile & identifiers, editable statistics, CV files, contact form, SEO/schema/citation tags, search and publication filters, REST endpoints, Polylang integration, import framework, sample-content installer.
* **Content survives a theme change** because data lives in the plugin.
* **Academic knowledge network:** Research Area → Projects → Publications → Presentations → Research Notes, connected by shared research-area taxonomy and explicit relation fields (project ↔ publications ↔ presentations ↔ notes). Research-area pages list everything linked to the area.

## Features checklist

- Multilingual EN/FR/AR (Polylang), `dir="rtl"` for Arabic, language switcher `EN | FR | AR`, URLs `/en/ /fr/ /ar/`
- Homepage: hero + portrait (Media Library), 8 research-area cards, editable impact statistics, featured publications/projects, teaching/service/presentations, latest notes, collaboration call-to-action
- Publications with authors, DOI, PDF, Open Access, repository, citation + BibTeX, related items, filters (year / type / area / keyword)
- About Timeline (education, positions, experience, awards – editable), Books, Projects, Presentations (PDF/PPTX/video), Academic Service timeline, Teaching portfolio, Resources (PDF/DOCX/PPTX/XLSX/CSV downloads), Research Notes (reading time, categories, tags)
- Online CV page with sidebar contents, per-language PDF download, print stylesheet
- Contact form with nonce, honeypot, time token, rate limit, validation, optional dashboard storage
- Researcher identifiers (ORCID, Scholar, Scopus, ResearchGate, LinkedIn, institutional) – **empty until you add verified values**
- SEO: meta, canonical, Open Graph, schema.org (Person, WebSite, ScholarlyArticle, Book, ResearchProject, BreadcrumbList), Google Scholar `citation_*` tags, Yoast/Rank Math compatibility, XML sitemap
- Site-wide search (pages, publications, books, projects, presentations, notes, resources) also over authors/journal/abstract/keywords
- Accessibility: skip link, semantic landmarks, visible focus, keyboard menu, ARIA states, accessible forms with error summary, AA colour contrast, reduced-motion support
- Performance: no external requests, self-hosted variable fonts, one 32 KB stylesheet, one 2 KB deferred script, lazy-loaded images, WebP sub-sizes, minimal DOM
- Security: capability checks, nonces, input sanitisation, escaped output, no secrets in the package
- Future connectors: Crossref (ready, DOI → pending record), ORCID, OpenAlex, OpenAIRE, OAI-PMH, DSpace (registered as *planned*); every import is *Pending Review* – never auto-published; no fake responses

## Honesty policy for content
Nothing about the researcher has been invented: no DOIs, ISBNs, citation counts, h-index, awards, positions, grants, ORCID/Scopus IDs or publications. Unknown data is the literal marker `[ADD VERIFIED INFORMATION]`; sample entries carry a visible *Demo* label. The only values pre-filled from the brief are the name, title, positioning statement, expertise list, public e-mail, LinkedIn URL and the five example impact statistics (to be confirmed by the owner).

## Quick start
See [`INSTALLATION.md`](INSTALLATION.md). In short: install WordPress → upload theme → upload plugin → install Polylang (EN/FR/AR) → *Researcher → Sample content* → upload photo and CV → add identifiers → replace demo content.

## Requirements
WordPress 6.0+ (tested on 6.5), PHP 7.4+ (tested on 8.3), MySQL/MariaDB. Polylang (free) for multilingual use. Optional: Yoast SEO or Rank Math.

## Licence
Code: GPL-2.0-or-later. Fonts (Inter, Noto Sans Arabic, Almarai): SIL Open Font Licence 1.1 (licence files are in `theme/ahcene-babori-academic/assets/fonts/`). Personal content © Dr. Ahcene Babori.
