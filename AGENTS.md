# Migration ownership

Follow the workspace working rules and the selected release branch.

- Keep table and column normalization in the package that owns the table. CMS core migrations must not name optional plugin tables.
- Preserve foreign keys and data during type changes. Test upgrades and rollbacks with the core and plugin migration order on MySQL and PostgreSQL.
- Keep core handling of external foreign keys generic; do not add plugin-specific branches to the core migration.
