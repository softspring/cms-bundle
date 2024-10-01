# How to test assets locally

```bash
composer install # requires vendor
cd assets
npm install
npx eslint scripts
```

**Important: if running in a project (package in composer vendor), after running eslint, remove node_modules folder.** 
It could interfere with the project node_modules (for example, bootstrap could be loaded twice)
