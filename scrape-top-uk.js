
// scrape-top-uk.js
const fs = require('fs');
const path = require('path');
const axios = require('axios');
const cheerio = require('cheerio');

const URL = 'https://www.officialcharts.com/charts/rock-and-metal-albums-chart/';

(async () => {
  const { data } = await axios.get(URL);
  const $ = cheerio.load(data);

  const topAlbums = [];
  $('.chart-positions .chart-positions__list li').slice(0, 10).each((i, el) => {
    const position = $(el).find('.position').text().trim();
    const title = $(el).find('.title').text().trim();
    const artist = $(el).find('.artist').text().trim();
    const weeks = $(el).find('.weeks').text().trim();

    topAlbums.push({ position, title, artist, weeks });
  });

  const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Top Rock & Metal UK</title>
  <style>
    body { background: #111; color: #fff; font-family: sans-serif; padding: 40px; }
    h1 { color: #1db954; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border: 1px solid #333; }
    th { background: #222; color: #1db954; }
    tr:nth-child(even) { background: #181818; }
  </style>
</head>
<body>
  <h1>Top Rock & Metal UK</h1>
  <table>
    <thead><tr><th>#</th><th>Album</th><th>Artiste</th><th>Semaines</th></tr></thead>
    <tbody>
      ${topAlbums.map(a => `<tr><td>${a.position}</td><td>${a.title}</td><td>${a.artist}</td><td>${a.weeks}</td></tr>`).join('\n')}
    <tr><td colspan='4' style='text-align:center;'>No data found</td></tr></tbody>
  </table>
</body>
</html>`;

  const rss = `<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
  <title>Top Rock & Metal UK</title>
  <link>${URL}</link>
  <description>Classement officiel UK des albums Rock & Metal</description>
  ${topAlbums.map(a => `
  <item>
    <title>${a.position}. ${a.artist} – ${a.title}</title>
    <link>${URL}</link>
    <description>${a.weeks} semaines dans le classement</description>
  </item>`).join('\n')}
</channel>
</rss>`;

  fs.mkdirSync('dist', { recursive: true });
  fs.writeFileSync(path.join('dist', 'index.html'), html);
  fs.writeFileSync(path.join('dist', 'rss.xml'), rss);

  console.log('✅ index.html et rss.xml générés dans le dossier /dist');
})();
