var ThemifyStyles;
(function (win, doc,und) {
    'use strict';
    if (typeof String.prototype.trimRight !== 'function') {
        String.prototype.trimRight = function () {
            return this.replace(/\s+$/, '');
        };
    }
    if (!String.prototype.replaceAll) {
        String.prototype.replaceAll = function (str, newStr){

            // If a regex pattern
            if (Object.prototype.toString.call(str).toLowerCase() === '[object regexp]') {
                return this.replace(str, newStr);
            }

            // If a string
            return this.replace(new RegExp(str, 'g'), newStr);

        };
    }
    var isVisual = null,
            tmpIframe, //Only for calculate padding/margin of cols from old flex to new grid 
            Rules = {},
            cacheGSProp = {},
            allAreas = {},
            allSizes = {},
            allGutters = {},
            AllFields = {},
            convertPaddings={};

    ThemifyStyles = {
        styleName: 'tb_component_customize_',
        breakpoint: null,
        builder_id: null,
        saving: null,
        disableNestedSel: null,
        isWorking:false,
        fonts: {},
        cf_fonts: {},
        bgImages: [],
        GS: {},
        convertPreset:function(slug, styling){
            if(slug==='buttons' || slug==='menu'){
                if(!styling.text_align){
                    if(slug==='icon' && styling.icon_position){
                        styling.text_align=styling.icon_position;
                    }
                    else if((slug==='buttons' || slug==='menu') && styling.alignment){
                        styling.text_align=styling.alignment;
                    }
                }
            }
            else if(!styling.g_t_a && styling.alignment && (slug==='overlay-content' || slug==='cart-icon')){
                styling.g_t_a=styling.alignment;
            }
            return styling;
        },
        normalizeArea: function (area, as_array) {
            if (!area || area.indexOf('var') !== -1 || area.indexOf(' ')===-1) {
                return area;
            }
            area = area.replace(/\s\s+/g, ' ').trim().split('" "');
            var main = [];
            for (var i = 0, len = area.length; i < len; ++i) {
                var _arr = [];
                for (var k = 0, tmp = area[i].replaceAll('"', '').split(' '), len2 = tmp.length; k < len2; ++k) {
                    var _keyCol = tmp[k][0] === 'c' || tmp[k] === '.' ? tmp[k] : ('col' + tmp[k]);//if it's already start with "col"
                    _arr.push(_keyCol);
                }
                main.push('"' + _arr.join(' ') + '"');
            }
            return as_array === true ? main : main.join(' ');
        },
        init: function (data, breakpointsReverse, bid, gutters) {
            for (var k in gutters) {
                if (gutters[k] > 0) {
                    gutters[k] += '%';
                }
                allGutters[k] = gutters[k];
            }
            this.breakpointsReverse = breakpointsReverse;
            AllFields = data;
            this.builder_id = bid;
            isVisual = typeof tb_app !== 'undefined' && tb_app.mode === 'visual';
            this.InitInlineStyles();
        },
        getRules: function (module) {
            return module ? Rules[Rules] : Rules;
        },
        getStyleData: function (styles, p) {
            var res = {};
            for (var i in styles) {
                if (i !== 'label' && i !== 'units' && i !== 'description' && i !== 'after' && i !== 'options' && i !== 'wrap_class' && i !== 'option_js' && i !== 'class' && i !== 'binding') {
                    res[i] = styles[i];
                }
            }
            res.p = p;
            return res;
        },
        parseFontName: function (font) {
            if (font) {
                font = font.split(',');
                var res = '';
                for (var i = 0, len = font.length; i < len; ++i) {
                    var v = font[i].trim();
                    if (v !== 'serif' && v !== 'sans-serif' && v !== 'monospace' && v !== 'fantasy' && v !== 'cursive' && v[0] !== '"' && v[0] !== "'") {
                        res += '"' + v + '"';
                    } else {
                        res += v;
                    }
                    if (i !== (len - 1)) {
                        res += ', ';
                    }
                }
            } else {
                return font;
            }
            return res;
        },
        getArea: function (areaValue, cssValue, bp, count) {
            var res = null,
                responsiveKey,
                globalKey;
            if (Object.keys(allAreas).length === 0) {
                var inner=doc.tfClass('row_inner')[0],
                    isCreated=false;
                    if(inner===und){
                        isCreated=true;
                        inner=doc.createElement('div');
                        inner.className='row_inner tf_abs';
                        inner.style.display='none';
                        doc.body.appendChild(inner);
                    }
                var computed = getComputedStyle(inner),
                    points=['','m','t','tl'],
                    sizes=[
                        1,
                        2,
                        3,
                        4,
                        5,
                        6,
                        '1_2',
                        '2_1',
                        '1_3',
                        '3_1',
                        '1_1_2',
                        '1_2_1',
                        '2_1_1',
                        'auto'
                    ];
                    for(var i=20;i>1;--i){
                        for(var j=sizes.length-1;j>-1;--j){
                            if(sizes[j]!==i){
                                for(var m=points.length-1;m>-1;--m){
                                    var k='--area'+points[m]+i+'_'+sizes[j],
                                        area = computed.getPropertyValue(k);
                                    if(area){
                                        allAreas[k] = area.replace(/col/ig, '').replace(/\s\s+/g, ' ').trim();
                                    }
                                }
                            }
                        }
                    }
                    for(i=sizes.length-1;i>-1;--i){
                        var k='--c'+sizes[i],
                            s = computed.getPropertyValue(k);
                        if(s){
                            allSizes[k] = s.replace(/\s\s+/g, ' ').replace(/\s0\./g, ' .').replace(/\.+0*?fr/g, 'fr').replace(/\.+0*?\%/g, '%').replace(/\.+0*?em/g, 'em').replace(/\.+0*?px/g, 'px').trim();
                        }
                    }
                 if(isCreated===true){
                        inner.remove();
                    }
            }
            if (areaValue) {
                if (areaValue.indexOf('var') !== -1) {
                    res = areaValue;
                } else if (areaValue.indexOf('_') !== -1) {
                    res = 'var(--area' + areaValue.replace('--area','') + ')';
                } else {
                    areaValue = areaValue.replace(/\s\s+/g, ' ').trim();
                    var _area = areaValue.replace(/col/ig, ''),
                            prefix = '--area',
                            found = null;
                    responsiveKey = prefix + bp[0];
                    globalKey = prefix + count;
                    if (bp.indexOf('_') !== -1) {
                        responsiveKey += bp.split('_')[1][0];
                    }
                    responsiveKey += count;
                    for (var k in allAreas) {
                        if (allAreas[k] === _area) {
                            if (k.indexOf(responsiveKey) === 0) {
                                res = 'var(' + k + ')';
                                break;
                            } else if (found === null && k.indexOf(globalKey) === 0) {
                                found = 'var(' + k + ')';
                            }
                        }
                    }
                    if (res === null) {
                        res = found === null ? areaValue : found;
                    }
                }
            }
            if (res !== null) {
                if (cssValue === false && res.indexOf('--area') !== -1) {
                    res = res.replace(/ /g, '');
                    if(responsiveKey){
                        res=res.replace(responsiveKey, '');
                    }
                    if(globalKey){
                        res=res.replace(globalKey, '');
                    }
                    res = res.replace(/var|\(|--areat|--areatl|--aream|--area|\)/g, '');
                    res=res.trim();
                    if(res[0]==='_'){
                        res=res.substring(1);
                    }
                }
                return res;
            }
            return allAreas;
        },
        getAreaValue: function (areaKey) {
            if (Object.keys(allAreas).length === 0) {
                this.getArea();
            }
            areaKey = '--area' + areaKey.replace('--area', '').replace('area', '').trim();
            return allAreas[areaKey] !== und ? allAreas[areaKey] : false;
        },
        getColSize: function (colValue, cssValue) {
            var res = null;
            if (Object.keys(allSizes).length === 0) {
                this.getArea();
            }
            if (colValue) {
                colValue = colValue.trim();
                if (colValue === 'auto' || colValue === 'none' || colValue === 'initial' || colValue.indexOf('var') !== -1) {
                    res = colValue;
                } else {
                    if (colValue.indexOf(' ') === -1) {
                        res = 'var(--c' + colValue + ')';
                    } else {
                        res = colValue = colValue.replace(/\s\s+/g, ' ').replace(/\s0\./g, ' .').replace(/\.+0*?fr/g, 'fr').replace(/\.+0*?\%/g, '%').replace(/\.+0*?em/g, 'em').replace(/\.+0*?px/g, 'px').trim();

                        for (var k in allSizes) {
                            if (allSizes[k] === colValue) {
                                res = 'var(' + k + ')';
                                break;
                            }
                        }
                    }
                }
            }
            if (res !== null) {
                if (res.indexOf('--c') !== -1 && (cssValue === false || res.indexOf(' ') !== -1)) {
                    res = res.replace(/var|\(|--c|\)/g, '');
                    if (res.indexOf(' ') === -1) {
                        res = res.replace(/ /g, '');
                    }
                }
                return res;
            }
            return allSizes;
        },
        getColSizeValue: function (colKey) {
            if (Object.keys(allSizes).length === 0) {
                this.getArea();
            }
            colKey = '--c' + colKey.replace('--c', '').replace('c', '').replace('var(', '').replace(')', '').trim();
            return allSizes[colKey] !== und ? allSizes[colKey] : false;
        },
        getGutter: function (gutterValue) {
            if (gutterValue === und) {
                return allGutters;
            }
            for (var k in allGutters) {
                if (allGutters[k] === gutterValue || (allGutters[k]===0 && parseFloat(gutterValue)===0)) {
                    return k;
                }
            }
            return gutterValue;
        },
        getGutterValue: function (gutterKey) {
            gutterKey = gutterKey.toString().trim();
            return allGutters[gutterKey] !== und ? allGutters[gutterKey].toString() : gutterKey;
        },
        extend: function () {
            // Variables
            var extended = {},
                    deep = false,
                    self = this,
                    i = 0,
                    length = arguments.length;
            // Check if a deep merge
            if (arguments[0] === true) {
                deep = arguments[0];
                ++i;
            }
            // Merge the object into the extended object
            var merge = function (obj) {
                for (var prop in obj) {
                    if (Object.prototype.hasOwnProperty.call(obj, prop)) {
                        // If deep merge and property is an object, merge properties
                        if (deep && Object.prototype.toString.call(obj[prop]) === '[object Object]') {
                            extended[prop] = self.extend(true, extended[prop], obj[prop]);
                        } else {
                            extended[prop] = obj[prop];
                        }
                    }
                }
            };
            // Loop through each object and conduct a merge
            for (; i < length; ++i) {
                var obj = arguments[i];
                merge(obj);
            }
            return extended;
        },
        InitInlineStyles: function () {
            var points = this.breakpointsReverse,
                    f = doc.createDocumentFragment();

            if (typeof tb_app!=='undefined') {
                for (var i = points.length - 1; i > -1; --i) {
                    var style = doc.createElement('style');
                    style.id = this.styleName + points[i] + '_global';
                    if (points[i] !== 'desktop'&& isVisual === true) {
                        style.media = '(max-width:' + tb_app.Utils.getBPWidth(points[i]) + 'px)';
                    }
                    f.appendChild(style);
                }
            }
            for (var i = points.length - 1; i > -1; --i) {
                style = doc.createElement('style');
                style.id = this.styleName + points[i];
                if (points[i] !== 'desktop' && isVisual === true) {
                    style.media = '(max-width:' + tb_app.Utils.getBPWidth(points[i]) + 'px)';
                }
                f.appendChild(style);
            }
            var el = doc.tfId('tb_active_style_' + this.builder_id);
            if (el !== null) {
                el.parentNode.replaceChild(f, el);
            } else {
                el = doc.tfId('themify_concate-css');
                if (el !== null) {
                    el.parentNode.insertBefore(f, el.nextSibling);
                } else {
                    doc.body.appendChild(f);
                }
            }
        },
        getSheet: function (breakpoint, isGlobal) {
            if (isGlobal === true) {
                breakpoint += '_global';
            }
            return  doc.tfId(this.styleName + breakpoint).sheet;
        },
        getBaseSelector: function (type, id, bp) {
            var selector = '.themify_builder_content-' + this.builder_id + ' .tb_' + id + '.module';
            selector += type === 'row' || type === 'column' || type === 'subrow' ? '_' : '-';
            selector += type;
            if (isVisual === false && bp !== und && bp !== 'desktop' && typeof tb_app !== 'undefined') {
                selector = '.builder-breakpoint-' + bp + ' ' + selector;
            }
            return selector;
        },
        getNestedSelector: function (selectors) {
            if (this.disableNestedSel === null) {
                var nested = ['p', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'span'],
                        nlen = nested.length;
                selectors = selectors.slice(0);
                for (var j = selectors.length - 1; j > -1; --j) {
                    if (selectors[j].indexOf('.tb_text_wrap') !== -1) {
                        var s = selectors[j].trimRight();
                        if (s.endsWith('.tb_text_wrap')) {//check if after .tb_text_wrap is empty 
                            for (var k = 0; k < nlen; ++k) {
                                selectors.push(s + ' ' + nested[k]);
                            }
                        }
                    }
                }
            }
            return selectors;
        },
        toRGBA: function (color) {
            if (color !== und && color !== '' && color !== '#') {
                if(color.indexOf('--') === 0){
                     return 'var('+color+')';
                }
                else if (color==='transparent' || color.indexOf('rgba') >= 0) {
                    return color;
                }
                var colorArr = color.split('_'),
                        patt = /^([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})$/;
                if (colorArr[0] !== und) {
                    var matches = patt.exec(colorArr[0].replace('#', '')),
                            opacity = colorArr[1] !== und && colorArr[1] !== null && colorArr[1] !== '' ? colorArr[1] : 1;
                    opacity = parseFloat(parseFloat(parseFloat(opacity).toFixed(2)).toString());
                    if (opacity >= .99) {
                        opacity = 1;
                    }
                    return matches ? 'rgba(' + parseInt(matches[1], 16) + ', ' + parseInt(matches[2], 16) + ', ' + parseInt(matches[3], 16) + ', ' + opacity + ')' : (color[0] !== '#' ? ('#' + color) : color);
                } else if (color[0] !== '#') {
                    color = '#' + color;
                }
            } else {
                color = '';
            }
            return color;
        },
        getStyleVal: function (id, data,bp) {
            var v=und;
            if(id){
                if(!bp){
                    bp = this.breakpoint;
                }
                if (bp === 'desktop') {
                    v= data[id] !== '' ? data[id] : und;
                }
                else{
                    if (data['breakpoint_' + bp] !== und && data['breakpoint_' + bp][id] !== und && data['breakpoint_' + bp][id] !== '') {
                        v=  data['breakpoint_' + bp][id];
                    }
                    else{
                        var points = this.breakpointsReverse;
                        for (var i = points.indexOf(bp) + 1, len = points.length; i < len; ++i) {
                            if (points[i] !== 'desktop') {
                                if (data['breakpoint_' + points[i]] !== und && data['breakpoint_' + points[i]][id] !== und && data['breakpoint_' + points[i]][id] !== '') {
                                    v= data['breakpoint_' + points[i]][id];
                                    break;
                                }
                            } else if (data[id] !== '' && data[id]!==und) {
                                v= data[id];
                                break;
                            }
                        }
                    }
                }
                if ((v===und || v==='') && id.endsWith('_unit') && id.indexOf('frame_') === -1) {//because in very old version px wasn't saved and we can't detect after removing it was px value or not
                    v='px';
                }
            }
            return v;
        },
        generateGSstyles: function (gsItems, elType, gsClass) {
            if (cacheGSProp[elType] === und) {
                cacheGSProp[elType] = {};
            }
            var elOptions = this.getStyleOptions(elType),
                    points = this.breakpointsReverse,
                    data = {},
                    check = function (option, id, gsType) {
                        if (cacheGSProp[elType][id] !== und) {
                            return cacheGSProp[elType][id];
                        }
                        if (option.is_overlay === und && option.type !== 'frame' && option.type !== 'video' && (option.type !== 'radio' || option.prop !== und)) {
                            var tab = option.p,
                                    t = option.type,
                                    r = option.is_responsive,
                                    o = null,
                                    h = option.is_hover,
                                    p = option.prop;
                            if (t === 'select' || t === 'icon_radio' || t === 'radio' || t === 'checkbox' || t === 'icon_checkbox') {
                                for (var i in option) {
                                    if (option[i] === true && i !== 'option_js') {
                                        o = i;
                                        break;
                                    }
                                }
                            }
                            var reChechk = function () {
                                for (var i in elOptions) {
                                    if (tab === elOptions[i].p && p === elOptions[i].prop && t === elOptions[i].type && h === elOptions[i].is_hover && (o === null || elOptions[i][o] === true) && r === elOptions[i].is_responsive) {
                                        cacheGSProp[elType][id] = i;
                                        return true;
                                    }
                                }
                                return false;
                            };
                            if (reChechk() === true) {
                                return cacheGSProp[elType][id];
                            }
                            if (p === 'background-image' && (t === 'image' || t === 'imageGradient')) {
                                t = t === 'image' ? 'imageGradient' : 'imageGradient';
                                if (reChechk() === true) {
                                    return cacheGSProp[elType][id];
                                }
                            } else if (p === 'margin-top' || p === 'margin-bottom') {
                                if (elType === 'row' || elType === 'column') {
                                    if (t === 'margin' && gsType !== 'row' && gsType !== 'column') {
                                        t = 'range';
                                        if (reChechk() === true) {
                                            return cacheGSProp[elType][id];
                                        }
                                    }
                                } else if (t === 'range' && (gsType === 'row' || gsType === 'column')) {
                                    t = 'margin';
                                    if (reChechk() === true) {
                                        return cacheGSProp[elType][id];
                                    }
                                }
                            }
                        }
                        cacheGSProp[elType][id] = false;
                        return false;
                    },
                    len = points.length;
            for (var k = 0, len2 = gsItems.length; k < len2; ++k) {
                var cl = gsItems[k].trim();
                if (cl !== '' && gsClass[cl] !== und) {
                    var args = gsClass[cl].data[0],
                            type = gsClass[cl].type;
                    if(!args){
                        continue;
                    }
                    if (type !== 'row' && type !== 'subrow') {
                        args = args.cols[0];
                    }
                    if (type === 'column' || type === 'row' || type === 'subrow') {
                        args = args.styling;
                    } else {
                        args = args.modules[0];
                        type = args.mod_name;
                        args = args.mod_settings;
                    }
                    if (args !== und) {
                        var opt = elType === type ? elOptions : this.getStyleOptions(type);
                        for (var i = len - 1; i > -1; --i) {
                            if (points[i] === 'desktop') {
                                for (var j in args) {
                                    if (j.indexOf('-frame_width') !== -1 || j.indexOf('-frame_height') !== -1) {
                                        elOptions[j] = opt[j] = {};
                                    } else if ('font_gradient_color-gradient' === j && opt[j] === und) {
                                        opt[j] = opt.font_gradient_color;
                                    }
                                    if (opt[j] !== und && (data[j] === und || data[j] === '' || data[j] === false)) {
                                        var index = elOptions[j] !== und ? j : check(opt[j], j, type);
                                        if (index === j || (index !== false && !data[index] && data[index] != '0')) {
                                            data[index] = args[j];
                                        }
                                    }
                                }
                            } else if (args['breakpoint_' + points[i]] !== und) {
                                var found = true,
                                        bp = 'breakpoint_' + points[i];
                                if (data[bp] === und) {
                                    data[bp] = {};
                                    found = false;
                                }
                                for (var j in args[bp]) {
                                    if (opt[j] !== und && opt[j].is_responsive === und && (data[bp][j] === und || data[bp][j] === '' || data[bp][j] === false)) {
                                        var index = elOptions[j] !== und ? j : check(opt[j], j, type);
                                        if (index === j || (index !== false && !data[bp][index] && data[bp][index] != '0')) {
                                            data[bp][index] = args[bp][j];
                                            found = true;
                                        }
                                    }
                                }
                                if (found === false) {
                                    delete data[bp];
                                }
                            }
                        }
                    }
                }
            }
            return data;
        },
        createCss: function (data, elType, saving, gsClass, isGSCall, optimize) {
            if (!elType) {
                elType = 'row';
            }
            this.saving = saving;
            var points = this.breakpointsReverse,
                    len = points.length,
                    css = {},
                    result = {},
                    self = this,
                    builder_id = this.builder_id,
                    recursiveLoop = function (data, type) {
                        var getCustomCss = function (component, elementId, st, allData) {
                            var styles = st !== und ? self.extend(true, {}, st) : {};
                            if (component === 'row' || component === 'subrow') {
                                var sizes,
                                        count = allData.cols ? allData.cols.length : 0;
                                if (allData.sizes !== und) {
                                    sizes = Object.assign( {}, allData.sizes);
                                    for (var i = 0; i < len - 1; ++i) {
                                        if (sizes[points[i] + '_size'] === und && sizes[points[i] + '_area']===und) {
                                            for (var j = i + 1; j < len - 1; ++j) {
                                                if (sizes[points[j] + '_size'] !== und) {
                                                    sizes[points[i] + '_size'] = sizes[points[j] + '_size'];
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                } else {//backward compatibility
                                    sizes = {};
                                    if (count > 1) {
                                        if (allData.gutter !== und && allData.gutter !== '' && allData.gutter !== 'gutter-default' && allData.gutter !== 'gutter') {
                                            sizes.desktop_gutter = allData.gutter.replace('gutter-', '');
                                        }

                                        var j = 0,
                                                gridWidth = [],
                                                classes = [],
                                                g = sizes.desktop_gutter ||  'def',
                                                desktop_size,
                                                hasCustomWidth=false,
                                                useResizing = false,
                                                colSizes = self.getOldColsSizes(g);
                                        for (var len2 = allData.cols.length; j < len2; ++j) {
                                            if (allData.cols[j].grid_width) {
                                                hasCustomWidth = true;
                                                gridWidth.push(allData.cols[j].grid_width);
                                            } else if (allData.cols[j].grid_class) {
                                                var cl = allData.cols[j].grid_class.split(' ')[0].replace(/tb_3col|tablet_landscape|tablet|mobile|column|first|last/ig, '').trim();
                                                if (colSizes[cl] !== und) {
                                                    gridWidth.push(colSizes[cl]);
                                                }
                                                classes.push(cl);
                                            }
                                        }
                                        useResizing=hasCustomWidth;
                                        if (useResizing === false && g !== 'def' && classes.length > 0) {
                                            desktop_size = self.gridBackwardCompatibility(classes);
                                            //in old version gutter narrow,none have been done wrong for sizes 1_2,2_1,1_1_2,1_2_1 and etc we need to convert them to custom sizes to save the same layout
                                            useResizing = desktop_size.indexOf('_') !== -1;
                                        }
                                        if (useResizing === true) {//we need to get min width value,which will be 1fr
                                            var min = Math.min.apply(null, gridWidth);
                                            for (j = gridWidth.length - 1; j > -1; --j) {
                                                gridWidth[j] = min === gridWidth[j] ? '1fr' : (parseFloat((gridWidth[j] / min).toFixed(5)).toString() + 'fr');
                                            }
                                            desktop_size = gridWidth.join(' ').trim();
                                        } else {
                                            gridWidth = null;
                                            if (!desktop_size && classes.length > 0) {
                                                desktop_size = self.gridBackwardCompatibility(classes);
                                            }
                                        }
										if( allData.desktop_dir==='rtl' && (hasCustomWidth===true || desktop_size.toString().indexOf('_')!==-1)){
											desktop_size=hasCustomWidth===true?desktop_size.split(' '):desktop_size.split('_');
											desktop_size=desktop_size.reverse();
											desktop_size=hasCustomWidth===true?desktop_size.join(' '):desktop_size.join('_');
										}
                                        sizes.desktop_size = desktop_size;
                                        classes = null;
                                        for (var i = len - 1; i > -1; --i) {
                                            var bp = points[i];

                                            sizes[bp + '_dir'] = allData[bp + '_dir'] || 'ltr';

                                            if (bp !== 'desktop') {
                                                var c = (allData['col_' + bp] && allData['col_' + bp] !== 'auto' && allData['col_' + bp] !== '-auto') ? self.gridBackwardCompatibility(allData['col_' + bp]) : 'auto';
                                                if (c === 'auto') {
                                                    if (hasCustomWidth===true) {
                                                        c = desktop_size;
                                                    } else {
                                                        var bpkey = bp[0],
                                                                grid;
                                                            if (bp.indexOf('_') !== -1) {
                                                                bpkey += bp.split('_')[1][0];
                                                            }
                                                            grid = self.getAreaValue(bpkey + count + '_' + c);//check first for area for breakpoint e.g --aream5_3
                                                            if (!grid) {
                                                                grid = self.getAreaValue(count + '_' + c);
                                                            }
                                                        if (!grid) {
                                                            for (j = i + 1; j < len; ++j) {
                                                                var bp2 = points[j],
                                                                        parentCol = sizes[bp2 + '_size'];
                                                                if (parentCol === und) {
                                                                    parentCol = allData['col_' + bp2] && allData['col_' + bp2] !== 'auto' && allData['col_' + bp2] !== '-auto' ? allData['col_' + bp2] : false;
                                                                }
                                                                if (parentCol && parentCol !== 'auto') {
                                                                    grid = parentCol.indexOf('fr') !== -1 ? parentCol : self.gridBackwardCompatibility(parentCol);
                                                                    break;
                                                                }
                                                            }
                                                            c = !grid ? count.toString() : grid;
                                                        }
                                                    }
                                                }
												else if(c.toString().indexOf('_')===-1 && c>0 && c<6 && count<c){
													for (j = i + 1; j <len; ++j) {
														if (sizes[points[j] + '_size'] !== und) {
															c=sizes[points[j] + '_size'];
															break;
														}
													}
												}
                                                sizes[bp + '_size'] = c;
                                            }
                                        }

                                        for (i = len - 2; i > -1; --i) {
                                            if (sizes[points[i] + '_size'] === und) {
                                                sizes[points[i] + '_size'] = 'auto';
                                            }
                                        }
                                    }
                                    if (allData.column_h !== und && allData.column_h !== '') {
                                        sizes.desktop_auto_h = '1';
                                    }
                                    if (allData.column_alignment !== und && allData.column_alignment !== '') {
                                        sizes.desktop_align = allData.column_alignment;
                                    }
                                }
                                for (var i = 0; i < len - 1; ++i) {//clean again duplicates
                                    var bp = points[i],
                                            gutter = sizes[bp + '_gutter'],
                                            colh = sizes[bp + '_auto_h'],
                                            size = sizes[bp + '_size'],
                                            align = sizes[bp + '_align'];
                                    if (count === 1) {
                                        delete sizes[bp + '_dir'];
                                        delete sizes[bp + '_size'];
                                        delete sizes[bp + '_area'];
                                        delete sizes[bp + '_gutter'];
                                        delete sizes[bp + '_auto_h'];
                                        size = colh = gutter = null;
                                    }
                                    if (gutter || align || colh || size) {
                                        for (var j = i + 1; j < len; ++j) {
                                            var bp2 = points[j];
                                            if (gutter && sizes[bp2 + '_gutter']) {
                                                if (sizes[bp2 + '_gutter'] === gutter) {
                                                    delete sizes[bp + '_gutter'];
                                                }
                                                gutter = null;
                                            }
                                            if (align && sizes[bp2 + '_align']) {
                                                if (sizes[bp2 + '_align'] === align) {
                                                    delete sizes[bp + '_align'];
                                                }
                                                align = null;
                                            }
                                            if (colh && sizes[bp2 + '_auto_h']) {
                                                if (sizes[bp2 + '_auto_h'] === colh) {
                                                    delete sizes[bp + '_auto_h'];
                                                }
                                                colh = null;
                                            }
                                            if (size && sizes[bp2 + '_size']) {
                                                if (sizes[bp2 + '_size'] === size && (sizes[bp + '_area'] || size.indexOf(' ') !== -1)){
                                                    delete sizes[bp + '_size'];
                                                }
                                                size = null;
                                            }
                                            if (!gutter && !align && !colh && !size) {
                                                break;
                                            }
                                        }
                                    }
                                }
                                for (i = len - 1; i > -1; --i) {
                                    var bp = points[i];
                                    for (var k in sizes) {
                                        if (k.indexOf(bp) === 0 && (bp !== 'tablet' || k.indexOf('tablet_landscape') === -1)) {
                                            if (bp === 'desktop') {
                                                if (styles.grid === und) {
                                                    styles.grid = {count: count};
                                                }
                                                styles.grid[k] = sizes[k];
                                            } else {
                                                if (styles['breakpoint_' + bp] === und) {
                                                    styles['breakpoint_' + bp] = {};
                                                }
                                                if (styles['breakpoint_' + bp].grid === und) {
                                                    styles['breakpoint_' + bp].grid = {count: count};
                                                }
                                                styles['breakpoint_' + bp].grid[k] = sizes[k];
                                            }
                                        }
                                    }
                                }
                                if (styles.grid === und) {
                                    styles.grid = {count: count};
                                }
                            }

                            if (Object.keys(styles).length !== 0) {
                                for (var i = len - 1; i > -1; --i) {
                                    var res = null;
                                    self.breakpoint = points[i];
                                    if (points[i] === 'desktop') {
                                        res = self.getFieldCss(elementId, component, styles, styles);
                                    } else if (styles['breakpoint_' + points[i]] !== und && Object.keys(styles['breakpoint_' + points[i]]).length !== 0) {
                                        res = self.getFieldCss(elementId, component, styles['breakpoint_' + points[i]], styles);
                                    }
                                    if (res && Object.keys(res).length !== 0) {
                                        if (css[points[i]] === und) {
                                            css[points[i]] = {};
                                        }
                                        for (var j in res) {
                                            if (css[points[i]][j] === und) {
                                                css[points[i]][j] = res[j];
                                            } else {
                                                for (var k = res[j].length - 1; k > -1; --k) {
                                                    css[points[i]][j].push(res[j][k]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        setData = function (elType, element_id, styling, allData) {
                            if (styling !== und && styling !== null) {
                                if(elType!=='row' && elType!=='column' && elType!=='subrow' && elType!=='sub-column'){
                                    styling=self.convertPreset(elType, styling);
                                }
                                if (gsClass !== und && styling.global_styles !== und && styling.global_styles !== '') {
                                    var gsData = self.generateGSstyles(styling.global_styles.split(' '), elType, gsClass);
                                    self.GS = self.extend(true, {}, self.GS, self.createCss([{styling: gsData, element_id: element_id}], elType, self.saving, und, true));
                                }
                                if (styling.builder_content !== und) {
                                    self.builder_id = element_id;
                                    if (typeof styling.builder_content === 'string') {
                                        styling.builder_content = JSON.parse(styling.builder_content);
                                    }
                                    loop(styling.builder_content, 'row');
                                    self.builder_id = builder_id;
                                }
                            }
                            getCustomCss(elType, element_id, styling, allData);
                        },
                        loop = function (data, type) {
                            for (var i in data) {
                                var row = data[i],
                                        styling = row.styling || row.mod_settings;
                                if (row.element_id !== und) {
                                    setData(type, row.element_id, styling, row);
                                }
                                if (row.cols !== und) {
                                    for (var j in row.cols) {
                                        var col = row.cols[j];
                                        if (col.styling !== und) {
                                            setData('column', col.element_id, col.styling, col);
                                        }
                                        if (col.modules !== und) {
                                            for (var m in col.modules) {
                                                var mod = col.modules[m];
                                                if (mod === null) {
                                                    continue;
                                                }
                                                if (mod.mod_name !== und) {
                                                    if (mod.mod_settings !== und) {
                                                        setData(mod.mod_name, mod.element_id, mod.mod_settings, mod);
                                                    }
                                                } else {
                                                    loop([mod], 'subrow');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        loop(data, type);
                    };
            recursiveLoop(data, elType);
            if (isGSCall === und) {
                var inlineFonts = JSON.stringify(data).replace(/&quot;/igm, '').match(/font-family\:(.*?)\;/igm);
                if (inlineFonts) {
                    var oldBp = this.breakpoint;
                    this.breakpoint = 'desktop';
                    for (var i = inlineFonts.length - 1; i > -1; --i) {
                        var f = inlineFonts[i].replace(/font-family:|;|\'/ig, '').trim();
                        if (f) {
                            var tmp = {};
                            tmp[i] = f;
                            this.fields.font_select.call(this, i, 'inline', {}, tmp);
                        }
                    }
                    this.breakpoint = oldBp;
                }
                inlineFonts = null;
                if (Object.keys(this.fonts).length > 0) {
                    result.fonts = this.fonts;
                }
                if (Object.keys(this.cf_fonts).length !== 0) {
                    result.cf_fonts = this.cf_fonts;
                }
                if (Object.keys(this.GS).length !== 0) {
                    result.gs = this.GS;
                }
                if (Object.keys(this.bgImages).length !== 0) {
                    result.bg = this.bgImages;
                }
                this.fonts = {};
                this.GS = {};
                this.cf_fonts = {};
                this.bgImages = [];
                this.saving = null;
            }
            self = builder_id = recursiveLoop = null;
            for (var i = len - 1; i > -1; --i) {//always fix the order: desktop,tablet_landscape,tablet,mobile
                if (css[points[i]] !== und) {
                    result[points[i]] = css[points[i]];
                }
            }
            points = len = null;
            return this.optimizeCss(result, optimize);
        },
        getStyleOptions: function (module) {
            if (Rules[module] === und) {
                var all_fields = AllFields;
                if (all_fields[module] !== und) {
                    Rules[module] = {};
                    var self = this,
                            getStyles = function (styles, parent) {
                                for (var i in styles) {
                                    if (styles[i] !== null) {
                                        var type = styles[i].type;
                                        if (type === 'expand' || type === 'multi' || type === 'group') {
                                            var p = parent;
                                            if (type === 'expand' && styles[i].label !== und) {
                                                p = styles[i].label.replace(/\s/g, '');
                                                if (parent !== und) {
                                                    p = parent + '_' + p;
                                                }
                                            }
                                            getStyles(styles[i].options, p);
                                        } else if (type === 'tabs') {
                                            for (var j in styles[i].options) {
                                                var p = '';
                                                if (parent === und) {
                                                    p = j;
                                                } else {
                                                    p = parent + '_' + j;
                                                }
                                                getStyles(styles[i].options[j].options, p);
                                            }
                                        } else if (styles[i].id !== und) {

                                            var id = styles[i].id;
                                            if (styles[i].prop !== und) {
                                                Rules[module][id] = self.getStyleData(styles[i], parent);
                                                var prop = styles[i].prop;

                                                if (prop === 'font-size' || prop === 'line-height' || prop === 'letter-spacing' || ('range' === type && ('margin-top' === prop || 'margin-bottom' === prop))) {
                                                    Rules[module][id + '_unit'] = {type: 'select', p: parent};
                                                }
                                                if (type === 'box_shadow' || type === 'text_shadow') {
                                                    var vals = type === 'box_shadow' ? ['hOffset', 'vOffset', 'blur', 'spread', 'color'] : ['hShadow', 'vShadow', 'blur', 'color'];
                                                    for (var j = vals.length - 1; j > -1; --j) {
                                                        var k = id + '_' + vals[j];
                                                        Rules[module][k] = self.getStyleData(styles[i], parent);
                                                        if (vals[j] !== 'color') {
                                                            Rules[module][k + '_unit'] = {type: 'select', p: parent};
                                                        }
                                                    }
                                                    if (type === 'box_shadow') {
                                                        Rules[module][id + '_inset'] = {type: 'checkbox', p: parent};
                                                    }
                                                } else if (type === 'fontColor') {
                                                    if (Rules[module][styles[i].s] === und) {
                                                        Rules[module][styles[i].s] = {type: 'color', prop: 'color', isFontColor: true, selector: styles[i].selector, origId: id, 'p': parent};
                                                    }
                                                    if (Rules[module][styles[i].g] === und) {
                                                        Rules[module][styles[i].g] = {type: 'gradient', p: parent};
                                                        Rules[module][styles[i].g] = Rules[module][styles[i].g + '-gradient-angle'] = Rules[module][styles[i].g + '-circle-radial'] = Rules[module][styles[i].g + '-gradient-type'] = {type: 'gradient', 'p': parent};
                                                    }
                                                } else if (type === 'padding' || type === 'margin' || type === 'border' || type === 'border_radius') {
                                                    var vals = ['top', 'right', 'bottom', 'left'],
                                                            is_border = type === 'border',
                                                            is_border_radius = is_border === false && type === 'border_radius';
                                                    if (is_border === true) {
                                                        Rules[module][id + '-type'] = {type: 'radio', p: parent};
                                                    } else {
                                                        Rules[module]['checkbox_' + id + '_apply_all'] = {type: 'checkbox', p: parent};
                                                        if (is_border_radius === false) {
                                                            Rules[module][id + '_opp_top'] = {type: 'checkbox', p: parent};
                                                            Rules[module][id + '_opp_left'] = {type: 'checkbox', p: parent};
                                                        }
                                                    }
                                                    for (var j = 3; j > -1; --j) {
                                                        var k = id + '_' + vals[j];
                                                        if (is_border === true) {
                                                            Rules[module][k + '_style'] = self.getStyleData(styles[i], parent);
                                                            Rules[module][k + '_color'] = self.getStyleData(styles[i], parent);
                                                            Rules[module][k + '_style'].prop = prop + '-' + vals[j] + '-style';
                                                            Rules[module][k + '_color'].prop = prop + '-' + vals[j] + '-color';
                                                            k += '_width';
                                                        }
                                                        Rules[module][k] = self.getStyleData(styles[i], parent);
                                                        Rules[module][k + '_unit'] = {type: 'select', p: parent};
                                                        if (is_border_radius === true) {
                                                            var tmpProp = 'border-';
                                                            if (vals[j] === 'top') {
                                                                tmpProp += 'top-left-radius';
                                                            } else if (vals[j] === 'right') {
                                                                tmpProp += 'top-right-radius';
                                                            } else if (vals[j] === 'left') {
                                                                tmpProp += 'bottom-left-radius';
                                                            } else if (vals[j] === 'bottom') {
                                                                tmpProp += 'bottom-right-radius';
                                                            }
                                                            Rules[module][k].prop = tmpProp;
                                                        } else {
                                                            Rules[module][k].prop = prop + '-' + vals[j];
                                                        }
                                                    }
                                                } else if (type === 'gradient' || type === 'imageGradient') {
                                                    Rules[module][id + '-gradient'] = self.getStyleData(styles[i], parent);
                                                    Rules[module][id + '-gradient-angle'] = Rules[module][id + '-circle-radial'] = Rules[module][id + '-gradient-type'] = {type: 'gradient', 'p': parent};
                                                    if (type === 'imageGradient') {
                                                        Rules[module][id + '-type'] = {type: 'radio', p: parent};
                                                        //bg
                                                        Rules[module][styles[i].colorId] = self.getStyleData(styles[i], parent);
                                                        Rules[module][styles[i].colorId].prop = 'background-color';
                                                        Rules[module][styles[i].colorId].type = 'color';
                                                        Rules[module][styles[i].colorId].id = styles[i].colorId;

                                                    }
                                                } else if (type === 'multiColumns') {
                                                    Rules[module][id + '_gap'] = Rules[module][id + '_divider_color'] = Rules[module][id + '_divider_width'] = Rules[module][id + '_divider_style'] = {type: type, 'p': parent};
                                                } else if (type === 'font_select') {
                                                    Rules[module][id + '_w'] = {type: 'font_weight'};
                                                } else if (type === 'filters') {
                                                    var vals = ['hue', 'saturation', 'brightness', 'contrast', 'invert', 'sepia', 'opacity', 'blur'];
                                                    for (var j = vals.length - 1; j > -1; --j) {
                                                        Rules[module][id + '_' + vals[j]] = self.getStyleData(styles[i], parent);
                                                    }
                                                } else if (type === 'width') {
                                                    Rules[module]['min_' + id] = {prop: 'min-width', selector: styles[i].selector, type: 'width', p: parent};
                                                    Rules[module]['max_' + id] = {prop: 'max-width', selector: styles[i].selector, type: 'width', p: parent};
                                                    Rules[module][id + '_auto_width'] = {prop: 'width', selector: styles[i].selector, type: 'width', p: parent};
                                                    Rules[module][id + '_unit'] = {type: 'select', p: parent};
                                                } else if (type === 'transform' || 'transform-origin' === prop) {
                                                    var t_data = self.getStyleData(styles[i], parent);
                                                    if (type === 'transform') {
                                                        var k;
                                                        for (var params = ['scale', 'translate', 'skew'], j = params.length - 1; j > -1; --j) {
                                                            k = id + '_' + params[j];
                                                            Rules[module][k + '_top'] = Rules[module][k + '_bottom'] = t_data;
                                                        }
                                                        for (params = ['x', 'y', 'z'], j = params.length - 1; j > -1; --j) {
                                                            k = id + '_rotate_' + params[j];
                                                            Rules[module][id + '_rotate_' + params[j]] = t_data;
                                                        }
                                                    } else {
                                                        t_data.type = 'transform';
                                                        Rules[module][id] = t_data;
                                                    }
                                                }
                                            } else {
                                                Rules[module][id] = self.getStyleData(styles[i], parent);
                                            }
                                        } else if (type === 'margin_opposity') {
                                            Rules[module][styles[i].topId] = {prop: 'margin-top', selector: styles[i].selector, type: 'range', p: parent};
                                            Rules[module][styles[i].bottomId] = {prop: 'margin-bottom', selector: styles[i].selector, type: 'range', p: parent};
                                            Rules[module][styles[i].topId + '_unit'] = {type: 'select', p: parent};
                                            Rules[module][styles[i].bottomId + '_unit'] = {type: 'select', p: parent};
                                            Rules[module][styles[i].topId + '_opp_top'] = {type: 'checkbox', p: parent};
                                        }
                                    }
                                }
                            };
                    if (all_fields[module].styling !== und) {
                        if (all_fields[module].styling.options.length !== und) {
                            getStyles(all_fields[module].styling.options);
                        } else {
                            getStyles(all_fields[module].styling);
                        }
                    } else {
                        getStyles(all_fields[module].type === und ? all_fields[module] : [all_fields[module]]);
                    }

                } else {
                    return false;
                }
            }
            return Rules[module];
        },
        getFieldCss: function (elementId, module, settings, allData) {
            if (AllFields[module] !== und) {

                var styles = {},
                        rules = this.getStyleOptions(module),
                        prefix = this.getBaseSelector(module, elementId),
                        isSaving = this.saving === true;
                if (settings.resp_no_bg !== und && settings.resp_no_bg !== false) {
                    settings[rules.resp_no_bg.origId] = 'none';
                }
                for (var i in settings) {

                    if (rules[i] !== und && rules[i].selector !== und) {
                        var type = rules[i].type;
                        if (type === 'margin') {
                            type = 'padding';
                        } else if (rules[i].style_handler !== und) {
                            type = rules[i].style_handler;
                        }
                        var st = this.fields[type].call(this, i, module, rules[i], settings, elementId, allData);
                        if (st !== false) {
                            var selectors = Array.isArray(rules[i].selector) ? rules[i].selector : [rules[i].selector],
                                    isHover = rules[i].ishover === true,
                                    res = [];
                            selectors = this.getNestedSelector(selectors);
                            for (var j = 0, len = selectors.length; j < len; ++j) {
                                var sel = selectors[j];
                                if (isHover === true && !sel.endsWith(':after') && !sel.endsWith(':before')) {
                                    sel += ':hover';
                                }
                                if (isVisual === true) {
                                    if (isSaving === false) {
                                        if (isHover === true || sel.indexOf(':hover') !== -1) {
                                            sel += ',' + prefix + sel.replace(':hover', '.tb_visual_hover');
                                        }
                                    } else if (sel.indexOf('.tb_visual_hover') !== -1) {
                                        var s = sel.split(',');
                                        for (var k = s.length - 1; k > -1; --k) {
                                            if (s[k].indexOf('.tb_visual_hover') !== -1) {
                                                s.splice(k, 1);
                                            }
                                        }
                                        sel = s.join(',');
                                        s = null;
                                    }
                                }
                                res.push(prefix + sel);
                            }
                            res = res.join(',').trim().replace(/\s\s+/g, ' ');
                            if (styles[res] === und) {
                                styles[res] = [];
                            }
                            st = st.split('#@#');
                            len = st.length;
                            for (j = 0; j < len; ++j) {
                                if (st[j] !== '' && styles[res].indexOf(st[j]) === -1) {
                                    styles[res].push(st[j]);
                                }
                            }
                        } else if (st === null) {
                            delete settings[i];
                        }
                    }
                }
                return styles;
            }
            return false;
        },
        fields: {
            frameCache: {},
            imageGradient: function (id, type, args, data, elementId, allData) {
                var selector = false,
                        is_gradient = id.indexOf('-gradient', 3) !== -1,
                        checked = is_gradient === true ? id.replace('-gradient', '-type') : id + '-type';
                checked = this.getStyleVal(checked, allData);
                if (checked === 'gradient') {
                    if (is_gradient === true) {
                        selector = this.fields.gradient.call(this, id, type, args, data, elementId, allData);
                        selector += 'background-color:transparent#@#';
                    }
                } else if (is_gradient === false) {
                    selector = this.fields.image.call(this, id, type, args, data, elementId, allData);
                    if (selector !== false && this.getStyleVal(id, allData) !== '') {
                        var v = this.fields.select.call(this, args.repeatId, type, {prop: 'background-mode', origId: args.origId}, data, elementId, allData);
                        if (v !== false) {
                            selector += v;
                        }
                        v = this.fields.position_box.call(this, args.posId, type, {prop: 'background-position', origId: args.origId}, data, elementId, allData);
                        selector += v !== false ? v : 'background-position:50% 50%#@#';
                    }
                }
                return selector;
            },
            image: function (id, type, args, data, elementId, allData) {
                var v = this.getStyleVal(id, allData),
                        selector = false;
                if (v !== und) {
                    if (id === 'background_image' || id === 'bg_i_h') {
                        var checked = id === 'background_image' ? 'background_type' : 'b_t_h';
                        checked = this.getStyleVal(checked, allData);
                        if (checked && 'image' !== checked && 'video' !== checked) {
                            return false;
                        }
                        v = this.breakpoint !== 'desktop' && 'none' === this.getStyleVal('resp_no_bg', allData) ? '' : v;
                    }
                    if (v === '' || v === 'none') {
                        if (this.breakpoint !== 'desktop') {
                            selector = args.prop + ':none#@#';
                        }
                    } else {
                        if (this.saving === true) {
                            this.bgImages.push(v);
                        }
                        selector = args.prop + ':url(' + v + ')#@#';
                    }
                }
                return selector;
            },
            gradient: function (id, type, args, data, elementId, allData) {
                var selector = false,
                        origId = args.id,
                        v = this.getStyleVal(id, allData);
                 if(!origId){
                    return false;
                }
                if (origId === 'background_gradient' || origId === 'b_g_h' || origId === 'cover_gradient' || origId === 'cover_gradient_hover') {
                    var checked;
                    if (origId === 'background_gradient') {
                        checked = 'background_type';
                    } else if (origId === 'b_g_h') {
                        checked = 'b_t_h';
                    } else if (origId === 'cover_gradient') {
                        checked = 'cover_color-type';
                    } else {
                        checked = 'cover_color_hover-type';
                    }
                    checked = this.getStyleVal(checked, allData);
                    if (checked !== 'gradient' && checked !== 'hover_gradient' && checked !== 'cover_gradient') {
                        return false;
                    }
                }
                if (v) {
                    var gradient = v.split('|'),
                            type = this.getStyleVal(origId + '-gradient-type', allData),
                            angle,
                            res = [];
                    if (!type) {
                        type = 'linear';
                    }
                    if (type === 'radial') {
                        angle = this.getStyleVal(origId + '-circle-radial', allData) ? 'circle' : '';
                    } else {
                        angle = this.getStyleVal(origId + '-gradient-angle', allData);
                        if (!angle) {
                            angle = '180';
                        }
                        angle += 'deg';
                    }
                    if (angle !== '') {
                        angle += ',';
                    }
                    for (var i = 0, len = gradient.length; i < len; ++i) {
                        var p = parseInt(gradient[i]) + '%',
                                color = gradient[i].replace(p, '').trim();
                        res.push(color + ' ' + p);
                    }
                    res = res.join(',');

                    selector = args.prop + ':' + type + '-gradient(' + angle + res + ')#@#';
                }
                return selector;
            },
            icon_radio: function (id, type, args, data, elementId, allData) {
                var v = this.getStyleVal(id, allData);
                return !v ? false : args.prop + ':' + v + '#@#';
            },
            color: function (id, type, args, data, elementId, allData) {
                if (args.prop === 'column-rule-color') {
                    return false;
                }

                var v = this.getStyleVal(id, allData);
                if (v === '' || v === und) {
                    delete data[id];
                    return false;
                }
                var c = this.toRGBA(v);
                if (c === '' || c === '_') {
                    delete data[id];
                    return false;
                }
                if (args.isFontColor === true) {
                    return this.fields.fontColor.call(this, args.origId, type, {s: id}, data, elementId, allData);
                }
                var selector = args.prop + ':' + c + '#@#';

                if (args.colorId === id && args.origId !== und && !this.getStyleVal(args.origId, allData)) {
                    if (this.getStyleVal(args.origId + '-type', allData) === 'gradient') {
                        return false;
                    }
                    selector += 'background-image:none#@#';
                } else if ((id === 'b_c_h' || id === 'b_c_i_h') && (type === 'row' || type === 'column' || type === 'subrow' || type === 'sub-column')) {
                    var imgId = id === 'b_c_h' ? 'bg_i_h' : 'b_i_i_h';
                    if (!this.getStyleVal(imgId, allData)) {
                        if (id !== 'b_c_h' || (id === 'b_c_h' && this.getStyleVal('b_t_h', allData) !== 'gradient')) {
                            selector += 'background-image:none#@#';
                        }
                    }
                }
                return selector;
            },
            fontColor: function (id, type, args, data, elementId, allData) {
                var v = this.getStyleVal(id, allData),
                        selector = false;
                if (v === und || v.indexOf('_gradient') === -1) {
                    selector = this.fields.color.call(this, args.s !== und ? args.s : v.replace(/_solid$/ig, ''), type, {prop: 'color'}, data, elementId, allData);

                    if (selector !== false) {
                        selector += 'background-image:none#@#background-clip:border-box#@#';
                    }
                } else if (v !== und) {
                    selector = this.fields.gradient.call(this, v.replace(/_gradient$/ig, '-gradient'), type, {prop: 'background-image', 'id': args.g}, data, elementId, allData);
                    if (selector !== false) {
                        selector += 'background-clip:text#@#-webkit-background-clip:text#@#color:transparent#@#';
                    }
                }
                return selector;
            },
            gap: function (id, type, args, data, elementId, allData) {
                var v = this.getStyleVal(id, allData);
                return v===und? false : args.prop + ':' + v + '#@#';
            },
            padding: function (id, type, args, data, elementId, allData) {
                var prop = args.prop,
                        propName = prop.indexOf('padding') !== -1 ? 'padding' : 'margin',
                        origId = args.id,
                        v = this.getStyleVal(id, allData);
                if (v === und || v === '') {
                    delete data[id + '_unit'];
                    return false;
                }
                if (data['checkbox_' + origId + '_apply_all'] && data['checkbox_' + origId + '_apply_all'] !== '|' && data['checkbox_' + origId + '_apply_all'] !== 'false') {
                    if (prop !== propName + '-top') {
                        return false;
                    }
                    prop = propName;
                }
                var unit = this.getStyleVal(id + '_unit', allData) || 'px',
                    split=v.toString().split(',');//for columns it can be "value1,value2" where "value1" is value for v5, "value2" is for v7
                if(unit==='%' && type==='column' && split[1]===und && ['padding_top','padding_bottom','padding_left','padding_right','margin-bottom','margin-top'].indexOf(id)!==-1){
                    if(convertPaddings[this.breakpoint]===und){
                        convertPaddings[this.breakpoint]={};
                    }
                    if(convertPaddings[this.breakpoint][elementId]===und){
                        convertPaddings[this.breakpoint][elementId]=[];
                    }
                    convertPaddings[this.breakpoint][elementId].push(id);
                }
                v=split[1]!==und && split[1]!==''?split[1]:split[0];
                v=v.trim();
                if (v === '0') {
                    unit = '';
                }
                return prop + ':' + v + unit + '#@#';
            },
            box_shadow: function (id, type, args, data, elementId, allData) {
                var prop = args.prop,
                        origId = args.id,
                        v = this.getStyleVal(id, allData);
                if (v === und || v === '') {
                    delete data[id + '_unit'];
                    return false;
                }
                var subSets = prop === 'box-shadow' ? ['hOffset', 'vOffset', 'blur', 'spread'] : ['hShadow', 'vShadow', 'blur'],
                        selector = '',
                        allIsempty = true;
                for (var i = 0, len = subSets.length; i < len; ++i) {
                    var tid = origId + '_' + subSets[i],
                            val = this.getStyleVal(tid, allData),
                            unit = this.getStyleVal(tid + '_unit', allData) || 'px';
                    if (val === und || val === '') {
                        val = '0';
                    } else {
                        allIsempty = false;
                    }
                    selector += val + unit + ' ';
                }
                if (allIsempty === false) {
                    selector += this.toRGBA(this.getStyleVal(origId + '_color', allData));
                    if (prop === 'box-shadow' && data[origId + '_inset'] === 'inset') {
                        selector = 'inset ' + selector;
                    }
                    selector = prop + ':' + selector + '#@#';
                } else {
                    selector = false;
                }
                return selector;
            },
            text_shadow(id, type, args, data, elementId, allData) {
                return this.fields.box_shadow.call(this, id, type, args, data, elementId, allData);
            },
            border_radius: function (id, type, args, data, elementId, allData) {
                var origId = args.id,
                        apply_all = data['checkbox_' + origId + '_apply_all'],
                        prop = args.prop;
                if (apply_all === '1') {
                    id = origId + '_top';
                    prop = 'border-radius';
                }
                var v = this.getStyleVal(id, allData);
                if (v === und || v === '') {
                    delete data[id + '_unit'];
                    return false;
                }
                var unit = this.getStyleVal(id + '_unit', allData) || 'px';
                return prop + ':' + v + unit + '#@#';
            },
            border: function (id, type, args, data, elementId, allData) {
                var prop = args.prop,
                        origId = args.id,
                        val,
                        v = this.getStyleVal(id, allData);
                if (id.indexOf('_color') !== -1 || ('none' !== v && id.indexOf('_style') !== -1)) {
                    return false;
                }
                var all = this.getStyleVal(origId + '-type', allData);
                if (all === und) {
                    all = 'top';
                } else if (all === 'all') {
                    if (prop.indexOf('border-top') === -1) {
                        return false;
                    }
                    prop = 'border';
                }
                var style = this.getStyleVal(id.replace('_width', '_style'), allData),
                        colorId = id.replace('_width', '_color');
                if (style === 'none') {
                    val = style;
                } else {
                    if (v === und) {
                        return false;
                    }
                    if (!style) {
                        style = 'solid';
                    }
                    val = v + 'px ' + style;
                    var color = this.getStyleVal(colorId, allData);
                    if (color !== '' && color !== und) {
                        val += ' ' + this.toRGBA(color);
                    } else {
                        delete data[colorId];
                    }
                }
                return prop + ':' + val + '#@#';
            },
            select: function (id, type, args, data, elementId, allData) {
                var prop = args.prop,
                        selector = '',
                        v = this.getStyleVal(id, allData);

                if (v === und || v === '' || prop === 'column-rule-style') {
                    return false;
                }
                if (prop === 'background-mode' || prop === 'background-repeat' || prop === 'background-attachment') {
                    if (data[args.origId] === und || data[args.origId] === '' || this.getStyleVal('resp_no_bg', allData)) {
                        return false;
                    }
                    var selectedType=this.getStyleVal('background_type', allData);
                    if(selectedType &&  selectedType!=='image'){
                        return false;
                    }
                    if (prop === 'background-mode') {
                        var bg_values = {
                            'repeat': 'repeat',
                            'repeat-x': 'repeat-x',
                            'repeat-y': 'repeat-y',
                            'repeat-none': 'no-repeat',
                            'no-repeat': 'no-repeat',
                            'fullcover': 'cover',
                            'best-fit-image': 'contain',
                            'builder-parallax-scrolling': 'cover',
                            'builder-zoom-scrolling': '100%',
                            'builder-zooming': '115%'
                        },
                        origV=v;
                        if (bg_values[v] !== und) {
                            if (v.indexOf('repeat') !== -1) {
                                prop = 'background-repeat';
                                if(this.breakpoint!=='desktop'){
                                    selector='background-size:auto#@#';
                                }
                            } else {
                                prop = 'background-size';
                                selector = 'background-repeat:no-repeat#@#';
                                if (v === 'best-fit-image' || v === 'builder-parallax-scrolling' || v === 'builder-zoom-scrolling' || v === 'builder-zooming') {
                                    var tmp = this.extend(true, {}, args);
                                    tmp.prop = 'background-position';
                                    var pos = this.fields.position_box.call(this, 'background_position', type, tmp, data, elementId, allData) ||  'background-position:center#@#';
                                    tmp = null;
                                    selector += pos;
                                    if (v === 'builder-parallax-scrolling') {
                                        selector += 'background-attachment:fixed#@#--tbBg:parallax#@#';
                                    }
                                    else if(v === 'builder-zooming'){
                                        selector += 'background-attachment:scroll#@#--tbBg:zooming#@#';
                                    }
                                    else if(v === 'builder-zoom-scrolling'){
                                        selector += 'background-attachment:scroll#@#--tbBg:zoom#@#';
                                    }
                                }
                            }
                            v = bg_values[v];
                        }
                        if(origV !== 'builder-parallax-scrolling' && origV !== 'builder-zooming' && origV !== 'builder-zoom-scrolling'){
                            selector += '--tbBg:0#@#';
                        }
                    }
                    else if (prop === 'background-repeat' && v === 'fullcover') {
                        prop = 'background-size';
                        v = 'cover';
                    }
                    else if(prop==='background-attachment'){
                        var bgMode=  this.getStyleVal('background_repeat', allData);
                        if(bgMode==='builder-parallax-scrolling' || bgMode === 'builder-zooming' || bgMode === 'builder-zoom-scrolling'){
                            return false;
                        }
                    }
                } 
                else if (prop === 'column-count') {
                    if (v == '0') {
                        var opt = [id, id + '_gap', id + '_divider_color', id + '_width', id + '_divider_style'];
                        for (var i = opt.length - 1; i > -1; --i) {
                            delete data[opt[i]];
                        }
                        return false;
                    }
                    var gap = this.getStyleVal(id + '_gap', allData);
                    if (gap) {
                        selector = 'column-gap:' + gap + 'px#@#';
                    }
                    var style = this.getStyleVal(id + '_divider_style', allData),
                            width = this.getStyleVal(id + '_width', allData);
                    if (style === 'none') {
                        delete data[id + '_divider_color'];
                        delete data[id + '_width'];
                        selector += 'column-rule:none#@#';
                    } else {
                        if (width === '' || width === und) {
                            delete data[id + '_divider_color'];
                            delete data[id + '_width'];
                            delete data[id + '_divider_style'];
                        } else {
                            if (!style) {
                                style = 'solid';
                            }
                            selector += 'column-rule:' + width + 'px ' + style;
                            var color = this.getStyleVal(id + '_divider_color', allData);
                            if (color !== '' && color !== und) {
                                selector += ' ' + this.toRGBA(color);
                            }
                            selector += '#@#';
                        }
                    }

                } else if ('vertical-align' === prop) {
                    if ('inline-block' !== data[args.origID]) {
                        delete data[id];
                        return false;
                    } else if ('' !== v && true !== this.saving && und !== themifyBuilder) {
                        var flexVal;
                        if ('top' === v) {
                            flexVal = 'flex-start';
                        } else if ('middle' === v) {
                            flexVal = 'center';
                        } else {
                            flexVal = 'flex-end';
                        }
                        selector += 'align-self:' + flexVal + '#@#';
                    }

                } else if (true === args.display && true !== this.saving && und !== themifyBuilder) {
                    if ('none' === v) {
                        return false;
                    } else {
                        selector += 'inline-block' === v ? 'width:auto#@#' : 'width:100%#@#';
                    }
                }
                selector += prop + ':' + v + '#@#';
                return selector;
            },
            position_box: function (id, type, args, data, elementId, allData) {
                var prop = args.prop,
                        v = this.getStyleVal(id, allData),
                        bp = '';

                if (v === und || v === '') {
                    return false;
                }
                if (prop === 'background-position') {
                    if ((data[args.origId] === und || data[args.origId] === '') && !(data['__dc__'] && data['__dc__'][args.origId] !== und && data['__dc__'][args.origId] !== '')) {
                        return false;
                    }
                    if (v.indexOf('-') !== -1) {
                        v = v.replace('-', ' ');
                    } else {
                        bp = v.split(',');
                        v = bp[0] + '% ' + bp[1] + '%';
                    }
                }
                return prop + ':' + v + '#@#';
            },
            font_select: function (id, type, args, data, elementId, allData) {
                var _data = allData ? allData : data,
                        v = this.getStyleVal(id, _data),
                        selector = '';
                if (v === 'default' || v === '' || v === und) {
                    delete data[id];
                    delete data[id + '_w'];
                    return false;
                }
                var is_google_font = (typeof ThemifyConstructor !== 'undefined' && ThemifyConstructor.font_select.google[v] !== und) || (typeof ThemifyBuilderStyle !== 'undefined' && ThemifyBuilderStyle.google[v] !== und),
                        is_cf_font = true === is_google_font ? false : (typeof ThemifyConstructor !== 'undefined' && ThemifyConstructor.font_select.cf[v] !== und) || (typeof ThemifyBuilderStyle !== 'undefined' && ThemifyBuilderStyle.cf[v] !== und);
                if (!is_google_font && !is_cf_font) {
                    is_google_font = typeof themifyBuilder !== 'undefined' && null !== themifyBuilder.google && themifyBuilder.google[v] !== und;
                    is_cf_font = true === is_google_font ? false : typeof themifyBuilder !== 'undefined' && null !== themifyBuilder.cf && themifyBuilder.cf[v] !== und;
                }
                if (is_google_font || is_cf_font) {
                    var w = this.getStyleVal(id + '_w', _data),
                            ftype = true === is_google_font ? 'fonts' : 'cf_fonts';
                    if (this[ftype][v] === und) {
                        this[ftype][v] = [];
                    }
                    if (w) {
                        var def = {
                            normal: 'normal',
                            regular: 400,
                            italic: 400,
                            bold: 700
                        };
                        if (this[ftype][v].indexOf(w) === -1) {
                            this[ftype][v].push((def[w] !== und ? def[w] : w));
                        }
                        var italic = w.indexOf('italic') !== -1 ? ';font-style:italic' : '';
                        w = def[w] !== und ? def[w] : w.replace(/[^0-9]/g, '');
                        w += italic;
                        selector = 'font-weight:' + w + '#@#';
                    }
                } else {
                    delete data[id + '_w'];
                }
                selector += args.prop + ':' + this.parseFontName(v) + '#@#';
                return selector;
            },
            frame: function (id, type, args, data, elementId, allData) {
                return false;
            },
            range: function (id, type, args, data, elementId, allData) {
                if ((args.prop === 'column-gap' && !args.grid_gap) || args.prop === 'column-rule-width') {
                    return false;
                }
                var v = this.getStyleVal(id, allData);
                if (v === '' || v === und) {
                    delete data[id];
                    delete data[id + '_unit'];
                    return false;
                }
				v=v.toString();
                var unit = args.prop!=='z-index'?(this.getStyleVal(id + '_unit', allData) || 'px'):'',
                    split=v.split(',');//for columns it can be "value1,value2" where "value1" is value for v5, "value2" is for v7
                if(unit==='%' && type==='column' && (id==='margin-bottom' || id==='margin-top') && split[1]!==und && split[1]!==''){
					/*
                    if(convertPaddings[this.breakpoint]===und){
                        convertPaddings[this.breakpoint]={};
                    }
                    if(convertPaddings[this.breakpoint][elementId]===und){
                        convertPaddings[this.breakpoint][elementId]=[];
                    }
                    convertPaddings[this.breakpoint][elementId].push(id);
					*/
					v=split[1];
                }
				else{
					v=split[0];
				}
                v=v.trim();
                if (v === '0') {
                    unit = '';
                }
                return args.prop + ':' + v + unit + '#@#';
            },
            radio: function (id, type, args, data, elementId, allData) {
                if (args.prop === 'frame-custom') {
                    var side = id.split('-')[0],
                            v = this.getStyleVal(id, allData),
                            layout = v === side + '-presets' ? this.getStyleVal(side + '-frame_layout', allData) : this.getStyleVal(side + '-frame_custom', allData);

                    if (!layout || layout === 'none') {
                        if (!layout) {
                            return false;
                        }
                        return this.breakpoint === 'desktop' ? false : 'background-image:none#@#';
                    }
                    var selector = '';
                    if (v === side + '-presets') {
                        if (side === 'left' || side === 'right') {
                            layout += '-l';
                        }
                        var key = Themify.hash(layout),
                                self = this,
                                callback = function (svg) {
                                    var color = self.getStyleVal(side + '-frame_color', allData);
                                    if (color !== und && color !== '') {
                                        svg = svg.replace(/\#D3D3D3/ig, self.toRGBA(color));
                                    }
                                    selector = 'background-image:url("data:image/svg+xml;utf8,' + encodeURIComponent(svg) + '")#@#';
                                };
                        if (self.fields.frameCache[key] !== und) {
                            callback(self.fields.frameCache[key]);
                        } else {
                            var frame = doc.tfId('tmpl-frame_' + layout);
                            if (frame !== null) {
                                self.fields.frameCache[key] = frame.textContent.trim();
                                callback(self.fields.frameCache[key]);
                            } else {
                                var url = isVisual !== true && typeof themifyBuilder !== 'undefined' ? themifyBuilder.builder_url : ThemifyBuilderStyle.builder_url,
                                        xhr = new XMLHttpRequest();
                                url += '/img/row-frame/' + layout + '.svg';
                                xhr.open('GET', url, false);
                                xhr.onreadystatechange = function () {
                                    if (this.readyState === 4 && (this.status === 200 || xhr.status === 0)) {
                                        self.fields.frameCache[key] = this.responseText;
                                        callback(this.responseText);
                                    }
                                };
                                xhr.send(null);
                            }
                        }
                    } else {
                        selector = 'background-image:url("' + layout + '")#@#';
                    }
                    var w = this.getStyleVal(side + '-frame_width', allData),
                        h = this.getStyleVal(side + '-frame_height', allData),
						w_unit = this.getStyleVal(side + '-frame_width_unit', allData) || '%',
						h_unit = this.getStyleVal(side + '-frame_height_unit', allData) || '%',
						repeat = this.getStyleVal(side + '-frame_repeat', allData);
					const animation_duration = parseFloat( this.getStyleVal(side + '-frame_ani_dur', allData) ),
						animation_reverse = this.getStyleVal(side + '-frame_ani_rev', allData),
						animated = animation_duration > 0;
					/* override some user settings when using animation */
					if ( animated ) {
						if ( side === 'left' || side === 'right' ) {
							h = 200;
							h_unit = '%';
						} else {
							w = 200;
							w_unit = '%';
						}
						repeat = ! repeat ? 2 : parseInt( repeat ) * 2;
					}
                    if (w) {
                        selector += 'width:' + w + w_unit + '#@#';
                    } else {
                        delete data[side + '-frame_width'];
                        delete data[side + '-frame_width_unit'];
                    }
                    if (h) {
                        selector += 'height:' + h + h_unit + '#@#';
                    } else {
                        delete data[side + '-frame_height'];
                        delete data[ side + '-frame_height_unit'];
                    }
                    if (repeat) {
                        var rep = .1 + (100 / repeat);
                        selector += 'background-size:';
                        selector += (side === 'left' || side === 'right') ? '100% ' + rep : rep + '% 100';
                        selector += '%#@#';
                    } else {
                        delete data[side + '-frame_repeat'];
                    }
					if ( animated ) {
						selector += 'animation-name:' + 'tb_frame_' + ( side === 'left' || side === 'right' ? 'vertical' : 'horizontal' ) + '#@#';
						selector += 'animation-iteration-count:infinite' + '#@#';
						selector += 'animation-timing-function:linear' + '#@#';
						selector += 'animation-duration:' + animation_duration + 's' + '#@#';
						selector += 'animation-direction:' + ( this.getStyleVal(side + '-frame_ani_rev', allData) === '1' ? 'reverse' : '' ) + '#@#';
					}
                    var shadow = [
                        this.getStyleVal(side + '-frame_sh_x', allData),
                        this.getStyleVal(side + '-frame_sh_y', allData),
                        this.getStyleVal(side + '-frame_sh_b', allData),
                        this.getStyleVal(side + '-frame_sh_c', allData)
                    ];
                    if (shadow[2] && shadow[3]) {
                        shadow[0] = shadow[0] ? shadow[0] + 'px' : 0;
                        shadow[1] = shadow[1] ? shadow[1] + 'px' : 0;
                        shadow[2] += 'px';
                        shadow[3] = self.toRGBA(shadow[3]);
                        selector += 'filter:drop-shadow( ' + shadow.join(' ') + ')#@#';
                    } else {
                        delete data[ side + '-frame_sh_c' ];
                        delete data[ side + '-frame_sh_b' ];
                        delete data[ side + '-frame_sh_x' ];
                        delete data[ side + '-frame_sh_y' ];
                    }
                    return selector === '' ? false : selector;
                }
                return false;

            },
            multiColumns: function (id, type, args, data, elementId, allData) {
                if (args.prop !== 'column-count') {
                    return false;
                }
                var v = this.getStyleVal(id, allData),
                        selector = false;
                if (v) {
                    selector = args.prop + ':' + v + '#@#';
                    var gap = this.getStyleVal(id + '_gap', allData),
                            w = this.getStyleVal(id + '_divider_width', allData);
                    if (gap !== '' && gap !== und) {
                        selector += 'column-gap:' + gap + 'px#@#';
                    } else {
                        delete data[id + '_gap'];
                    }
                    if (w) {
                        var s = this.getStyleVal(id + '_divider_style', allData),
                                c = this.getStyleVal(id + '_divider_color', allData);
                        selector += 'column-rule:' + w + 'px ';
                        selector += s ? s : 'solid';
                        selector += c !== '' && c !== und ? ' ' + this.toRGBA(c) : '';
                        selector += '#@#';
                    } else {
                        delete data[id + '_divider_color'];
                        delete data[id + '_divider_width'];
                        delete data[id + '_divider_style'];
                    }
                } else {
                    delete data[id];
                    delete data[id + '_gap'];
                    delete data[id + '_divider_color'];
                    delete data[id + '_divider_width'];
                    delete data[id + '_divider_style'];
                }
                return selector;
            },
            height: function (id, type, args, data, elementId, allData) {
                var prop = 'height',
                        selector = false;
                if ('auto' === this.getStyleVal(id + '_auto_height', allData)) {
                    selector = prop + ':auto#@#';
                } else {
                    var v = this.getStyleVal(id, allData);
                    if (v) {
                        var unit = this.getStyleVal(id + '_unit', allData) || 'px';
                        selector = prop + ':' + v + unit + '#@#';
                    }
                }
                return selector;
            },
            filters(id, type, args, data, elementId, allData) {
                var ranges = {
                    hue: {
                        unit: 'deg',
                        prop: 'hue-rotate'
                    },
                    saturation: {
                        unit: '%',
                        prop: 'saturate'
                    },
                    brightness: {
                        unit: '%',
                        prop: 'brightness'
                    },
                    contrast: {
                        unit: '%',
                        prop: 'contrast'
                    },
                    invert: {
                        unit: '%',
                        prop: 'invert'
                    },
                    sepia: {
                        unit: '%',
                        prop: 'sepia'
                    },
                    opacity: {
                        unit: '%',
                        prop: 'opacity'
                    },
                    blur: {
                        unit: 'px',
                        prop: 'blur'
                    }
                },
                        selector = '';
                for (var k in ranges) {
                    var v = this.getStyleVal(args.id + '_' + k, allData);
                    if (v) {
                        selector += ranges[k].prop + '(' + v + ranges[k].unit + ') ';
                    } else {
                        delete data[args.id + '_' + k];
                    }
                }
                return '' === selector ? false : 'filter:' + selector + '#@#';
            },
            text: function (id, type, args, data, elementId, allData) {
                var v = this.getStyleVal(id, allData),
                        selector = false;
                if (v !== und && v !== '') {
                    selector = args.prop + ':' + v + '#@#';
                }
                return selector;
            },
            number: function (id, type, args, data, elementId, allData) {
                return  this.fields.text.call(this, id, type, args, data, elementId, allData);
            },
            width: function (id, type, args, data, elementId, allData) {
                var prop = args.prop,
                        v,
                        selector = false,
                        v = this.getStyleVal(id, allData);
                if ('auto' === v) {
                    selector = 'width:auto#@#';
                } else if (v && ('width' !== prop || 'auto' !== this.getStyleVal(id + '_auto_width', allData))) {
                    var unit = this.getStyleVal(id + '_unit', allData) || 'px';
                    selector = prop + ':' + v + unit + '#@#';
                }
                return selector;
            },
            position: function (id, type, args, data, elementId, allData) {
                var selector = false,
                        v = this.getStyleVal(id, allData);
                if ('' !== v) {
                    selector = 'position:' + v + '#@#';
                    if ('absolute' === v || 'fixed' === v) {
                        var pos = ['top', 'right', 'bottom', 'left'],
                                auto,
                                val;
                        for (var i = pos.length - 1; i >= 0; --i) {
                            auto = this.getStyleVal(id + '_' + pos[i] + '_auto', allData);
                            if ('auto' === auto) {
                                val = 'auto';
                            } else {
                                val = this.getStyleVal(id + '_' + pos[i], allData);
                                val = '' !== val && !isNaN(val) ? val + (this.getStyleVal(id + '_' + pos[i] + '_unit', allData) || 'px') : '';
                            }
                            selector += '' !== val ? pos[i] + ':' + val + '#@#' : '';
                        }
                    }
                }
                return selector;
            },
            transform: function (id, type, args, data, elementId, allData) {
                var selector = '',
                        v, x, y, unit,
                        options = ['skew', 'rotate', 'translate', 'scale'],
                        orig_id = id.split('_')[0];
                for (var i = 3; i > -1; --i) {
                    switch (options[i]) {
                        case 'scale':
                        case 'translate':
                        case 'skew':
                            x = this.getStyleVal(orig_id + '_' + options[i] + '_top', allData);
                            y = this.getStyleVal(orig_id + '_' + options[i] + '_bottom', allData);
                            if ('translate' === options[i]) {
                                unit = {
                                    x: this.getStyleVal(orig_id + '_' + options[i] + '_top_unit', allData) || 'px',
                                    y: this.getStyleVal(orig_id + '_' + options[i] + '_bottom_unit', allData) || 'px'
                                };
                            } else {
                                unit = 'skew' === options[i] ? 'deg' : '';
                            }
                            if (x || y) {
                                if (x && this.getStyleVal(orig_id + '_' + options[i] + '_opp_bottom', allData)) {
                                    selector += options[i] + '(' + x + ('translate' === options[i] ? unit.x : unit) + ') ';
                                } else if (x && y) {
                                    selector += options[i] + '(' + x + ('translate' === options[i] ? unit.x : unit) + ',' + y + ('translate' === options[i] ? unit.y : unit) + ') ';
                                } else {
                                    selector += x ? options[i] + 'X(' + x + ('translate' === options[i] ? unit.x : unit) + ') ' : options[i] + 'Y(' + y + ('translate' === options[i] ? unit.y : unit) + ') ';
                                }
                            }
                            break;
                        case 'rotate':
                            for (var inputs = ['z', 'y', 'x'], k = 2; k > -1; --k) {
                                v = this.getStyleVal(orig_id + '_' + options[i] + '_' + inputs[k], allData);
                                if (v) {
                                    selector += options[i] + inputs[k].toUpperCase() + '(' + v + 'deg) ';
                                }
                            }
                            break;
                    }
                }
                if ('' !== selector) {
                    selector = 'transform:' + selector + '#@#';
                    // Transform origin
                    v = this.getStyleVal(orig_id + '_position', allData);
                    if (v) {
                        v = v.split(',');
                        selector += 'transform-origin:' + v[0] + '% ' + v[1] + '%#@#';
                    }
                } else {
                    selector = false;
                }
                return selector;
            },
            grid: function (id, type, args, data, element_id, allData, bp, as_array) {
                if (!bp) {
                    bp = this.breakpoint;
                }
              
                var selectors = {},
                        dir = null,
                        vals = data[id],
                        inner = Themify.is_builder_active === true && win.tb_app?tb_app.Builder.get().el.querySelector('.tb_' + element_id + ' .' + type + '_inner'):null;
						if(!inner){
							inner=doc.querySelector('.themify_builder_content-' + this.builder_id + ' .tb_' + element_id + ' .' + type + '_inner');
						}
                        var _data = allData !== null && Object.keys(allData).length > 0 ? allData : null,
                        count = inner !== null ? inner.childElementCount : vals.count,
                        suffixArr = ['dir', 'align', 'gutter', 'area', 'size', 'auto_h'],
                        foundSuffix = [];     
                for (var k in vals) {
                    var v = vals[k];
                    if (v && k.indexOf(bp) === 0 && (bp !== 'tablet' || k.indexOf('tablet_landscape') === -1)) {
                        var suffix = suffixArr.indexOf(k.replace(bp + '_', ''));
                        if (suffix === -1 || !vals[bp + '_' + suffixArr[suffix]] || (count === 1 && suffixArr[suffix] !== 'align')) {
                            continue;
                        }
                        suffix = suffixArr[suffix];
                        v = vals[bp + '_' + suffix].toString();
                        foundSuffix.push(suffix);
                        if (suffix === 'size') {
                            if (v.indexOf(' ') === -1) {//is preset grid selected
                                if (bp !== 'desktop') {
                                    var bpkey = bp[0];
                                    if (bp.indexOf('_') !== -1) {
                                        bpkey += bp.split('_')[1][0];
                                    }
                                    var key = '--area' + bpkey + count + '_' + v, //check first for area for breakpoint e.g --aream5_3
                                            grid = this.getAreaValue(key),
                                            areaVal = ''; 
                                    if (!grid) {
                                        key = '--area' + count + '_' + v;
                                        grid = this.getAreaValue(key);
                                    }
                                    if (grid || vals[bp + '_area']) {
                                        areaVal = vals[bp + '_area'] ? this.normalizeArea(vals[bp + '_area']) : 'var(' + key + ')';
                                        v = v.indexOf('_') !== -1 ? v : 'none';
                                    } 
                                    else {//when template doesn't exist(e.g when there are 7,8,9 cols and selected grid 3) we need to generate it
                                        var start = true,
                                                grid = v !== 'auto' ? this.getColSizeValue(v) : null,
                                                colsLength = grid ? grid.replace(/\s\s+/g, ' ').trim().split(' ').length : count,
                                                remainder = count % colsLength,
												_v=v.indexOf('_')===-1?parseInt(v):0;
										if(count>=_v){
											if (colsLength > count) {
												remainder = 0;
												v = colsLength = count;
												v = v.toString();
											}
											var len = count - remainder;
											for (var i = 1; i <= len; ++i) {
												if (start === true) {
													areaVal += '"';
													start = false;
												}
												areaVal += 'col' + i + ' ';
												if (i % colsLength === 0 || i === len) {
													areaVal = areaVal.trim();
													areaVal += '" ';
													start = true;
												}
											}
											if (remainder > 0) {
												var arr = [];
												for (i = count; i > len; --i) {
													arr.push('col' + i);
												}
												arr.reverse();
												for (i = arr.length; i < colsLength; ++i) {
												   arr.push('.');
												}
												if (v.indexOf('_') === -1) {
													v = 'none';
												}
												areaVal += '"' + arr.join(' ').trim() + '"';
											}
										}
                                    }
                                    if (areaVal !== '') {
                                        selectors['--area'] = areaVal.trim();
                                    }
                                }
                                if (v !== '' && (bp !== 'desktop' || (v !== '1' && v !== '2' && v !== '3' && v !== '4' && v !== '5' && v !== '6'))) {
                                    if (v !== 'none') {
                                        if (selectors['--area'] && v.indexOf('_') === -1) {
                                            v =count<parseInt(v)?'':'none';
                                        } else {
                                            v = this.getColSizeValue(v) ? 'var(--c' + v + ')' : '';
                                        }
                                    }
                                } else {
                                    v = '';
                                }
                            } 
                            selectors['--col'] = v;
                        } 
                        else if (suffix === 'area') {
                            selectors['--area'] = this.normalizeArea(v);
                        } 
                        else if (suffix === 'auto_h') {
                            if (bp !== 'desktop' || v !== '-1') {
                                selectors['--align_items'] = v === '1' ? 'var(--align_content)' : 'var(--auto_height)';
                            }
                        } 
                        else if (suffix === 'align') {
                            if (count === 1 && inner && !inner.closest('.module_row').classList.contains('fullheight')) {
                                continue;
                            }
                            if (v === 'col_align_bottom' || v === 'end') {
                                v = 'end';
                            } else if (v === 'col_align_middle' || v === 'center') {
                                v = 'center';
                            } else {
                                v = 'start';
                            }
                            selectors['--align_content'] = 'var(--align_' + v + ')';
                        }
                        else if (suffix === 'gutter') {
                            if (v !== und && v !== 'undefined') {
                                var val = parseFloat(v);
                                if (!isNaN(val)) {
                                    if (val === 0) {
                                        v = val;
                                    }
                                    v = this.getGutter(v);
                                }
                                if (bp !== 'desktop' || (v !== 'gutter' && v !== 'gutter-default')) {
                                    if (v === 'gutter' || v === 'none' || v === 'narrow') {
                                        v = 'var(--' + v + ')';
                                    }
                                    selectors['--colG'] = v;
                                }
                            }
                        } 
                        else if (suffix === 'dir' && count > 1) {
                            dir = v;
                        }
                    }
                }
                if (bp === 'desktop' && count !== 1 && (as_array !== true || foundSuffix.indexOf('area') !== -1 || foundSuffix.indexOf('size') !== -1)) {
                    var _cols = [];
                    for (var i = 1; i <= count; ++i) {
                        _cols.push('col' + i);
                    }
                    if (!selectors['--area']) {
                        selectors['--area'] = '"' + _cols.join(' ') + '"';
                    }
                }
				if(dir !== null && count > 1&& bp !== 'desktop' ){//for desktop we need reverse area in doc,we are doing it in backend(builder is off) or in js(builder is on) for old version
				
					var Reverse = function (area, cols) {
								var newArea = [];
								if (cols && cols !== 'none' && cols !== 'initial' && cols !== 'auto') {
									cols = cols.replace(/  +/g, ' ').trim();
									if (cols.indexOf(' ') === -1) {
										cols = cols.replace('var(--c', '').replace(')', '').trim().split('_').reverse().join('_');
										cols = 'var(--c' + cols + ')';
									} else {
										cols = cols.split(' ').reverse().join(' ');
									}
								}
								if(area){
									if (area.indexOf('var') !== -1) {
										area = area.replace('var(', '').replace(')', '').trim();
										area = self.getAreaValue(area);
									}
									var colsSize = area.split('" "')[0].split(' ').length,
									_tmpArr = [],
									j = 0;
									area = area.replace(/\"/g, '').replace(/  +/g, ' ').trim().split(' ').reverse();
									while (area[0] === '.') {
										area.push(area.splice(0, 1)[0]);
									}
									for (var i = 0, len = area.length; i < len; ++i) {//convert back
										_tmpArr.push(area[i]);
										++j;
										if ((j > 0 && (j % colsSize) === 0) || i === len - 1) {
											newArea.push('"' + _tmpArr.join(' ') + '"');
											_tmpArr = [];
											j = 0;
										}
									}
									newArea=newArea.join(' ');
								}
								else{
									newArea=area;
								}
								return {area: newArea, cols: cols};
						},
                        area = selectors['--area'] || false,
                        cols = selectors['--col'] || false,
                        self = this,
                        isDesktopRtl = false,
                        checkCols = function (area, cols) {
                            if (cols && cols !== 'none') {
                                if (area.indexOf('var') !== -1) {
                                    area = area.replace('var(', '').replace(')', '').trim();
                                    area = self.getAreaValue(area);
                                }
                                var areaLength = area.split('" "')[0].split(' ').length,
                                        col = self.getColSizeValue(cols);
                                if (!col) {
                                    col = cols;
                                }
                                if (col.split(' ').length !== areaLength) {
                                    cols = 'none';
                                }
                            }
                            return cols;
                        };
						if (_data !== null) {
							if (cols === 'none') {
								cols = _data['breakpoint_' + bp] && _data['breakpoint_' + bp][id] && _data['breakpoint_' + bp][id][bp + '_size'] ? _data['breakpoint_' + bp][id][bp + '_size'] : false;
								if (cols === 'auto') {
									cols = false;
								}
							}
							isDesktopRtl = _data[id] !== und && _data[id].desktop_dir === 'rtl';
							var origDir = dir;
							if (isDesktopRtl === true) {
								dir = dir === 'rtl' ? 'ltr' : 'rtl';
							}
							if (area === false || cols === false) {
								var points = ThemifyStyles.breakpointsReverse,
										bpLength = points.length;
								for (k = points.indexOf(bp) + 1; k < bpLength; ++k) {
									var parentBbp = points[k],
											bpData = parentBbp !== 'desktop' ? _data['breakpoint_' + parentBbp] : _data;
									if (bpData !== und && bpData[id] !== und && bpData[id][parentBbp + '_size'] !== und) {
										var _tmp = {};
										_tmp[id] = {};
										_tmp[id][bp + '_size'] = bpData[id][parentBbp + '_size'];
										if (area === false) {
											var res = this.fields[id].call(this, id, type, args, _tmp, element_id, _data, bp, true);
											area = res['--area'];
										}
										if (cols === false && _tmp[id][bp + '_size'] !== 'auto') {
											cols = _tmp[id][bp + '_size'].indexOf(' ') === -1 ? 'var(--c' + _tmp[id][bp + '_size'] + ')' : _tmp[id][bp + '_size'];
										}

										break;
									}
								}
								if (!area) {
									if (!area || !area['--area']) {
										var _cols = [];
										for (k = 1; k <= count; ++k) {
											_cols.push('col' + k);
										}
										area = '"' + _cols.join(' ') + '"';
									} else if (!area) {
										area = area['--area'];
									}
								}
								
							}
						}
						if (dir === 'rtl' || (origDir === 'rtl' && cols && cols.indexOf('_') !== -1)) {
							var ret = Reverse(area, cols);
							if (dir === 'rtl') {
								area = ret.area;
							}
							cols = ret.cols;
						}


						if (area) {
							cols = checkCols(area, cols);									
							if (_data !== null &&  Themify.is_builder_active === true) {//hack to change sizes
                                var model = vals.model?vals.model:(inner?tb_app.Registry.get(inner.closest('[data-cid]').dataset.cid):null);
                                if(model && (model.type==='row' || model.type==='subrow')){
                                    if (area.indexOf('--area') === -1) {
                                        model.setCols({area:area.replace(/col/g, '')},bp);
                                    }
                                    if (cols && cols !== 'none' && cols !== 'initial') {
                                        model.setCols({size:cols.toString().replace('var(--c', '').replace(')', '').trim()},bp);
                                    }
									delete model.fields.sizes[bp+'_dir'];
                                }
							}
							selectors['--area'] = this.normalizeArea(area);
							if (cols) {
								selectors['--col'] = cols;
							}
						}
				}
                if(selectors['--area']){
                    if ( bp !== 'desktop' && selectors['--area'].indexOf('var') === -1) {
                        selectors['--area'] = this.getArea(selectors['--area'], true, bp, count);
                        var _area = this.getAreaValue(selectors['--area']) || selectors['--area'];
                        if (_area.split('" "')[0].split(' ').length === 1) {
                            selectors['--col'] = 'none';
                        }
                    }
                    else if(bp==='desktop' && count<9){
                        selectors['--area']='';
                    }
                }
                if (selectors['--col'] === 'none' && bp === 'desktop') {
                    delete selectors['--col'];
                } else if (selectors['--col']) {
					if(selectors['--col'].indexOf(' ')!==-1){
						var tmpV = selectors['--col'].split(' ');
						if (selectors['--area']) {
							tmpV = tmpV.slice(0, selectors['--area'].split('" "')[0].replace(/\s\s+/g, ' ').trim().split(' ').length);
						}
						for (var i = tmpV.length - 1; i > -1; --i) {
							var fr = parseFloat(tmpV[i].trim());
							if (fr !== 1) {
								tmpV[i] = tmpV[i].replace(fr.toString(), parseFloat(fr.toFixed(4)).toString());
							}
						}
						selectors['--col'] = tmpV.join(' ');
					}
                    selectors['--col'] = this.getColSize(selectors['--col']);
                }
                if (as_array !== true) {
                    var sel = '';
                    for (k in selectors) {
                        if (selectors[k] !== '') {
                            sel += k + ':' + selectors[k] + '#@#';
                        }
                    }
                    selectors = sel === '' ? false : sel;
                }
										
                return selectors;
            }
        },
        optimizeCss: function (styles, optimize) {
            var points = this.breakpointsReverse,
                    pointsLen = points.length,
                    sides = ['left', 'bottom', 'right', 'top'],
                    bgProperties = ['color', 'clip', 'origin', 'attachment', 'repeat', 'size', 'position', 'image'],
                    borderSides = ['bottom-left', 'bottom-right', 'top-right', 'top-left'],
                    fonts = ['family', 'line-height', 'size', 'weight', 'variant', 'style'],
                    fLength = fonts.length - 1,
                    bgLength = bgProperties.length - 1,
                    orderLength = sides.length - 1,
                    borderLength = borderSides.length - 1,
                    bp,
                    rgbToHex = function (v) {
                        v = v.replace(';', '');
                        if (v.indexOf('rgb') !== -1) {
                            var val = v.replace(')', '').split('(')[1].split(',');
                            if (val[2] !== und) {
                                if (val[3] !== und) {//is rgba
                                    if (val[3] == 1) {
                                        val.splice(3, 1);//convert to rgb
                                    } else if (val[3] == 0) {
                                        return 'transparent';
                                    }
                                }
                                if (val[3] === und) {//is rgb
                                    v = ('#' + ((1 << 24) + (parseFloat(val[0].trim()) << 16) + (parseFloat(val[1].trim()) << 8) + parseFloat(val[2].trim())).toString(16).slice(1));
                                }
                            }
                        }
                        if (v[0] === '#' && v[1] === v[2] && v[3] === v[4] && v[5] === v[6]) {//try to convert to hex shorthand
                            v = '#' + v[1] + v[3] + v[5];
                        }
                        return v;
                    },
					checkAreaOrder=function(v){
						if(!v || v.indexOf('var')!==-1 || v.indexOf('" "')!==-1 || v.indexOf(' ')===-1){
							return false;
						}
						var cols = v.replace(/col|"/ig, '').replace(/\s\s+/g, ' ').trim().split(' ');
						for(var i = cols.length-1;i>0;--i){
							if(parseInt(cols[i]) <= parseInt(cols[i-1])) {
								return false;
							}
						}
						return true;
					};
            //try to convert css shorthand
            for (var i = 0; i < pointsLen; ++i) {
                bp = points[i];
                if (styles[bp] !== und) {
                    for (var sel in styles[bp]) {
                        var css = styles[bp][sel],
                                props = {},
                                resArr = [];
                        for (var j = 0, len = css.length; j < len; ++j) {//if there are the same property leave only the last
                            if (css[j] !== '') {
                                if(css[j][0]!=='@'){
                                    var index = css[j].indexOf(':'),
                                            prop = css[j].substring(0, index);
                                    props[prop] = css[j].substring(index + 1);
                                }
                                else{
                                    var support=css[j].split('{');
                                    styles[bp][support[0]+'{'+sel]=support[1].replace('}','').split('#@@#');
                                }
                            }
                        }
                        for (var prop in props) {//make shorthand,e.g padding-left:5px,padding-right:5px,padding-top:5px,padding-bottom:5px=>padding:5px
                            var v = props[prop],
                                    propsTypes = (prop[0]==='-' && prop[1]==='-')?[prop]:prop.split('-'),
                                    type = propsTypes[0];
                            if (v) {
                                
                                if (optimize === true && ((propsTypes[1] !== und && (type === 'padding' || type === 'margin' || type === 'background' || type === 'border' || type === 'font' || prop === 'line-height')) || type==='--area' || type === 'color')) {

                                    if (type === 'padding' || type === 'margin') {
                                        var vals = [];
                                        for (var s = orderLength; s > -1; --s) {
                                            if (props[type + '-' + sides[s]] !== und) {
                                                vals.push(props[type + '-' + sides[s]]);
                                            }
                                        }
                                        if (vals.length === 4) {
                                            for (s = vals.length - 1; s > -1; --s) {
                                                if (vals[s][vals[s].length - 1] === ';') {
                                                    vals[s] = vals[s].slice(0, -1);
                                                }
                                            }
                                            if (vals[0] === vals[2] && vals[1] === vals[3]) {
                                                vals[2] = vals[3] = null;
                                                if (vals[0] === vals[1]) {
                                                    vals[1] = null;
                                                }
                                            } else if (vals[1] === vals[3]) {
                                                vals[3] = null;
                                            }
                                            for (s = orderLength; s > -1; --s) {
                                                if (vals[s] === null) {
                                                    vals.splice(s, 1);
                                                }
                                            }
                                            prop = type;
                                            v = vals.join(' ');
                                            for (s = orderLength; s > -1; --s) {
                                                delete props[type + '-' + sides[s]];
                                            }
                                        }
                                    } else if (type === 'border') {
                                        if (propsTypes[3] === 'radius') {//is border-radius
                                            var vals = [];
                                            for (var s = borderLength; s > -1; --s) {
                                                if (props[type + '-' + borderSides[s] + '-radius'] !== und) {
                                                    vals.push(props[type + '-' + borderSides[s] + '-' + propsTypes[3]]);
                                                }
                                            }
                                            if (vals.length === 4) {
                                                for (s = vals.length - 1; s > -1; --s) {
                                                    if (vals[s][vals[s].length - 1] === ';') {
                                                        vals[s] = vals[s].slice(0, -1);
                                                    }
                                                }
                                                if (vals[0] === vals[2] && vals[1] === vals[3]) {
                                                    vals[2] = vals[3] = null;
                                                    if (vals[0] === vals[1]) {
                                                        vals[1] = null;
                                                    }
                                                } else if (vals[1] === vals[3]) {
                                                    vals[3] = null;
                                                }
                                                for (s = borderLength; s > -1; --s) {
                                                    if (vals[s] === null) {
                                                        vals.splice(s, 1);
                                                    }
                                                }
                                                prop = type + '-' + propsTypes[3];
                                                v = vals.join(' ');
                                                for (s = borderLength; s > -1; --s) {
                                                    delete props[type + '-' + borderSides[s] + '-' + propsTypes[3]];
                                                }
                                            }
                                        } else if (v === 'none') {
                                           
                                        }
                                    } else if (type === 'font') {
                                        if (props['font-family'] !== und && props['font-size'] !== und) {
                                            var vals=[];
                                            for (var s = fLength; s > -1; --s) {
                                                var p = fonts[s] === 'line-height' ? fonts[s] : (type + '-' + fonts[s]);
                                                if (props[p] !== und) {
                                                    var vv = fonts[s] === 'line-height' ? ('/' + props[p]) : props[p];
                                                    vals.push(vv);
                                                }
                                            }
                                            if (vals.length === (fLength + 1)) {
                                                prop = type;
                                                for (s = vals.length - 1; s > -1; --s) {
                                                    if (vals[s][vals[s].length - 1] === ';') {
                                                        vals[s] = vals[s].slice(0, -1);
                                                    }
                                                }
                                                v = vals.join(' ');
                                                for (s = fLength; s > -1; --s) {
                                                    var p = fonts[s] === 'line-height' ? fonts[s] : (type + '-' + fonts[s]);
                                                    delete props[p];
                                                }
                                            }
                                        }
                                    } else if (type === 'color') {
                                        v = rgbToHex(v);
                                    } else if (type === 'background') {
                                        if (false && bgProperties.indexOf(propsTypes[1]) !== -1) {//temprorary disable
                                            var vals = [],
                                                    hasSize = props['background-size'] !== und;

                                            if (props['background-repeat-x'] !== und || props['background-repeat-y'] !== und) {

                                                if (props['background-repeat'] === und) {
                                                    if (props['background-repeat-x'] !== und) {
                                                        props['background-repeat'] = props['background-repeat-x'] + ' ';
                                                        delete props['background-repeat-x'];
                                                    } else {
                                                        props['background-repeat'] = ' ';
                                                    }
                                                    if (props['background-repeat-y'] !== und) {
                                                        props['background-repeat'] += props['background-repeat-y'];
                                                        delete props['background-repeat-y'];
                                                    }
                                                    props['background-repeat'] = props['background-repeat'].trim();
                                                } else {
                                                    var keysOrder = Object.keys(props),
                                                            posIndex = keysOrder.indexOf('background-repeat'),
                                                            split = props['background-repeat'].split(' ');

                                                    if (props['background-repeat-x'] !== und) {
                                                        if (keysOrder.indexOf('background-repeat-x') > posIndex) {
                                                            split[0] = props['background-repeat-x'];
                                                        }
                                                        delete props['background-repeat-x'];
                                                    }
                                                    if (props['background-repeat-y'] !== und) {
                                                        if (keysOrder.indexOf('background-repeat-y') > posIndex) {
                                                            split[1] = props['background-repeat-y'];
                                                        }
                                                        delete props['background-repeat-y'];
                                                    }
                                                    props['background-repeat'] = split.join(' ');
                                                }

                                            }
                                            if (props['background-position-x'] !== und || props['background-position-y'] !== und) {

                                                if (props['background-position'] === und) {
                                                    if (props['background-position-x'] !== und) {
                                                        props['background-position'] = props['background-position-x'];
                                                        delete props['background-position-x'];
                                                    } else {
                                                        props['background-position'] = 0;
                                                    }
                                                    if (props['background-position-y'] !== und) {
                                                        props['background-position'] += ' ' + props['background-position-y'];
                                                        delete props['background-position-y'];
                                                    } else {
                                                        props['background-position'] += ' ' + 0;
                                                    }
                                                } else {
                                                    var keysOrder = Object.keys(props),
                                                            posIndex = keysOrder.indexOf('background-position'),
                                                            split = props['background-position'].split(' ');

                                                    if (props['background-position-x'] !== und) {
                                                        if (keysOrder.indexOf('background-position-x') > posIndex) {
                                                            split[0] = props['background-position-x'];
                                                        }
                                                        delete props['background-position-x'];
                                                    }
                                                    if (props['background-position-y'] !== und) {
                                                        if (keysOrder.indexOf('background-position-y') > posIndex) {
                                                            split[1] = props['background-position-y'];
                                                        }
                                                        delete props['background-position-y'];
                                                    }
                                                    props['background-position'] = split.join(' ');
                                                }

                                            }
                                            if (!props['background-image'] || props['background-image'] === 'none') {
                                                for (var s = bgLength; s > -1; --s) {
                                                    var p = 'background-' + bgProperties[s];
                                                    if (bgProperties[s] !== 'color' && bgProperties[s] !== 'image' && (bgProperties[s] !== 'clip' || props[p] === 'text')) {
                                                        delete props[p];
                                                    }
                                                }
                                                if (!props[prop]) {
                                                    v = '';
                                                }
                                                hasSize = false;
                                            } else if (props['background-clip'] !== und && props['background-clip'] !== 'text' && props['background-origin'] === und) {
                                                props['background-origin'] = 'padding-box';
                                            }

                                            for (var s = bgLength; s > -1; --s) {
                                                var p = 'background-' + bgProperties[s];
                                                if (props[p] !== und) {
                                                    if (bgProperties[s] === 'clip' && props[p] === 'text') {
                                                        continue;
                                                    }
                                                    var bg = bgProperties[s] === 'size' ? ('/' + props[p]) : props[p];
                                                    if (bgProperties[s] === 'color') {
                                                        bg = rgbToHex(bg);
                                                    }
                                                    vals.push(bg);
                                                } else if (hasSize === true && bgProperties[s] === 'position') {
                                                    vals.push('0 0');
                                                }
                                            }
                                            var vlength = vals.length;
                                            if (vlength > 1 && (bp === 'desktop' || (vlength === bgLength))) {
                                                for (s = bgLength; s > -1; --s) {
                                                    var p = 'background-' + bgProperties[s];
                                                    if (bgProperties[s] === 'clip' && props[p] === 'text') {
                                                        continue;
                                                    }
                                                    delete props[p];
                                                }
                                                for (s = vlength - 1; s > -1; --s) {
                                                    if (vals[s][vals[s].length - 1] === ';') {
                                                        vals[s] = vals[s].slice(0, -1);
                                                    }
                                                }
                                                v = vals.join(' ');
                                                prop = type;
                                            }
                                        }
                                    }
									else if(type==='--area'){
										if(bp==='tablet_landscape' && checkAreaOrder(v)){
											v='';
										}
									}
                                }
                                if (v!=='' && v!==und && v!==false && v!==null) {
                                    if (type !== 'background' || prop === 'background-color') {
                                        if (v.indexOf('rgb') !== -1) {
                                            var founds = v.match(/(rgb.+?\))/ig);
                                            if (founds !== null) {
                                                for (var s = founds.length - 1; s > -1; --s) {
                                                    var convert = rgbToHex(founds[s]);
                                                    if (convert !== founds[s]) {
                                                        v = v.replace(founds[s], convert);
                                                    }
                                                }
                                            }
                                        }
                                        v = v.replace(/\s0\./g, ' .').replace(/\s\s+/g, ' ').replace(/\.+0*?fr/g, 'fr').replace(/\.+0*?\%/g, '%').replace(/\.+0*?em/g, 'em').replace(/\.+0*?px/g, 'px').replace(/\b(?:0px|0%|0em|0fr)/g, '0');
                                    }
                                    if (prop[0]!=='@' && v.slice(-1) !== ';') {
                                        v += ';';
                                    }
                                    resArr.push(prop + ':' + v);
                                }
                            }
                        }
                        styles[bp][sel] = resArr;
                    }
                }
            }
            if (optimize === true) {
                //remove duplicates e.g if mobile and tablet has padding-left:5px remove mobile padding-left

                for (i = 0; i < pointsLen - 1; ++i) {
                    bp = points[i];
                    if (styles[bp] !== und) {
                        for (var sel in styles[bp]) {
                            var css = styles[bp][sel];
                            for (var j = css.length - 1; j > -1; --j) {
                                var index = css[j].indexOf(':'),
                                        prop = css[j].substring(0, index),
                                        val = css[j].substring(index + 1);

                                if (val[val.length - 1] === ';') {
                                    val = val.slice(0, -1);
                                }
                                if (val === 'none' && prop === '--col' && bp === 'tablet_landscape' && (styles.desktop === und || styles.desktop[sel] === und || styles.desktop[sel].join('').indexOf('--col:') === -1)) {
                                    css.splice(j, 1);
                                    continue;
                                }
								else if( prop === '--area' && bp === 'tablet' && (styles.tablet_landscape===und || styles.tablet_landscape[sel]===und || styles.tablet_landscape[sel].join('').indexOf('--area:') === -1) && checkAreaOrder(val)){
									css.splice(j, 1);
									continue;
								}
                                for (var k = i + 1; k < pointsLen; ++k) {
                                    if (styles[points[k]] !== und && styles[points[k]][sel] !== und) {
                                        var parentCss = styles[points[k]][sel],
                                                found = false;
                                        for (var m = parentCss.length - 1; m > -1; --m) {
                                            if (parentCss[m].indexOf(prop) !== -1) {
                                                var parentIndex = parentCss[m].indexOf(':'),
                                                        parentProp = parentCss[m].substring(0, parentIndex),
                                                        parentval = parentCss[m].substring(parentIndex + 1);
                                                if (parentProp === prop) {
                                                    if (parentval[parentval.length - 1] === ';') {
                                                        parentval = parentval.slice(0, -1);
                                                    }
                                                    if (parentval === val) {
                                                        css.splice(j, 1);
                                                    }
                                                    found = true;
                                                    break;
                                                }
                                            }
                                        }
                                        if (found === true) {
                                            break;
                                        }
                                    }
                                }
                            }
                            if (css.length === 0) {
                                delete styles[bp][sel];
                            }
                        }
                        if (Object.keys(styles[bp]).length === 0) {
                            delete styles[bp];
                        }
                    }
                }
				if(styles.mobile!==und){
					for (var sel in styles.mobile) {
						if(styles.tablet===und || styles.tablet[sel]===und || styles.tablet[sel].join('').indexOf('--area:') === -1){
							var css = styles.mobile[sel];
                            for (var j = css.length - 1; j > -1; --j) {
								if(css[j].indexOf('--area:')!==-1){
									if(checkAreaOrder(css[j].split(':')[1])){
										styles.mobile[sel].splice(j, 1);
									}
									break;
								}
							}
						}
                    }
				}
                if(styles.desktop!==und){
                    for (var sel in styles.desktop) {
                        var index = styles.desktop[sel].indexOf('--tbBg:0;');
                        if(index===-1){
                            index=styles.desktop[sel].indexOf('--tbBg:0');
                        }
                        if(index!==-1){
                           styles.desktop[sel].splice(index, 1);
                        }
                    }
                }
                //group selectors by styles
                for (i = 0; i < pointsLen; ++i) {
                    bp = points[i];
                    if (styles[bp] !== und) {
                        var res = {};  
                        for (var sel in styles[bp]) {
                            var css = styles[bp][sel],
                                    hash = [];
                                for (var j = css.length - 1; j > -1; --j) {
                                    hash.push(css[j]);

                                }
                                hash = hash.join('A#_B#');
                                if (res[hash] === und) {
                                    res[hash] = [];
                                }
                                res[hash].push(sel);
                        }
                        for (var h in res) {
                            for (j = res[h].length - 1; j > -1; --j) {
                                delete styles[bp][res[h][j]];
                            }
                          
                            sel = res[h].join(',');
                            if (h[h.length - 1] === ';') {
                                h = h.slice(0, -1);
                            }
                            styles[bp][sel] = h.split('A#_B#');
                        }
                        if(sel[0]==='@'){
                            var support=sel.split('{')[0];
                            styles[bp][sel.replace(','+support+'{',',')]=styles[bp][sel];
                            delete styles[bp][sel];
                        }
                    }
                }
            }
            return styles;
        },
        gridBackwardCompatibility: function (cols) {//backward compatibility convert old cols to new data
            if (cols === '-auto' || cols === 'auto') {
                return 'auto';
            }
            var isArray = Array.isArray(cols),
                    isOld = false,
                    first;
            if (isArray === true) {
                for (var i = cols.length - 1; i > -1; --i) {
                    if (isOld === false) {
                        isOld = cols[i].indexOf('col') !== -1;
                    }
                    cols[i] = cols[i].toString().replace(/tb_3col|tablet_landscape|tablet|mobile|column|col|first|last/ig, '').replaceAll('-', '_').trim();
                    if (cols[i].indexOf(' ') !== -1) {
                        cols[i] = cols[i].split(' ')[0].trim();
                    }
                    if (cols[i] === '4_1_4_2') {
                        cols[i] = '4_1_4_1_4_2';
                    } else if (cols[i] === '4_2_4_1') {
                        cols[i] = '4_2_4_1_4_1';
                    }
                }
                first = cols[0];
            } else {
                cols = cols.toString().trim();
                if (cols.indexOf(' ') !== -1) {
                    cols = cols.split(' ')[0].trim();
                }
                first = cols;
            }
            first = first.toString();
            if (isOld === false) {
                isOld = first.indexOf('col') !== -1;
            }
            first = first.replace(/tb_3col|tablet_landscape|tablet|mobile|column|col|first|last/ig, '').replaceAll('-', '_').trim();
            if (first === '4_1_4_2') {
                return '1_1_2';
            } else if (first === '4_2_4_1') {
                return '2_1_1';
            }
            if (first === '_full') {
                return '1';
            }
            if ((first === '4_2' && (isArray === false || cols[1] === '4_2')) || (first === '2_1' && isArray === true && cols[1] === '2_1')) {
                return '2';
            }
            if (first === '1' || first === '2' || first === '3' || first === '4' || first === '5' || first === '6' || first[0] === '1' || (isOld === false && (first === '2_1' || first === '3_1' || first === '2_1_1'))) {
                return first;
            }
            if (first === '6_1' || first === '5_1' || (first === '4_1' && (isArray === false || cols[3] !== und)) || (first === '3_1' && (isArray === false || cols[2] !== und))) {
                return first[0];
            }
            if (isArray === false) {
                return first.replaceAll(first[0] + '_', '').trim();
            }
            cols = cols.join('_');
            return cols.replaceAll(cols[0] + '_', '').trim();
        },
        getOldColsSizes: function (type) {
            var sizes = {
                'def': {
                    'col6-1': 14,
                    'col5-1': 17.44,
                    'col4-1': 22.6,
                    'col3-1': 31.2,
                    'col4-2': 48.4,
                    'col3-2': 65.6,
                    'col4-3': 74.2
                },
                'narrow': {
                    'col6-1': 15.33,
                    'col5-1': 18.72,
                    'col4-1': 23.8,
                    'col3-1': 32.266,
                    'col4-2': 49.2,
                    'col3-2': 66.05,
                    'col4-3': 74.539
                },
                'none': {
                    'col6-1': 16.666,
                    'col5-1': 20,
                    'col4-1': 25,
                    'col3-1': 33.333,
                    'col4-2': 50,
                    'col3-2': 66.666,
                    'col4-3': 75
                }

            };
            if (type !== und) {
                return sizes[type] === und ? false : sizes[type];
            }
            return sizes;
        }
    };
    if (typeof ThemifyBuilderStyle !== 'undefined') {

        var points = Object.keys(ThemifyBuilderStyle.points).reverse(),
                fonts;
        points.push('desktop');
        ThemifyStyles.init(ThemifyBuilderStyle.styles, points, ThemifyBuilderStyle.gutters);
        ThemifyBuilderStyle.styles = points = null;

        if (ThemifyBuilderStyle.google !== und) {
            fonts = ThemifyBuilderStyle.google;
            ThemifyBuilderStyle.google = {};
            for (var i = fonts.length - 1; i > -1; --i) {
                if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                    ThemifyBuilderStyle.google[fonts[i].value] = {n: fonts[i].name, v: fonts[i].variant};
                }
            }
            fonts = null;
        }
        if (ThemifyBuilderStyle.cf !== und) {
            fonts = ThemifyBuilderStyle.cf;
            ThemifyBuilderStyle.cf = {};
            for (var i = fonts.length - 1; i > -1; --i) {
                if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                    ThemifyBuilderStyle.cf[fonts[i].value] = {n: fonts[i].name, v: fonts[i].variant};
                }
            }
            fonts = null;
        }
        var Regenerate = function () {
            var winW = win.innerWidth;
            for (var bkey in win) {
                if (bkey.indexOf('themify_builder_data_') === 0 && win[bkey] !== null) {
                    var id = bkey.replace('themify_builder_data_', ''),
                            builder_data = win[bkey].data;
                    ThemifyStyles.builder_id = id;
                    convertPaddings={};
                    var css = ThemifyStyles.createCss( builder_data, null, true, win[bkey].gs ),
                            cssFonts = {fonts: [], cf_fonts: []},
                            item = doc.tfId('themify_builder_content-' + id),
                            convert = {},
                            i,
                            j,
                            cssText = '',
                            d = doc.createDocumentFragment();
                    if (css.gs !== und) {
                        for (i in css.gs) {
                            var st = doc.createElement('style');
                            st.id = 'tb_temp_global_styles_' + id;
                            if (i !== 'desktop') {
                                var w = ThemifyBuilderStyle.points[i];
                                if (i !== 'mobile') {
                                    w = w[1];
                                }
                                st.media = '(max-width:' + w + 'px)';
                            }
                            for (j  in css.gs[i]) {
                                var del=j[0]==='@'?';':' ';
                                cssText += j + '{' + css.gs[i][j].join(del) + '}';
                                if(j[0]==='@'){
                                    cssText +='}';
                                }
                            }
                            st.appendChild(doc.createTextNode(cssText));
                            d.appendChild(st);
                        }
                    }
                    for (i in css) {
                        if (i !== 'gs' && i !== 'bg') {
                            if (i !== 'fonts' && i !== 'cf_fonts') {
                                var st = doc.createElement('style');
                                st.id = 'tb_temp_styles_' + i + '_' + id;
                                cssText = '';
                                if (i !== 'desktop') {
                                    var w = ThemifyBuilderStyle.points[i];
                                    if (i !== 'mobile') {
                                        w = w[1];
                                    }
                                    st.media = '(max-width:' + w + 'px)';
                                }
                                for (j  in css[i]) {
                                    var del=j[0]==='@'?';':' ';
                                    cssText += j + '{' + css[i][j].join(del) + '}';
                                    if(j[0]==='@'){
                                        cssText +='}';
                                    }
                                }
                                st.appendChild(doc.createTextNode(cssText));
                                d.appendChild(st);
                            } else {
                                for (j in css[i]) {
                                    var f = j.split(' ').join('+');
                                    if (css[i][j].length > 0) {
                                        f += ':' + css[i][j].join(',');
                                    }
                                    cssFonts[i].push(f);
                                }
                            }
                        }
                    }
                    var fontKeys = Object.keys(cssFonts);
                    for (var key = fontKeys.length - 1; key >= 0; --key) {
                        if (cssFonts[fontKeys[key]].length > 0) {
                            var url = 'fonts' === fontKeys[key] ? '//fonts.googleapis.com/css?family=' + cssFonts[fontKeys[key]].join('|') : ThemifyBuilderStyle.cf_api_url + cssFonts[fontKeys[key]].join('|');
                            Themify.loadCss(url,null, false);
                        } else {
                            delete css[fontKeys[key]];
                        }
                    }
                    cssFonts = fontKeys = null;
                    if (typeof win[bkey].custom_css !== 'undefined') {
                        var cst = doc.createElement('style');
                        cst.id = 'tb_temp_styles_custom_css_' + id;
                        cst.innerHTML=win[ bkey ].custom_css;
                        d.appendChild(cst);
                    }
                    var fr = Object.keys(convertPaddings).length>0 ? d.cloneNode(true) : null;
                    doc.head.appendChild(d);
                    if (fr !== null) {
                        //hack to detect the row_inner/subro_inner width to convert old padding/margin(in percent only) of cols to new grid padding/margin pergin percent
                        var stData = doc.createElement('style');
                        stData.textContent = 'html,body,body *,div,a{transition:none!important;animation:none!important;pointer-events:none!important}';
                        fr.appendChild(stData);
                        tmpIframe.contentWindow.document.head.appendChild(fr);
                        var breakpoints = ThemifyStyles.breakpointsReverse,
                            bpLength = breakpoints.length;
                    
                        for (i = bpLength - 1; i > -1; --i) {
                            var bp = breakpoints[i];
                            if (css[bp] !== und && convertPaddings[bp]!==und) {
                                var iframeSt = 'max-width',
                                        width = 1 * ThemifyBuilderStyle.points[bp],
                                        sheet = doc.tfId('tb_temp_styles_' + bp + '_' + id),
                                        cssRules = null;
                                if (!width || width >= (winW + 5)) {
                                    iframeSt = 'min-width';
                                    if (!width) {
                                        width = ThemifyBuilderStyle.points.tablet_landscape[1] + 1;
                                        if (width < winW) {
                                            width = '';
                                        }
                                    }
                                    if (width === winW) {
                                        --width;
                                    }
                                }
                                tmpIframe.style.setProperty('max-width', 'none','important');
                                tmpIframe.style.setProperty('min-width', 'auto','important');
                                tmpIframe.style.setProperty(iframeSt, width + 'px','important');
                                if (sheet !== null) {
                                    sheet = sheet.sheet;
                                    cssRules = sheet.cssRules;
                                }
                                for (j  in css[bp]) {
                                    if (j.indexOf('.module_column') !== -1 && j.indexOf('.module_column ') === -1) {
                                        var col = null,
                                                innerW,
                                                colW,
                                                elementId = j.split(' ')[1].replace('.module_column', '').replace('.tb_', '');
                                        if(convertPaddings[bp][elementId]!==und){
                                            for (var k = css[bp][j].length - 1; k > -1; --k) {
                                                var value = css[bp][j][k];
                                                if (value.indexOf('%') !== -1 && (value.indexOf('padding') === 0 || value.indexOf('margin') === 0)) {
                                                    var split = value.split(':'),
                                                        prop = split[0],
                                                        inputId=prop[0] === 'p'?prop.replace('-', '_'):prop;
                                                    if((prop==='padding' && convertPaddings[bp][elementId].indexOf('padding_top')===-1) || (prop!=='padding' && convertPaddings[bp][elementId].indexOf(inputId)===-1)){
                                                        continue;
                                                    }
                                                    if (col === null) {
                                                        col = tmpIframe.contentWindow.document.querySelector(j);
                                                        if (col === null) {
                                                            continue;
                                                        }
                                                        var position=getComputedStyle(col).getPropertyValue('position');
                                                        if(position==='absolute' || position==='fixed'){
                                                            continue;
                                                        }
                                                        col.style.setProperty('padding', '0','important');
                                                        innerW = col.parentNode.getBoundingClientRect().width;
                                                        colW = col.getBoundingClientRect().width;
                                                    }
                                                    var v = parseFloat(((parseFloat(split[1]) / 100) * innerW) / colW) * 100;
                                                    v = parseFloat(parseFloat(v.toFixed(2))).toString() + '%';
                                                    css[bp][j][k] = prop + ':' + v + ';';
                                                    if (cssRules !== null) {
                                                        var foundRule = false;
                                                        for (var index = cssRules.length - 1; index > -1; --index) {
                                                            if (cssRules[index].selectorText === j && cssRules[index].style.getPropertyValue(prop)) {
                                                                cssRules[index].style.setProperty(prop, v);
                                                                foundRule = true;
                                                                break;
                                                            }
                                                        }
                                                        if (foundRule === false) {
                                                            sheet.insertRule(j + '{' + css[bp][j][k] + '}', cssRules.length);
                                                        }
                                                    }
                                                    if (convert[elementId] === und) {
                                                        convert[elementId] = {};
                                                    }
                                                    if (convert[elementId][bp] === und) {
                                                        convert[elementId][bp] = {};
                                                    }
                                                    v = v.replace('%', '');
                                                    if (prop === 'padding') {
                                                        convert[elementId][bp][inputId + '_top'] = v;
                                                        convert[elementId][bp][inputId + '_top_unit'] = '%';
                                                    } else {
                                                        convert[elementId][bp][inputId] = v;
                                                        convert[elementId][bp][inputId + '_unit'] = '%';
                                                    }
                                                    
                                                    //duplicate css values in breakpoints,because if we have mobile 5% and tablet 5%, after converting they can be different value,because the width are different in mobile and tablet
                                                    if (bp !== 'mobile') {
                                                        var childBp=breakpoints[i - 1],
                                                            childCss = css[childBp],
                                                                found = false;
                                                        if (childCss && childCss[j]) {
                                                            var isPadding = prop.indexOf('padding') !== -1;
                                                            for (var k2 = childCss[j].length - 1; k2 > -1; --k2) {
                                                                var value2 = childCss[j][k2];
                                                                if ((isPadding === true && value2.indexOf('padding') === 0) || (isPadding === false && value2.indexOf('margin') === 0)) {
                                                                    var prop2 = value2.split(':')[0];
                                                                    if (prop2 === prop || (isPadding === true && prop2 === 'padding') || (isPadding === false && prop2 === 'margin')) {
                                                                        found = true;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        if (found === false) {
                                                            if (childCss === und) {
                                                                childCss = {};
                                                            }
                                                            if (childCss[j] === und) {
                                                                childCss[j] = [];
                                                            }
                                                            childCss[j].push(value);
                                                            
                                                            if(convertPaddings[childBp]===und){
                                                                convertPaddings[childBp]={};
                                                            }
                                                            if(convertPaddings[childBp][elementId]===und){
                                                                convertPaddings[childBp][elementId]=[];
                                                            }
                                                            convertPaddings[childBp][elementId].push(inputId);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (item !== null) {
                        item.style.visibility = item.style.opacity = '';
                        item.classList.remove('tb_generate_css');
                    }
                    var xhr = new XMLHttpRequest(),
                            data = {
                                css: JSON.stringify(ThemifyStyles.optimizeCss(css, true)),
                                action: 'tb_generate_on_fly',
                                nonce: ThemifyBuilderStyle.nonce,
                                bid: id
                            },
                            body = '';
                    if (typeof win[bkey].custom_css !== 'undefined') {
                        data.custom_css = win[bkey].custom_css;
                    }
                    for (i in data) {
                        if (body !== '') {
                            body += '&';
                        }
                        body += encodeURIComponent(i) + '=' + encodeURIComponent(data[i]);
                    }
                    data = null;
                    xhr.open('POST', ThemifyBuilderStyle.ajaxurl);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    xhr.send(body);

                    if (Object.keys(convert).length > 0) {
                        body = '';
                        data = {
                            data: JSON.stringify(convert),
                            action: 'tb_update_old_data',
                            nonce: ThemifyBuilderStyle.nonce,
                            bid: id
                        };
                        var xhr2 = new XMLHttpRequest();
                        for (i in data) {
                            if (body !== '') {
                                body += '&';
                            }
                            body += encodeURIComponent(i) + '=' + encodeURIComponent(data[i]);
                        }
                        data = null;
                        xhr2.open('POST', ThemifyBuilderStyle.ajaxurl);
                        xhr2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                        xhr2.send(body);
                    }
                    
                }
            };
            if (tmpIframe) {
                tmpIframe.parentNode.removeChild(tmpIframe);
                tmpIframe = null;
            }
            ThemifyStyles.isWorking=false;
        };
        function duplicatePage() {//Only need to convert old percent of padding(for columns) to new version with grid
            if (win.parent === win.self) {
                var allConverted = true,
                    colPaddingsIds=['padding_top','padding_bottom','padding_left','padding_right','margin-bottom','margin-top'],
                    paddingLen=colPaddingsIds.length,
                    points = ThemifyStyles.breakpointsReverse,
                    bpLength = points.length,
                    check=function(styling){
                        for(var i=paddingLen-1;i>-1;--i){
                            if(styling[colPaddingsIds[i]+'_unit']==='%' && styling[colPaddingsIds[i]] && styling[colPaddingsIds[i]].toString().indexOf(',')===-1){ 
                                return true;
                            }
                            for (var j = bpLength - 2; j>-1; --j) {
                                if(styling['breakpoint_'+points[j]]){
                                    var p=styling['breakpoint_'+points[j]][colPaddingsIds[i]];
                                    if(p!=='' && p!==und && p.toString().indexOf(',')===-1 && ThemifyStyles.getStyleVal(colPaddingsIds[i]+'_unit',styling,points[j])==='%'){
                                        return true;
                                    }
                                }
                            }
                        }
                    };
                    for (var k in win) { //check if there is any old data,if no there is no need to duplicate page
                        if(allConverted===false){
                            break;
                        }
                        if (k.indexOf('themify_builder_data_') === 0 && win[k]) {
                            var data=win[k].data;
                            for (var i=data.length-1;i>-1;--i) {
                                if(allConverted===false){
                                    break;
                                }
                                var row = data[i];
                                if (row.cols !== und) {
                                    for (var j in row.cols) {
                                        if(allConverted===false){
                                            break;
                                        }
                                        var col = row.cols[j];
                                        if (col.styling && check(col.styling)) {
                                           allConverted=false;
                                           break;
                                        }
                                        if (col.modules !== und) {
                                            for (var m in col.modules) {
                                                if(allConverted===false){
                                                    break;
                                                }
                                                var mod = col.modules[m];
                                                if (mod && mod.cols !== und) {
                                                   for(var n in mod.cols){
                                                        var subcol = mod.cols[n];
                                                        if (subcol.styling && check(subcol.styling)) {
                                                           allConverted=false;
                                                           break;
                                                        }
                                                   }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                if (allConverted === false) { 
                    ThemifyStyles.isWorking=true;
                    tmpIframe = doc.createElement('iframe');
                    tmpIframe.id = 'tb_regenerate_css_iframe';
                    tmpIframe.style.setProperty('position', 'fixed','important');
					tmpIframe.style.setProperty('top', '-100000000px','important');
					tmpIframe.style.setProperty('left', '-100000000px','important');
					tmpIframe.style.setProperty('visibility', 'hidden','important');
					tmpIframe.style.setProperty('min-width', 'auto','important');
					tmpIframe.style.setProperty('max-height', 'none','important');
					tmpIframe.style.setProperty('min-height', 'auto','important');
					tmpIframe.style.setProperty('contain', 'none','important');
					tmpIframe.style.setProperty('width', '100%','important');
					tmpIframe.style.setProperty('height', '100%','important');
					tmpIframe.style.setProperty('opacity', '0','important');
                    tmpIframe.src = 'about:blank';
                    doc.body.appendChild(tmpIframe);
                    var iframeW = tmpIframe.contentWindow.document;
                    iframeW.open();
                    iframeW.write('<!DOCTYPE html>'+doc.documentElement.outerHTML);
                    tmpIframe.tfOn('load', function () {
                        iframeW.tfId('tb_regenerate_css_iframe').remove();
                        iframeW = null;
                        Regenerate();
                    }, {passive: true, once: true});
                    iframeW.close();
                } else {
                    Regenerate();
                }
            }
        }
		if (doc.readyState === 'complete') {
			duplicatePage();
		}
		else{
			win.tfOn('load', duplicatePage, {passive: true, once: true});
		}
        doc.tfOn('tb_regenerate_css', duplicatePage, {passive: true});
    }
    else if (win.themifyBuilder !== und) {
            var fonts;
            if (themifyBuilder.google !== und && Array.isArray(themifyBuilder.google)) {
                fonts = themifyBuilder.google;
                themifyBuilder.google = {};
                for (var i = fonts.length - 1; i > -1; --i) {
                    if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                        themifyBuilder.google[fonts[i].value] = {n: fonts[i].name, v: fonts[i].variant};
                    }
                }
            }
            if (themifyBuilder.cf !== und && Array.isArray(themifyBuilder.cf)) {
                fonts = themifyBuilder.cf;
                themifyBuilder.cf = {};
                for (var i = fonts.length - 1; i > -1; --i) {
                    if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                        themifyBuilder.cf[fonts[i].value] = {n: fonts[i].name, v: fonts[i].variant};
                    }
                }
            }
    }
})(window, document,undefined);
