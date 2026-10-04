# Changelog

All notable changes to local_coursehead are documented in this file.

## 1.2.0 (2026-10-04)

- Compatibility: supported on Moodle 4.5 LTS, 5.0, 5.1, 5.2 and 5.3.
- New: the stored assets of a course are removed when the course is deleted.
- Fix: the site home guard compared the course id against `SITEID` with a
  strict type check that could never match, so the front page course was not
  reliably excluded. The comparison is now type-safe.
- Fix: the course name was HTML-escaped twice in the status panel.
- Bootstrap 5 class names added alongside the Bootstrap 4 ones in the status
  panel and the code editors.
- Coding style brought in line with the current Moodle code checker.

## 1.1.1 (2026-06-11)

- Fix: head injection never ran because the hook tested `empty($PAGE->course)`.
  `moodle_page` exposes `course` as a magic property without `__isset()`, so
  `empty()` always returned true and the injection check exited early on every
  page. The course is now read into a local variable before being tested.

## 1.1.0 (2026-06-11)

- Course backup and restore support: the per-course asset configuration is
  included in course backups and restored automatically (including course
  duplication).
- Restore trust model: CSS/JS is only restored when the restoring user holds
  the matching editing capability in the target course; content is
  re-validated, the revision is regenerated, dropped content is reported in
  the restore log, and existing target-course configuration is never
  overwritten.
- New PHPUnit coverage for backup/restore.

## 1.0.0 (2026-06-11)

Initial release.

- Per-course custom CSS and JavaScript stored in the `local_coursehead` table.
- Head injection through the Moodle 4.5 Hooks API
  (`core\hook\output\before_standard_head_html_generation`).
- External asset endpoints `style.php` and `script.php` with course access
  control, ETag/Last-Modified support and revision-based cache busting.
- Capabilities `local/coursehead:manage`, `local/coursehead:managecss` and
  `local/coursehead:managejs` (manager archetype only).
- Global admin settings: master switch, CSS/JS allow switches, page-type
  injection toggles, excluded paths, maximum lengths, JS load strategy.
- Course-level management page with Moodle form, validation and status panel.
- Events: course_assets_created, course_assets_updated, course_assets_deleted.
- Privacy API null provider (no personal data stored).
- MUC application cache for asset metadata.
- English and Spanish language packs.
- PHPUnit tests, Behat tests and GitHub Actions CI workflow.
