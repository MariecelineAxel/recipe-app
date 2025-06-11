import express from 'express';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const app = express();

// Servir les fichiers statiques depuis le dossier dist
app.use(express.static(join(__dirname, 'dist')));

// Rediriger toutes les requêtes vers index.html
app.get('*', (req, res) => {
  res.sendFile(join(__dirname, 'dist', 'index.html'), { 
    headers: { 'Content-Type': 'text/html' }
  });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});