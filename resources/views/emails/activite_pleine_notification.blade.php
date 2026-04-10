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
            ⏳ Liste d'attente
        </h1>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px; text-align: center;">
            Une nouvelle personne souhaite être prévenue si une place se libère dans une activité actuellement <strong style="color: #222A60;">complète</strong>.
        </p>

        <div style="text-align: center; background-color: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
            <span style="display: block; font-size: 12px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 6px; letter-spacing: 1px;">Activité demandée</span>

            <span style="display: block; font-family: 'Space Grotesk', sans-serif; font-size: 20px; color: #222A60; font-weight: 700; margin-bottom: 4px;">{{ $activite->nom }}</span>

            @if($activite->ville)
                <span style="display: block; font-size: 14px; color: #16987C; font-weight: 700; margin-bottom: 8px;">📍 {{ $activite->ville }}</span>
            @endif

            @if(!empty($activite->horaires_list))
                <div style="margin-top: 12px;">
                    @foreach($activite->horaires_list as $horaire)
                        <span style="display: inline-block; background-color: #ffffff; border: 1px solid #e2e8f0; color: #64748b; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; margin: 2px;">
                            🕒 {{ $horaire }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
            <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 12px; text-transform: uppercase; color: #9ca3af; letter-spacing: 1px;">Coordonnées du contact</h3>

            <p style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: #222A60;">
                👤 {{ $prenom }} {{ $nom }}
            </p>

            <p style="margin: 0; font-size: 15px;">
                @if($mail)
                    ✉️ <a href="mailto:{{ $mail }}" style="color: #16987C; text-decoration: none; font-weight: 600;">{{ $mail }}</a>
                @else
                    <span style="color: #9ca3af; font-style: italic;">(Email non renseigné)</span>
                @endif
            </p>
        </div>

        <hr style="border: none; border-top: 1px solid #f3f4f6; margin: 32px 0;">

        <p style="color: #9ca3af; font-size: 13px; line-height: 1.5; text-align: center; margin: 0;">
            Savoirs Vivants — notification générée automatiquement.
        </p>

    </div>

</body>
</html>
