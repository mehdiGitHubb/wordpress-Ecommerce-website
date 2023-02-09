var ThemifyCodeMiror;
((Themify, doc, win) => {
    const cdnUrl = 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/',
        darkTheme='ayu-dark',
            def = {
                indentUnit:4,
                indentWithTabs:true,
                inputStyle:'contenteditable',
                lineNumbers:true,
                lineWrapping:true,
                styleActiveLine:false,
                autoCloseBrackets:true,
                matchBrackets:true,
                scrollbarStyle:null,
                completeSingle:false,
                allowFullScreen:true,
                theme:'default',
                extraKeys:{
                    'Ctrl-Space':'autocomplete',
                    'Ctrl-/':'toggleComment',
                    'Cmd-/':'toggleComment',
                    'Alt-F':'findPersistent',
                    'Ctrl-F':'findPersistent',
                    'Cmd-F':'findPersistent'
                },
                direction:'ltr',
                gutters:["CodeMirror-lint-markers"]
            };
    ThemifyCodeMiror = class {
        constructor(el, mode, options) {
            const module = this.getSupportedModes(mode);
            if (module) {
                this.mode = mode;
                this.el = el;
                this.options = Object.assign({},def, (typeof module !== 'string' ? module.options :{}), options);
                this.options.mode = typeof module !== 'string' ? module.mode :module;
				if(this.options.theme==='default' && this.options.isDarkMode){
					this.options.theme=darkTheme;
				}
                this.wrap = doc.createElement('div');
                this.wrap.className = 'tf_cdm tf_w tf_rel';
                this.wrap.tabIndex='-1';
                this.el.after(this.wrap);
                this.wrap.appendChild(this.el);
            } else {
                throw '';
            }
        }
        destroy() {
            if (this.editor) {
                this.editor.off('keyup', this.showHint);
                this.editor.toTextArea();
                this.wrap.after(this.el);
                this.wrap.remove();
                this.editor = this.wrap=this.el = null;
            }
        }
        save() {
            if (this.editor) {
                this.editor.save();
            }
        }
        getModeAsssets() {
            const module = this.getSupportedModes(),
                    isObject = typeof module !== 'string',
                    mode = isObject && module.mode && module.mode.name ? module.mode.name :this.mode;
            return isObject && module.assets ? module.assets :['mode/' + mode + '/' + mode + '.min.js'];
        }
        loadHint() {
            const prms = [],
                    assets = this.getModeAsssets(),
                    hasPython = assets.includes('mode/python/python.min.js');
            if (assets.includes('mode/css/css.min.js')) {
                prms.push(Themify.loadJs(cdnUrl + 'addon/hint/css-hint.min.js', false, false));
            }
            if (assets.includes('mode/javascript/javascript.min.js')) {
                prms.push(Themify.loadJs(cdnUrl + 'addon/hint/javascript-hint.min.js', false, false));
            }
            if (assets.includes('mode/xml/xml.min.js')) {
                prms.push(Themify.loadJs(cdnUrl + 'addon/hint/xml-hint.min.js', false, false));
            }
            if (assets.includes('mode/htmlmixed/htmlmixed.min.js')) {
                prms.push(Themify.loadJs(cdnUrl + 'addon/hint/html-hint.min.js', false, false));
            }
            if (assets.includes('mode/sql/sql.min.js')) {
                prms.push(Themify.loadJs(cdnUrl + 'addon/hint/sql-hint.min.js', false, false));
            }
            if (hasPython || assets.includes('mode/clike/clike.min.js')) {
                prms.push(Themify.loadJs(cdnUrl + 'addon/hint/anyword-hint.min.js', false, false));
                if (hasPython) {
                    prms.push(Themify.loadJs('https://cdnjs.cloudflare.com/ajax/libs/codemirror/4.8.0/addon/hint/python-hint.min.js', false, false));
                }
            }
            if (prms.length > 0) {
                prms.push(Themify.loadJs(cdnUrl + 'addon/hint/show-hint.min.js', false, false));
                prms.push(Themify.loadCss(cdnUrl + 'addon/hint/show-hint.min.css', 'tf_codemirror_hint', false, this.getCssRoot()));

                this.options.hintOptions = {
                    container:this.wrap
                };
            }
            return Promise.all(prms);
        }
        getSupportedModes(m) {
            const modes = {
                apl:'text/apl',
                'avro-idl':{
                    mode:{
                        name:'idl',
                        version:1,
                        singleLineStringErrors:false
                    }
                },
                markup:{
                    mode:'htmlmixed',
                    assets:[
                        'mode/xml/xml.min.js',
                        'mode/javascript/javascript.min.js',
                        'mode/css/css.min.js',
                        'mode/htmlmixed/htmlmixed.min.js'
                    ],
                    options:{
                        continueComments:true,
                        autoCloseTags:true,
                        matchTags:{
                            bothTags:true
                        }
                    }
                },
                css:{
                    mode:'text/css',
                    options:{
                        continueComments:true
                    },
                    assets:[
                        'mode/css/css.min.js'
                    ]
                },
                javascript:{
                    mode:'text/javascript',
                    options:{
                        continueComments:true
                    },
                    assets:[
                        'mode/javascript/javascript.min.js'
                    ]
                },
                c:{
                    mode:'text/x-csrc',
                    assets:[
                        'mode/clike/clike.min.js'
                    ],
                    options:{
                        continueComments:true
                    }
                },
                fsharp:{
                    mode:'text/x-fsharp',
                    assets:[
                        'mode/mllike/mllike.min.js'
                    ]
                },
                bash:{
                    mode:'text/x-sh',
                    options:{
                        styleActiveLine:true
                    }
                },
                armasm:{
                    mode:{name:'gas', architecture:'ARMv6'},
                    options:{
                        styleActiveLine:true
                    }
                },
                php:{
                    mode:'application/x-httpd-php',
                    assets:[
                        'mode/xml/xml.min.js',
                        'mode/htmlmixed/htmlmixed.min.js',
                        'mode/javascript/javascript.min.js',
                        'mode/css/css.min.js',
                        'mode/clike/clike.min.js',
                        'mode/php/php.min.js'
                    ],
                    options:{
                        continueComments:true,
                        autoCloseTags:true,
                        matchTags:{
                            bothTags:true
                        }
                    }
                },
                phpdoc:{
                    mode:'text/x-php',
                    assets:[
                        'mode/clike/clike.min.js',
                        'mode/php/php.min.js'
                    ],
                    options:{
                        continueComments:true
                    }
                },

                brainfuck:'text/x-brainfuck',
                clojure:'text/x-clojure',
                cmake:'text/x-cmake',
                cobol:{
                    mode:'text/x-cobol',
                    options:{
                        styleActiveLine:true,
                        showCursorWhenSelecting:true
                    }
                },
                coffeescript:'text/coffeescript',
                crystal:'text/x-crystal',
                csv:{
                    mode:'text/x-q',
                    assets:[
                        'mode/q/q.min.js'
                    ]
                },
                cypher:'application/x-cypher-query',
                d:'text/x-d',
                dart:{
                    mode:'application/dart',
                    assets:[
                        'mode/clike/clike.min.js',
                        'mode/dart/dart.min.js'
                    ],
                    options:{
                        continueComments:true
                    }
                },
                diff:'text/x-diff',
                django:{
                    mode:'text/x-django',
                    assets:[
                        'mode/xml/xml.min.js',
                        'mode/htmlmixed/htmlmixed.min.js',
                        'mode/django/django.min.js'
                    ],
                    options:{
                        continueComments:true,
                        autoCloseTags:true,
                        matchTags:{
                            bothTags:true
                        }
                    }
                },
                docker:{
                    mode:'text/x-dockerfile',
                    assets:[
                        'addon/mode/simple.min.js',
                        'mode/dockerfile/dockerfile.min.js'
                    ]
                },
                ebnf:{
                    mode:{name:'ebnf'},
                    options:{
                        bracesMode:'javascript'
                    },
                    assets:[
                        'mode/javascript/javascript.min.js',
                        'mode/ebnf/ebnf.min.js'
                    ]
                },
                editorconfig:{
                    mode:'text/x-properties',
                    assets:[
                        'mode/properties/properties.min.js'
                    ]
                },
                eiffel:'text/x-eiffel',
                elm:'text/x-elm',
                etlua:'text/x-lua',
                erlang:'text/x-erlang',

                factor:{
                    mode:'text/x-factor',
                    assets:[
                        'addon/mode/simple.mn.js',
                        'mode/factor/factor.min.js'
                    ]
                },
                fortran:'text/x-fortran',
                gherkin:'text/x-feature',
                go:'text/x-go',
                'go-module':'text/x-go',
                groovy:'text/x-groovy',
                haml:{
                    mode:'text/x-haml',
                    assets:[
                        'mode/xml/xml.min.js',
                        'mode/htmlmixed/htmlmixed.min.js',
                        'mode/javascript/javascript.min.js',
                        'mode/ruby/ruby.min.js',
                        'mode/haml/haml.min.js'
                    ],
                    options:{
                        continueComments:true,
                        autoCloseTags:true,
                        matchTags:{
                            bothTags:true
                        }
                    }
                },
                handlebars:{
                    mode:{
                        name:'handlebars',
                        base:'text/html'
                    },
                    assets:[
                        'addon/mode/simple.min.js',
                        'addon/mode/multiplex.min.js',
                        'mode/xml/xml.min.js',
                        'mode/handlebars/handlebars.min.js'
                    ]
                },
                haskell:'text/x-literate-haskell',
                haxe:'text/x-haxe',
                http:'message/http',
                julia:'text/x-julia',
                latex:{
                    mode:'text/x-stex',
                    assets:[
                        'mode/stex/stex.min.js'
                    ]
                },
                lisp:{
                    mode:'text/x-common-lisp',
                    assets:[
                        'mode/commonlisp/commonlisp.min.js'
                    ]
                },
                livescript:'text/x-livescript',
                lua:'text/x-lua',
                markdown:{
                    mode:'gfm',
                    assets:[
                        'addon/mode/overlay.min.js',
                        'mode/xml/xml.min.js',
                        'mode/markdown/markdown.min.js',
                        'mode/gfm/gfm.min.js',
                        'mode/javascript/javascript.min.js',
                        'mode/css/css.min.js',
                        'mode/htmlmixed/htmlmixed.min.js',
                        'mode/clike/clike.min.js'
                    ],
                    options:{
                        continueComments:true,
                        autoCloseTags:true,
                        matchTags:{
                            bothTags:true
                        }
                    }
                },
                matlab:{
                    mode:'text/x-octave',
                    assets:[
                        'mode/octave/octave.min.js'
                    ]
                },
                nasm:{name:'gas', architecture:'x86'},
                nginx:'text/nginx',
                nsis:{
                    mode:'text/x-nsis',
                    assets:[
                        'addon/mode/simple.min.js',
                        'mode/nsis/nsis.min.js'
                    ]
                },
                oz:'text/x-oz',
                pascal:'text/x-pascal',
                perl:'text/x-perl',
                powershell:{
                    mode:'application/x-powershell',
                    options:{
                        tabMode:'shift'
                    }
                },
                protobuf:'text/x-protobuf',
                puppet:'text/x-puppet',
                python:{
                    mode:{
                        name:'python',
                        version:3,
                        singleLineStringErrors:false
                    }
                },

                r:'text/x-rsrc',
                rest:'text/x-rst',
                ruby:'text/x-ruby',
                rust:{
                    mode:'text/x-rustsrc',
                    assets:[
                        'addon/mode/simple.min.js',
                        'mode/rust/rust.min.js'
                    ]
                },
                sas:{
                    mode:'text/x-sas',
                    assets:[
                        'mode/xml/xml.min.js',
                        'mode/sas/sas.min.js'
                    ],
                    options:{
                        autoCloseTags:true,
                        matchTags:{
                            bothTags:true
                        }
                    }
                },
                sass:'text/x-sass',
                scheme:'text/x-scheme',
                smalltalk:'text/x-stsrc',
                smarty:{
                    mode:{name:'smarty', version:3, baseMode:'text/html'},
                    assets:[
                        'mode/xml/xml.min.js',
                        'mode/smarty/smarty.min.js'
                    ],
                    options:{
                        autoCloseTags:true,
                        matchTags:{
                            bothTags:true
                        }
                    }
                },
                sparql:'application/sparql-query',
                sql:{
                    mode:'text/x-sql',
                    assets:[
                        'mode/sql/sql.min.js'
                    ],
                    options:{
                        smartIndent:true
                    }
                },
                stylus:'text/x-styl',
                swift:'text/x-swift',
                tcl:'text/x-tcl',
                textile:'text/x-textile',
                toml:'text/x-toml',
                turtle:'text/turtle',
                twig:{name:'twig', htmlMode:true},
                vbnet:{
                    mode:'text/x-vb',
                    assets:[
                        'mode/vb/vb.min.js'
                    ]
                },
                velocity:'text/velocity',
                verilog:{
                    name:'verilog',
                    noIndentKeywords:['package']
                },
                vhdl:'text/x-vhdl',
                'visual-basic':'text/vbscript',
                wasm:'text/webassembly',
                'web-idl':'text/x-webidl',
                wolfram:'text/x-mathematica',
                'xml-doc':{
                    mode:'application/xml',
                    assets:[
                        'mode/xml/xml.min.js'
                    ],
                    options:{
                        htmlMode:false,
                        autoCloseTags:true,
                        matchTags:{
                            bothTags:true
                        }
                    }
                },
                xquery:'application/xquery',
                yaml:'text/x-yaml'
            },
            subModes = {
                htmlmixed:modes.markup,
                aspnet:{
                    mode:'application/x-aspx',
                    assets:[...modes.markup.assets, ...['addon/mode/multiplex.min.js', 'addon/mode/htmlembedded.min.js']],
                    options:modes.markup.options
                },
                cpp:Object.assign({}, modes.c, {mode:'text/x-c++src'}),
                csharp:Object.assign({}, modes.c, {mode:'text/x-csharp'}),
                cilkc:modes.c,
                ocaml:Object.assign({}, modes.fsharp, {mode:'text/x-ocaml'}),
                ini:Object.assign({}, modes.editorconfig, {mode:'text/x-ini'}),
                java:Object.assign({}, modes.c, {mode:'text/x-java'}),
                jsdoc:Object.assign({}, modes.javascript, {mode:'text/x-java'}),
                json:Object.assign({}, modes.javascript, {mode:'application/json'}),
                jsstacktrace:modes.javascript,
                kotlin:Object.assign({}, modes.c, {mode:'text/x-kotlin'}),
                less:Object.assign({}, modes.css, {mode:'text/x-less'}),
                objectivec:Object.assign({}, modes.c, {mode:'text/x-objectivec'}),
                plsql:Object.assign({}, modes.sql, {mode:'text/x-plsql'}),
                properties:modes.editorconfig,
                scss:Object.assign({}, modes.css, {mode:'text/x-scss'}),
                scala:Object.assign({}, modes.c, {mode:'text/x-scala'}),
                vim:{
                    mode:modes.c.mode,
                    assets:[...modes.c.assets, ...['keymap/vim.min.js']],
                    options:{
                        keyMap:'vim',
                        showCursorWhenSelecting:true
                    }
                },
                'shell-session':modes.bash,
                soy:{
                    mode:'text/x-soy',
                    assets:[...modes.markup.assets, ...['mode/soy/soy.min.js']],
                    options:{
                        autoCloseTags:true,
                        matchTags:{
                            bothTags:true
                        }
                    }
                },
                squirrel:Object.assign({}, modes.c, {mode:'text/x-squirrel'}),
                typescript:Object.assign({}, modes.javascript, {mode:'application/typescript'})
            },
            allModes = Object.assign({}, modes, subModes, {
                cilkcpp:subModes.cpp,
                ignore:subModes.ini,
                json5:subModes.json,
                jsonp:subModes.json,
                javadoclike:subModes.java,
                javastacktrace:subModes.java
            });


            if (!m) {
                m = this.mode;
            }
            return allModes[m];
        }
        async loadLint() {
            const getLint = async m => {
                const lints = {
                    css:{
                        url:'https://cdnjs.cloudflare.com/ajax/libs/csslint/1.0.5/csslint.min.js',
                        check:!!win.CSSLint,
                        options:{
                            errors:true, // Parsing errors.
                            'box-model':true,
                            'display-property-grouping':true,
                            'duplicate-properties':true,
                            'known-properties':true,
                            'outline-none':true
                        }
                    },
                    javascript:{
                        url:'https://cdnjs.cloudflare.com/ajax/libs/jshint/2.13.5/jshint.min.js',
                        options:{
                            boss:true,
                            curly:true,
                            eqeqeq:true,
                            eqnull:true,
                            esversion:11,
                            expr:true,
                            immed:true,
                            noarg:true,
                            nonbsp:true,
                            onevar:true,
                            quotmark:'single',
                            trailing:true,
                            undef:true,
                            unused:true,
                            browser:true,
                            globals:{
                                _:false,
                                Backbone:false,
                                jQuery:false,
                                JSON:false,
                                wp:false,
                                Prism:false,
                                Themify:false,
                                window:false,
                                document:false,
                                Promise:false,
                                $:false
                            }
                        }
                    },
                    coffeescript:{
                        dependce:'https://cdn.jsdelivr.net/npm/coffeescript@2.7.0/lib/coffeescript-browser-compiler-legacy/coffeescript.js',
                        url:'https://cdn.jsdelivr.net/npm/coffeelint@2.1.0/lib/coffeelint.min.js',
                        check:!!win.coffeelint
                    },
                    json:{
                        url:'https://cdnjs.cloudflare.com/ajax/libs/jsonlint/1.6.0/jsonlint.min.js',
                        check:!!win.jsonlint
                    },
                    yaml:{
                        url:'https://cdnjs.cloudflare.com/ajax/libs/js-yaml/4.1.0/js-yaml.min.js',
                        check:!!win.jsyaml
                    },
                    html:{
                        url:'https://cdn.jsdelivr.net/npm/htmlhint@1.1.4/dist/htmlhint.js',
                        check:!!win.HTMLHint,
                        options:{
                            'tagname-lowercase':true,
                            'attr-lowercase':true,
                            'attr-value-double-quotes':false,
                            'doctype-first':false,
                            'tag-pair':true,
                            'spec-char-escape':true,
                            'id-unique':true,
                            'src-not-empty':true,
                            'attr-no-duplication':true,
                            'alt-require':true,
                            'space-tab-mixed-disabled':'tab',
                            'attr-unsafe-chars':true
                        }
                    }
                },
                lint = lints[m],
                url=m==='html'?(Themify.url+'js/admin/modules/codemirror/lints/htmlmixed'):(cdnUrl + 'addon/lint/' + m + '-lint.min.js');
                if (lint.dependce) {
                    await Themify.loadJs(lint.dependce, null, false);
                }
                await Themify.loadJs(lint.url, lint.check, false);
                if (lint.options) {
                    if (!this.options.lint) {
                        this.options.lint = {};
                    }
                    this.options.lint[m] = {options:lint.options};
                }
                return Themify.loadJs(url, false, false);
            },
                    assets = this.getModeAsssets(),
                    prms = [];
            if (assets.includes('mode/css/css.min.js')) {
                prms.push(getLint('css'));
            }
            if (assets.includes('mode/javascript/javascript.min.js')) {
                if (this.options.mode === 'application/json') {
                    prms.push(getLint('json'));
                } else {
                    prms.push(getLint('javascript'));
                }
            }
            if (assets.includes('mode/yaml/yaml.min.js')) {
                prms.push(getLint('yaml'));
            }
            if (assets.includes('mode/coffeescript/coffeescript.min.js')) {
                prms.push(getLint('coffeescript'));
            }
            if (assets.includes('mode/htmlmixed/htmlmixed.min.js')) {
                prms.push(getLint('html'));
            }
            if (prms.length > 0) {
                prms.push(Themify.loadJs(cdnUrl + 'addon/lint/lint.min.js', false, false));
                prms.push(Themify.loadCss(cdnUrl + 'addon/lint/lint.min.css', 'tf_codemirror_lint', false, this.getCssRoot()));
            }
            return Promise.all(prms.flat());
        }
        loadMode() {
            const assets = this.getModeAsssets(),
                    prms = [];
            for (let i = 0, len = assets.length; i < len; ++i) {
                prms.push(Themify.loadJs(cdnUrl + assets[i], false, false));
            }
            return Promise.all(prms);
        }
        getCssRoot() {
            return this.el.getRootNode().querySelector('style,link');//need for shadow doms
        }
        requestFullscreen() {
            if (!doc.fullscreenElement) {
                const wrap = this.wrap;
                if (wrap.requestFullscreen) {
                    return wrap.requestFullscreen();
                }
                if (wrap.webkitEnterFullscreen) {
                    return wrap.webkitEnterFullscreen();
                }
                if (wrap.webkitrequestFullscreen) {
                    return wrap.webkitRequestFullscreen();
                }
                if (wrap.mozRequestFullscreen) {
                    return wrap.mozRequestFullScreen();
                }
                return Promise.reject();
            }
        }
        exitFullscreen() {
            if (doc.exitFullscreen) {
                return doc.exitFullscreen();
            }
            if (doc.webkitExitFullscreen) {
                return doc.webkitExitFullscreen();
            }
            if (doc.webkitExitFullscreen) {
                return doc.webkitExitFullscreen();
            }
            if (doc.mozCancelFullScreen) {
                return doc.mozCancelFullScreen();
            }
            if (doc.cancelFullScreen) {
                return doc.cancelFullScreen();
            }
            if (doc.msExitFullscreen) {
                return doc.msExitFullscreen();
            }
            return Promise.reject();
        }
        showHint(editor, e) {
            let shouldAutocomplete,
                    isAlphaKey = /^[a-zA-Z]$/.test(e.key) || e.key==='Backspace',
                    lineBeforeCursor,
                    innerMode,
                    token;
            if (editor.state.completionActive && isAlphaKey) {
                return;
            }

            // Prevent autocompletion in string literals or comments.
            token = editor.getTokenAt(editor.getCursor());
            if ('string' === token.type || 'comment' === token.type) {
                return;
            }

            innerMode = win.CodeMirror.innerMode(editor.getMode(), token.state).mode.name;
            lineBeforeCursor = editor.doc.getLine(editor.doc.getCursor().line).substr(0, editor.doc.getCursor().ch);
            
            if ('html' === innerMode || 'xml' === innerMode) {
                shouldAutocomplete =
                        '<' === e.key ||
                        '/' === e.key && 'tag' === token.type ||
                        isAlphaKey && 'tag' === token.type ||
                        isAlphaKey && 'attribute' === token.type ||
                        '=' === token.string && token.state.htmlState && token.state.htmlState.tagName;
            } else if ('css' === innerMode) {
                shouldAutocomplete =
                        isAlphaKey ||
                        ':' === e.key ||
                        ' ' === e.key && /:\s+$/.test(lineBeforeCursor);
            } else if ('javascript' === innerMode) {
                shouldAutocomplete = isAlphaKey || '.' === e.key;
            } else if (isAlphaKey && ('clike' === innerMode || 'python' === innerMode)) {
                shouldAutocomplete = 'keyword' === token.type || 'variable' === token.type;
            }
            if (shouldAutocomplete) {
                editor.showHint({completeSingle:false});
            }
        }
        themeSwitcher(){
            const ns = 'http://www.w3.org/2000/svg',
                btn=doc.createElement('button'),
            svg = doc.createElementNS(ns, 'svg'),
            g = doc.createElementNS(ns,'g'),
            moonMask =doc.createElementNS(ns,'mask'),
            moonRect =doc.createElementNS(ns,'rect'),
            moonCircle =doc.createElementNS(ns,'circle'),
            sunCircle =doc.createElementNS(ns,'circle'),
            fr=doc.createDocumentFragment(),
            lines=[
                [12,1,12,3],
                [12,21,12,23],
                [4.22,4.22,5.64,5.64],
                [18.36,18.36,19.78,19.78],
                [1,12,3,12],
                [21,12,23,12],
                [4.22,19.78,5.64,18.36],
                [18.36,5.64,19.78,4.22]
            ];
            
            
            btn.className='tf_cdm_tgl_theme';
            btn.type='button';
            btn.title='Toggles light & dark';
            
            
            svg.setAttributeNS(null,'viewBox','0 0 24 24');
            svg.setAttribute('width',14);
            svg.setAttribute('height',14);
            
            moonMask.setAttribute('id','tf-moon-mask');
            moonMask.setAttribute('class','tf_cdm_moon');
            
            moonRect.setAttribute('width','100%');
            moonRect.setAttribute('height','100%');
            moonCircle.setAttribute('r',6);
            moonRect.setAttribute('fill', 'white');
            
            moonCircle.setAttribute('cx',24);
            moonCircle.setAttribute('cy',10);
            moonCircle.setAttribute('fill', 'black');
            
            
            sunCircle.setAttribute('class','tf_cdm_sun');
            sunCircle.setAttribute('cx','12');
            sunCircle.setAttribute('cy','12');
            sunCircle.setAttribute('r','6');
            sunCircle.setAttribute('mask', 'url(#tf-moon-mask)');
            sunCircle.setAttribute('stroke', 'currentColor');
            
            g.setAttribute('stroke', 'currentColor');
            
            for(let i=0,len=lines.length;i<len;++i){
                let line=doc.createElementNS(ns,'line');
                line.setAttribute('x1',lines[i][0]);
                line.setAttribute('y1',lines[i][1]);
                line.setAttribute('x2',lines[i][2]);
                line.setAttribute('y2',lines[i][3]);
                fr.appendChild(line);
            }
            g.appendChild(fr);
            moonMask.append(moonRect,moonCircle);
            svg.append(moonMask,sunCircle,g);
            btn.appendChild(svg);
            
            btn.tfOn(Themify.click,e=>{
                e.stopImmediatePropagation();
               this.setDarkMode();
            },{passive:true});
			if(this.options.theme===darkTheme){
				this.setDarkMode(true);
			}
            return btn;
        }
		async setDarkMode(dark){
			const cl=this.wrap.classList,
				isDark=dark===undefined?cl.contains('tf_cdm_dark'):!dark;
			if(!isDark){
				cl.add('tf_cdm_dark','tf_lazy');
				await Themify.loadCss(cdnUrl + 'theme/'+darkTheme+'.min.css', 'tf_codemirror_'+darkTheme, false, this.getCssRoot());
				cl.remove('tf_lazy');
			}
			else{
				cl.remove('tf_cdm_dark');
			}
			this.editor.setOption('theme', (!isDark?darkTheme:'default'));
		}
        run() {
            this.el.disabled = 1;
            this.el.style.opacity=.5;
            const lazy = doc.createElement('div');
            lazy.className = 'tf_loader tf_abs_c';
            this.el.before(lazy);
            const pr = new Promise(async(resolve, reject) => {
                try {
                    const prms = [],
                        controls=doc.createElement('div');
                    await Promise.all([
                        Themify.loadCss(Themify.url + 'css/admin/codemirror.css', 'tf_codemirror', null, this.getCssRoot()),
                        Themify.loadCss(cdnUrl + 'codemirror.min.css', 'tf_codemirror_cdn', false, this.getCssRoot()),
                        Themify.loadJs(cdnUrl + 'codemirror.min.js', !!win.CodeMirror, false)
                    ]);
                    win.CodeMirror.modeURL = cdnUrl + 'mode/';
                    if (this.options.allowFullScreen) {
                        this.options.extraKeys.F11=this.options.extraKeys.LeftTripleClick = cm => {
                            if (!doc.fullscreenElement) {
                                this.requestFullscreen();
                            } else {
                                this.exitFullscreen();
                            }
                        };
                    }
                    if (this.options.theme!=='default') {
                        prms.push(Themify.loadCss(cdnUrl + 'theme/'+this.options.theme+'.min.css', 'tf_codemirror_'+this.options.theme, false, this.getCssRoot()));
                    }
                    if (this.options.matchBrackets) {
                        prms.push(Themify.loadJs(cdnUrl + 'addon/edit/matchbrackets.min.js', false, false));
                    }
                    if (this.options.autoCloseBrackets) {
                        prms.push(Themify.loadJs(cdnUrl + 'addon/edit/closebrackets.min.js', false, false));
                    }
                    if (this.options.autoCloseTags) {
                        prms.push(Themify.loadJs(cdnUrl + 'addon/edit/closetag.min.js', false, false));
                    }
                    if (this.options.matchTags) {
                        prms.push(Themify.loadJs(cdnUrl + 'addon/fold/xml-fold.min.js', false, false));
                        prms.push(Themify.loadJs(cdnUrl + 'addon/edit/matchtags.min.js', false, false));
                    }
                    if (this.options.styleActiveLine) {
                        prms.push(Themify.loadJs(cdnUrl + 'addon/selection/active-line.min.js', false, false));
                    }
                    if (this.options.continueComments) {
                        prms.push(Themify.loadJs(cdnUrl + 'addon/comment/continuecomment.min.js', false, false));
                    }
                    if (this.options.runMode) {
                        prms.push(Themify.loadJs(cdnUrl + 'addon/runmode/runmode.min.js', false, false));
                    }
                    prms.push(this.loadHint());
                    prms.push(this.loadLint());
                    prms.push(this.loadMode());
                    await Promise.all(prms.flat());
                    setTimeout(() => {
                        this.editor = win.CodeMirror.fromTextArea(this.el, this.options);
                        this.editor.on('change', cm => {
                            this.el.value = cm.getValue();
                        });
                        this.el.tf_mirror = this;
                        this.editor.on('keyup', this.showHint);
                        
                        controls.className='tf_cdm_controls tf_opacity';
                        controls.appendChild(this.themeSwitcher());
                        if (this.options.allowFullScreen) {
                            const fullscreen = doc.createElement('button');
                            fullscreen.className = 'tf_cdm_fullscreen_btn tf_rel';
                            fullscreen.type = 'button';
                            fullscreen.title = 'FullScreen Mode(Hot Key F11) or Tripple Click';
                            fullscreen.tfOn(Themify.click, e => {
                                e.stopImmediatePropagation();
                                if (!doc.fullscreenElement) {
                                    this.requestFullscreen();
                                } else {
                                    this.exitFullscreen();
                                }
                            }, {passive:true});
                            controls.appendChild(fullscreen);
                        }
                        this.wrap.appendChild(controls);
                        resolve(this);
                    }, 500);
                } catch (e) {
                    console.log(e);
                    reject(e);
                }
            });
            pr.finally(() => {
                lazy.remove();
                this.el.disabled= this.el.style.opacity= '';
            });
            return pr;
        }
    }

})(Themify, document, window);