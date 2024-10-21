const fs = require('fs');
const path = require('path');

const zipPath = path.join(__dirname, 'wp-support-ticket-system.zip');

if (fs.existsSync(zipPath)) {
  const zipContent = fs.readFileSync(zipPath);
  const base64Zip = zipContent.toString('base64');
  console.log(JSON.stringify({ filename: 'wp-support-ticket-system.zip', content: base64Zip }));
} else {
  console.error('El archivo ZIP no existe. Asegúrate de haberlo creado primero.');
}