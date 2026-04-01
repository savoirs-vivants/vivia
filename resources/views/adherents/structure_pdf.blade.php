<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fiche Structure - {{ $structure->nom }}</title>
    <style>
        /* DejaVu Sans est OBLIGATOIRE avec DomPDF pour afficher le symbole € et les accents correctement */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; line-height: 1.4; margin: 0; padding: 20px; }

        .header { background-color: #222A60; color: #ffffff; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0 0 5px 0; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 0; color: #16987C; font-weight: bold; font-size: 14px; }

        .section-title { color: #16987C; border-bottom: 2px solid #16987C; padding-bottom: 5px; font-size: 15px; font-weight: bold; text-transform: uppercase; margin-top: 25px; margin-bottom: 15px; }

        table { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
        th { text-align: left; padding: 8px 10px; color: #666; font-weight: normal; width: 35%; vertical-align: top; border-bottom: 1px solid #eee; }
        td { padding: 8px 10px; font-weight: bold; color: #0F143A; vertical-align: top; border-bottom: 1px solid #eee; }

        /* Style pour le tableau des paiements */
        .table-striped th { background-color: #f8fafc; color: #333; font-weight: bold; border: 1px solid #ddd; width: auto; font-size: 11px; text-transform: uppercase; }
        .table-striped td { border: 1px solid #ddd; font-weight: normal; font-size: 12px; }
        .table-striped tr:nth-child(even) td { background-color: #f9fafb; }

        .tag { background-color: #f3f4f6; padding: 3px 8px; border-radius: 4px; font-size: 11px; color: #4b5563; display: inline-block; margin-right: 5px; margin-bottom: 4px; }
        .tag-green { background-color: #d1fae5; color: #065f46; }
        .tag-red { background-color: #ffe4e6; color: #9f1239; }

        .footer { text-align: center; margin-top: 40px; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }

        /* Style spécifique pour la signature */
        .signature-box { border: 1px dashed #cbd5e1; background-color: #f8fafc; padding: 15px; width: 260px; margin: 15px 0 15px auto; text-align: center; border-radius: 8px; }
        .signature-img { max-height: 90px; max-width: 100%; }
        .signature-text { font-size: 10px; color: #64748b; margin: 5px 0 0 0; font-style: italic; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $structure->nom }}</h1>
        <p>Numéro d'adhérent : {{ $structure->numero_adherent ?? 'En attente' }}</p>
    </div>

    <div class="section-title">Informations sur la Structure</div>
    <table>
        <tr>
            <th>Nom complet</th>
            <td>{{ $structure->nom }}</td>
        </tr>
        @if($structure->sigle)
        <tr>
            <th>Sigle</th>
            <td>{{ $structure->sigle }}</td>
        </tr>
        @endif
        <tr>
            <th>Date de création</th>
            <td>{{ $structure->date_creation ? $structure->date_creation->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Adresse complète</th>
            <td>{{ $structure->adresse }}<br>{{ $structure->code_postal }} {{ $structure->ville }}</td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td>{{ $structure->tel ?? 'N/A' }}</td>
        </tr>
         <tr>
            <th>Téléphone portable</th>
            <td>{{ $structure->tel_portable ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $structure->mail ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Site web</th>
            <td>{{ $structure->site_web ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Informations sur le correspondant</div>
     <table>
        <tr>
            <th>Nom</th>
            <td>{{ $structure->nom_correspondant ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td>{{ $structure->tel_correspondant ?? 'N/A' }}</td>
        </tr>
    </table>


    <div class="section-title">Engagements & Inscriptions</div>
    <table>
        <tr>
            <th>Autorisations</th>
            <td>
                <span class="tag {{ $structure->autorisation_photo ? 'tag-green' : 'tag-red' }}">Droit à l'image : {{ $structure->autorisation_photo ? 'OUI' : 'NON' }}</span>
                <span class="tag {{ $structure->bulletin ? 'tag-green' : 'tag-red' }}">Newsletter : {{ $structure->bulletin ? 'OUI' : 'NON' }}</span>
            </td>
        </tr>
        @if ($structure->inscription)
            <tr>
                <th>Date d'inscription</th>
                <td>{{ $structure->inscription->date_inscription->isoFormat('D MMM YYYY') }}</td>
            </tr>
            <tr>
                <th>Statut du paiement</th>
                 @php
                    $aPaye = $structure->inscription->a_paye;
                    $badgeClass = match($aPaye) {
                        'Payé'       => 'tag-green',
                        'En attente' => 'tag-red',
                        default      => '',
                    };
                @endphp
                <td><span class="tag {{ $badgeClass }}">{{ $aPaye }}</span></td>
            </tr>
        @endif
    </table>

    {{-- HISTORIQUE DES PAIEMENTS --}}
    @if($structure->paiements && $structure->paiements->count() > 0)
        <div class="section-title">Historique des Paiements</div>
        <table class="table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Source / Type</th>
                    <th>Montant</th>
                    <th>Note / Commentaire</th>
                </tr>
            </thead>
            <tbody>
                @foreach($structure->paiements->sortByDesc('date_paiement') as $paiement)
                <tr>
                    <td>{{ $paiement->date_paiement ? \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') : 'N/A' }}</td>
                    <td><strong>{{ $paiement->source }}</strong></td>
                    <td style="color: #16987C; font-weight: bold;">{{ number_format($paiement->montant, 2, ',', ' ') }} &euro;</td>
                    <td style="font-size: 11px;">{{ $paiement->commentaire ?? '-' }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="text-align: right; border: none; padding-top: 15px;"><strong>TOTAL ENCAISSÉ :</strong></td>
                    <td colspan="2" style="border: none; padding-top: 15px; color: #16987C; font-size: 16px; font-weight: bold;">
                        {{ number_format($totalPaye, 2, ',', ' ') }} &euro;
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    @if($structure->signature)
        <div class="section-title">Validation & Signature</div>
        <div class="signature-box">
            <img src="{{ $structure->signature }}" alt="Signature du correspondant" class="signature-img">
            <p class="signature-text">Document certifié et signé électroniquement</p>
        </div>
    @endif

    <div class="footer">
        Fiche générée automatiquement par le système Savoirs Vivants le {{ now()->format('d/m/Y à H:i') }}.
    </div>

</body>
</html>
