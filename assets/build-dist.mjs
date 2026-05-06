import {access, cp, mkdir, readFile, readdir, rm, writeFile} from 'node:fs/promises';
import {constants} from 'node:fs';
import {dirname, join, relative, resolve} from 'node:path';
import {fileURLToPath} from 'node:url';

const {compile} = await import('sass');

const assetsRootUrl = new URL('.', import.meta.url);
const assetsRoot = fileURLToPath(assetsRootUrl);
const dist = new URL('./dist/', assetsRootUrl);
const entries = ['scripts', 'styles'];
const bundleRoot = resolve(assetsRoot, '..');
const loadPaths = [resolve(bundleRoot, 'node_modules')];

await rm(dist, {recursive: true, force: true});
await mkdir(dist, {recursive: true});

for (const entry of entries) {
  const source = new URL(`./${entry}/`, assetsRootUrl);
  try {
    await access(source, constants.F_OK);
    await cp(source, new URL(`./${entry}/`, dist), {recursive: true});
  } catch {
    // Optional asset folders are skipped.
  }
}

const distRoot = resolve(bundleRoot, 'assets', 'dist');
const cmsScriptsRoot = resolve(distRoot, 'scripts');
const packageTargets = {
  '@softspring/cms-bundle/scripts/': cmsScriptsRoot,
  '@softspring/media-bundle/scripts/': resolve(bundleRoot, '../media-bundle/assets/dist/scripts'),
  '@softspring/collection-form-type/scripts/': resolve(bundleRoot, '../collection-form-type/assets/dist'),
};

async function rewriteScriptImports(directory) {
  let items = [];
  try {
    items = await readdir(directory, {withFileTypes: true});
  } catch {
    return;
  }

  for (const item of items) {
    const itemPath = resolve(directory, item.name);

    if (item.isDirectory()) {
      await rewriteScriptImports(itemPath);
      continue;
    }

    if (!item.isFile() || !item.name.endsWith('.js')) {
      continue;
    }

    let content = await readFile(itemPath, 'utf8');

    for (const [importPrefix, targetDirectory] of Object.entries(packageTargets)) {
      content = content.replaceAll(importPrefix, createRelativeImportPrefix(itemPath, targetDirectory));
    }

    content = content.replaceAll("../../tools'", "../../tools.js'");
    content = content.replaceAll('../../tools"', '../../tools.js"');
    content = content.replaceAll(
      '@softspring/media-bundle/scripts/media-type.js',
      '@softspring/media-bundle/media-type'
    );
    content = content.replaceAll(
      '@softspring/collection-form-type/scripts/collection-form-type.js',
      '@softspring/collection-form-type/collection-form-type.js'
    );

    await writeFile(itemPath, content);
  }
}

function createRelativeImportPrefix(sourceFile, targetDirectory) {
  let relativePath = relative(dirname(sourceFile), targetDirectory).replaceAll('\\', '/');
  if (!relativePath.startsWith('.')) {
    relativePath = `./${relativePath}`;
  }

  return `${relativePath}/`;
}

await rewriteScriptImports(cmsScriptsRoot);

async function compileScssFiles(sourceDir) {
  const items = await readdir(sourceDir, {withFileTypes: true});

  for (const item of items) {
    const sourcePath = resolve(sourceDir, item.name);

    if (item.isDirectory()) {
      await compileScssFiles(sourcePath);
      continue;
    }

    if (!item.isFile() || !item.name.endsWith('.scss') || item.name.startsWith('_')) {
      continue;
    }

    const css = compile(sourcePath, {
      style: 'expanded',
      loadPaths: [assetsRoot, ...loadPaths],
    }).css;

    const targetPath = resolve(distRoot, relative(assetsRoot, sourcePath).replace(/\.scss$/, '.css'));
    await mkdir(dirname(targetPath), {recursive: true});
    await writeFile(targetPath, css);
  }
}

await compileScssFiles(resolve(assetsRoot, 'styles'));

async function removeScssFiles(directory) {
  const items = await readdir(directory, {withFileTypes: true});

  for (const item of items) {
    const itemPath = resolve(directory, item.name);

    if (item.isDirectory()) {
      await removeScssFiles(itemPath);
      continue;
    }

    if (item.isFile() && item.name.endsWith('.scss')) {
      await rm(itemPath, {force: true});
    }
  }
}

await removeScssFiles(distRoot);
