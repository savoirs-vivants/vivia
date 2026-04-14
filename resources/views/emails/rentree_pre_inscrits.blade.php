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
            🎉 C'est la rentrée !
        </h1>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
            Bonjour <strong style="color: #222A60;">{{ $adherent->prenom }}</strong>,
        </p>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
            J'espère que vous avez passé un bel été ! ☀️<br><br>
            Il y a quelques semaines, vous avez effectué une pré-inscription pour nos activités. L'heure a sonné : <strong style="color: #16987C;">la nouvelle saison {{ $inscription->saison }} est officiellement ouverte !</strong> 🚀
        </p>

        <div style="text-align: center; background-color: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 12px; padding: 24px 20px; margin-bottom: 24px;">
            <span style="display: block; font-size: 12px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 12px; letter-spacing: 1px;">Pour valider votre place</span>

            <p style="font-size: 15px; color: #4b5563; margin-top: 0; margin-bottom: 20px; line-height: 1.5;">
                Il ne vous reste plus qu'à régler le solde de votre adhésion. Rendez-vous sur notre portail, cliquez sur <strong style="color: #222A60;">"Déjà adhérent"</strong> et laissez-vous guider !
            </p>

            <a href="{{ route('adhesion.index') }}" style="display: inline-block; background-color: #16987C; color: #ffffff; text-decoration: none; font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 16px; padding: 14px 28px; border-radius: 8px; box-shadow: 0 2px 4px rgba(22, 152, 124, 0.2);">
                💳 Régler mon solde
            </a>
        </div>

        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
            <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 12px; text-transform: uppercase; color: #9ca3af; letter-spacing: 1px;">Vos accès rapides</h3>

            <p style="margin: 0 0 8px 0; font-size: 15px;">
                <span style="color: #64748b;">Numéro d'adhérent :</span> <strong style="color: #222A60; font-family: 'Space Mono', Courier, monospace;">{{ $adherent->numero_adherent }}</strong>
            </p>
            <p style="margin: 0; font-size: 13px; color: #94a3b8; font-style: italic;">
                (Vous pouvez également utiliser votre adresse e-mail pour vous retrouver).
            </p>
        </div>

        <p style="font-size: 14px; line-height: 1.6; color: #64748b; margin-bottom: 24px;">
            <em>Vous avez changé d'avis ?</em> Vous pourrez annuler votre pré-inscription d'été directement depuis la plateforme pour en effectuer une nouvelle.
        </p>

        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 0;">
            Nos animateurs ont hâte de vous retrouver !<br>
            <strong style="color: #222A60;">L'équipe de Savoirs Vivants</strong>
        </p>

        <hr style="border: none; border-top: 1px solid #f3f4f6; margin: 32px 0;">

        <p style="color: #9ca3af; font-size: 13px; line-height: 1.5; text-align: center; margin: 0;">
            Savoirs Vivants — Cet e-mail vous a été envoyé car vous avez une pré-inscription en attente de validation.
        </p>

    </div>

</body>
</html>
