# Changelog

## 1.0.27 – 2026-10-12
- Homepage: the **Research impact** (statistics) block and the **Collaborate with me** call-to-action were removed. The statistics settings stay available and can still be shown anywhere with the `[abc_stats]` shortcode.

## 1.0.26 – 2026-10-12
- Homepage research-area tiles: icons removed – the area image and title carry the tile; the image area is slightly taller.

## 1.0.25 – 2026-10-12
- **Homepage research areas as colour tiles** (same family as the quick-access tiles): eight tiles in one row (4 on tablets, 2 on phones) in alternating scheme colours, icon in a glass circle, the area title at the bottom and the area image fading from the top of the tile into its colour. On hover/keyboard focus a tile shows the description and the publication/project counters. The Research page keeps the detailed cards.

## 1.0.24 – 2026-10-12
- Research-areas row: a small side margin (0.5 rem) so the first and last cards no longer touch the screen edges.

## 1.0.23 – 2026-10-12
- Research-areas row: no side margins any more – the cards run from the very left edge to the very right edge of the screen (the heading stays aligned with the page content).

## 1.0.22 – 2026-10-12
- **Research-areas row now spans the full page width** (edge to edge): the first card stays aligned with the page content, the cards continue to the screen edge, arrows sit at the screen edges; no horizontal page scroll at any width.

## 1.0.21 – 2026-10-12
- **Research areas in a single scrollable row** on the homepage (about 3½ cards visible, scroll-snap, round arrow buttons on desktop, swipe on phones; works in RTL). The Research page keeps the full grid.

## 1.0.20 – 2026-10-12
- **Research-area cards: coloured title ribbon + prominent image.** The area name now sits in a gold ribbon at the top (dark navy text, icon tile and outlined number), followed by a larger image with a light colour-scheme tint, then the description and counters. Same structure with or without an image; the ribbon has a fixed height so images line up across the row.

## 1.0.19 – 2026-10-12
- Research-area images now use the **top-image layout**: a short image band with a unified colour-scheme tint (greyscale + gradient), icon and number over it, text below on white. The card still turns into the deep gradient on hover.

## 1.0.18 – 2026-10-12
- **Optional image per research area** (*Research Areas → edit → Image*, Media Library picker): shown as a full card background with a dark colour-scheme gradient (text always readable), a glass icon tile and a slow zoom on hover; the area page header gets the same image as a subtle tinted backdrop. Areas without an image keep the numbered icon card. Images are lazy-loaded and decorative (empty alt).

## 1.0.17 – 2026-10-12
- **Research areas redesigned** (“knowledge network” cards): large outlined numbers 01–08, gradient icon tile, pill-shaped counters for related publications/projects, a gold-green progress edge, and a hover/focus state that turns the card into a deep green gradient with a gold icon (motion disabled for reduced-motion visitors). Follows the chosen colour scheme.

## 1.0.16 – 2026-10-12
- **Simple footer**: name and title (from Researcher → Profile), contact e-mail, and the social/academic profile icons only; slim bottom bar with the copyright and a round “Back to top” button.

## 1.0.15 – 2026-10-11
- Homepage: the biography strip and the research-areas block now use tints of the chosen colour scheme (soft green / pale green with white cards) instead of white; bands alternate tinted → dark → white → tinted down the page.

## 1.0.14 – 2026-10-11
- **Biography strip on the homepage uses the full width** (the text no longer stops halfway, leaving the other side empty).
- **Same colours on every page**: all inner pages (About, Publications, Projects, single items, Blog, Contact, CV, search, 404…) now open with the same green gradient header as the homepage hero, with light breadcrumbs and titles.

## 1.0.13 – 2026-10-11
- **Emerald & Gold is now the default colour scheme** (chosen by the owner). Other schemes remain available in *Researcher → Appearance*.

## 1.0.12 – 2026-10-11
- **Quick-access tiles**: eight large coloured tiles with icons (Publications, Books, Projects, Presentations, Teaching, Academic Service, Blog, Contact) overlapping the bottom of the hero; they follow the chosen colour scheme. The old three-column teaching/service/presentations row was removed (the tiles replace it).
- **Mega menu**: Publications, Projects, Presentations, Teaching, Academic Service and Blog open a wide panel with their categories/types/research areas and latest items (automatic, cached 10 min); hover, keyboard focus, touch toggle button and Escape supported; accordion on mobile.
- **Alternating section bands** on the homepage with a coloured stripe before each title and a gradient edge on tinted bands.

## 1.0.11 – 2026-10-11
- **Appearance settings** (*Researcher → Appearance*): six colour schemes (Teal Navy – default, Indigo & Amber, Emerald & Gold, Burgundy & Gold, Ocean Blue & Sky, Slate & Coral) and three main-menu styles (Light, Dark, Accent). Each scheme meets WCAG AA contrast; administrators can preview any choice on the live site before saving. Default look is unchanged.

## 1.0.10 – 2026-10-11
- **“About me” / “Contact” / “Download CV” buttons can no longer silently open the homepage**: new **page links** in *Researcher → Profile & Photo* let you choose the About, Contact, Online CV and Research pages (pick the page of one language; the others are found through Polylang translations). Automatic detection also tries the page title (About / À propos / نبذة عني…) when slugs differ.
- Footer: the research-interests paragraph (statement under the name) was removed.

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
