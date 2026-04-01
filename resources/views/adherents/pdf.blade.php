<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fiche Adhérent - {{ $adherent->prenom }} {{ $adherent->nom }}</title>
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

        .alert-box { background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px; margin-bottom: 10px; border-radius: 4px; }
        .alert-box strong { color: #b45309; display: block; margin-bottom: 5px; }

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
        <h1>{{ $adherent->prenom }} {{ $adherent->nom }}</h1>
        <p>Numéro d'adhérent : {{ $adherent->numero_adherent ?? 'En attente' }}</p>
    </div>

    <div class="section-title">Informations Personnelles</div>
    <table>
        <tr>
            <th>Date de naissance / Âge</th>
            <td>{{ $adherent->date_naiss ? $adherent->date_naiss->format('d/m/Y') : 'N/A' }} ({{ $adherent->age_courant ?? 'N/A' }} ans)</td>
        </tr>
        <tr>
            <th>Genre</th>
            <td>{{ $adherent->genre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td>{{ $adherent->tel ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $adherent->mail ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Adresse complète</th>
            <td>{{ $adherent->adresse }}<br>{{ $adherent->code_postal }} {{ $adherent->ville }}</td>
        </tr>
        <tr>
            <th>Situation scolaire / pro.</th>
            <td>{{ $adherent->occupation ?? 'N/A' }} {{ $adherent->etablissement ? '('.$adherent->etablissement.')' : '' }}</td>
        </tr>
    </table>

    @if($adherent->problemes_sante || $adherent->allergies || $adherent->conduite_a_tenir || $adherent->restrictions_alimentaires)
        <div class="section-title" style="color: #e11d48; border-color: #e11d48;">Informations Médicales</div>
        @if($adherent->conduite_a_tenir)
            <div class="alert-box">
                <strong>PROTOCOLE D'URGENCE :</strong>
                {{ $adherent->conduite_a_tenir }}
            </div>
        @endif
        <table>
            @if($adherent->problemes_sante)
            <tr><th>Problèmes de santé</th><td style="color: #e11d48;">{{ $adherent->problemes_sante }}</td></tr>
            @endif
            @if($adherent->allergies)
            <tr><th>Allergies</th><td style="color: #e11d48;">{{ $adherent->allergies }}</td></tr>
            @endif
            @if($adherent->restrictions_alimentaires)
            <tr><th>Restrictions alimentaires</th><td>{{ $adherent->restrictions_alimentaires }}</td></tr>
            @endif
        </table>
    @endif

    @if($adherent->tousLesTuteurs && $adherent->tousLesTuteurs->count() > 0)
        <div class="section-title">Représentants & Tuteurs</div>
        @foreach($adherent->tousLesTuteurs as $tuteur)
            <table style="background-color: #f8fafc; border-radius: 8px; margin-bottom: 15px;">
                <tr>
                    <td colspan="2" style="border: none; padding-bottom: 0;">
                        <span style="font-size: 14px;">{{ $tuteur->nom_complet }}</span>
                        <span style="float: right; font-size: 10px; text-transform: uppercase;">
                            @if($tuteur->type === 'parent_tuteur') REPRÉSENTANT LÉGAL
                            @elseif($tuteur->type === 'autre_autorise') PERSONNE AUTORISÉE
                            @else NON AUTORISÉ(E) @endif
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; color: #666; font-weight: normal; padding-top: 5px;">Tél : <strong style="color: #333;">{{ $tuteur->tel ?? 'N/A' }}</strong></td>
                    <td style="border: none; color: #666; font-weight: normal; padding-top: 5px;">Email : <strong style="color: #333;">{{ $tuteur->mail ?? 'N/A' }}</strong></td>
                </tr>
                @if($tuteur->type === 'parent_tuteur' && $tuteur->signature)
                <tr>
                    <td colspan="2" style="border: none; padding-top: 5px;">
                        <div class="signature-box" style="margin: 5px 0 5px auto;">
                            <img src="{{ $tuteur->signature }}" alt="Signature du représentant légal" class="signature-img">
                            <p class="signature-text">
                                Signé le {{ $tuteur->date_signature ? \Carbon\Carbon::parse($tuteur->date_signature)->format('d/m/Y') : 'N/A' }}
                            </p>
                        </div>
                    </td>
                </tr>
                @endif
            </table>
        @endforeach
    @endif

    <div class="section-title">Engagements & Inscriptions</div>
    <table>
        <tr>
            <th>Autorisations</th>
            <td>
                <span class="tag {{ $adherent->communication ? 'tag-green' : 'tag-red' }}">Image : {{ $adherent->communication ? 'OUI' : 'NON' }}</span>
                <span class="tag {{ $adherent->bulletin ? 'tag-green' : 'tag-red' }}">Newsletter : {{ $adherent->bulletin ? 'OUI' : 'NON' }}</span>
                <span class="tag {{ $adherent->manif ? 'tag-green' : 'tag-red' }}">Manifestations : {{ $adherent->manif ? 'OUI' : 'NON' }}</span>
            </td>
        </tr>
        @php $actions = is_string($adherent->actions) ? json_decode($adherent->actions, true) : $adherent->actions; @endphp
        @if(!empty($actions) && is_array($actions) && count($actions) > 0)
        <tr>
            <th>Implication bénévole</th>
            <td>
                @foreach($actions as $action)
                    <span class="tag">{{ $action }}</span>
                @endforeach
            </td>
        </tr>
        @endif
        <tr>
            <th>Activités inscrites</th>
            <td>
                @forelse($adherent->activitesActives as $act)
                    • {{ $act->nom }}<br>
                @empty
                    Aucune activité
                @endforelse
            </td>
        </tr>
    </table>

    {{-- HISTORIQUE DES PAIEMENTS --}}
    @if($adherent->paiements && $adherent->paiements->count() > 0)
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
                @foreach($adherent->paiements->sortByDesc('date_paiement') as $paiement)
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
                        {{ number_format($adherent->montant_total, 2, ',', ' ') }} &euro;
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    @if($adherent->signature)
        <div class="section-title">Validation & Signature</div>
        <div class="signature-box">
            <img src="{{ $adherent->signature }}" alt="Signature de l'adhérent" class="signature-img">
            <p class="signature-text">Document certifié et signé électroniquement</p>
        </div>
    @endif

    <div class="footer">
        Fiche générée automatiquement par le système Savoirs Vivants le {{ now()->format('d/m/Y à H:i') }}.
    </div>

</body>
</html>
