const AdmZip = require('adm-zip');
const fs = require('fs');
const path = require('path');

const zip = new AdmZip();

const pluginDir = path.join(__dirname, 'wp-support-ticket-system');
const outputPath = path.join(__dirname, 'wp-support-ticket-system.zip');

function addDirectoryToZip(directory) {
  const files = fs.readdirSync(directory);
  for (const file of files) {
    const filePath = path.join(directory, file);
    const stats = fs.statSync(filePath);
    if (stats.isDirectory()) {
      addDirectoryToZip(filePath);
    } else {
      const zipPath = path.relative(__dirname, filePath);
      zip.addLocalFile(filePath, path.dirname(zipPath));
    }
  }
}

addDirectoryToZip(pluginDir);
zip.writeZip(outputPath);

console.log(`Plugin empaquetado en: ${outputPath}`);

// Leer y codificar el archivo ZIP en base64
const zipContent = fs.readFileSync(outputPath);
const base64Zip = zipContent.toString('base64');
console.log(JSON.stringify({ filename: 'wp-support-ticket-system.zip', content: base64Zip }));