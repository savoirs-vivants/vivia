<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body style="font-family: 'Space Grotesk', Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 40px 20px; color: #4b5563;">

    <div style="max-width: 520px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb;">

        <div style="text-align: center; margin-bottom: 32px;">
            <img src="{{ asset('storage/logo_sv.png') }}" alt="Logo Savoirs Vivants" style="height: 48px; width: auto;">
        </div>

        <div style="text-align: center; margin-bottom: 24px;">
            <span style="display: inline-block; background-color: #f0fdf4; color: #0f766e; padding: 6px 16px; border-radius: 9999px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">
                Nouvelle Inscription 🎉
            </span>
        </div>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 16px; color: #1f2937;">
            Bonjour,
        </p>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
            Une nouvelle adhésion vient d'être finalisée sur le site par <strong style="color: #222A60; font-size: 18px;">{{ trim($prenom . ' ' . $nom) }}</strong>.
        </p>

        <div style="background-color: #f8fafc; border-left: 4px solid #16987C; border-radius: 4px 8px 8px 4px; padding: 16px; margin-bottom: 32px;">
            <p style="margin: 0; font-size: 15px; color: #475569; line-height: 1.5;">
                Vous pouvez dès à présent consulter les détails de cette inscription, les activités choisies et les informations de contact directement depuis l'espace d'administration.
            </p>
        </div>

        <div style="text-align: center; margin-bottom: 32px;">
            <a href="{{ route('adherents.index') }}" style="display: inline-block; background-color: #16987C; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; padding: 14px 32px; border-radius: 8px; box-shadow: 0 4px 6px rgba(22, 152, 124, 0.2); transition: background-color 0.2s;">
                Accéder à l'application
            </a>
        </div>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">

        <p style="color: #9ca3af; font-size: 13px; line-height: 1.5; text-align: center; margin: 0;">
            Cet email est généré automatiquement par la plateforme Savoirs Vivants.
        </p>

    </div>

</body>
</html>
