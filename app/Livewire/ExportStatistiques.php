<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Adherent;
use App\Models\AdherentStructure;
use App\Models\Inscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExportStatistiques extends Component
{
    public string $saisonCourante;

    public function mount($saison)
    {
        $this->saisonCourante = $saison;
    }

    private function getAggregatedData(): array
    {
        $inscriptions = Inscription::where('saison', $this->saisonCourante)->get();

        // --- Adhérents Physiques ---
        $inscriptionsPhysiques = $inscriptions->whereNotNull('id_adherent');
        $totalAdherents = $inscriptionsPhysiques->count();

        $adherentsIds = $inscriptionsPhysiques->pluck('id_adherent');
        $adherents = Adherent::with('tousLesTuteurs')->whereIn('id', $adherentsIds)->get();

        $idsPayes = $inscriptionsPhysiques->where('a_paye', 'Payé')->pluck('id_adherent');
        $adherentsPayes = $adherents->whereIn('id', $idsPayes);

        $nbFilles = $adherents->whereIn('genre', ['Fille', 'Femme'])->count();
        $nbGarcons = $adherents->whereIn('genre', ['Garçon', 'Homme'])->count();
        $genreMap = [
            'Filles / Femmes' => $nbFilles,
            'Garçons / Hommes' => $nbGarcons,
        ];

        // CORRECTION TUTEURS (CSP) : On prend uniquement ceux de type "parent_tuteur"
        $cspMap = $adherents->map(function ($a) {
            $parent = $a->tousLesTuteurs->where('type', 'parent_tuteur')->first();
            return $parent && !empty($parent->profession) ? $parent->profession : null;
        })->filter()->countBy()->sortDesc()->toArray();

        $villeMap = $adherents->groupBy(fn($a) => $a->ville ?: 'Non renseigné')
                              ->map->count()->sortDesc()->toArray();

        $tranchesAge = [
            '< 6 ans' => [0, 5], '6-8 ans' => [6, 8], '9-11 ans' => [9, 11],
            '12-14 ans' => [12, 14], '15-17 ans' => [15, 17], '18-25 ans' => [18, 25],
            '26-40 ans' => [26, 40], '41-60 ans' => [41, 60], '> 60 ans' => [61, 200]
        ];
        $ageMap = [];
        foreach ($tranchesAge as $label => $range) {
            $ageMap[$label] = $adherents->filter(fn($a) => $a->date_naiss && Carbon::parse($a->date_naiss)->age >= $range[0] && Carbon::parse($a->date_naiss)->age <= $range[1])->count();
        }

        $evolutionMap = array_fill_keys(['Sep', 'Oct', 'Nov', 'Déc', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Jul', 'Aoû'], 0);
        $moisLabels = array_keys($evolutionMap);
        foreach ($inscriptions as $insc) { // On prend toutes les inscriptions (y compris structures) pour l'évolution
            if ($insc->date_inscription) {
                $m = Carbon::parse($insc->date_inscription)->month;
                $idx = $m >= 9 ? $m - 9 : $m + 3;
                if ($idx >= 0 && $idx <= 11) {
                    $evolutionMap[$moisLabels[$idx]]++;
                }
            }
        }
        $cumul = 0;
        foreach($evolutionMap as $k => $v) {
            $cumul += $v;
            $evolutionMap[$k] = $cumul;
        }

        $idsSaisonsPassees = Inscription::where('saison', '<', $this->saisonCourante)->pluck('id_adherent')->unique();
        $nbReinscrits = 0;
        $nbNouveaux = 0;
        foreach ($adherentsIds as $id) {
            if ($idsSaisonsPassees->contains($id)) $nbReinscrits++;
            else $nbNouveaux++;
        }

        $abandons = DB::table('activites_adherents')
            ->where('saison', $this->saisonCourante)
            ->where('est_un_abandon', 1)
            ->distinct('id_adherent')
            ->count('id_adherent');

        $statutMap = [
            'Nouveaux Inscrits' => $nbNouveaux,
            'Réinscrits (Fidélisés)' => $nbReinscrits,
            'Abandons en cours' => $abandons
        ];

        // --- Structures ---
        $structuresIdsPayes = $inscriptions->whereNotNull('id_structure')->where('a_paye', 'Payé')->pluck('id_structure');
        $structuresPayees = AdherentStructure::whereIn('id', $structuresIdsPayes)->get();

        return compact('totalAdherents', 'genreMap', 'cspMap', 'villeMap', 'ageMap', 'evolutionMap', 'statutMap', 'adherentsPayes', 'structuresPayees');
    }

    private function createSimpleSheet($spreadsheet, $sheetIndex, $title, $headerA, $headerB, $data, $headerStyle)
    {
        $sheet = $spreadsheet->createSheet($sheetIndex);
        $sheet->setTitle(substr($title, 0, 31));

        $sheet->setCellValue('A1', $headerA)->setCellValue('B1', $headerB);
        $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($data as $label => $value) {
            $sheet->setCellValue("A$row", $label)->setCellValue("B$row", $value);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }

    public function exportExcel()
    {
        $d = $this->getAggregatedData();
        $filename = 'statistiques_vivia_' . str_replace('-', '_', $this->saisonCourante) . '_' . now()->format('Ymd_Hi') . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setTitle('Statistiques ' . $this->saisonCourante);

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '16987C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        $s = $spreadsheet->getActiveSheet()->setTitle('Accueil');
        $s->setCellValue('A1', 'EXPORT DES STATISTIQUES')->mergeCells('A1:B1');
        $s->getStyle('A1:B1')->applyFromArray($headerStyle)->getFont()->setSize(14);

        $s->setCellValue('A3', 'Nom du fichier :')->setCellValue('B3', $filename);
        $s->setCellValue('A4', 'Saison étudiée :')->setCellValue('B4', $this->saisonCourante);
        $s->setCellValue('A5', 'Sujets testés (Adhérents Physiques) :')->setCellValue('B5', $d['totalAdherents']);

        $s->getStyle('A3:A5')->getFont()->setBold(true);
        $s->getColumnDimension('A')->setAutoSize(true);
        $s->getColumnDimension('B')->setAutoSize(true);

        $this->createSimpleSheet($spreadsheet, 1, 'Répartition par âges', 'Tranche d\'âge', 'Nombre', $d['ageMap'], $headerStyle);
        $this->createSimpleSheet($spreadsheet, 2, 'Répartition par genre', 'Genre', 'Nombre', $d['genreMap'], $headerStyle);
        $this->createSimpleSheet($spreadsheet, 3, 'Profession Parents', 'CSP', 'Nombre', $d['cspMap'], $headerStyle);
        $this->createSimpleSheet($spreadsheet, 4, 'Évolution inscriptions', 'Mois', 'Cumul', $d['evolutionMap'], $headerStyle);
        $this->createSimpleSheet($spreadsheet, 5, 'Ville d\'origine', 'Ville / Quartier', 'Nombre', $d['villeMap'], $headerStyle);
        $this->createSimpleSheet($spreadsheet, 6, 'Statut des adhérents', 'Statut', 'Nombre', $d['statutMap'], $headerStyle);

        // ==========================================
        // ONGLET 7 : ADHÉRENTS PHYSIQUES
        // ==========================================
        $sd = $spreadsheet->createSheet(7)->setTitle('Adhérents Payés');

        $headers = [
            'ID', 'Numéro', 'Nom', 'Prénom', 'Carnet', 'Date de Naiss.', 'Âge',
            'Genre', 'Adresse', 'Ville', 'Code Postal', 'Tél', 'Mail',
            'Occupation', 'Établissement', 'Régime Social',
            'Idée Métier', 'Découverte Métier', 'Problèmes Santé', 'Allergies', 'Conduite à tenir', 'Restrictions Alim.',
            'Actions Bénévoles', 'Commentaire', 'Manifestation', 'Communication', 'Bulletin'
        ];

        foreach ($headers as $col => $header) {
            $sd->setCellValue([$col + 1, 1], $header);
        }
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sd->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);

        $row = 2;
        foreach ($d['adherentsPayes'] as $a) {
            $ageCalc = $a->age ?? ($a->date_naiss ? Carbon::parse($a->date_naiss)->age : 'N/C');

            $rowData = [
                $a->id,
                $a->numero_adherent,
                $a->nom,
                $a->prenom,
                $a->carnet ?? '',
                $a->date_naiss ? Carbon::parse($a->date_naiss)->format('d/m/Y') : '',
                $ageCalc,
                $a->genre,
                $a->adresse,
                $a->ville,
                $a->code_postal,
                $a->tel,
                $a->mail,
                $a->occupation,
                $a->etablissement,
                $a->regime_social,
                $a->idee_metier,
                $a->decouverte_metier,
                $a->problemes_sante,
                $a->allergies,
                $a->conduite_a_tenir,
                $a->restrictions_alimentaires,
                $a->actions,
                $a->commentaire,
                $a->manif ? 'Oui' : 'Non',
                $a->communication ? 'Oui' : 'Non',
                $a->bulletin ? 'Oui' : 'Non'
            ];

            foreach ($rowData as $col => $value) {
                $sd->setCellValue([$col + 1, $row], $value);
            }

            if ($row % 2 === 0) {
                $sd->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
            }
            $row++;
        }

        for ($col = 1; $col <= count($headers); $col++) {
            $sd->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // ==========================================
        // ONGLET 8 : STRUCTURES
        // ==========================================
        $ss = $spreadsheet->createSheet(8)->setTitle('Structures Payées');

        $headersStruct = [
            'ID', 'Numéro', 'Nom', 'Sigle', 'Statut Juridique', 'Statut Activité',
            'Adresse', 'Code Postal', 'Ville', 'Date Création',
            'Tél', 'Tél Portable', 'Mail', 'Site Web',
            'Correspondant', 'Tél Correspondant',
            'Bulletin', 'Autorisation Photo'
        ];

        foreach ($headersStruct as $col => $header) {
            $ss->setCellValue([$col + 1, 1], $header);
        }
        $lastColStruct = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headersStruct));
        $ss->getStyle("A1:{$lastColStruct}1")->applyFromArray($headerStyle);

        $rowStruct = 2;
        foreach ($d['structuresPayees'] as $st) {
            $rowDataStruct = [
                $st->id,
                $st->numero_adherent,
                $st->nom,
                $st->sigle,
                $st->statut_juridique,
                $st->statut,
                $st->adresse,
                $st->code_postal,
                $st->ville,
                $st->date_creation ? Carbon::parse($st->date_creation)->format('d/m/Y') : '',
                $st->tel,
                $st->tel_portable,
                $st->mail,
                $st->site_web,
                $st->nom_correspondant,
                $st->tel_correspondant,
                $st->bulletin ? 'Oui' : 'Non',
                $st->autorisation_photo ? 'Oui' : 'Non'
            ];

            foreach ($rowDataStruct as $col => $value) {
                $ss->setCellValue([$col + 1, $rowStruct], $value);
            }

            if ($rowStruct % 2 === 0) {
                $ss->getStyle("A{$rowStruct}:{$lastColStruct}{$rowStruct}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
            }
            $rowStruct++;
        }

        for ($col = 1; $col <= count($headersStruct); $col++) {
            $ss->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportCsv()
    {
        $d = $this->getAggregatedData();
        $filename = 'statistiques_vivia_' . str_replace('-', '_', $this->saisonCourante) . '_' . now()->format('Ymd_Hi') . '.csv';

        return response()->streamDownload(function () use ($d, $filename) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM pour Excel

            $printStats = function($title, $headerA, $headerB, $data) use ($f) {
                fputcsv($f, ["--- $title ---"], ';');
                fputcsv($f, [$headerA, $headerB], ';');
                foreach ($data as $key => $val) {
                    fputcsv($f, [$key, $val], ';');
                }
                fputcsv($f, [], ';');
            };

            fputcsv($f, ['EXPORT STATISTIQUES'], ';');
            fputcsv($f, ['Nom du fichier :', $filename], ';');
            fputcsv($f, ['Saison étudiée :', $this->saisonCourante], ';');
            fputcsv($f, ['Sujets testés (Adhérents Physiques) :', $d['totalAdherents']], ';');
            fputcsv($f, [], ';');

            $printStats('RÉPARTITION PAR ÂGES', "Tranche d'âge", 'Nombre', $d['ageMap']);
            $printStats('RÉPARTITION PAR GENRE', 'Genre', 'Nombre', $d['genreMap']);
            $printStats('PROFESSION PARENTS', 'CSP', 'Nombre', $d['cspMap']);
            $printStats('ÉVOLUTION INSCRIPTIONS', 'Mois', 'Cumul', $d['evolutionMap']);
            $printStats('VILLE D\'ORIGINE', 'Ville / Quartier', 'Nombre', $d['villeMap']);
            $printStats('STATUT DES ADHÉRENTS', 'Statut', 'Nombre', $d['statutMap']);

            // --- ADHERENTS PHYSIQUES ---
            fputcsv($f, ['--- DÉTAIL DES ADHÉRENTS PHYSIQUES (PAYÉS UNIQUEMENT) ---'], ';');

            $headers = [
                'ID', 'Numéro', 'Nom', 'Prénom', 'Carnet', 'Date de Naiss.', 'Âge',
                'Genre', 'Adresse', 'Ville', 'Code Postal', 'Tél', 'Mail',
                'Occupation', 'Établissement', 'Régime Social',
                'Idée Métier', 'Découverte Métier', 'Problèmes Santé', 'Allergies', 'Conduite à tenir', 'Restrictions Alim.',
                'Actions Bénévoles', 'Commentaire', 'Manifestation', 'Communication', 'Bulletin'
            ];
            fputcsv($f, $headers, ';');

            foreach ($d['adherentsPayes'] as $a) {
                $ageCalc = $a->age ?? ($a->date_naiss ? Carbon::parse($a->date_naiss)->age : 'N/C');

                fputcsv($f, [
                    $a->id,
                    $a->numero_adherent,
                    $a->nom,
                    $a->prenom,
                    $a->carnet ?? '',
                    $a->date_naiss ? Carbon::parse($a->date_naiss)->format('d/m/Y') : '',
                    $ageCalc,
                    $a->genre,
                    $a->adresse,
                    $a->ville,
                    $a->code_postal,
                    $a->tel,
                    $a->mail,
                    $a->occupation,
                    $a->etablissement,
                    $a->regime_social,
                    $a->idee_metier,
                    $a->decouverte_metier,
                    $a->problemes_sante,
                    $a->allergies,
                    $a->conduite_a_tenir,
                    $a->restrictions_alimentaires,
                    $a->actions,
                    $a->commentaire,
                    $a->manif ? 'Oui' : 'Non',
                    $a->communication ? 'Oui' : 'Non',
                    $a->bulletin ? 'Oui' : 'Non'
                ], ';');
            }

            fputcsv($f, [], ';');

            // --- STRUCTURES ---
            fputcsv($f, ['--- DÉTAIL DES STRUCTURES (PAYÉES UNIQUEMENT) ---'], ';');

            $headersStruct = [
                'ID', 'Numéro', 'Nom', 'Sigle', 'Statut Juridique', 'Statut Activité',
                'Adresse', 'Code Postal', 'Ville', 'Date Création',
                'Tél', 'Tél Portable', 'Mail', 'Site Web',
                'Correspondant', 'Tél Correspondant',
                'Bulletin', 'Autorisation Photo'
            ];
            fputcsv($f, $headersStruct, ';');

            foreach ($d['structuresPayees'] as $st) {
                fputcsv($f, [
                    $st->id,
                    $st->numero_adherent,
                    $st->nom,
                    $st->sigle,
                    $st->statut_juridique,
                    $st->statut,
                    $st->adresse,
                    $st->code_postal,
                    $st->ville,
                    $st->date_creation ? Carbon::parse($st->date_creation)->format('d/m/Y') : '',
                    $st->tel,
                    $st->tel_portable,
                    $st->mail,
                    $st->site_web,
                    $st->nom_correspondant,
                    $st->tel_correspondant,
                    $st->bulletin ? 'Oui' : 'Non',
                    $st->autorisation_photo ? 'Oui' : 'Non'
                ], ';');
            }

            fclose($f);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function render()
    {
        return view('livewire.export-statistiques');
    }
}
