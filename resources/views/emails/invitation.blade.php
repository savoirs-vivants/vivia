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

        <h1 style="color: #222A60; font-family: 'Space Mono', Courier, monospace; font-size: 24px; font-weight: 700; margin-top: 0; margin-bottom: 24px; text-align: center;">
            Bienvenue sur Vivia
        </h1>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
            Bonjour <strong style="color: #222A60;">{{ $user->firstname }} {{ $user->name }}</strong>,
        </p>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 32px;">
            Un compte a été créé pour vous sur la plateforme. Cliquez sur le bouton ci-dessous pour finaliser votre inscription et choisir votre mot de passe.
        </p>

        <div style="text-align: center;">
            <a href="{{ route('inscription', ['token' => $token]) }}"
               style="display: inline-block; padding: 14px 32px; background-color: #16987C; color: #ffffff; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 16px;">
                Finaliser mon inscription
            </a>
        </div>

        <hr style="border: none; border-top: 1px solid #f3f4f6; margin: 32px 0;">

        <p style="color: #9ca3af; font-size: 13px; line-height: 1.5; text-align: center; margin: 0;">
            Ce lien est personnel et confidentiel, merci de ne pas le partager.<br>
            Si vous n'avez pas demandé ce compte, vous pouvez ignorer cet email.
        </p>

    </div>

</body>
</html>
