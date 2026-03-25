<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
</head>
<body style="font-family: 'Space Grotesk', Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 40px 20px; color: #4b5563;">

    <div style="max-width: 520px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb;">

        <div style="text-align: center; margin-bottom: 32px;">
            <img src="{{ asset('storage/logo_sv.png') }}" alt="Logo Savoirs Vivants" style="height: 48px; width: auto;">
        </div>

        <h1 style="color: #222A60; font-family: 'Space Mono', Courier, monospace; font-size: 22px; font-weight: 700; margin-top: 0; margin-bottom: 24px; text-align: center; text-transform: uppercase; letter-spacing: -0.5px;">
            Numéro d'adhérent
        </h1>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
            Bonjour <strong style="color: #222A60;">{{ $adherent->prenom }}</strong>,
        </p>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
            Vous avez demandé à retrouver votre numéro d'adhérent pour finaliser votre inscription. Le voici :
        </p>

        <div style="text-align: center; background-color: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
            <span style="display: block; font-size: 12px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 4px; letter-spacing: 1px;">Votre numéro officiel</span>
            <span style="font-family: 'Space Mono', monospace; font-size: 28px; color: #16987C; font-weight: 700;">{{ $adherent->numero_adherent }}</span>
        </div>

        <p style="font-size: 15px; line-height: 1.6; margin-bottom: 16px; text-align: center;">
            Pour aller plus vite, vous pouvez aussi copier ce <strong>code temporaire</strong> directement dans le formulaire :
        </p>

        <div style="text-align: center; margin-bottom: 32px;">
            <div style="display: inline-block; padding: 12px 24px; background-color: #222A60; color: #ffffff; border-radius: 10px; font-family: 'Space Mono', monospace; font-weight: 700; font-size: 20px; letter-spacing: 3px;">
                {{ $codeTemporaire }}
            </div>
            <p style="color: #94a3b8; font-size: 11px; margin-top: 8px;">Valable pendant 30 minutes</p>
        </div>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 0; text-align: center;">
            À très vite,<br>
            <strong style="color: #222A60;">L'équipe Savoirs Vivants</strong>
        </p>

        <hr style="border: none; border-top: 1px solid #f3f4f6; margin: 32px 0;">

        <p style="color: #9ca3af; font-size: 13px; line-height: 1.5; text-align: center; margin: 0;">
            Si vous n'avez pas demandé ce code, vous pouvez ignorer cet email en toute sécurité.
        </p>

    </div>

</body>
</html>
