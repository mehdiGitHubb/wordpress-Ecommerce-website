((api,doc,Themify,topWindow,und)=>{
    'use strict';

    api.undoManager = {
        isWorking: false,
        isDisabled:false,
        stack:[],
        state:new Map(),
        index:-1,
        btnUndo:null,
        btnRedo:null,
        compactBtn:null,
        cid:null,
        type:und,
        init() {
            this.btnUndo = api.ToolBar.el.tfClass('undo')[0];
            this.btnRedo = api.ToolBar.el.tfClass('redo')[0];
            this.compactBtn = api.ToolBar.el.tfClass('compact_undo')[0];
	    
            api.ToolBar.el.tfClass('menu_undo')[0].tfOn(Themify.click,e=>{
                if(e.target!==this.compactBtn){
                    e.preventDefault();
                    e.stopPropagation();
                    this.doChange(e);
                }
            });	
            if (!Themify.isTouch && !themifyBuilder.disableShortcuts) {
                topWindow.document.tfOn('keydown',e=>{
                    this.keypres(e);
                });
                if (api.mode === 'visual') {
                    doc.tfOn('keydown',e=>{
                        this.keypres(e);
                    });
                }
            }
        },
        start(type,cid){
            if(this.has(type)===true){
                console.warn('UndoManager:'+type+' is already started');
                return false;
            };
            this.type=type;
            this.cid=cid;
            this.state.set(type,this.getCurrentState(type,cid));
        },
        end(type){
            if(!type){
                type=this.type;
            }
            if(this.has(type)===false){
                console.warn('UndoManager:'+type+' isn`t started');
                return false;
            }
            Themify.trigger('tb_undo_add',type);
            const diff=this.getDiff(type,this.getState(type),this.getCurrentState(type,this.cid));
            if(Object.keys(diff).length>0){
                this.push(diff);
            }
            this.state.delete(type);
            this.type=this.cid=null;
        },
        getState(type){
            return this.state.get(type);
        },
        has(type){
            return !!this.state.has(type);
        },
        clear(type){
            if(type===this.type){
                this.type=null;
            }
            this.state.delete(type);
            this.cid=null;
        },
        
        hasRedo() {
            return this.index < (this.stack.length - 1);
        },
        hasUndo() {
            return this.index>-1;
        },
        disable() {
            this.isDisabled=true;
            this.btnUndo.classList.add('disabled');
            this.btnRedo.classList.add('disabled');
            this.compactBtn.classList.add('disabled');
        },
        enable() {
            this.isDisabled=false;
            this.updateUndoBtns();
        },
        update(is_undo){
            if (is_undo===true) {
                --this.index;
            } else {
                ++this.index;
            }
            this.updateUndoBtns();
            api.pageBreakModule.countModules();
        },
        updateUndoBtns() {
            if(this.isDisabled!==true){
                const undo = this.hasUndo(),
                        redo = this.hasRedo();
                this.btnUndo.classList.toggle('disabled', !undo);
                this.btnRedo.classList.toggle('disabled', !redo);
                this.compactBtn.classList.toggle('disabled', !(undo || redo));
            }
        },
        reset() {
            this.stack = [];
            this.state.clear();
            this.index = -1;
            this.updateUndoBtns();
        },
        doChange(e) {
            if (this.isWorking === false && this.isDisabled===false) {
                this.isWorking = true;
                const target=e.target.closest('.undo_redo');
                if(target!==null && !target.classList.contains('disabled')){
                    this.changes(target.classList.contains('undo'));
                }
                this.isWorking = false;
            }
        },
        getCurrentState(type){
            const styles={},
                result={},
                breakpoints=api.breakpointsReverse;
                for(let i=breakpoints.length-1;i>-1;--i){
                    let bp=breakpoints[i],
                        rules=ThemifyStyles.getSheet(bp).cssRules,
                        gsRules=ThemifyStyles.getSheet(bp,true).cssRules;
                    styles[bp]={st:{},gs:{}};
                    for(let j=rules.length-1;j>-1;--j){
                        styles[bp].st[rules[j].selectorText]=rules[j].style.cssText;
                    }
                    for(let j=gsRules.length-1;j>-1;--j){
                        styles[bp].gs[gsRules[j].selectorText]=gsRules[j].style.cssText;
                    }
                }
            const models=Array.from(api.Registry.items),
                map=new Map();
            for(let i=models.length-1;i>-1;--i){
                map.set(models[i][0], api.Helper.cloneObject(models[i][1].fields));
            }
            if(type!=='style'){
                result.builder=api.Builder.get().el.innerHTML;
            }
            result.style=styles;  
            result.model=map;
            return result;
        },
        getDiff(type,oldState,newState){
            //start diff changes
        
           let domChanges=[];
           if(type!=='style'){
                     
               //compare html
                let oldBuilder=doc.createElement('template'),
                    currentBuilder=doc.createElement('template'),
                    movedRowCid;
                oldBuilder.innerHTML='<div>'+oldState.builder.replaceAll('class=\"\"','').replaceAll('style=\"\"','').replace(/class="\s*?"/,'').replace(/style="\s*?"/,'')+'</div>';
                currentBuilder.innerHTML='<div>'+newState.builder.replaceAll('class=\"\"','').replaceAll('style=\"\"','').replace(/class="\s*?"/,'').replace(/style="\s*?"/,'')+'</div>';
                
              
                if(type==='move'){//check if row sort
                    const movedRow=oldBuilder.content.querySelector('.tb_draggable_item');
                    if(movedRow!==null && movedRow.classList.contains('module_row')){
                       movedRowCid= movedRow.dataset.cid;
                    }
                }
                //remove UI elements
                oldBuilder=api.Helper.cloneDom(oldBuilder.content.firstChild,true);
                currentBuilder= api.Helper.cloneDom(currentBuilder.content.firstChild,true);
                let removeItems=oldBuilder.querySelectorAll('#tb_last_row_add_btn,.tb_dragger,.tb_backstretch');
                
                for(let i=removeItems.length-1;i>-1;--i){
                    removeItems[i].remove();
                }
                removeItems=currentBuilder.querySelectorAll('#tb_last_row_add_btn,.tb_dragger,.tb_backstretch');
                for(let i=removeItems.length-1;i>-1;--i){
                    removeItems[i].remove();
                }
                removeItems=null;
                
                for(let childs=oldBuilder.children,i=childs.length-1;i>-1;--i){
                    let cid=childs[i].getAttribute('data-cid');
                    if(!cid){
                        continue;
                    }
                    let item=currentBuilder.querySelector('.tb_'+cid);
                    if(item!==null){//element is removed or id has been changed(e.g on copy/paste)
                        childs[i].style.backgroundPosition=childs[i].style.backgroundSize=item.style.backgroundPosition=item.style.backgroundSize='';
                        if(!childs[i].getAttribute('style')){
                            childs[i].removeAttribute('style');
                        }
                        if(!item.getAttribute('style')){
                            item.removeAttribute('style');
                        }
                        if(!childs[i].isEqualNode(item)){//row checking
                            //find deep differents
                            if(item.childElementCount===childs[i].childElementCount){
                                let oldChildren=childs[i].children,
                                    currentChildren=item.children,
                                    isDiff=false;
                                    for(let j=oldChildren.length-1;j>-1;--j){
                                        if(!oldChildren[j].classList.contains('row_inner') && !oldChildren[j].isEqualNode(currentChildren[j])){
                                            isDiff=true;
                                            break;
                                        }
                                    }
                                    if(isDiff===false){
                                        let oldInner=childs[i].querySelector('.row_inner'),
                                            newInner=item.querySelector('.row_inner');
                                        if(newInner.childElementCount===oldInner.childElementCount && newInner.cloneNode(false).isEqualNode(oldInner.cloneNode(false))){
                                            let oldCols=oldInner.children,
                                                newCols=newInner.children,
                                                diffItems=[];
                                            for(let j=oldCols.length-1;j>-1;--j){
                                                if(!oldCols[j].isEqualNode(newCols[j])){

                                                    if(oldCols[j].childElementCount!==newCols[j].childElementCount || !oldCols[j].cloneNode(false).isEqualNode(newCols[j].cloneNode(false))){
                                                        diffItems.push({old:oldCols[j],new:newCols[j]});
                                                    }
                                                    else{
                                                        let oldColsChildren=oldCols[j].children,
                                                            newColsChildren=newCols[j].children;
                                                        for(let k=oldColsChildren.length-1;k>-1;--k){
                                                            if(!oldColsChildren[k].classList.contains('tb_holder') && !oldColsChildren[k].isEqualNode(newColsChildren[k])){
                                                                diffItems.push({old:oldCols[j],new:newCols[j]});
                                                                isDiff=true;
                                                                break;
                                                            }
                                                        }
                                                        if(isDiff===false){
                                                            let oldHolder=oldCols[j].tfClass('tb_holder')[0],
                                                                newHolder=newCols[j].tfClass('tb_holder')[0];
                                                            if(oldHolder.childElementCount===newHolder.childElementCount){
                                                                let oldModules=oldHolder.children,
                                                                    newModules=newHolder.children;
                                                               
                                                                for(let k=oldModules.length-1;k>-1;--k){
                                                                    if(!oldModules[k].isEqualNode(newModules[k])){

                                                                        if(oldModules[k].childElementCount!==newModules[k].childElementCount || !oldModules[k].cloneNode(false).isEqualNode(newModules[k].cloneNode(false))){
                                                                            diffItems.push({old:oldCols[j],new:newCols[j]});
                                                                            break;
                                                                        }
                                                                        else if(oldModules[k].classList.contains('active_subrow')){
                                                                            let oldSubRow=oldModules[k].tfClass('module_subrow')[0],
                                                                                newSubRow=newModules[k].tfClass('module_subrow')[0];

                                                                            if(oldSubRow.childElementCount!==newSubRow.childElementCount || !oldSubRow.cloneNode(false).isEqualNode(newSubRow.cloneNode(false))){
                                                                                diffItems.push({old:oldModules[k],new:newModules[k]});
                                                                            }
                                                                            else{
                                                                                let oldSubRowChildren=oldSubRow.children,
                                                                                    newSubRowChildren=newSubRow.children;
                                                                                for(let m=oldSubRowChildren.length-1;m>-1;--m){
                                                                                    if(!oldSubRowChildren[m].classList.contains('subrow_inner') && !oldSubRowChildren[m].isEqualNode(newSubRowChildren[m])){
                                                                                        diffItems.push({old:oldModules[k],new:newModules[k]});
                                                                                        isDiff=true;
                                                                                        break;
                                                                                    }
                                                                                }
                                                                                if(isDiff===false){
                                                                                    let oldSubRowInner=oldSubRow.tfClass('subrow_inner')[0],
                                                                                        newSubRowInner=newSubRow.tfClass('subrow_inner')[0];
                                                                                    if(oldSubRowInner.childElementCount!==newSubRowInner.childElementCount || !oldSubRowInner.cloneNode(false).isEqualNode(newSubRowInner.cloneNode(false))){
                                                                                        diffItems.push({old:oldModules[k],new:newModules[k]});
                                                                                    }
                                                                                    else{
                                                                                        let oldSubCols=oldSubRowInner.children,
                                                                                            newSubCols=newSubRowInner.children;

                                                                                        for(let m=oldSubCols.length-1;m>-1;--m){
                                                                                            if(!oldSubCols[m].isEqualNode(newSubCols[m])){
                                                                                                if(oldSubCols[m].childElementCount!==newSubCols[m].childElementCount || !oldSubCols[m].cloneNode(false).isEqualNode(newSubCols[m].cloneNode(false))){
                                                                                                    diffItems.push({old:oldSubCols[m],new:newSubCols[m]});
                                                                                                }
                                                                                                else{
                                                                                                    let oldSubColsChildren=oldSubCols[m].children,
                                                                                                       newSubColsChildren=newSubCols[m].children;
                                                                                                     for(let n=oldSubColsChildren.length-1;n>-1;--n){
                                                                                                        if(!oldSubColsChildren[n].classList.contains('tb_holder') && !oldSubColsChildren[n].isEqualNode(newSubColsChildren[n])){
                                                                                                            diffItems.push({old:oldSubCols[m],new:newSubCols[m]});
                                                                                                            isDiff=true;
                                                                                                            break;
                                                                                                        }
                                                                                                    }
                                                                                                    if(isDiff===false){
                                                                                                        let oldSubRowHolder=oldSubCols[m].tfClass('tb_holder')[0],
                                                                                                            newSubRowHolder=newSubCols[m].tfClass('tb_holder')[0];
                                                                                                        if(oldSubRowHolder.childElementCount===newSubRowHolder.childElementCount){
                                                                                                            let oldSubModules=oldSubRowHolder.children,
                                                                                                                newSubModules=newSubRowHolder.children;
                                                                                                                for(let n=oldSubModules.length-1;n>-1;--n){
                                                                                                                    if(!oldSubModules[n].isEqualNode(newSubModules[n])){
                                                                                                                        diffItems.push({old:oldSubCols[m],new:newSubCols[m]});
                                                                                                                        break;
                                                                                                                    }
                                                                                                                }
                                                                                                        }
                                                                                                        else{
                                                                                                            diffItems.push({old:oldSubCols[m],new:newSubCols[m]});
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                        else{
                                                                            let oldModuleChildren=oldModules[k].children,
                                                                                newModuleChildren=newModules[k].children;
                                                                            for(let m=oldModuleChildren.length-1;m>-1;--m){
                                                                                if(!oldModuleChildren[m].isEqualNode(newModuleChildren[m])){
                                                                                    diffItems.push({old:oldModules[k],new:newModules[k]});
                                                                                    break;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            else{
                                                                diffItems.push({old:oldCols[j],new:newCols[j]});
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            if(diffItems.length>0){
                                                for(let j=diffItems.length-1;j>-1;--j){
                                                    domChanges.push(diffItems[j]);
                                                }
                                                continue;
                                            }
                                        }
                                }
                            }
                        }
                        else{
                            if(cid===movedRowCid){
                                let index=Themify.convert(currentBuilder.children).indexOf(item);
                                if(index!==-1 && index!==i){//nodes are the same,but maybe the positions have been changed?
                                    let oldPrevRow=childs[i].previousElementSibling?childs[i].previousElementSibling.getAttribute('data-cid'):0,
                                        newPrevRow=item.previousElementSibling?item.previousElementSibling.getAttribute('data-cid'):0;
                                    domChanges.push({old:oldPrevRow,new:newPrevRow,cid:cid,type:'row_sort'});
                                }
                            }
                            continue;
                        }
                    }
                    if(item){
                        domChanges.push({new:item,old:childs[i]});
                    }
                    else{
                        let prevCid=childs[i].previousElementSibling?childs[i].previousElementSibling.getAttribute('data-cid'):0;//maybe row deleted?
                        domChanges.push({old:childs[i],prevCid:prevCid});
                    }
                }
                for(let childs=currentBuilder.children,i=childs.length-1;i>-1;--i){//check new elements or id changed elements
                    let cid=childs[i].getAttribute('data-cid');
                    if(oldBuilder.querySelector('.tb_'+cid)===null){
                        let oldCid=childs[i].getAttribute('data-old-cid'),
                            found=false;
                        if(oldCid){
                            childs[i].removeAttribute('data-old-cid');
                            for(let j=domChanges.length-1;j>-1;--j){
                                if(domChanges[j].old!==und && domChanges[j].old.getAttribute('data-cid')===oldCid){
                                    domChanges[j].new=childs[i];
                                    found=true;
                                    break;
                                }
                            }
                        }
                        if(found===false){
                            let newItem={new:childs[i]};
                            if(childs[i].classList.contains('module_row')){
                                newItem.prevCid=childs[i].previousElementSibling?childs[i].previousElementSibling.getAttribute('data-cid'):0;
                            }
                            domChanges.push(newItem);
                        }
                    }
                }
                
                oldBuilder=currentBuilder=null;
            }
            //compare models
            const modelChanges=new Map(),
                newModel=newState.model,
                oldModel=oldState.model;
            for(let [k,oldFields] of oldModel){
                let newFields=newModel.get(k);
                if(newFields){ 
                    if(api.Helper.compareObject(oldFields,newFields)){
                        modelChanges.set(k,{old:oldFields,new:newFields});
                    }
                    newModel.delete(k);
                }
                else{
                    modelChanges.set(k,{old:oldFields});
                }
                oldModel.delete(k);
            }
            for(let [k,v] of newModel){
                modelChanges.set(k,{new:v});
            }
            let oldStyles=oldState.style,
                currentStyles=newState.style,
                stylesChanges={},
                parseCssText=cssText=>{
                    cssText=cssText.split('; ');
                    const res={};
                    for(let i=cssText.length-1;i>-1;--i){
                        let index = cssText[i].indexOf(':'),
                            prop = cssText[i].substring(0, index);
                        res[prop]=cssText[i].substring(index + 1).trim();
                        let len=res[prop].length;
                        if (res[prop][len - 1] === ';') {
                            res[prop] = res[prop].slice(0, -1);
                        }
                        else if(res[prop][len - 1]==='"' && res[prop][len - 2]===';'){
                            let index=len - 2;
                            res[prop]=res[prop].substring(0, index)+res[prop].substring(index+1);
                        }
                    }
                    return res;
                },
                diffStyles=(oldStyles,newStyles)=>{
                    let diff={old:{},new:{}};
                    for(let sel in oldStyles){//check changes
                        if(newStyles[sel]!==und){
                            if(newStyles[sel]!==oldStyles[sel]){
                                let oldCss=parseCssText(oldStyles[sel]),
                                    newCss= parseCssText(newStyles[sel]);
                                
                                for(let prop in oldCss){//check props changes
                                    if(newCss[prop]!==oldCss[prop]){
                                        let oldV=oldCss[prop].trim(),
                                            newV=newCss[prop];
                                      
                                        newV=newV!==und?newV.trim():'';
                                        if(newV!==oldV){
                                            if(diff.old[sel]===und){
                                                diff.old[sel]={};
                                            }
                                            if(diff.new[sel]===und){
                                                diff.new[sel]={};
                                            }
                                            diff.old[sel][prop]=oldV;
                                            diff.new[sel][prop]=newV;
                                        }
                                    }
                                }
                                for(let prop in newCss){//new props
                                    if(oldCss[prop]===und){
                                        let newV=newCss[prop].trim();
                                        if(diff.old[sel]===und){
                                            diff.old[sel]={};
                                        }
                                        if(diff.new[sel]===und){
                                            diff.new[sel]={};
                                        }
                                        diff.old[sel][prop]='';
                                        diff.new[sel][prop]=newV;
                                    }
                                }
                                
                            }
                        }
                        else{
                            diff.old[sel]=parseCssText(oldStyles[sel]);
                            diff.new[sel]='';
                        }
                    }
                     
                    for(let sel in newStyles){//new selectors
                        if(oldStyles[sel]===und){
                            diff.new[sel]=parseCssText(newStyles[sel]);
                            diff.old[sel]='';
                        }
                    }   
                    if(Object.keys(diff.old).length===0){
                        delete diff.old;
                    }
                    if(Object.keys(diff.new).length===0){
                        delete diff.new;
                    }
                    return diff;
                };
                
                for(let bp in oldStyles){
                    if(currentStyles[bp]!==und){
                        let stChanges=diffStyles(oldStyles[bp].st,currentStyles[bp].st),
                            gsChanges=diffStyles(oldStyles[bp].gs,currentStyles[bp].gs);
                        if(Object.keys(stChanges).length>0){
                            stylesChanges[bp]={st:stChanges};
                        }
                        if(Object.keys(gsChanges).length>0){
                            stylesChanges[bp]={gs:gsChanges};
                        }
                    }
                }
                for(let bp in currentStyles){//new breakpoints
                    if(oldStyles[bp]===und){
                        let stChanges=diffStyles({},currentStyles[bp].st),
                            gsChanges=diffStyles({},currentStyles[bp].gs);
                        if(Object.keys(stChanges).length>0){
                            stylesChanges[bp]={st:stChanges};
                        }
                        if(Object.keys(gsChanges).length>0){
                            stylesChanges[bp]={gs:gsChanges};
                        }
                    }
                }
            newState=currentStyles=oldStyles=null;
            const data={};
            if(Object.keys(stylesChanges).length>0){
                data.styles=stylesChanges;
            }
            if(modelChanges.size>0){
                data.model=modelChanges;
            }
            if(type!=='style' && domChanges.length>0){
                data.html=domChanges;
            }
             if(Object.keys(data).length>0){
                data.type=type;
             }
            return data;
        },
        push(data) {
            api.editing = false;
            this.stack.splice(this.index + 1, this.stack.length - this.index);
            this.stack.push(data);
            this.index = this.stack.length - 1;
            this.updateUndoBtns();
            Themify.trigger('add_undo');
            api.Builder.get().isSaved=false;
        },
        keypres(e) {
            if (this.isWorking === false &&this.isDisabled===false && (true === e.ctrlKey || true === e.metaKey)){
                const activeTag = doc.activeElement.tagName,
                        topActiveTag = topWindow.document.activeElement.tagName,
                        key = e.code;
                if (activeTag !== 'INPUT' && activeTag !== 'TEXTAREA' && topActiveTag !== 'INPUT' && topActiveTag !== 'TEXTAREA') {
                    if ('KeyY' === key || ('KeyZ' === key && true === e.shiftKey)) {// Redo
                        e.preventDefault();
                        if (this.hasRedo()) {
                            this.changes(false);
                        }
                    } 
                    else if ('KeyZ' === key) { // UNDO
                        e.preventDefault();
                        if (this.hasUndo()) {
                            this.changes(true);
                        }
                    }
                }
            }
        },
        changes(is_undo) {
            
            api.ActionBar.clearClicked();
            if (api.activeModel !== null && (api.mode !== 'visual' || (!doc.activeElement.contentEditable && api.activeModel.contains(doc.activeElement)))) {
                api.LightBox.save().then(()=>{
                        this.changes(true);
                    })
                    .catch(e=>{
                        
                    });
                    return;
            }
            const index = is_undo===true ? 0 : 1,
                stack = this.stack[this.index + index];
                
            if (stack !== und) {
                
                const type=is_undo===true?'old':'new';
                if(stack.styles){
                    this.styleChanges(stack.styles,type,!stack.html);
                }
                if(stack.html){
                    this.domChanges(stack.html,type,stack.type);
                }
                if(stack.model){
                    this.modelChanges(stack.model,type);
                }
                this.update(is_undo);
            }
        },
        styleChanges(styles,mode,runJs){
            //replace styles
            
            const selectors=new Set();
            for(let bp in styles){
                for(let k in styles[bp]){
                    let sheet=ThemifyStyles.getSheet(bp,k==='gs'),
                        rules=sheet.cssRules;
                    for(let sel in styles[bp][k][mode]){
                        let vals=styles[bp][k][mode][sel],
                        index=api.Utils.findCssRule(rules, sel);
                        if(vals===''){
                            if(index !== false && rules[index]!==und){
                                sheet.deleteRule(index);
                            }
                        }
                        else{
                            if(index === false || rules[index]===und){
                                let cssText='';
                                for(let prop in vals){
                                    cssText+=prop + ':' + vals[prop] + ';';
                                }
                                sheet.insertRule(sel + '{' + cssText + ';}', rules.length);
                            }
                            else{
                                for(let prop in vals){
                                    let val=vals[prop].trim(),
                                        priority = val !== '' && val.indexOf('!important') !== -1 ? 'important' : '';
                                    if (priority !== '') {
                                        val = val.replace('!important', '').trim();
                                    }
                                    rules[index].style.setProperty(prop, val,priority);
                                }
                            }
                        }
                        if(runJs===true){
                            selectors.add(sel);
                        }
                    }
                }
            }
            
            if(selectors.size>0){
                for(let sel of selectors){
                    let item=doc.querySelector(sel);
                    if(item){
                        api.Utils.runJs(item);
                    }
                }
            }
        },
        domChanges(arr,mode,type){
            try{
                const register=api.Registry,
                    builder=api.Builder.get().el;
                for(let i=arr.length-1;i>-1;--i){
                    if(arr[i][mode]!==und){
                        if(arr[i].type==='row_sort'){//row sort
                            let row=builder.querySelector('[data-cid="'+arr[i].cid+'"]');
                            if(!row){
                                continue;
                            }
                            let prevCid=arr[i][mode];
                            if(prevCid===0){
                                builder.prepend(row);
                            }
                            else{
                                let prevRow=builder.querySelector('[data-cid="'+prevCid+'"]');
                                if(prevRow){
                                    prevRow.after(row);
                                }
                            }
                        }
                        else{//others

                            let el=arr[i][mode],
                                id=el.dataset.cid,
                                currentEl=builder.querySelector('[data-cid="'+id+'"]');
                            if(currentEl===null){
                                //id has been changed,after an action(e.g copy/paste)
                                id=mode==='old' && arr[i].new!==und?arr[i].new.dataset.cid:(mode==='new' && arr[i].old!==und?arr[i].old.dataset.cid:null);
                                if(id!==null){
                                    currentEl=builder.querySelector('[data-cid="'+id+'"]');
                                }
                                if(currentEl===null && arr[i].prevCid!==und){//row is deleted
                                    currentEl = doc.createElement('div');
                                    if(arr[i].prevCid===0){
                                        builder.prepend(currentEl);
                                    }
                                    else{
                                        let prevElement=builder.querySelector('[data-cid="'+arr[i].prevCid+'"]');
                                        if(prevElement===und){
                                            continue;
                                        }
                                        prevElement.after(currentEl);
                                    }
                                }
                            }
                            if(currentEl!==und){
                                el=el.cloneNode(true);
                                let items=Themify.convert(el.querySelectorAll('[data-cid]'));
                                items.unshift(el);
                                for(let i=items.length-1;i>-1;--i){
                                    let cid=items[i].getAttribute('data-cid'),
                                    item=register.get(cid);
                                    if(item.type==='module' && items[i].tfClass('module')[0]===und){
                                        items[i].remove();
                                        continue;
                                    }
                                    item.el=items[i];
                                }
                                currentEl.replaceWith(el);
                                api.Utils.runJs(el);
                                Themify.trigger('undo', [el, mode==='old']);
                            }
                        }
                    }
                    else if((mode==='old' && arr[i].new!==und) || (mode==='new' && arr[i].old!==und)){//old doesn't exist new is exist,means need to delete the new element
                        let item=mode==='old'?arr[i].new:arr[i].old,
                            el=builder.querySelector('[data-cid="'+item.dataset.cid+'"]');
                        if(el!==null){
                            el.remove();
                        }
                    }
                }
            }
            catch(e){

            }
        },
        modelChanges(model,mode){
            const register=api.Registry;
            for(let [cid,v] of model){
                if(v[mode]!==und){
                    let m=register.get(cid);
                    if(m){
                        m.fields=api.Helper.cloneObject(v[mode]);
                    }
                }
            }  
        }
    };
	
    Themify.on('tb_toolbar_loaded',()=>{
        api.undoManager.init();
    },true,!!(api.ToolBar && api.ToolBar.isLoaded===true));
	
})(tb_app,document,Themify,window.top,undefined);