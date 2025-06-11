import express from 'express';
import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';
const app = express();

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

app.use(express.static(resolve(__dirname, 'dist')));

app.get('*', (req, res) => {
  console.log(`Requête reçue pour : ${req.url}`);
  const filePath = resolve(__dirname, 'dist', 'index.html');
  res.sendFile(filePath, (err) => {
    if (err) {
      console.error(`Erreur 404 pour ${req.url}:`, err);
      res.status(404).send('Not Found');
    }
  });
});

const port = process.env.PORT || 3000;
app.listen(port, () => {
  console.log(`Server running on port ${port}`);
});