# Solo changelog

## Next release

**Sub-theme owners: this release renames Solo's CSS classes and element IDs, so your sub-theme's CSS, Twig and JavaScript break until you migrate them.** Run `drush solo:migrate-classes my_theme` first. It is a dry run and changes nothing. Read the list, then run it again with `--apply`. `drush updatedb` also rewrites the inline CSS saved in Solo's settings.

- Every Solo class and ID now starts with `solo-` and follows one naming pattern. The full list of old and new names is in `docs/CLASS-MIGRATION.md`.
- The starter sub-theme (`solo_subtheme`) uses the new names.
- Solo and Solo Utilities work without the Search module, which Drupal 12 core no longer ships. The Search Results settings show a short note asking you to install Search, and the saved search settings are kept.
