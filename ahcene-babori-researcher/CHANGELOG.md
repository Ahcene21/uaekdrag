# Changelog

## 1.0.3 – 2026-10-11
- **Almarai (Regular 400, Bold 700) is now the primary typeface of the Arabic interface**, bundled in the theme (SIL OFL); Noto Sans Arabic remains as fallback.

## 1.0.2 – 2026-10-11
- **About page is now a chronological timeline** (Education & Academic Qualifications, Academic Positions), in EN / FR / AR, using the owner’s CV details; same navy/teal design. Pages still holding the untouched sample text are updated automatically when the plugin is updated; edited pages are never overwritten.

## 1.0.1 – 2026-10-11
- Fix: menu links to starter pages (e.g. *About / نبذة عني*) are now resolved at run time to the page of the current language, so they cannot point to a wrong-language or missing page.
- “Research” removed from the starter primary menus (installer, `demo-content.xml`, fallback menu); the plugin removes it from existing *Primary menu (EN/FR/AR)* menus once. The Research page itself is kept.
- **Presentation Type is now a taxonomy** (*Presentations → Presentation Types*): add, rename and translate types freely. Default types: Keynote, Invited talk, Conference paper, Poster, Workshop, Webinar, Seminar. Old values are migrated automatically. Types appear as filter chips on the Presentations archive.

## 1.0.0 – 2026-10-08
First release.

### Plugin `ahcene-babori-core`
- Custom post types: Publications, Books, Research Projects, Presentations, Academic Service, Teaching, Research Resources, Research Notes (+ private Contact messages)
- Taxonomies: Publication Type, Research Area (icon + order), Year (auto-synced), Keywords, Service Category, Teaching Section, Resource Type, Note Category, Note Tag
- Admin meta boxes for all fields (media pickers, relation checklists), REST-exposed meta, admin list columns
- “Researcher” dashboard: Overview with quick actions and checklist, Profile & Photo, Identifiers & Links, Statistics, CV files, Contact form, Sample content, Import (beta)
- Trilingual interface strings (EN/FR/AR), Polylang integration (post types, taxonomies, meta copying, language switcher data)
- Shortcodes: `[abc_stats] [abc_research_areas] [abc_profiles] [abc_cv_button] [abc_publications] [abc_list] [abc_contact_card] [abc_contact_form] [abc_search_form] [abc_link]`
- SEO: description, Open Graph, Twitter, canonical, JSON-LD (Person, WebSite, ScholarlyArticle, Book, ResearchProject, Article, BreadcrumbList), Google Scholar tags; automatic hand-off to Yoast/Rank Math/AIOSEO
- Search over title/content plus authors, journal, abstract, DOI, keywords; publication filters
- Contact form (nonce, honeypot, signed time token, rate limit, validation)
- REST: `/wp-json/abc/v1/profile`, `/wp-json/abc/v1/publications`, plus standard `/wp/v2/` endpoints
- Import framework: Crossref by DOI (pending review), planned ORCID / OpenAlex / OpenAIRE / OAI-PMH / DSpace
- Idempotent sample-content installer (EN/FR/AR, translation-linked) and removal of sample entries

### Theme `ahcene-babori-academic`
- Responsive, accessible academic design (navy / charcoal / white / off-white / teal), full RTL
- Templates: front page, publications (filters), single publication/book/project/presentation/resource/note, service timeline, teaching portfolio, research-area hub, search, CV, 404
- Bundled Inter and Noto Sans Arabic (no external requests), WebP sub-sizes, print CV
