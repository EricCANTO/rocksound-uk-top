
// scrape-snep-rock.js
const fs = require('fs');
const path = require('path');
const axios = require('axios');
const cheerio = require('cheerio');

const URL = 'https://snepmusique.com/les-tops/le-top-de-la-semaine/top-albums/?categorie=Top%20Rock%20%26%20Metal';

function escapeXML(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

(async () => {
  const { data } = await axios.get(URL);
  const $ = cheerio.load(data);

  const topAlbums = [];

  $('.top-row').each((i, el) => {
    const position = $(el).find('.position').text().trim() || (i + 1).toString();
    const title = $(el).find('.title').text().trim();
    const artist = $(el).find('.artist').text().trim();
    const label = $(el).find('.label').text().trim();
    if (title && artist) {
      topAlbums.push({ position, title, artist, label });
    }
  });

  const html = `<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Top Rock & Metal France – SNEP</title>
  <style>
    body { background: #111; color: #fff; font-family: sans-serif; padding: 40px; }
    h1 { color: #f39c12; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border: 1px solid #333; }
    th { background: #222; color: #f39c12; }
    tr:nth-child(even) { background: #181818; }
  </style>
</head>
<body>
  <h1>Top 10 Rock & Metal – France (source : SNEP)</h1>
  <table>
    <thead><tr><th>#</th><th>Album</th><th>Artiste</th><th>Label</th></tr></thead>
    <tbody>
      ${topAlbums.map(a => `<tr><td>${a.position}</td><td>${a.title}</td><td>${a.artist}</td><td>${a.label}</td></tr>`).join("\n")}
    </tbody>
  </table>
</body>
</html>`;

  const rss = `<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
  <title>Top Rock & Metal – SNEP</title>
  <link>${URL}</link>
  <description>Classement officiel Rock & Metal France (source : SNEP)</description>
  ${topAlbums.map(a => `
  <item>
    <title>${escapeXML(a.position + '. ' + a.artist + ' – ' + a.title)}</title>
    <link>${URL}</link>
    <description>${escapeXML('Label : ' + a.label)}</description>
  </item>`).join("\n")}
</channel>
</rss>`;

  fs.mkdirSync('dist', { recursive: true });
  fs.writeFileSync(path.join('dist', 'index.html'), html);
  fs.writeFileSync(path.join('dist', 'rss.xml'), rss);

  console.log('✅ index.html et rss.xml générés dans le dossier /dist');
})();
