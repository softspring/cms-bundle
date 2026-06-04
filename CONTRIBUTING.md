# Contributing

Use the standard package commands before sending changes:

```bash
composer fix
composer test
composer test-bc
npm install --no-package-lock --no-audit --no-fund
npm run lint
npm run build
```

Run `composer test` and `composer test-bc` sequentially because both commands rewrite the dependency set and `vendor/`.

[Report issues](https://github.com/softspring/cms-bundle/issues) and [send Pull Requests](https://github.com/softspring/cms-bundle/pulls)
