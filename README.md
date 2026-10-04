# Course custom head assets (local_coursehead)

A clean, secure Moodle local plugin (Moodle 4.5 LTS to 5.3) that lets **trusted administrators and
managers** define custom CSS and JavaScript **per course**, injected into the page
`<head>` as proper external assets — without HTML blocks, theme hacks, course
formats or core patches.

```html
<!-- local_coursehead: course custom assets start -->
<link rel="stylesheet" href="https://example.edu/local/coursehead/style.php?courseid=123&rev=abc123">
<script defer src="https://example.edu/local/coursehead/script.php?courseid=123&rev=abc123"></script>
<!-- local_coursehead: course custom assets end -->
```

## Purpose

Institutions frequently need course-specific branding, layout tweaks or small
front-end behaviours. The usual workarounds (HTML blocks with `<style>` tags,
theme SCSS edits per course, hard-coded hacks) are fragile, invisible to
auditing and painful to maintain. This plugin replaces them with a proper,
capability-controlled, cache-aware mechanism built on the Moodle Hooks API.

## Features

- Per-course custom CSS and JavaScript stored in the database.
- Injection of `<link rel="stylesheet">` and `<script defer>` tags via the
  `core\hook\output\before_standard_head_html_generation` hook (no deprecated
  callbacks, no blocks, no theme changes).
- External asset endpoints (`style.php`, `script.php`) with correct content
  types, ETag/Last-Modified support, private cache policy and revision-based
  cache busting.
- Course access control on the endpoints: assets are never exposed to users
  who cannot access the course.
- Strict capabilities: page access, CSS editing and JS editing are three
  separate capabilities, all defaulting to the manager archetype only.
- Global admin settings: master switch, CSS/JS allow switches, page-type
  injection toggles (course home / activities / grade pages), excluded path
  list, maximum content lengths, JS load strategy.
- Validation that rejects pasted HTML (`<style>`, `<script>`) and PHP tags
  without silently mangling trusted code.
- MUC application cache for lightweight metadata; the head hook never loads
  the CSS/JS bodies.
- Course backup and restore support: the asset configuration travels with the
  course, with capability-based filtering at restore time (see below).
- Moodle events on create/update/delete (no raw code in event data).
- Automatic clean-up: the stored assets are removed when the course is deleted.
- Privacy API `null_provider` — the plugin stores no personal data.
- English and Spanish language packs.
- PHPUnit and Behat coverage plus a GitHub Actions CI workflow.

## Requirements

- Moodle 4.5 LTS (`2024100700`), 5.0, 5.1, 5.2 or 5.3.
- A PHP version supported by your Moodle release (PHP 8.1+ for Moodle 4.5).

## Installation

1. Copy the plugin into your Moodle installation:
   ```
   cp -r coursehead /path/to/moodle/local/coursehead
   ```
   Or install the ZIP via *Site administration → Plugins → Install plugins*.
2. Log in as administrator and visit *Site administration → Notifications* to
   run the installation. The `local_coursehead` table is created automatically.
3. Purge caches (*Site administration → Development → Purge caches*) if you
   installed by copying files manually.

## Administrator configuration

Go to *Site administration → Plugins → Local plugins → Course custom head assets*:

| Setting | Default | Description |
|---|---|---|
| Enable plugin | Yes | Master switch for injection and serving. |
| Allow custom CSS | Yes | Whether per-course CSS is editable/served. |
| Allow custom JavaScript | **No** | Whether per-course JS is editable/served. Read the security section before enabling. |
| Inject on course home | Yes | Inject on course view pages. |
| Inject on activity pages | Yes | Inject on module pages inside the course. |
| Inject on grade pages | No | Inject on gradebook pages. |
| Excluded paths | `/mod/quiz/attempt.php` | One path prefix per line; matching pages never receive assets. |
| Maximum CSS length | 200000 | Character limit for course CSS (0 = unlimited). |
| Maximum JavaScript length | 200000 | Character limit for course JS (0 = unlimited). |
| Show security warnings | Yes | Show the JS trust warning on the management page. |
| Default JavaScript load strategy | Defer | `defer` (recommended) or `blocking`. |

## Capabilities

| Capability | Default roles | Risk flags | Purpose |
|---|---|---|---|
| `local/coursehead:manage` | Manager | XSS | Access the course management page. |
| `local/coursehead:managecss` | Manager | XSS | Edit course custom CSS. |
| `local/coursehead:managejs` | Manager | XSS, CONFIG | Edit course custom JavaScript. |

Editing teachers are intentionally **not** allowed by default. If your
institution decides to grant CSS editing to teachers, do so via a custom role —
and never grant `managejs` to non-trusted roles.

## Course manager usage

1. Open the course and go to *Course administration → Course custom head assets*
   (under the **More** menu of the course navigation).
2. Tick *Enable custom assets for this course*.
3. Paste CSS rules (no `<style>` tags) and/or JavaScript (no `<script>` tags).
4. Save. The status panel shows the generated asset URLs and the current
   revision. Every save produces a new revision, so browsers pick up changes
   immediately.
5. Use *Delete course custom assets* to remove everything for the course.

## Security considerations

**Granting `local/coursehead:managejs` is equivalent to trusting that user to
run arbitrary JavaScript in the browser of every participant viewing the
course.** That includes reading the session-bound DOM, submitting forms and
interacting with anything the viewing user can do. This is acceptable only
because:

- The capability defaults to managers/administrators only.
- Global JS serving (`allowjs`) is **disabled by default** and must be
  consciously enabled by a site administrator.
- All changes raise Moodle events and are auditable in the standard logs.

Additional posture:

- All state changes go through `moodleform` with sesskey protection, plus
  capability re-checks at processing time.
- Endpoints validate course access with `require_login` (no redirect mode) and
  answer 403/404 with `Cache-Control: private` so shared caches never leak
  assets across users or courses.
- The validator rejects `<style>`, `<script>` and PHP tags to prevent
  accidental misuse; it never silently rewrites the trusted code.
- No eval(), no file writes, no remote URL loading, no third-party libraries,
  no CDN dependencies, no user data in served assets, no raw code in logs or
  exception messages.

## Backup and restore

The course asset configuration is included in standard course backups (course
level) and restored automatically, including course duplication. The restore
step applies a strict trust model, because a backup file is untrusted input
that anyone with restore permission could craft by hand:

- Custom CSS is only restored when the restoring user holds
  `local/coursehead:managecss` in the target course.
- Custom JavaScript is only restored when the restoring user holds
  `local/coursehead:managejs` in the target course.
- Restored content is re-validated (forbidden tags, maximum lengths); invalid
  content is dropped, never silently rewritten.
- Dropped content is reported as a warning in the restore log.
- The cache-busting revision is always regenerated on restore.
- If the target course already has custom assets, the existing configuration
  is kept and the backup content is ignored (reported in the restore log).

## Limitations

- CSS/JS apply per course, not per section, group or role.
- The Behat scenario for head-tag inspection reads the raw page source via a
  small custom step (standard steps cannot inspect `<head>`).
- The site home (front page) is intentionally not supported — this plugin is
  about courses; use your theme for site-wide customisation.
- When a restore is performed by a user without the editing capabilities (for
  example an editing teacher duplicating a course), the CSS/JS is dropped with
  a restore-log warning and must be re-created by a manager.
- The "blocking" load strategy is provided for edge cases only; keep `defer`.

## Upgrade notes

- 1.2.0 adds support for Moodle 5.0 to 5.3 and removes the stored assets of
  deleted courses. No database changes.
- 1.1.0 adds course backup/restore support. No database changes; upgrading
  from 1.0.0 requires no manual steps.
- Future schema changes will ship with proper `upgrade.php` savepoints.

## Testing

PHPUnit (from the Moodle root, with PHPUnit initialised):

```bash
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite local_coursehead_testsuite
# or a single file:
vendor/bin/phpunit local/coursehead/tests/manager_test.php
```

Behat (from the Moodle root, with Behat initialised):

```bash
php admin/tool/behat/cli/init.php
vendor/bin/behat --config /path/to/behatdata/behatrun/behat/behat.yml --tags=@local_coursehead
```

Rebuild the AMD module after editing `amd/src`:

```bash
npx grunt amd --root=local/coursehead
```

## Plugin directory submission

Screenshots for the moodle.org/plugins listing should show: the admin settings
page, the course management form, and the injected tags in browser dev tools.
Place them in the plugin directory submission form (they are not bundled in
the code repository).

## License

GPL v3 or later. See [LICENSE](LICENSE).

2026 Pluginia <jmolpea@gmail.com>
