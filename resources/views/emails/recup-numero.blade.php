<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>
<p>Bonjour {{ $adherent->prenom }},</p>

<p>Vous avez demandé à retrouver votre numéro d'adhérent. Le voici :<br>
<strong>{{ $adherent->numero_adherent }}</strong></p>

<p>Pour continuer votre inscription plus rapidement, vous pouvez copier-coller ce code temporaire (valable 30 minutes) directement dans le formulaire :</p>
<h2 style="color: #0f172a; letter-spacing: 2px;">{{ $codeTemporaire }}</h2>

<p>À très vite !<br>L'équipe Savoirs Vivants</p>

</body>
</html>
