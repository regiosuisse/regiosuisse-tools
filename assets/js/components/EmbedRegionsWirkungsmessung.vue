<template>

    <div class="embed-regions" :class="[$env.INSTANCE_ID+'-regions', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div v-if="templateHook('regionsBefore', locale)" v-html="templateHook('regionsBefore', locale)"></div>

        <div class="embed-regions-filters">

            <div class="embed-regions-filters-select" data-filter-type="topics">
                <div class="embed-regions-filters-select-label"
                     @click.stop="clickFilterSelect('topic')">{{ $t('Thema', locale) }}</div>

                <div class="embed-regions-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'topic'}"></div>

                <transition name="embed-regions-filters-select-options" mode="out-in">
                    <div class="embed-regions-filters-select-options" v-if="activeFilterSelect === 'topic'">
                        <div class="embed-regions-filters-select-options-item"
                             v-for="topic in filteredTopics"
                             :key="topic.id"
                             :class="{ 'is-selected': isFilterSelected({ type: 'topic', entity: topic }) }"
                             @click.stop="clickToggleFilter({ type: 'topic', entity: topic })">
                            {{ translateField(topic, 'name', locale) }}
                        </div>
                    </div>
                </transition>
            </div>

            <div class="embed-regions-filters-select" data-filter-type="programs">
                <div class="embed-regions-filters-select-label"
                     @click.stop="clickFilterSelect('program')">{{ $t('Programm', locale) }}</div>

                <div class="embed-regions-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'program'}"></div>

                <transition name="embed-regions-filters-select-options" mode="out-in">
                    <div class="embed-regions-filters-select-options" v-if="activeFilterSelect === 'program'">
                        <div class="embed-regions-filters-select-options-item"
                             v-for="program in filteredPrograms"
                             :key="program.id"
                             :class="{ 'is-selected': isFilterSelected({ type: 'program', entity: program }) }"
                             @click.stop="clickToggleFilter({ type: 'program', entity: program })">
                            {{ translateField(program, 'name', locale) }}
                        </div>
                    </div>
                </transition>
            </div>

            <div class="embed-regions-filters-select" data-filter-type="states">
                <div class="embed-regions-filters-select-label"
                     @click.stop="clickFilterSelect('state')">{{ $t('Kanton', locale) }}</div>

                <div class="embed-regions-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'state'}"></div>

                <transition name="embed-regions-filters-select-options" mode="out-in">
                    <div class="embed-regions-filters-select-options" v-if="activeFilterSelect === 'state'">
                        <div class="embed-regions-filters-select-options-item"
                             v-for="state in filteredStates"
                             :key="state.id"
                             :class="{ 'is-selected': isFilterSelected({ type: 'state', entity: state }) }"
                             @click.stop="clickToggleFilter({ type: 'state', entity: state })">
                            {{ translateField(state, 'name', locale) }}
                        </div>
                    </div>
                </transition>
            </div>

            <div class="embed-regions-filters-list">
                <div class="embed-regions-filters-list-item"
                     v-for="(filter, fIndex) in filters"
                     :key="fIndex"
                     @click.stop="clickToggleFilter(filter)">{{ translateField(filter.entity, 'name', locale) }}</div>
            </div>

        </div>

        <div class="embed-regions-content">

            <div class="embed-regions-content-map">

                <div class="embed-regions-content-map-container" :id="'random-'+random" ref="mapContainer" :class="{'is-loading': isLoading}"></div>

            </div>

        </div>

        <div v-if="templateHook('regionsAfter', locale)" v-html="templateHook('regionsAfter', locale)"></div>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import {track, trackDevice, trackPageView} from '../utils/logger';
import mapboxgl from "mapbox-gl/dist/mapbox-gl";
import swissmaptiles from "../../../config/gis/swissmaptiles.json";

const data = [
    {
        "Jahr": 2024,
        "Kanton": [
            "BE"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Digitale Dorfstrasse",
        "Projektträgerschaft": "Verein IG Dorf, Adelboden",
        "Koordinaten Pin": [
            46.491375396420466,
            7.5572736080959295
        ],
        "Projektbeschreibung": "Die Adelbodner Dorfstrasse punktet mit Tradition und einem hochwertigen lokalen Detailhandelsangebot. Doch die Ansprüche an die Verfügbarkeit dieser Angebote sind spürbar gestiegen und bringen das lokale Gewerbe an seine Grenzen. Dank dem Einsatz innovativer digitaler Technologien sollen Lücken im bestehenden Angebot geschlossen und die Wertschöpfungskette ausgeweitet werden. Damit könnten Ferienwohnungsbesitzer bei der Anreise spätabends noch frische Lebensmittel vor Ort beziehen, Einheimische und Hotelgäste jederzeit einkaufen und die letzteren könnten auch nach ihren Ferien von den Lieblingsprodukten aus dem Berner Oberland zehren. Das Projekt \"Umsetzungsprojekt Digitale Dorfstrasse\" soll das bisherige Angebot mit zukunftsweisenden neuen Vertriebswegen ergänzen. Der NRP-Beitrag soll die Kosten für die Realisierung decken um die Angebote rasch zugänglich zu machen.",
        "Hinweise": null,
        "Start": 2021,
        "End": 2022,
        "Eval": 2024,
        "Projektkosten": 312000.0,
        "Anteil NRP in %": 0.64,
        "Tags": [
            "Tourismus",
            "Digitalisierung",
            "Lokale Wirtschaft"
        ],
        "id": "82818419-1b68-4fe8-9477-df1c576bb708",
        "Link": "https://regiosuisse.ch/sites/default/files/2025-02/Wirkungsmessung_2024_BE_DigitaleDorfstrasse.pdf"
    },
    {
        "Jahr": 2024,
        "Kanton": [
            "BE"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Pop Up Academy TALK",
        "Projektträgerschaft": "Tourismus Adelboden-Lenk-Kandersteg, TALK AG",
        "Koordinaten Pin": [
            46.58926510276785,
            7.651442066626917
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2020,
        "End": 2023,
        "Eval": 2024,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Digitalisierung"
        ],
        "id": "ba232bf8-a36e-48be-9960-7d8853407fea",
        "Link": "https://regiosuisse.ch/sites/default/files/2025-02/Wirkungsmessung_2024_BE_PopupAcademyTALK_final.pdf"
    },
    {
        "Jahr": 2024,
        "Kanton": [
            "SO"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "3D-Druck in der Medizintechnik",
        "Projektträgerschaft": "Swiss m4m Center, Bettlach",
        "Koordinaten Pin": [
            47.1972272656921,
            7.420069991514592
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2020,
        "End": 2022,
        "Eval": 2024,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Digitalisierung"
        ],
        "id": "058eca44-6dcb-4c86-ac40-646395e94dc3",
        "Link": "https://regiosuisse.ch/sites/default/files/2025-02/Wirkungsmessung_2024_SO_TrainingAndEducation3DDruck_0.pdf"
    },
    {
        "Jahr": 2024,
        "Kanton": [
            "VS"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Idéation & Innovation Santé Digitale",
        "Projektträgerschaft": "CimArk, Sion",
        "Koordinaten Pin": [
            46.22759550813632,
            7.364309506870936
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2021,
        "End": 2023,
        "Eval": 2024,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Digitalisierung"
        ],
        "id": "2e13f38c-2da3-45e3-94bf-c4f197d4b303",
        "Link": "https://regiosuisse.ch/sites/default/files/2025-02/MesureDImpact_2024_VS_SanteDigitale.pdf"
    },
    {
        "Jahr": 2023,
        "Kanton": [
            "GL"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Agrotourismus 5.0",
        "Projektträgerschaft": "VISIT Glarnerland AG",
        "Koordinaten Pin": [
            47.00173556671895,
            9.078508492958225
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2020,
        "End": 2021,
        "Eval": 2023,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Lokale Wirtschaft"
        ],
        "id": "3c42e683-34ad-4781-bc3b-a1a8b63b7d7f",
        "Link": "https://regiosuisse.ch/sites/default/files/2023-11/Wirkungsmessung%202023%20Agrotourismus%205.0.pdf"
    },
    {
        "Jahr": 2023,
        "Kanton": [
            "GL"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Impulsprogramm Hotellerie",
        "Projektträgerschaft": "VISIT Glarnerland AG",
        "Koordinaten Pin": [
            47.00173556671895,
            9.078508492958225
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2022,
        "End": 2023,
        "Eval": 2023,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Lokale Wirtschaft"
        ],
        "id": "17da9b32-e46c-4117-8abc-aeefa734d535",
        "Link": "https://regiosuisse.ch/sites/default/files/2023-12/Wirkungsmessung%202023%20Impulsprogramm%20Hotellerie.pdf"
    },
    {
        "Jahr": 2023,
        "Kanton": [
            "LU"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Kreativfabrik 62",
        "Projektträgerschaft": "Kreativfabrik 62 GmbH",
        "Koordinaten Pin": [
            47.15181504396899,
            8.115985953275793
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2016,
        "End": 2017,
        "Eval": 2023,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Lokale Wirtschaft"
        ],
        "id": "3d4921b2-3d59-412d-92f7-61bf65dfb8a2",
        "Link": "https://regiosuisse.ch/sites/default/files/2023-12/Wirkungsmessung%202023%20Kreativfabrik62.pdf"
    },
    {
        "Jahr": 2023,
        "Kanton": [
            "TI"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Il Larice",
        "Projektträgerschaft": "Associazione il Larice",
        "Koordinaten Pin": [
            46.457515707129886,
            8.923748084046792
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2020,
        "End": 2024,
        "Eval": 2023,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Lokale Wirtschaft"
        ],
        "id": "b98afdad-ffc8-454a-abce-534b0e088e1f",
        "Link": "https://regiosuisse.ch/sites/default/files/2023-12/Misurazione%20degli%20effetti%202023%20Il%20Larice.pdf"
    },
    {
        "Jahr": 2023,
        "Kanton": [
            "FR"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Maison de Vully",
        "Projektträgerschaft": "Vully Tourisme",
        "Koordinaten Pin": [
            46.948918715188924,
            7.084840560265758
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2021,
        "End": 2023,
        "Eval": 2023,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Lokale Wirtschaft"
        ],
        "id": "73899201-c5b1-4c90-8e40-e3e36211a546",
        "Link": "https://regiosuisse.ch/sites/default/files/2023-12/Mesure%20d%27efficacité%202023%20Maison%20Du%20Vully.pdf"
    },
    {
        "Jahr": 2022,
        "Kanton": [
            "JU"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Centrale de Roche de Mars couplage chaleur-force",
        "Projektträgerschaft": "Thermoréseau-Porrentruy SA",
        "Koordinaten Pin": [
            47.41961590796668,
            7.093311923320015
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2015,
        "End": 2017,
        "Eval": 2022,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Nachhaltigkeit"
        ],
        "id": "7c72dbd1-17e3-4cef-be0f-750bb6e3f25a",
        "Link": "https://regiosuisse.ch/sites/default/files/2023-02/Mesure%20d%27efficacité%202022%20Thermoréseau%20Jura.pdf"
    },
    {
        "Jahr": 2022,
        "Kanton": [
            "NE"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Innovation environnementale et sociétale (hub Neuchâtel)",
        "Projektträgerschaft": "hub Neuchâtel",
        "Koordinaten Pin": [
            46.99262628441352,
            6.930419479614409
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2018,
        "End": 2022,
        "Eval": 2022,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Nachhaltigkeit"
        ],
        "id": "b37bedea-fd6b-47fc-8abe-7cf492d7fadd",
        "Link": "https://regiosuisse.ch/sites/default/files/2023-02/Mesure%20d%27efficacité%202022%20hub%20neuchatel.pdf"
    },
    {
        "Jahr": 2022,
        "Kanton": [
            "UR"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Kompetenzzentrum Fischzucht Erstfeld am Neuenburgersee",
        "Projektträgerschaft": "hub Neuchâtel",
        "Koordinaten Pin": [
            46.99262628441352,
            6.930419479614409
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2016,
        "End": 2023,
        "Eval": 2022,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Nachhaltigkeit",
            "Lokale Wirtschaft"
        ],
        "id": "f10380a8-7510-4a29-b434-19a90e9983e9",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-12/Wirkungsmessung%202022%20Kompetenzzentrum%20Fischzucht%20Erstfeld.pdf"
    },
    {
        "Jahr": 2022,
        "Kanton": [
            "LU"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Nachhaltigkeit Rigi 2030",
        "Projektträgerschaft": "RigiPlusAG",
        "Koordinaten Pin": [
            47.00982244160184,
            8.482961548465086
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2020,
        "End": 2021,
        "Eval": 2022,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Nachhaltigkeit"
        ],
        "id": "d60e42f5-6db1-40f5-9e87-3234ab137664",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-10/Wirkungsmessung%202022%20Nachhaltige%20Entwicklung%20Rigi%202030.pdf"
    },
    {
        "Jahr": 2022,
        "Kanton": [
            "TG"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Innovationszelle Wald und Holz",
        "Projektträgerschaft": "Lignum Ost",
        "Koordinaten Pin": [
            47.555661982617806,
            8.891482663830576
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2016,
        "End": 2019,
        "Eval": 2022,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Nachhaltigkeit"
        ],
        "id": "f724679e-d916-4f08-8405-b7cd1fb18f27",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-09/Kurzbericht%20IZ%20Wald%20und%20Holz%20%282022%29.pdf"
    },
    {
        "Jahr": 2021,
        "Kanton": [
            "BE"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "«Initiative Holz | BE»",
        "Projektträgerschaft": "Verein BEO HOLZ",
        "Koordinaten Pin": [
            46.69015619209914,
            7.67088516293774
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2018,
        "End": 2022,
        "Eval": 2022,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Nachhaltigkeit"
        ],
        "id": "d92fc223-f614-4dc0-bb47-33dec6a1b7e0",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-04/Wirkungsmessung%202021%20Kurzbericht%20Initiative%20Holz%20BE.pdf"
    },
    {
        "Jahr": 2020,
        "Kanton": [
            "VS"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Nouveau télésiège “La Forêt”",
        "Projektträgerschaft": "Funiculaire St Luc-Chandolin SA",
        "Koordinaten Pin": [
            46.21994034104571,
            7.6033829373229445
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2015,
        "End": 2016,
        "Eval": 2020,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "fa4825e3-d964-4caa-ab2a-3808d46ca5c1",
        "Link": "https://regiosuisse.ch/sites/default/files/2021-04/L4%20Mesures%20d%27efficacité%202020%20Rapport%20Telesiege%20LaForet_0.pdf"
    },
    {
        "Jahr": 2020,
        "Kanton": [
            "GR"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "4er-Sesselbahn Misanenga-Untermatt",
        "Projektträgerschaft": "Bergbahnen Obersaxen AG",
        "Koordinaten Pin": [
            46.75064710574112,
            9.11969016391898
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2017,
        "End": 2018,
        "Eval": 2020,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "038f5f47-18b5-4124-b02f-d35233225253",
        "Link": "https://regiosuisse.ch/sites/default/files/2021-04/L4_Wirkungsmessung%202020%20Kurzbericht%20Bergbahnen%20Obersaxen.pdf"
    },
    {
        "Jahr": 2020,
        "Kanton": [
            "OW"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Neubau Gondelbahn Stöckalp-Melchsee-Frutt",
        "Projektträgerschaft": "Sportbahnen Melchsee-Frutt",
        "Koordinaten Pin": [
            46.80216994439276,
            8.279157123134395
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2012,
        "End": 2015,
        "Eval": 2020,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "aec67df0-4772-4f2e-9a15-533cbc28f7f4",
        "Link": "https://regiosuisse.ch/sites/default/files/2021-04/L4%20Wirkungsmessung%202020%20Kurzbericht%20Gondelbahn%20Melchsee-Frutt%20D0-2_4.pdf"
    },
    {
        "Jahr": 2020,
        "Kanton": [
            "VS"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Ersatzneubau Gemmibahnen",
        "Projektträgerschaft": "Gemmibahnen AG",
        "Koordinaten Pin": [
            46.383983766876035,
            7.624715579041776
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2012,
        "End": 2015,
        "Eval": 2020,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "1e8a7c6d-480e-4ec7-bfe9-4e08d1a20408",
        "Link": "https://regiosuisse.ch/sites/default/files/2021-04/L4%20Wirkungsmessung%202020%20Kurzbericht%20Gemmibahn.pdf"
    },
    {
        "Jahr": 2019,
        "Kanton": [
            "JU"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "FAGUS",
        "Projektträgerschaft": "FAGUS Suisse SA",
        "Koordinaten Pin": [
            47.210983911398856,
            7.0072336649472975
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2017,
        "End": 2020,
        "Eval": 2019,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Nachhaltigkeit"
        ],
        "id": "4189309f-2e14-46ca-a7dd-4cf803f8e3a5",
        "Link": "https://regiosuisse.ch/sites/default/files/2021-10/L4%20Mesures%20d%27efficacité%202019%20Rapport%20FAGUS.pdf"
    },
    {
        "Jahr": 2019,
        "Kanton": [
            "UR"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "«Online-Buchungs-Offensive» (OBO) Uri",
        "Projektträgerschaft": "IG Tourismus Uri",
        "Koordinaten Pin": [
            46.88138108002537,
            8.645004796636826
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2017,
        "End": 2018,
        "Eval": 2019,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Digitalisierung"
        ],
        "id": "e9fbd3d8-5f0f-4725-99f2-97f8a32edd0e",
        "Link": "https://regiosuisse.ch/sites/default/files/2021-04/L4%20Wirkungsmessung%202019%20Kurzbericht%20Online-Buchungsoffensive%20Uri.pdf"
    },
    {
        "Jahr": 2019,
        "Kanton": [
            "BE"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Weiterentwicklung / Markteinführung Berglodges Gadmen",
        "Projektträgerschaft": "Alpenrose Gadmen",
        "Koordinaten Pin": [
            46.73876646758569,
            8.360631280142172
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2013,
        "End": 2015,
        "Eval": 2019,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "e8dd6720-b015-4cc6-a45a-de34a099ec4a",
        "Link": "https://regiosuisse.ch/sites/default/files/2021-04/L4%20Wirkungsmessung%202019%20Kurzbericht%20Berglodges%20Gadmen.pdf"
    },
    {
        "Jahr": 2018,
        "Kanton": [
            "LU"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Pfahlkopffräse ",
        "Projektträgerschaft": "BRC, Rain",
        "Koordinaten Pin": [
            47.12528854431312,
            8.244565965856372
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2017,
        "End": 2017,
        "Eval": 2018,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "021e2d1b-8050-44e9-91fe-fd50b4a4acca",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-02/Wirkungsmessung%202018%20Kurzbericht%20BRC%20Pfahlkopffräse.pdf"
    },
    {
        "Jahr": 2018,
        "Kanton": [
            "BE"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Centre Technique du Moule (CTM) ",
        "Projektträgerschaft": "Centre de Technologies Microtechniques CTM",
        "Koordinaten Pin": [
            47.15407770973733,
            7.016846514120142
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2010,
        "End": 2014,
        "Eval": 2018,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "120ce6fd-e0e3-43e4-986c-d1a2b7f347a0",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-02/Mesure%20d%27efficacité%202018%20Rapport%20CTM.pdf"
    },
    {
        "Jahr": 2018,
        "Kanton": [
            "BE, JU"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Système complet de Mesure Auto-matisée avec Corrections-machine (SMAC) ",
        "Projektträgerschaft": "Tornos SA",
        "Koordinaten Pin": [
            47.275563474862025,
            7.363195604610134
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2017,
        "End": 2018,
        "Eval": 2018,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Digitalisierung"
        ],
        "id": "2d876a98-38f7-4f3e-b205-e2d319a44ee6",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-02/Mesure%20d%27efficacité%202018%20Rapport%20SMAC.pdf"
    },
    {
        "Jahr": 2018,
        "Kanton": [
            "Interreg"
        ],
        "Fördermassnahme": "Interreg",
        "Projekt": "Micelab ",
        "Projektträgerschaft": "St. Gallen-Bodensee-Tourismus",
        "Koordinaten Pin": [
            47.4233566820332,
            9.376559844660353
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2015,
        "End": 2019,
        "Eval": 2018,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "2e0ddd75-16bf-4e50-bd32-23fed6c6590b",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-02/Wirkungsmessung%202018%20Kurzbericht%20micelab-bodensee.pdf"
    },
    {
        "Jahr": 2018,
        "Kanton": [
            "NE"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Totemi ",
        "Projektträgerschaft": "Talk to me Sàrl",
        "Koordinaten Pin": [
            46.99065956425471,
            6.929361736565639
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2016,
        "End": 2018,
        "Eval": 2018,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Digitalisierung"
        ],
        "id": "5557b0ed-78ca-408b-b27b-874ea0ab2834",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-02/Mesure%20d%27efficacité%202018%20Rapport%20Totemi.pdf"
    },
    {
        "Jahr": 2018,
        "Kanton": [
            "GR"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Skigebietsverbindung Arosa-Lenzerheide ",
        "Projektträgerschaft": "Urden AG, c/o Arosa Bergbahnen AG",
        "Koordinaten Pin": [
            46.766410313958104,
            9.620884228240214
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2013,
        "End": 2014,
        "Eval": 2018,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "cc555cfc-9dae-4af6-9e51-b9a5c090251d",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-06/Wirkungsmessung%202018%20Kurzbericht%20Skigebietsverbindung%20Lenzerheide-Arosa.pdf"
    },
    {
        "Jahr": 2017,
        "Kanton": [
            "SG"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Entwicklung und Erschliessung des Areals Gebenloo-Tüfi ",
        "Projektträgerschaft": "Gemeinde Bronschhofen",
        "Koordinaten Pin": [
            47.47660087009972,
            9.035382456077196
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2010,
        "End": 2012,
        "Eval": 2017,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "56cd049f-7991-4277-a14c-c8eb288b1921",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-02/Wirkungsmessung%202017%20Kurzbericht%20Gebenloo-Tüfi.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "SG"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Entwicklung und Erschliessung des Areals Gebenloo-Tüfi ",
        "Projektträgerschaft": "Gemeinde Bronschhofen",
        "Koordinaten Pin": [
            47.47660087009972,
            9.035382456077196
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2010,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "bcaecda5-89da-4e9d-9882-f937745bb9c5",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Arealentwicklung%20Gebenloo-Tüfi.pdf"
    },
    {
        "Jahr": 2017,
        "Kanton": [
            "TG"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Kompetenz-Zentrum Erneuerbare Energie-Systeme KEEST ",
        "Projektträgerschaft": "Verein Wirtschaftsraum Südthurgau",
        "Koordinaten Pin": [
            47.477648449044416,
            8.999488129816646
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2008,
        "End": 2011,
        "Eval": 2017,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Nachhaltigkeit"
        ],
        "id": "8a3064fd-926a-4625-bcf6-39462256ef3d",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-02/Wirkungsmessung%202017%20Kurzbericht%20KEEST.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "TG"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Kompetenz-Zentrum Erneuerbare Energie-Systeme KEEST ",
        "Projektträgerschaft": "Verein Wirtschaftsraum Südthurgau",
        "Koordinaten Pin": [
            47.466555557331056,
            8.971769680642359
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2009,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Nachhaltigkeit"
        ],
        "id": "4777d56e-9bcd-453f-8213-eaf079ed9236",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20%20Kompetenz-Zentrum%20Erneuerbare%20Energie%20SüdThurgau%20%28KEEST%29.pdf"
    },
    {
        "Jahr": 2017,
        "Kanton": [
            "FR"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Pôle Scientifique et Technologique-FR ",
        "Projektträgerschaft": "PST-FR / Innosquare",
        "Koordinaten Pin": [
            46.799214456854756,
            7.151096705994981
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2012,
        "End": 2015,
        "Eval": 2017,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "bc070e66-e109-4347-ab21-0beefa2af345",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-02/Mesure%20d%27efficacité%202017%20Rapport%20PST-FR.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "FR"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Pôle Scientifique et Technologique-FR ",
        "Projektträgerschaft": "Association PST-FR",
        "Koordinaten Pin": [
            46.798643887029286,
            7.1482255240674215
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2009,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "aff7c86e-7d27-4848-a5cd-5747d1a2a6ab",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Pole%20Scientifique%20et%20Technolgique%20du%20Canton%20de%20Fribourg%20%28PST-FR%29.pdf"
    },
    {
        "Jahr": 2017,
        "Kanton": [
            "BE"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Sbrinz-Route ",
        "Projektträgerschaft": "Förderverein Sbrinz-Route",
        "Koordinaten Pin": [
            46.96132164622065,
            8.288108237988338
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2008,
        "End": 2011,
        "Eval": 2017,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "89819307-db2f-435a-a22b-48ac9eb6573d",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-02/Wirkungsmessung%202017%20Kurzbericht%20Sbrinz-Route.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "BE"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Sbrinz-Route ",
        "Projektträgerschaft": "Förderverein Sbrinz Route",
        "Koordinaten Pin": [
            46.96132164622065,
            8.288108237988338
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2009,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "9ee0bcf9-d634-4932-8fe8-f1a18e7b39dd",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Sbrinz%20Route.pdf"
    },
    {
        "Jahr": 2017,
        "Kanton": [
            "VD"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Souvenirs du futur ",
        "Projektträgerschaft": "La Maison d’Ailleurs",
        "Koordinaten Pin": [
            46.782061780084796,
            6.6419367538353855
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2011,
        "End": 2013,
        "Eval": 2017,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "ee1b9657-fc82-48a1-a70b-3d75df96bfb4",
        "Link": "https://regiosuisse.ch/sites/default/files/2019-02/Mesure%20d%27efficacité%202017%20Rapport%20Souvenirs%20du%20futur.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "VD"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Souvenirs du futur ",
        "Projektträgerschaft": "Fondation de la Maison d’Ailleurs",
        "Koordinaten Pin": [
            46.782061780084796,
            6.6419367538353855
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2011,
        "End": 2013,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "e36028aa-65d4-4e50-bfb0-5ed6d3aee9ed",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Souvenirs%20du%20futur.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "SG"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Nano-Cluster Bodensee ",
        "Projektträgerschaft": "Verein Mikro- und Nanotechnologie Euregio Bodensee",
        "Koordinaten Pin": [
            47.689577592369034,
            9.187457067808623
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2008,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "668e4c85-43da-40a7-aa95-4b078480b924",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20%20Nano-Cluster%20Bodensee.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "SZ"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "WTT Pullcoach  ",
        "Projektträgerschaft": "Technologiezentren Linth und Schwyz",
        "Koordinaten Pin": [
            47.02272588702374,
            8.650436071259504
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2010,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "a5159d42-34d8-40bd-86ab-4c6ae771df52",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20%20WTT%20Pullcoaching%20%28Kanton%20Schwyz%29.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "LU"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Bioburn  ",
        "Projektträgerschaft": "Studer Maschinenbau AG bzw. Bioburn AG",
        "Koordinaten Pin": [
            47.13710134492864,
            7.9423160380559095
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2008,
        "End": 2009,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe",
            "Nachhaltigkeit"
        ],
        "id": "03301280-1cb1-4fd7-976f-31558bdda527",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20%20Bioburn%20-%20Intergrierte%20Biomasse-Nutzung.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "BS/BL"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Phaenovum  ",
        "Projektträgerschaft": "Gemeinde Lörrach (D)",
        "Koordinaten Pin": [
            47.616709661693925,
            7.669005344201383
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2009,
        "End": 2013,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "80587bae-bfe2-48a1-b779-b6fc4f4f5050",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Phaenovum.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "AG"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Seetal − erlebnis, genuss, kultur  ",
        "Projektträgerschaft": "Lenzburg Seetal Tourismus",
        "Koordinaten Pin": [
            47.38954268609259,
            8.180519473868085
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2008,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Lokale Wirtschaft"
        ],
        "id": "904beb51-de05-4152-a075-960a584c2992",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Seetal%20-%20erlenis%2C%20genuss%2C%20kultur.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "GR"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Machbarkeit Ruinaulta  ",
        "Projektträgerschaft": "Verein Die Rheinschlucht/Ruinaulta",
        "Koordinaten Pin": [
            46.79343619409398,
            9.337219949715092
        ],
        "Projektbeschreibung": null,
        "Hinweise": "Projekt noch nicht wirklich verwirklicht",
        "Start": 2011,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "4a2b3940-87e5-4a54-ab03-95788db89976",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Machbarkeit%20Ruinaulta%20-%20Teilprojekt%202.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "AI"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "e-Marketing im Tourismusbereich ",
        "Projektträgerschaft": "Verein Appenzellerland Tourismus Appenzell Innerrhoden (VAT AI)",
        "Koordinaten Pin": [
            47.33172280504541,
            9.407863054782291
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2009,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Digitalisierung"
        ],
        "id": "72d31353-6b0f-43dd-a9f6-cef11e20a9db",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Aufbau%20und%20Umsetzung%20von%20e-Marketing%20im%20Tourismusbereich.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "AR"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Wellbeing & Health Resort Appenzellerland  ",
        "Projektträgerschaft": "Amt für Wirtschaft Appenzell Ausserrhoden",
        "Koordinaten Pin": [
            47.38556285776855,
            9.280512061313331
        ],
        "Projektbeschreibung": null,
        "Hinweise": "Evaluation während des Projektes",
        "Start": 2012,
        "End": 2012,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "899ac4cf-473a-499a-b472-af9a2c47eed2",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20%20Wellbeing%20%26%20Health%20Resort%20Appenzellerland.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "JU"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Route de l’horlogerie  ",
        "Projektträgerschaft": "Fondation Horlogère de Porrentruy",
        "Koordinaten Pin": [
            47.41678133669585,
            7.074808719921124
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2010,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "9918ad1f-e67a-41fd-866b-50d8d42e045b",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Route%20de%20l%27horlogerie.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "GR"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Nationalparkregion – Gesundheitsregion  ",
        "Projektträgerschaft": "Center da sandà Engiadina Bassa; Projektpartner:DMO Engadin Scuol Samnaun, Institut für Systemisches Management und Public Governance (IMP-HSG) der Universität St. Gallen (HSG)",
        "Koordinaten Pin": [
            46.80020382212934,
            10.30389677183332
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2011,
        "End": 2015,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "30bd6ea2-0ce8-4367-b4ae-36ddb26c22f0",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20%20Nationalparkregion%20-%20Gesundheitsregion.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "VS"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Apprendre à Entreprendre  ",
        "Projektträgerschaft": "Canton du Valais",
        "Koordinaten Pin": [
            46.33335941983949,
            7.329308004377722
        ],
        "Projektbeschreibung": null,
        "Hinweise": "Relevanz zu disktutieren",
        "Start": 2008,
        "End": 2015,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "5780f4e9-891a-45c6-8ed4-17055e0e1d17",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Apprendre%20à%20Entreprendre%20%28AàE%29.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "SH"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Angebotserweiterung Oberstufe für internationale Schule  ",
        "Projektträgerschaft": "ISSH AG und ISSH Foundation",
        "Koordinaten Pin": [
            47.7242601029675,
            8.630897871718098
        ],
        "Projektbeschreibung": null,
        "Hinweise": "Relevanz zu disktutieren",
        "Start": 2009,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "74ba7650-6107-4afd-8714-4f0f32ed0752",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Angebotserweiterung%20Oberstufe%20ISSH%20%28Variante%20Push%29.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "ZH"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "natürli Milchspezialitäten  ",
        "Projektträgerschaft": "Pro Zürcher Berggebiet",
        "Koordinaten Pin": [
            47.36893262477352,
            8.87873996467196
        ],
        "Projektbeschreibung": null,
        "Hinweise": null,
        "Start": 2008,
        "End": 2011,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus",
            "Lokale Wirtschaft"
        ],
        "id": "963ec0fa-ed3c-469b-bdb5-b1946239dcac",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20%20natürli%20Milchspezialitäten.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "AI"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Ausbau & Professionalisierung Appenzellerland Regionalmarketing",
        "Projektträgerschaft": "Appenzellerland Regionalmarketing AG",
        "Koordinaten Pin": [
            47.33168644622082,
            9.40790596813538
        ],
        "Projektbeschreibung": null,
        "Hinweise": "Ausführungen zu Effectiveness beruht auf Annahmen",
        "Start": 2009,
        "End": 2010,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Tourismus"
        ],
        "id": "4d11645d-858d-4332-8f9e-d989f844edcc",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Ausbau%20%26%20Professionalisierung%20Appenzellerland%20Regionalmarketing%20AG.pdf"
    },
    {
        "Jahr": 2012,
        "Kanton": [
            "BE"
        ],
        "Fördermassnahme": "NRP",
        "Projekt": "Suissessences  ",
        "Projektträgerschaft": "Genossenschaft Suissessences",
        "Koordinaten Pin": [
            47.22618069422129,
            7.64756145572701
        ],
        "Projektbeschreibung": null,
        "Hinweise": "Evaluation während des Projektes",
        "Start": 2010,
        "End": 2012,
        "Eval": 2012,
        "Projektkosten": null,
        "Anteil NRP in %": null,
        "Tags": [
            "Industrie & Gewerbe"
        ],
        "id": "711c2578-8a03-4758-ad2e-acec977ab12e",
        "Link": "https://regiosuisse.ch/sites/default/files/2022-01/L4%20Wirkungsmessung%202012%20Suissessences.pdf"
    }
];

export default {

    components: {
    },

    data() {
        return {
            random: Math.random().toString(16),
            isLoading: false,
            term: '',
            filters: [],
            activeFilterSelect: null,
            map: null,
            markers: [],
        };
    },

    computed: {
        locale () {
            return this.$clientOptions?.locale || 'de';
        },
        responsive () {
            return this.$clientOptions?.responsive ?? true;
        },
        fixedFilters () {
            return this.$clientOptions?.fixedFilters || [];
        },
        disableTelemetry () {
            return this.$clientOptions?.disableTelemetry || false;
        },
        history () {
            return this.$clientOptions?.history || false;
        },
        historyMode () {
            return this.$clientOptions?.history?.mode || 'query';
        },
        historyBase () {
            return this.$clientOptions?.history?.base || '';
        },
        ...mapState({
            topics: state => state.topics.all,
            states: state => state.states.all,
            programs: state => state.programs.all,
        }),
        ...mapGetters({
            getTopicById: 'topics/getById',
            getStateById: 'states/getById',
            getProgramById: 'programs/getById',
        }),
        projects() {
            return data.map(project => {
                return {
                    ...project,
                    name: project['Projekt'],
                    topics: this.topics
                        .filter(e => !e.context || e.context === 'project')
                        .filter(topic => project['Tags'].includes(topic.name)),
                    states: this.states
                        .filter(e => !e.context || e.context === 'project')
                        .filter(state => project['Kanton'].includes(state.code)),
                    programs: this.programs
                        .filter(e => !e.context || e.context === 'project')
                        .filter(program => project['Fördermassnahme'] === program.name),
                    year: project['Jahr'],
                    lngLat: [project['Koordinaten Pin'][1], project['Koordinaten Pin'][0]],
                    link: project['Link'],
                };
            });
        },
        filteredProjects() {
            return this.projects.filter(project => {
                return this.filters.every(filter => {
                    return project[filter.type+'s']?.find(f => f.id === filter.entity.id);
                });
            });
        },
        filteredTopics() {
            return this.topics.filter(e => this.projects.find(project => project.topics.find(ee => ee.id === e.id)));
        },
        filteredStates() {
            return this.states.filter(e => this.projects.find(project => project.states.find(ee => ee.id === e.id)));
        },
        filteredPrograms() {
            return this.programs.filter(e => this.projects.find(project => project.programs.find(ee => ee.id === e.id)));
        },
    },

    methods: {

        translateField,

        templateHook(name, ...params) {
            if(this?.$clientOptions?.templateHooks?.[name]) {
                return this.$clientOptions.templateHooks[name](this, ...params);
            }

            return null;
        },

        keyUp (event) {

            if(event.keyCode === 27) {
                this.activeFilterSelect = null;
                this.region = null;
            }

        },

        clickOutside (event) {

            this.activeFilterSelect = null;

        },

        clickInside (event) {

            this.activeFilterSelect = null;

        },

        isFilterSelected(filter) {
            return this.filters.find(e => e.type === filter.type && e.entity.id === filter.entity.id);
        },

        clickFilterSelect(name) {
            if(this.activeFilterSelect === name) {
                if(!this.disableTelemetry) {
                    track('Regions Wirkungsmessung Filter', 'Hide', name);
                }
                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Regions Wirkungsmessung Filter', 'Show', name);
            }

            this.activeFilterSelect = name;
        },

        clickToggleFilter(filter) {
            this.activeFilterSelect = null;

            let index = this.filters.findIndex(e => e.type === filter.type && e.entity.id === filter.entity.id);

            if(index !== -1) {
                this.filters.splice(index, 1);
                //this.reload();

                if(this.history) {
                    window.history.replaceState(null, null, this.getHistoryQueryString());
                }

                if(!this.disableTelemetry) {
                    track('Regions Wirkungsmessung Filter', 'Disable', {
                        type: filter.type,
                        id: filter.entity.id,
                    });
                }

                return;
            }

            this.filters.push(filter);
            //this.reload();

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Regions Wirkungsmessung Filter', 'Enable', {
                    type: filter.type,
                    id: filter.entity.id,
                });
            }
        },

        clickSearchResultItem(city) {
            this.viewMode = 'city';
            this.selectedCity = city;
            this.term = null;
        },

        popState(event) {

            this.region = null;

            if(this.getUrlParams()['region-id']) {
                this.$store.dispatch('regions/load', this.getUrlParams()['region-id']).then((region) => {
                    this.region = region;
                });
            }

        },

        getUrlParams () {
            let queryString = window.location.search;

            if(this.historyMode === 'hash') {
                queryString = window.location.hash.substring(1);
            }

            let urlParams = new URLSearchParams(queryString);
            let result = {};

            for(const [key, value] of urlParams) {

                let k = key.split('[')[0];

                if(!['cities'].includes(k)) {
                    result[k] = value;
                    continue;
                }

                if(!result[k]) {
                    result[k] = [];
                }

                result[k].push(value);

            }

            return result;
        },

        getHistoryQueryString(region) {

            let result = [];

            if(region) {
                result.push('region-id='+region.id+'&title='+encodeURIComponent(translateField(region, 'name', this.locale)));
            }

            if(this.term) {
                result.push('term='+encodeURIComponent(this.term));
            }

            result = result.join('&');

            if(!result) {
                return this.historyBase;
            }

            return this.historyBase + (this.historyMode === 'hash' ? '#' : '') + '?' + result;

        },

        async reload() {
            this.isLoading = true;

            await Promise.all([
                this.$store.dispatch('states/loadAll'),
                this.$store.dispatch('programs/loadAll'),
                this.$store.dispatch('topics/loadAll'),
            ]);

            this.isLoading = false;
        },

    },

    created() {

        if(this.history && this.getUrlParams()['term']) {
            this.term = this.getUrlParams()['term'];
        }

        this.reload();

    },

    mounted() {
        window.addEventListener('click', this.clickOutside);
        window.addEventListener('keyup', this.keyUp);

        let minZoom = 7;

        if(this.$refs.mapContainer && this.$refs.mapContainer.offsetWidth < 720) {
            minZoom = 5.8;
        }

        mapboxgl.accessToken = this.$env.MAPBOX_API_TOKEN;
        this.map = new mapboxgl.Map({
            container: this.$refs.mapContainer.id,
            style: swissmaptiles,
            center: [
                8.24245129900188,
                46.82058782182699
            ],
            zoom: minZoom,
            minZoom: minZoom,
            maxZoom: 14,
        });

        if(this.history) {
            window.addEventListener('popstate', this.popState);
        }

        if(!this.disableTelemetry) {
            trackDevice();
            trackPageView();
        }
    },

    watch: {
        filteredProjects(projects) {
            if(!this.map) {
                return;
            }
            const renderMarkers = () => {
                while(this.markers.length) {
                    this.markers.pop().remove();
                }
                let mouseLeaveTimeout = null;
                for(let project of projects) {
                    let el = document.createElement('div');
                    el.className = 'embed-regions-content-map-marker';
                    el.innerHTML = `
                        <div class="embed-regions-content-map-marker-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        </div>
                        <div class="embed-regions-content-map-marker-overlay">
                            <p><strong>${project.name}</strong><br>${this.$t('Jahr')} ${project.year}</p>
                            ${project.link ? `<p><a href="${project.link}" target="_blank">${this.$t('Zum Kurzbericht')}</a></p>` : ``}
                        </div>
                    `;
                    if(parseInt(project.year) >= 2020) {
                        //el.querySelector('svg').style.fill = '#2b9db1';
                    }
                    let marker = new mapboxgl.Marker(el).setLngLat([project.lngLat[0], project.lngLat[1]]).addTo(this.map);
                    this.markers.push(marker);
                    el.querySelector('a')?.addEventListener('click', (event) => {
                        event.stopPropagation();
                    });
                    el.addEventListener('mouseenter', (event) => {

                        clearTimeout(mouseLeaveTimeout);

                        for(let m of this.markers) {
                            m.getElement().classList.remove('is-active');
                        }

                        el.classList.add('is-active');

                    });
                    el.addEventListener('mouseleave', (event) => {
                        mouseLeaveTimeout = setTimeout(() => {
                            el.classList.remove('is-active');
                        }, 2000);
                    });
                    el.addEventListener('click', () => {
                        this.map.flyTo({
                            center: project.lngLat,
                            zoom: 9,
                        })
                    });
                }
            }
            if(this.map.loaded()) {
                renderMarkers();
                return;
            }
            this.map.on('load', () => {
                renderMarkers();
            });
        },
    },

    beforeUnmount() {
        window.removeEventListener('click', this.clickOutside);
        window.removeEventListener('keyup', this.keyUp);
        window.removeEventListener('popstate', this.popState);
    }

};

</script>