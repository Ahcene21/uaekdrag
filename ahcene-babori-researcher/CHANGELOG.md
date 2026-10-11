# Changelog

## 1.0.9 – 2026-10-11
- **Compact homepage** (about one third shorter): tighter sections, smaller cards and research-area tiles, one-line teaching/service/presentations row, slimmer statistics and call-to-action. Base font 16 px.
- **“Selected publications” block removed** from the homepage (publications stay in their own section).
- **Justified text** (inter-word justification, start-aligned headings) for body text, introductions, biography and abstracts; narrow cards keep normal alignment to avoid wide gaps.

## 1.0.8 – 2026-10-11
- **Fix: *About* link in every language.** Menu links to pages are now resolved through the page’s Polylang translation first, and a link is never replaced by the homepage; the “About me” and “Contact” buttons also find hand-made translations (the page key is now copied when a translation is created, and pages are also found by their usual slug or shortcode).
- **Fix: *Back to top* in the footer.** The target is no longer the sticky header (browsers cannot scroll to an element that is already stuck at the top).

## 1.0.7 – 2026-10-11
- **Resources removed** (menu, dashboard, search, templates, sample data). Previously saved resource data stays untouched in the database.
- **Research Notes became the Blog** (menu, URLs `/blog/`, labels, categories and tags; old `/research-notes/…` links redirect). Existing posts are kept.
- **CV removed from the primary menu** (the *Download CV* button and the CV page remain; CV stays in the footer menu).
- **Contact messages are dashboard entries**: the form now asks for First name, Last name, Email, Reason for contact and Message. Every message is saved under the new top-level **Contact Messages** menu (sender, e-mail, reason, message, status New / Read / Replied / Archived, red counter for new ones, “Reply by e-mail” button) and also e-mailed.
- The *Contact* button on the homepage (and in menus/footer) always works: it opens the Contact page, or a `mailto:` link when no Contact page exists.

## 1.0.6 – 2026-10-11
- **About timeline is now editable from the dashboard** (*About Timeline* menu): add, edit, reorder and translate entries (start year for sorting, period text, badge, institution, details) grouped in **Timeline Sections** (Education & Academic Qualifications, Academic Positions, Professional Experience, Awards & Honours, Certificates – add your own). The About page and the CV page display them via the `[abc_career]` shortcode; entries are sorted newest first and empty sections are hidden.
- Existing About pages are migrated automatically (current entries become editable posts; your own text in the page is kept).

## 1.0.5 – 2026-10-11
- Hero portrait: orbit design – slowly rotating dashed rings and four floating topic badges (library, open access, AI, repository) around the arch portrait; motion disabled for visitors who prefer reduced motion.

## 1.0.4 – 2026-10-11
- Homepage hero redesigned to be about half as tall: arch-shaped portrait with offset ring and dotted accent, smaller name/title/tagline, tighter spacing, slimmer topics bar.

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
