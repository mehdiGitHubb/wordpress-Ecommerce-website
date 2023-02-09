((api, Themify, doc)=> {
    'use strict';
    api.Drop =  {
        async row(drag, type,slug,scrollTo) {
            const rowDrop=async data=>{
                const fragment = doc.createDocumentFragment(),
                    rows = [],
                    styles = [],
                    isRow=drag.closest('.tb_holder')===null;

                for (let i = 0, len = data.length; i < len; ++i) {
                    let row = isRow===true?(new api.Row(data[i])):(new api.Subrow(data[i]));
                        fragment.appendChild(row.el);
                        rows.push(row);
                    if (api.mode === 'visual' && type!=='grid') {
                        let items = row.el.querySelectorAll('[data-cid]');
                        styles.push(row.id);
                        for (let j = items.length - 1; j > -1; --j) {
                            styles.push(items[j].dataset.cid);
                        }
                        row.el.style.visibility='hidden';
                    }
                }

                drag.replaceWith(fragment);
                api.Builder.get().removeLayoutButton();
                if (api.mode === 'visual') {
                    await api.bootstrap(styles);
                } 
                if(type!=='pagebreak'){
                    await api.correctColumnPaddings();
                    const points = api.breakpointsReverse,
                        builder=api.Builder.get().el;
                    for (let i = 0, len = rows.length; i < len; ++i) {
                        api.Utils.setCompactMode(rows[i].el.tfClass('module_column'));
                        api.Utils.runJs(rows[i].el);
                        rows[i].el.style.visibility='';
                    }
                }
                else{
                    api.pageBreakModule.countModules();
                }
                if(type!=='grid' && type!=='pagebreak'){
                    api.Spinner.showLoader('done');
                }
            };
            if(scrollTo!==false){
                api.Utils.scrollTo(drag);
            }
            if (type === 'library' || type==='predesign') {
                const data=type === 'library'?(await this.Library.row(slug)):(await api.preDesignedRows.get(slug));
                await rowDrop(data);
            } 
            else if (type==='pagebreak') {
                await rowDrop(api.pageBreakModule.get());
            } 
            else if (type==='grid') {
                await rowDrop(api.Utils.grid(slug));
            }
            else{
                throw '';
            }
        },
        /* "from" always exist,
         * "to" not always
         * if "to" doesn't from should be deleted
         * if from===to => "to" should be added
         */
        async column(from, to, side) {
            const from_inner = from.parentNode,
                isDelete = !to,
                isAdd=!from_inner,
                fromModel=isAdd===true?null:api.Registry.get(from_inner.closest('[data-cid]').dataset.cid),
                to_inner = to ? to.parentNode : from_inner,
                toModel=api.Registry.get(to_inner.closest('[data-cid]').dataset.cid),
                toData=toModel.get('sizes'),
                fromData=fromModel?fromModel.get('sizes'):{},
                oldArea = fromData.desktop_area?ThemifyStyles.normalizeArea(fromData.desktop_area):'',
                next = side === 'left' || !to ? to : to.nextElementSibling,
                fromGridArea = getComputedStyle(from).getPropertyValue('grid-area').split('/')[0].replace('"', '').trim();

            if (from_inner !== to_inner) {
                to_inner.insertBefore(from, next);
                const is_sub_row = to_inner.classList.contains('subrow_inner');
                from.classList.toggle('sub_column', is_sub_row);
                from.classList.toggle('tb-column', !is_sub_row);
                if (from_inner && !from_inner.firstChild) {
                    const col=new api.Column({grid_class: 'col-full'},from_inner.classList.contains('subrow_inner'));
                    from_inner.appendChild(col.el);
                }
            } 
            else if (isDelete === true) {
                from.remove();
            }
            const currentBp = api.activeBreakPoint,
                toCount = to_inner.childElementCount,
                fromCount = from_inner?from_inner.childElementCount:0,
                computed = getComputedStyle(to_inner),
                points = api.breakpointsReverse,
                toGridArea = isDelete === false && to ? getComputedStyle(to).getPropertyValue('grid-area').split('/')[0].replaceAll('"', '').trim() : null,
                parseRepeat = col => {
                    if (col.indexOf(' ') === -1) {
                        return computed.getPropertyValue('--c' + col);
                    }
                    col = col.replace(/\s\s+/g, ' ').trim();
                    if (col.indexOf('repeat') !== -1) {
                        if (col.indexOf('auto-fit') === -1 && col.indexOf('auto-fill') === -1) {
                            let tmp = '',
                                repeat = col.replace(/\s\,\s|\s\,|\,\s/g, ',').replace(/\s\(\s|\s\(|\(\s/g, '(').replaceAll(' )', ')').trim().split(' ');
                            for (let i = 0, len = repeat.length; i < len; ++i) {
                                if (repeat[i].indexOf('repeat') !== -1) {
                                    let item = repeat[i].split('(')[1].replace(')', '').split(','),
                                        count = parseInt(item[0]),
                                        unit = item[1].trim();
                                    if (isNaN(count)) {
                                        unit = '1fr';
                                        count = childCount;
                                    }
                                    tmp += ' ' + (' ' + unit).repeat(count);
                                } else {
                                    tmp += ' ' + repeat[i];
                                }
                            }
                            col = tmp.trim();
                        } else {
                            return '';
                        }
                    }
                    return col;
                },
                addColClasses = (grid, cols) => {//backward compatibility
                    const count = cols.length,
                        _COL_CLASSES=api.getColClass(),
                        _COL_CLASSES_VALUES=api.getColClassValues(),
                        colsClass = grid && grid.indexOf(' ')===-1 && _COL_CLASSES[grid] !== undefined ? _COL_CLASSES[grid] : _COL_CLASSES[count],
                        len = _COL_CLASSES_VALUES.length - 1;
                    for (let i = count - 1; i > -1; --i) {
                        let c=cols[i].classList;
                        for (let j = len; j > -1; --j) {
                            c.remove(_COL_CLASSES_VALUES[j]);
                        }
                        if (colsClass !== undefined && count < 7) {
                            c.add(colsClass[i]);
                        }
                         c.remove('first','last');
                    }
                    if(count>1){
                        cols[0].classList.add('first');
                        cols[count-1].classList.add('last');
                    }
                };

            /*we have 3 ways to drop column
             * 1. in the same row in the desktop mode
             * 2. in the same row in the responsive mode
             * 3. to different rows/subrows in desktop mode
             * dropping to different rows in responsive mode isn't allowed
            */

            //in the same row
            if (from_inner === to_inner && isDelete === false && isAdd===false) {//if it's change in the same row/subrow(only for responsive mode), just change the area order,because order is responsive

                if (currentBp === 'desktop') {//in desktop mode we need to move html and save the order in responsive mode
                    const oldcolsAreas = {},
                        newColsAreas = {},
                        desktopArea = computed.getPropertyValue('--area').replaceAll('"', '').trim().split(' '),
                        childs = from_inner.children,
                        len = childs.length;

                    from_inner.classList.remove('direction_rtl');
                    fromModel.setSizes({dir:''});

                    for (let i = len - 1; i > -1; --i) {//save old position
                        let cid = childs[i].dataset.cid;
                        if (cid) {
                            oldcolsAreas[cid] = (i + 1);
                        }
                    }
                    let desktopSize = computed.getPropertyValue('--col');
                    from_inner.insertBefore(from, next);//move call

                    if (desktopSize && desktopSize !== 'unset' && desktopSize !== 'initial' && desktopSize !== 'none' && desktopSize.indexOf('repeat') === -1) {//moving sizes

                        const oldDesktopSizeIndex = desktopArea.indexOf(fromGridArea),
                            newFromGridArea = getComputedStyle(from).getPropertyValue('grid-area').split('/')[0].replaceAll('"', '').trim(), //get new position(col1,col2 ...) after dropping
                            newIndex = desktopArea.indexOf(newFromGridArea);
                        desktopSize = desktopSize.split(' ');
                        const value = desktopSize[newIndex];
                        desktopSize[newIndex] = desktopSize[oldDesktopSizeIndex];
                        desktopSize[oldDesktopSizeIndex] = value;

                        fromModel.setCols( {size: desktopSize.join(' ')},  currentBp);
                    }
                    for (let i = len - 1; i > -1; --i) {//save new position
                        let cid = childs[i].dataset.cid;
                        if (cid) {
                            newColsAreas[cid] = (i + 1);
                        }
                    }
                    for (let i = points.length - 2; i > -1; --i) {
                        let bp = points[i],
                            respArea = fromData[bp+'_area'];
                        if (respArea) {
                            if (respArea.indexOf('"') === -1) {//is css variable
                                respArea = computed.getPropertyValue('--area' + respArea).replace(/\s\s+/g, ' ').trim();
                            }
                            for (let cid in newColsAreas) {
                                if (oldcolsAreas[cid] !== newColsAreas[cid]) {
                                    respArea = respArea
                                        .replaceAll(oldcolsAreas[cid] + ' ', '#' + newColsAreas[cid] + '# ')
                                        .replaceAll(oldcolsAreas[cid] + '"', '#' + newColsAreas[cid] + '#"');
                                }

                            }
                            fromModel.setCols( {area: respArea.replaceAll('#', '')},  bp);
                        }
                    }
                    addColClasses(desktopSize, childs);
                } else {

                    let area = computed.getPropertyValue('--area').replace(/  +/g, ' ').trim(), //e.g "col1 col1 col1 col2 col2 col2" "col3 col3 col4 col4 col5 col5"
                        col = computed.getPropertyValue('--col'),
                        colIndex = side === 'right' ? 1 : 0,
                        shift = 'left',
                        toColArea,
                        colsSize = area.split('" "')[0].split(' ').length;//save original col size for above example it's 6

                    area = area.replaceAll('"', '').trim().split(' ');//convert the matrix to single array, e,g "col1 col1 col1" "col2 col2 col2" "col3 col3"=> "col1 col1 col1 col2 col2 col2 col3 col3"
                    let droppIndex = area.indexOf(toGridArea),
                        firstIndex=area.indexOf(fromGridArea),
                        draggedIndex = firstIndex,
                        oldArea=Themify.convert(area),
                        len = area.length,
                        newArea = [];
                    if (draggedIndex < droppIndex) {
                        shift = 'right';
                        colIndex = side === 'right' ? 0 : -1;
                        draggedIndex = area.lastIndexOf(fromGridArea);
                    }
                    droppIndex += colIndex;
                    toColArea = area[droppIndex];

                    if (shift === 'left') {
                        for (let i = draggedIndex - 1; i >= droppIndex; --i) {
                            let currentCol = area[i],
                                replaceCol = area[i + 1];
                            if (currentCol !== replaceCol) {
                                for (let j = i; j < len; ++j) {
                                    if (area[j] === replaceCol) {
                                        area[j] = '_' + currentCol;
                                    }
                                }
                            }
                        }
                    } else {
                        for (let i = draggedIndex + 1; i <= droppIndex; ++i) {
                            let currentCol = area[i],
                                replaceCol = area[i - 1];
                            if (currentCol !== replaceCol) {
                                for (let j = 0; j < i; ++j) {
                                    if (area[j] === replaceCol) {
                                        area[j] = '_' + currentCol;
                                    }
                                }
                            }
                        }
                    }
                    for (let i = len - 1; i > -1; --i) {
                        if (area[i][0] === '_') {
                            area[i] = area[i].substring(1);
                        } else if (toColArea === area[i]) {
                            area[i] = fromGridArea;
                        }
                    }
                    for (let i = 0,len2=(len/colsSize); i < len2; ++i) {
                        newArea.push('"' + area.slice(i*colsSize,(i+1)*colsSize).join(' ') + '"');
                    }
                    newArea=newArea.join(' ');
                    const update={area: newArea};
                    if (col && col !== 'unset' && col !== 'initial' && col !== 'none') {//move resized col value only if movement the same grid rows(e.g "col1 col2" "col3 col4" save size when col2 moved to col1,don't save when col2 moved to col3/col4) 
                        const wasInRow=parseInt(firstIndex/colsSize),
                            indexAfter=newArea.replaceAll('"', '').trim().split(' ').indexOf(fromGridArea),
                            currentRow=parseInt(indexAfter/colsSize);
                        if(currentRow===wasInRow){//is the same 
                            col = parseRepeat(col);
                            if (col) {
                                col = col.split(' ');
                                const newSizes=[],
                                    newOrder=newArea.split('" "')[wasInRow].replaceAll('"', '').split(' ');
                                    oldArea=oldArea.slice(wasInRow*colsSize,(wasInRow*colsSize)+colsSize);//cut the grid row where the column is

                                for(let i=0,len=newOrder.length;i<len;++i){
                                    let index=oldArea.indexOf(newOrder[i]);
                                    newSizes[i]=col[index];
                                    oldArea.slice(index,1);
                                }
                                update.size=newSizes.join(' ');
                            }
                        }
                    }
                    toModel.setCols(update,  currentBp);
                }
            } 
            else {//to different row

                //desktop mode
                let fr='1fr';
                if(isAdd===false){  
                    let fromCss={},
                        fromUpdate={},
                        fromArea = [],
                        fromSize =  fromData.desktop_size,
                        fromColNumber = parseInt(fromGridArea.replace('col', ''));
                    ////in desktop mode the order is ALWAYS the same as document order,e.g col1 col2 col3 can't be col3 col1 col2
                    for (let j = 1; j <=(fromCount + 1); ++j) {
                        fromArea[j-1]=j;
                    }

                    if(fromSize && fromSize!=='1' && fromSize!==1){
                        fromSize = parseRepeat(fromSize);
                        if (fromSize) {
                            fromSize = fromSize.split(' ');
                        }
                    }
                    else{
                        fromSize=null;
                    }

                    //the maximum(css is using nth-child) column is removed=>make col4 to col3,col3 to col2 and etc.
                    for(let j=fromArea.length-1;j>-1;--j){
                        let index=parseInt(fromArea[j]);
                        if(index===fromColNumber){
                            if(fromSize && fromSize[j]!==undefined){
                                fr=fromSize[j];
                                fromSize.splice(j, 1);
                            }
                            fromArea.splice(j, 1);
                        }
                        else {
                            if(index>fromColNumber){
                                --index;
                            }
                            fromArea[j]='col'+index;
                        }
                    }

                    if(fr && fr!=='1fr'){//increase other columns sizes proportional
                        let frVal = parseFloat(fr),
                            count = 0;
                        if((frVal-1)>0.1){//if the diff is very small we don't need to do anything
                            for (let j = fromSize.length - 1; j > -1; --j) {
                                let v = parseFloat(fromSize[j]);
                                if ((frVal > 1 && v < 1) || (frVal < 1 && v > 1)) {
                                    ++count;
                                }
                            }
                            if (count > 0) {
                                let diff = parseFloat(frVal / count);
                                if (frVal < 1) {
                                    diff *= -1;
                                }
                                for (let j = fromSize.length - 1; j > -1; --j) {
                                    let v = parseFloat(fromSize[j]);
                                    if ((frVal > 1 && v < 1) || (frVal < 1 && v > 1)) {
                                        fromSize[j] = (v + diff) + 'fr';
                                    }
                                }
                            }
                        }
                    }

                    if(fromCount!==1 && fromSize){
                        //if there is grid with the same size use it instead of custom size(e.g "2.1fr 1fr" will be become to grid 2_1)
                        fromSize=ThemifyStyles.getColSize(fromSize.join(' '),false);
                    }
                    else{
                        fromSize=null;
                    }
                    addColClasses(fromSize, from_inner.children);
                    if(fromCount===1){
                        fromArea=null;
                        fromUpdate.gutter=fromCss['--area']=fromCss['--colG']='';
                    }
                    else{
                        fromArea = '"' + fromArea.join(' ') + '"';
                    }   
                    fromUpdate.area=fromArea;
                    fromUpdate.size=fromSize;
                    fromModel.setCols(fromUpdate);

                    if(!fromSize){
                        fromCss['--col']='';
                    }
                    //remove the last col css variable
                    fromModel.setGridCss(fromCss);

                    from_inner.classList.remove('tb_col_count_'+(fromCount+1));
                    from_inner.classList.add('tb_col_count_'+fromCount);
                }

                if(isDelete===false){
                    let toArea=[],
                        toCss={},
                        toSize =toData.desktop_size;
                    ////in desktop mode the order is ALWAYS the same as document order,e.g col1 col2 col3 can't be col3 col1 col2
                    for (let j = 1; j <(toCount+1); ++j) {
                        toArea[j-1]='col'+j;
                    }
                    if(!toSize && fr!=='1fr'){
                        toSize='1fr '.repeat(toCount).trim();
                    }
                    if (toSize) {//move resized size
                        toSize = parseRepeat(toSize);
                        if (toSize) {
                            toSize=toSize.split(' ');
                            let toColNumber=Themify.convert(to_inner.children).indexOf(from);//get new index in html,will be the same in area
                            toSize.splice(toColNumber, 0, fr);
                            toSize = ThemifyStyles.getColSize(toSize.join(' '),false);//if there is grid with the same size use it instead of custom size(e.g "2.2fr 1fr" will be become to grid 2_1)
                        }
                        else{
                            toSize=null;
                        }
                    }
                    if(!toSize){
                        toCss['--col']='';
                    }
                     addColClasses(toSize, to_inner.children);
                    //add the last col css variable
                    toArea = '"' + toArea.join(' ') + '"';
                    toModel.setCols({area: toArea, size: toSize});
                    toModel.setGridCss(toCss);
                    to_inner.classList.remove('tb_col_count_'+(toCount-1));
                    to_inner.classList.add('tb_col_count_'+toCount);
                    //set max gutter
                    toModel.setMaxGutter();
                }


                //reset responsive modes
                const changedPoints=new Set();
                for (let i = points.length - 2; i > -1; --i) {
                    //reseting to auto, if breakpoint has auto value select it otherwise the parent value should be applied
                    let bp = points[i];
                    if(isAdd===false){
                        let fromArea= fromModel.getGridCss({size:'auto'},bp);
                        if(fromModel.getSizes('size',bp)!=='auto'){
                            changedPoints.add(api.Helper.getBreakpointName(bp));
                        }
                        if(fromArea['--area'] && fromArea['--area'].indexOf(' ')===-1){//apply css and update data,if there is no auto grid should be inherted from parent breakpoint
                            fromModel.setCols({size: 'auto'},bp);
                        }
                        else{
                            fromModel.setGridCss({'--area':'','--col':''},bp);//reset css
                            fromModel.setSizes({size: 'auto'},bp);// update data
                        }
                    }
                    if(isDelete===false){
                        if(toModel.getSizes('size',bp)!=='auto'){
                            changedPoints.add(api.Helper.getBreakpointName(bp));
                        }
                        let toArea=toModel.getGridCss({size:'auto'},bp);
                        if(toArea['--area'] && toArea['--area'].indexOf(' ')===-1){//apply css and update data,if there is no auto grid should be inherted from parent breakpoint
                            toModel.setCols({size: 'auto'},bp);
                        }
                        else{
                            toModel.setGridCss({'--area':'','--col':''},bp);//reset css
                            toModel.setSizes({size: 'auto'},bp);// update data
                        }
                        toModel.setMaxGutter();
                    }
                }
                if(changedPoints.size>0){
              //      TF_Notification.showHide('tf',themifyBuilder.i18n.gridChanged.replace('%s',(Array.from(changedPoints)).join(', ')),5000);
                }
            }
            api.Utils._onResize(true);
        },
        async module(drag, type,slug,scrollTo) {
            if(scrollTo!==false){
                api.Utils.scrollTo(drag);
            }
            //drop a new modules
            if(type === 'part' || type === 'module'){
                await this.Library.module(drag, type,slug);
            }
            else{
                const _default=api.Module.getDefault(slug),
                    module = new api.Module({mod_settings:_default,mod_name:slug});
                module.is_new=true;
                drag.replaceWith(module.el);
                module.trigger('edit');
                if (api.mode === 'visual' && 'layout-part' !== slug && 'overlay-content' !== slug) {
                    await module.trigger(module.getPreviewType(), _default);
                }
                return module;
            }
        },
        Library:{
            async row(id){
                let row = await api.Library.get(id, 'row');
                if (!Array.isArray(row)) {
                    row = new Array(row);
                    // Attach used GS to data
                    const usedGS = api.GS.findUsedItems(row);
                    if (usedGS !== false && usedGS.length) {
                        row[0].used_gs = usedGS;
                    }
                }
                return row;
            },
            async module(drag,type,slug,scrollTo){
                const options=await api.Library.get(slug, type),
                    module = new api.Module(options),
                    settings = module.get('mod_settings');
                    if (api.mode === 'visual') {
						await api.bootstrap([module.id]);
					}
                    drag.replaceWith(module.el);
                    module.is_new=true;
                    await module.trigger('edit');
                    await module.trigger(module.getPreviewType(), settings);
                    if(scrollTo!==false){
                        api.Utils.scrollTo(module.el);
                    }
                    api.Builder.get().removeLayoutButton();
                    return module;
            }
        }
    };
})(tb_app, Themify, document);