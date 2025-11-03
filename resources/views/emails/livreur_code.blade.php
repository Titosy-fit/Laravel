<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Code de validation</title>
</head>
<body style="font-family: Arial, sans-serif;">
  <h2>Bonjour {{ $livreur->nomLivreur }} 👋</h2>
  <p>Merci de vous être inscrit sur <strong>NirShop</strong>.</p>
  <p>Voici votre code de validation :</p>
  <h1 style="color:#2e86de;">{{ $code }}</h1>
  <p>Ce code est valable pendant 10 minutes.</p>
  <p>À bientôt,<br>L’équipe NirShop</p>
</body>
</html>
