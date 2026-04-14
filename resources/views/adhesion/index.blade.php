@extends('layouts.app')

@section('title', 'Formulaire d\'adhésion')

@section('content')

    @php
        $field =
            'w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/25 focus:border-teal-500 transition-colors text-sm placeholder:text-gray-400';
        $label = 'block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide';
        $card =
            'border-2 rounded-xl p-4 transition-all duration-150 h-full flex flex-col bg-white cursor-pointer select-none';
        $btn =
            'inline-flex items-center justify-center gap-2 bg-teal-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-teal-700 active:scale-95 focus:ring-2 focus:ring-teal-500/30 transition text-sm shadow-sm';
        $btnBack =
            'inline-flex items-center justify-center gap-2 text-gray-500 font-medium px-4 py-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 hover:text-gray-700 transition text-sm';
        $check = 'h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 cursor-pointer';
        $radio = 'h-4 w-4 border-gray-300 text-teal-600 focus:ring-teal-500 cursor-pointer';
    @endphp

    <div class="min-h-screen bg-gray-50 py-6 px-4 font-grotesk">
        <div class="max-w-xl mx-auto">

            <div class="text-center mb-5">
                <h1 class="text-2xl font-bold text-gray-900">📝 {{ $titreFormulaire }}</h1>
                <p class="text-gray-500 mt-1 text-sm">Remplissez les informations étape par étape</p>
            </div>

            @if ($step !== 11)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 pt-4 pb-5 mb-5">

                    <div class="flex items-center justify-between mb-2.5">
                        <p class="text-xs text-gray-400 font-medium">
                            Étape <span class="font-bold text-gray-700">{{ $currentNum }}</span>
                            <span class="text-gray-300 mx-1">/</span>
                            <span class="text-gray-500">{{ $totalSteps }}</span>
                        </p>
                        <span class="text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-100 px-2.5 py-1 rounded-full">
                            {{ $stepMeta[$step]['icon'] }} {{ $stepMeta[$step]['label'] }}
                        </span>
                    </div>

                    <div class="w-full bg-gray-100 rounded-full h-1.5 mb-4">
                        <div class="h-1.5 rounded-full bg-teal-500 transition-all duration-500"
                            style="width: {{ ($currentNum / $totalSteps) * 100 }}%;"></div>
                    </div>

                    <div class="flex items-center">
                        @foreach ($path as $i => $s)
                            @php
                                $pathIdx = array_search($step, $path);
                                $isDone    = $i < $pathIdx;
                                $isCurrent = $s === $step;
                            @endphp

                            @if ($i > 0)
                                <div class="h-px flex-1 min-w-[6px] transition-colors duration-300
                                    {{ $isDone ? 'bg-teal-400' : 'bg-gray-200' }}"></div>
                            @endif

                            <div class="shrink-0 rounded-full transition-all duration-300
                                {{ $isCurrent
                                    ? 'w-3 h-3 bg-teal-600 ring-2 ring-offset-1 ring-teal-300'
                                    : ($isDone
                                        ? 'w-2.5 h-2.5 bg-teal-400'
                                        : 'w-2 h-2 bg-gray-200') }}"
                                title="{{ $stepMeta[$s]['label'] }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                @if ($step === 1)
                    @include('adhesion.step_statut')
                @elseif ($step === 12)
                    @include('adhesion.step_statut_juridique')
                @elseif ($step === 2)
                    @include('adhesion.step_activite')
                @elseif ($step === 3)
                    @include('adhesion.step_informations_personnelles')
                @elseif ($step === 15)
                    @include('adhesion.step_orientation_professionnelle')
                @elseif ($step === 4)
                    @include('adhesion.step_medical')
                @elseif ($step === 5)
                    @include('adhesion.step_situation')
                @elseif ($step === 6)
                    @include('adhesion.step_ateliers')
                @elseif ($step === 7)
                    @include('adhesion.step_benevole')
                @elseif ($step === 8)
                    @include('adhesion.step_tuteur')
                @elseif ($step === 13)
                    @include('adhesion.step_structure')
                @elseif ($step === 14)
                    @include('adhesion.step_autorisations')
                @elseif ($step === 9)
                    @include('adhesion.step_signature')
                @elseif ($step === 10)
                    @include('adhesion.step_paiement')
                @elseif ($step === 11)
                    @include('adhesion.step_confirmation')
                @elseif ($step === 16)
                    @include('adhesion.step_pre_inscription')
                @endif

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/4.1.7/signature_pad.umd.min.js"></script>

    <script>

        function cotisationPaiement() {
                        return {
                            loading: false,
                            dejaClique: {{ !empty($formData['_via_url_checkout']) ? 'true' : 'false' }},
                            init() {},
                            async ouvrirHelloAsso() {
                                this.loading = true;
                                try {
                                    const response = await fetch('{{ route('adhesion.helloasso2.checkout', $token) }}', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                        },
                                    });
                                    const data = await response.json();
                                    if (data.url) {
                                        window.open(data.url, '_blank');
                                        this.dejaClique = true;
                                    }
                                } catch (e) {
                                    console.error(e);
                                } finally {
                                    this.loading = false;
                                }
                            }
                        }
                    }

        (function() {
            const canvas = document.getElementById('canvas-adherent');
            if (!canvas) return;

            const sigPad = new SignaturePad(canvas, {
                penColor: '#0f172a',
                backgroundColor: 'rgba(255,255,255,1)'
            });

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const data = sigPad.toData();
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                sigPad.clear();
                sigPad.fromData(data);
            }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            const existingData = document.getElementById('sig-data-adherent').value;
            if (existingData && existingData.startsWith('data:')) {
                sigPad.fromDataURL(existingData);
            }

            document.getElementById('form-signature')?.addEventListener('submit', function() {
                if (!sigPad.isEmpty()) {
                    document.getElementById('sig-data-adherent').value = sigPad.toDataURL();
                }
            });

            document.getElementById('clear-sig-adherent')?.addEventListener('click', function() {
                sigPad.clear();
                document.getElementById('sig-data-adherent').value = '';
            });
        })();

        function tuteurManager() {
            const existing = @json($formData['tuteurs'] ?? null);

            return {
                tuteurs: [],
                sigPads: {},

                init() {
                    if (existing && Array.isArray(existing) && existing.length > 0) {
                        this.tuteurs = existing;
                    } else {
                        this.tuteurs = [this.emptyTuteur('parent_tuteur')];
                    }
                    this.$nextTick(() => {
                        this.tuteurs.forEach((t, i) => {
                            if (t.type === 'parent_tuteur') this.initSigPad(i);
                        });
                    });
                },

                emptyTuteur(type) {
                    const base = {
                        type: type,
                        nom: '',
                        prenom: '',
                        tel: '',
                        mail: '',
                        profession: '',
                    };
                    if (type === 'parent_tuteur') {
                        Object.assign(base, {
                            nom_enfant: '{{ ($formData['prenom'] ?? '') . ' ' . ($formData['nom'] ?? '') }}',
                            adhere: false,
                            rentre_fin: false,
                            rentre_annul: false,
                            date_signature: new Date().toISOString().split('T')[0],
                            signature: ''
                        });
                    }
                    return base;
                },

                addTuteur(type) {
                    this.tuteurs.push(this.emptyTuteur(type));
                    const newIdx = this.tuteurs.length - 1;
                    if (type === 'parent_tuteur') {
                        this.$nextTick(() => this.initSigPad(newIdx));
                    }
                },

                removeTuteur(i) {
                    if (this.sigPads[i]) {
                        delete this.sigPads[i];
                    }
                    this.tuteurs.splice(i, 1);
                },

                initSigPad(i) {
                    const canvas = document.getElementById('canvas-tuteur-' + i);
                    if (!canvas || this.sigPads[i]) return;

                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);

                    const sp = new SignaturePad(canvas, {
                        penColor: '#0f172a',
                        backgroundColor: 'rgba(255,255,255,1)'
                    });
                    this.sigPads[i] = sp;

                    if (this.tuteurs[i]?.signature) {
                        sp.fromDataURL(this.tuteurs[i].signature);
                    }

                    sp.addEventListener('endStroke', () => {
                        document.getElementById('sig-data-tuteur-' + i).value = sp.toDataURL();
                    });
                },

                clearCanvas(i) {
                    if (this.sigPads[i]) {
                        this.sigPads[i].clear();
                        this.tuteurs[i].signature = '';
                        document.getElementById('sig-data-tuteur-' + i).value = '';
                    }
                },
            };
        }
    </script>

@endsection
