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

Keep the existing `EventSubscriberInterface` contract when maintaining event subscribers.
Their public `getSubscribedEvents()` method is used by service tags and application integrations.
Rector excludes `EventSubscriberToAttributeRector` to preserve this contract; other Symfony rules remain enabled.

[Report issues](https://github.com/softspring/cms-bundle/issues) and [send Pull Requests](https://github.com/softspring/cms-bundle/pulls)
