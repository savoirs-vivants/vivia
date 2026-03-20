<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:500px;">

                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <span style="font-family: monospace; font-size: 24px; font-weight: 700; color: #1a2340;">Vivia</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 40px 40px 32px;">

                                        <h1 style="margin:0 0 16px; font-size:20px; color:#1a2340; font-weight:700;">
                                            Réinitialisation de mot de passe
                                        </h1>

                                        <p style="margin:0 0 12px; font-size:15px; color:#475569; line-height:1.6;">
                                            Nous avons reçu une demande de réinitialisation du mot de passe associé à votre compte Usuel.
                                        </p>
                                        <p style="margin:0 0 32px; font-size:15px; color:#475569; line-height:1.6;">
                                            Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.
                                        </p>

                                        <table cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td align="center" style="padding-bottom: 32px;">
                                                    <a href="{{ url('/reinitialiser/' . $token . '/' . urlencode($email)) }}"
                                                       style="display:inline-block; background:#1a2340; color:white; font-weight:600; font-size:15px; padding:14px 32px; border-radius:10px; text-decoration:none;">
                                                        Créer un nouveau mot de passe
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="border-top: 1px solid #f1f5f9; padding-top: 24px;">
                                                    <p style="margin:0 0 8px; font-size:12px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">
                                                        Ou copiez ce lien dans votre navigateur :
                                                    </p>
                                                    <p style="margin:0; font-size:12px; color:#64748b; word-break:break-all; background:#f8fafc; border-radius:6px; padding: 12px; border: 1px solid #e2e8f0;">
                                                        {{ url('/reinitialiser/' . $token . '/' . urlencode($email)) }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:20px 40px;">
                                        <p style="margin:0; font-size:13px; color:#64748b; line-height:1.5;">
                                            Ce lien expirera dans <strong>60 minutes</strong>.<br>
                                            Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding-top: 24px;">
                            <p style="margin:0; font-size:12px; color:#94a3b8;">
                                © {{ date('Y') }} Usuel — Évaluation des compétences
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
