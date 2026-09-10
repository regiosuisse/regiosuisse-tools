<template>

    <div class="interactive-graphic-editor-component">

        <div v-html="getSvgStyle()"></div>

        <div class="interactive-graphic-editor-component-svg" ref="svg" @click="clickSvg">
            <div v-html="svg"></div>
            <div class="interactive-graphic-editor-component-svg-markers" v-if="localConfig?.markers?.length">
                <div v-for="marker in localConfig.markers"
                     class="interactive-graphic-editor-component-svg-markers-marker"
                     :style="{left: marker.x+'%', top: marker.y+'%'}">
                    <div v-if="marker.symbol === 'number'">{{ marker.number ?? '#' }}</div>
                    <div v-else-if="marker.symbol === 'video'" class="material-icons">movie</div>
                    <div v-else-if="marker.symbol === 'audio'" class="material-icons">audiotrack</div>
                </div>
            </div>
        </div>

        <div class="interactive-graphic-editor-component-content"
             v-if="type === 'default'">

            <template v-if="selectedElementIdentifier">

                <div class="form-group">
                    <label for="type">Typ</label>
                    <div class="select-wrapper">
                        <select id="type" class="form-control" @change="onChangeType($event.target.value)">
                            <option value="text" :selected="['string', 'undefined'].includes(typeof config[selectedElementIdentifier])">Text</option>
                            <option value="project" :selected="['object'].includes(typeof config[selectedElementIdentifier]) && config[selectedElementIdentifier].type === 'project'">Projekt</option>
                        </select>
                    </div>
                </div>

                <div v-if="['string', 'undefined'].includes(typeof config[selectedElementIdentifier])">
                    <ckeditor :editor="editor" :config="editorConfig"
                              v-model="localConfig[selectedElementIdentifier]" @blur="onChangeConfig()"></ckeditor>
                </div>

                <div v-if="['object'].includes(typeof config[selectedElementIdentifier]) && config[selectedElementIdentifier].type === 'project'">
                    <div class="form-group">
                        <label>Projekt-ID</label>
                        <input type="text" class="form-control" v-model="localConfig[selectedElementIdentifier].id" @change="onChangeConfig()">
                    </div>
                </div>

            </template>

        </div>

        <div class="interactive-graphic-editor-component-content"
             v-if="type === 'hotspots'">

            <div class="interactive-graphic-editor-component-content-section"
                 v-for="(marker, idx) in localConfig?.markers ?? []">


                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="symbol">Symbol</label>
                            <div class="select-wrapper">
                                <select id="symbol"
                                        class="form-control"
                                        v-model="marker.symbol"
                                        @change="onChangeConfig()">
                                    <option value="number">Nummer</option>
                                    <option value="video">Video</option>
                                    <option value="audio">Audio</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" v-if="marker.symbol === 'number'">
                            <label for="symbol">Nummer</label>
                            <input type="number" class="form-control" v-model="marker.number" @change="onChangeConfig()">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Inhalt</label>
                    <ckeditor :editor="editor" :config="editorConfig"
                              v-model="marker.content"
                              @blur="onChangeConfig()"></ckeditor>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>X-Koordinate</label>
                            <input type="number" class="form-control" v-model.number="marker.x" @change="onChangeConfig()">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Y-Koordinate</label>
                            <input type="number" class="form-control" v-model.number="marker.y" @change="onChangeConfig()">
                        </div>
                    </div>
                </div>

                <a class="button error" @click="clickRemoveMarker(idx)">Entfernen</a>

            </div>

            <a class="button" @click="clickAddMarker()">Hinzufügen</a>

        </div>

    </div>

</template>

<script>
    import draggable from 'vuedraggable';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {
        data() {
            return {
                localConfig: {},
                selectedElement: null,
                selectedElementIdentifier: null,
                editor: ClassicEditor,
                editorConfig: {
                    basicEntities: false,
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'link',
                            '|',
                            'numberedList',
                            'bulletedList',
                            '|',
                            'undo',
                            'redo',
                        ]
                    },
                    mediaEmbed: {
                        previewsInData: true,
                    },
                },
            };
        },
        components: {
            draggable,
        },
        props: {
            svg: {
                type: String,
                required: false,
            },
            selector: {
                type: String,
                required: false,
            },
            config: {
                type: Object,
                required: false,
            },
            type: {
                type: String,
                required: false,
            },
        },
        computed: {
        },
        methods: {
            clickSvg (event) {

                if(this.type === 'hotspots') {
                    return;
                }

                let target = event.target;

                if(!target.matches(this.selector) && !target.matches(this.selector+' *')) {
                    return;
                }

                if(!target.matches(this.selector)) {
                    target = event.target.closest(this.selector);
                }

                this.clickSvgElement(target);

            },
            clickSvgElement (target) {
                this.$refs.svg.querySelectorAll('svg '+this.selector+', svg '+this.selector+' *').forEach((e) => {
                    e.classList.remove('active');
                });

                let svgClone = this.$refs.svg.querySelector('svg').cloneNode(false);

                svgClone.append(target.cloneNode(true));

                this.selectedElement = target;
                this.selectedElementIdentifier = svgClone.innerHTML;

                this.selectedElement.classList.add('active');
            },
            getSvgStyle () {
                let style = '';

                if(!this.selector) {
                    return style;
                }

                style += '.interactive-graphic-editor-component svg *' + ' ' +
                    '{' +
                        'pointer-events: none;' +
                    '}';

                style += '.interactive-graphic-editor-component svg ' + this.selector + ' ' +
                    '{' +
                        'opacity: .25;' +
                        'cursor: pointer;' +
                        'pointer-events: auto;' +
                        'transition: all .25s;' +
                    '}';

                style += '.interactive-graphic-editor-component svg ' + this.selector + ' * ' +
                    '{' +
                        'pointer-events: auto;' +
                    '}';

                style += '.interactive-graphic-editor-component svg ' + this.selector + ':hover ' +
                    '{' +
                        'opacity: 1;' +
                    '}';

                style += '.interactive-graphic-editor-component svg ' + this.selector + '.active ' +
                    '{' +
                        'opacity: 1;' +
                    '}';

                return '<style>'+style+'</style>';
            },
            onChangeConfig () {
                this.$emit('onChangeConfig', this.localConfig);
            },
            onChangeType (type) {
                this.localConfig[this.selectedElementIdentifier] = '';

                if(type === 'project') {
                    this.localConfig[this.selectedElementIdentifier] = {
                        type: type,
                        id: '',
                    };
                }

                this.onChangeConfig();
            },

            clickAddMarker() {
                this.localConfig.markers = this.localConfig?.markers ?? [];
                this.localConfig.markers = [
                    ...this.localConfig.markers,
                    {
                        symbol: 'number',
                        x: 50,
                        y: 50,
                        content: '',
                    }
                ];

                this.onChangeConfig();
            },

            clickRemoveMarker(idx) {
                this.localConfig.markers.splice(idx, 1);
                this.onChangeConfig();
            },

        },
        watch: {
            config: {
                immediate: true,
                handler(newVal) {
                    this.localConfig = {...newVal};
                },
            },
        },
    }
</script>