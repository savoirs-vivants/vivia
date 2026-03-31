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
            Cours annulé
        </h1>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
            Bonjour,
        </p>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
            Nous vous informons qu'exceptionnellement, le cours de <strong style="color: #222A60;">{{ $nomActivite }}</strong> prévu à la date ci-dessous ne pourra pas être assuré :
        </p>

        <div style="text-align: center; background-color: #fff1f2; border: 2px dashed #fecdd3; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
            <span style="display: block; font-size: 12px; text-transform: uppercase; color: #fb7185; font-weight: 700; margin-bottom: 4px; letter-spacing: 1px;">Date annulée</span>
            <span style="font-family: 'Space Mono', monospace; font-size: 20px; color: #e11d48; font-weight: 700;">{{ $dateSeance }}</span>
        </div>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
            Veuillez nous excuser pour la gêne occasionnée.
        </p>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 0; text-align: center;">
            À très vite,<br>
            <strong style="color: #222A60;">L'équipe Savoirs Vivants</strong>
        </p>

        <hr style="border: none; border-top: 1px solid #f3f4f6; margin: 32px 0;">

        <p style="color: #9ca3af; font-size: 13px; line-height: 1.5; text-align: center; margin: 0;">
            Cet email est envoyé automatiquement suite à une modification du planning.
        </p>

    </div>

</body>
</html>
