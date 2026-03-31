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
            Avis d'absence
        </h1>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
            Bonjour,
        </p>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
            Nous vous informons que <strong style="color: #222A60;">{{ $prenomEnfant }}</strong> a été noté(e) absent(e) lors de la séance d'aujourd'hui :
        </p>

        <div style="text-align: center; background-color: #fffbeb; border: 2px dashed #fde68a; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
            <span style="display: block; font-size: 12px; text-transform: uppercase; color: #d97706; font-weight: 700; margin-bottom: 4px; letter-spacing: 1px;">{{ $nomActivite }}</span>
            <span style="font-family: 'Space Mono', monospace; font-size: 18px; color: #b45309; font-weight: 700;">{{ $dateSeance }}</span>
        </div>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
            S'il s'agit d'une absence prévue de votre part, vous n'avez pas besoin de tenir compte de ce message.
        </p>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 0; text-align: center;">
            À très vite,<br>
            <strong style="color: #222A60;">L'équipe Savoirs Vivants</strong>
        </p>

    </div>

</body>
</html>
