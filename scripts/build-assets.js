const fs = require('fs');
const path = require('path');

const src = (...p) => path.resolve(__dirname, '..', 'node_modules', ...p);
const dst = (...p) => path.resolve(__dirname, '..', 'public', ...p);

let ok = true;

const files = [
  ['admin-lte/dist/css/adminlte.min.css',          'adminlte/css/adminlte.min.css'],
  ['admin-lte/dist/js/adminlte.min.js',            'adminlte/js/adminlte.min.js'],
  ['bootstrap/dist/css/bootstrap.min.css',          'bootstrap/css/bootstrap.min.css'],
  ['bootstrap/dist/js/bootstrap.bundle.min.js',     'bootstrap/js/bootstrap.bundle.min.js'],
  ['@fortawesome/fontawesome-free/css/all.min.css', 'fontawesome/css/all.min.css'],
];

// Copy individual files
for (const [from, to] of files) {
  const srcPath = src(from);
  if (!fs.existsSync(srcPath)) { console.error(`MISSING: ${from}`); ok = false; continue; }
  const target = dst(to);
  fs.mkdirSync(path.dirname(target), { recursive: true });
  fs.copyFileSync(srcPath, target);
  console.log(`Copied: ${from} -> ${to}`);
}

// Copy webfonts directory
const webfontsSrc = src('@fortawesome/fontawesome-free/webfonts');
if (fs.existsSync(webfontsSrc)) {
  const webfontsDst = dst('fontawesome/webfonts');
  fs.mkdirSync(webfontsDst, { recursive: true });
  fs.cpSync(webfontsSrc, webfontsDst, { recursive: true });
  console.log('Copied: @fortawesome/fontawesome-free/webfonts/ -> fontawesome/webfonts/');
} else {
  console.error('MISSING: @fortawesome/fontawesome-free/webfonts/');
  ok = false;
}

if (!ok) process.exit(1);
