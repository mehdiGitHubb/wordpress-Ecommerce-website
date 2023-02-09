(($,api,Themify,doc,und)=> {
    'use strict';
	let instance;
    const contactFormBuilder = function (selector, data) {
        this.$table = $(selector);
        this.init(data);
    };

    contactFormBuilder.prototype = {
        $table: null,
        app: null,
        init(self) {
            this.app = self;
            this.loadExtraFields(self.values);
            this.events();
            setTimeout(()=>{
                this.sortableFields();
            },1500);
        },
        loadOrders(data, extra) {

            let orders,
                add;
            try {
                orders = typeof data.field_order==='string'?JSON.parse(data.field_order):data.field_order;
            } catch (e) {
            }

            if (!orders) {
                orders = {};
            }
            const tbody = this.$table[0].tfTag('tbody')[0],
                    items = tbody.tfTag('tr'),
                    sorted = [],
                    fr = doc.createDocumentFragment();
            for (let i = 0, len = items.length; i < len; ++i) {
                if (!items[i].classList.contains('tb_no_sort')) {
                    sorted.push(items[i]);
                }
                else {
                    add = items[i];
                }
            }
            sorted.sort( (a, b)=> {
                let name1, name2, order1, order2,
                    is_extra1=a.classList.contains('tb_contact_new_row'),
                    is_extra2=b.classList.contains('tb_contact_new_row'),
                    getItem = v=>{
                        for (let i = extra.length - 1; i > -1; --i) {
                            if ((extra[i].label === v || extra[i].id === v)  && extra[i].order !== und) {
                                return extra[i].order;
                            }
                        }
                        return false;
                    };
                const a_el=is_extra1?a.tfClass('tb_new_field_textbox')[0]:a.tfClass('tb_lb_option')[0],
					b_el=is_extra2?b.tfClass('tb_new_field_textbox')[0]:b.tfClass('tb_lb_option')[0];
                if(is_extra1 && a_el.dataset.order){
                    order1 = a_el.dataset.order;
                }
				else{
                    name1 = is_extra1?a_el.value:a_el.id;
                    name1 = is_extra1 && '' === name1 ? a_el.dataset.id : name1;
                    name1 = name1.trim();
                    order1 = orders[name1] !== und ? orders[name1] : (is_extra1 ? getItem(name1) : false);
                }
                if(is_extra2 && b_el.dataset.order){
                    order2 = b_el.dataset.order;
                }else{
                    name2 = is_extra2?b_el.value:b_el.id;
                    name2 = is_extra2 && '' === name2 ? b_el.dataset.id : name2;
                    name2 = name2.trim();
                    order2 = orders[name2] !== und ? orders[name2] : (is_extra2 ? getItem(name2) : false);
                }
                return order1 - order2;
            });

            for (let i = 0, len = sorted.length; i < len; ++i) {
                fr.appendChild(sorted[i]);
            }
            fr.appendChild(add);
            while (tbody.firstChild) {
                tbody.lastChild.remove();
            }
            tbody.appendChild(fr);
        },
        loadExtraFields(data) {
            let options,
                    row = this.$table[0].tfClass('tb_no_sort')[0];
            try {
                options =typeof data.field_extra==='string'? JSON.parse(data.field_extra):data.field_extra;
                if(options){
                    options=options.fields;
                }
            } catch (e) {
            }
            if (!options) {
                options = {fields: []};
            }
            const fr = doc.createDocumentFragment();
            for (let i = 0, len = options.length; i < len; ++i) {
                fr.appendChild(this.addField(options[i]));
            }
            row.after(fr);
            this.loadOrders(data, options);
        },
        events() {
            const click=Themify.click;
            this.$table[0].tfOn(click,e=>{
                const item=e.target?e.target.closest('.tb_new_field_action,.tb_add_field_option,.tb_contact_value_remove,.tb_contact_field_remove,.tb_arrow'):null;
                if(item){
                    if(e.target.tagName==='A'){
                        e.preventDefault();
                    }
                    e.stopPropagation();
                    const cl=item.classList;
                    if(cl.contains('tb_new_field_action')){
                        item.closest('.tb_no_sort').before(this.addField({}));
                        this.sortableFields('refresh');
                    }
                    else if(cl.contains('tb_add_field_option')){
                        item.previousElementSibling.appendChild(this.render.getOptions(['']));
                    }
                    else if(cl.contains('tb_arrow')){
                        const li=item.closest('li'),
                            next=cl.contains('tb_down_row')?li.nextElementSibling:li.previousElementSibling;
                        if(!next){
                            return;
                        }
                        cl.contains('tb_down_row')?next.after(li):next.before(li);
                    }
                    else{
                        if (cl.contains('tb_contact_value_remove')) {
                            item.closest('li').remove();
                        }
                        else {
                            const row = item.closest('.tb_contact_new_row'),
                            name = row.tfClass( 'tb_new_field_textbox' )[0].value.trim();
                            row.remove();
                            // remove the corresponding template tag
                            const tags=api.LightBox.el.querySelectorAll( '.tb_contact_custom_tags span' );
                            for(let i=tags.length-1;i>-1;--i){
                                if(tags[i].textContent.trim() === '%' + name + '%'){
                                    tags[i].remove();
                                }
                            }
                        }
                    }
                    this.changeObject();
                }
            })
            .tfOn('change',e=>{
                const item=e.target?e.target.closest('.tb_new_field_type,.tb_contact_new_row .tb_new_field_required, .tb_new_field_icon'):null;
                if(item){
                    if(item.classList.contains('tb_new_field_type')){
                        this.switchField(item);
                    }
                    this.changeObject();
                }
            })
            .tfOn('input',e=>{
                const item=e.target?e.target.closest('.tb_contact_new_row input[type="text"], .tb_contact_new_row textarea'):null;
                if(item){
                    this.changeObject();
                }
            });
            
            api.LightBox.el.querySelector( '.template_fields' ).tfOn( click, e=> {
                if ( e.target.tagName=== 'SPAN' ) {
                    api.LightBox.el.querySelector( '#template' ).value += e.target.textContent;
                }
            } );
        },
        sortableFields(type) {
            const _this = this,
                    tbody=this.$table.find('tbody');
            if(type==='refresh'){
                tbody.sortable('refresh');
            }
            else{
                tbody.sortable({
                    items: 'tr:not(.tb_no_sort)',
                    placeholder: 'ui-state-highlight',
                    axis: 'y',
                    containment: 'parent',
                    cancel:'.tb_move_opt,input,textarea,button,select,option,a',
                    update() {
                        _this.changeObject();
                    }
                });
            }
            this.sortableFieldItems(type); 
        },
        sortableFieldItems(type){
            const _this = this;
            this.$table.find('.control-input ul').sortable({
                items: 'li',
                placeholder: 'ui-state-highlight',
                handle:'.tb_drag_opt',
                axis: 'y',
                containment: 'parent',
                cursor:'grab',
                cancel:'input,textarea,button,select,option,a',
                update() {
                    _this.changeObject();
                }
            });
        },
        render: {
            call(data, type) {
                return this[type] === und ? this._default(data, type) : this[type].call(this, data, type);
            },
            setType(el, type) {
                el.dataset.type=type;
            },
            getText(data, type, inputType) {
                const input = doc.createElement(inputType);
                    if(inputType !== 'textarea' ){
                        input.type = inputType !== 'tel'?'text': 'tel';
                    }
                if (data.value) {
                    data.value = data.value.replace(/\\\\n/g,'\n');
                    input.value = data.value.replace(/(&quot;)|(\\\\")/g,'"');
                }
                input.className = 'tb_new_field_value tb_field_type_text';
				input.placeholder = tb_contact_l10n.pl;
                this.setType(input, type);
                return input;
            },
            static(data, type) {
                const el = this._default(data, 'textarea');
                el.placeholder = tb_contact_l10n.static_text;
                this.setType(el, 'static');
                return el;
            },
            upload(data,type){
                return doc.createElement('div');
            },
			icon( value = '' ) {
				const el = ThemifyConstructor.create( [
					{
						type : 'icon',
						id : '',
						wrap_class : 'tb_disable_dc tb_new_field_icon'
					}
				] );
				const icon_field = el.querySelector( '.themify_field_icon' );
				icon_field.value = value;
				const after = doc.createElement( 'span' );
				after.className = 'tb_input_after';
				after.textContent = ThemifyConstructor.label.icon;
				el.querySelector( '.tb_field' ).append( after );
				return el;
			},
            getOptions(opt) {
                const fr = doc.createDocumentFragment();
                for (let i in opt) {
                    let li = doc.createElement('li'),
                            a = doc.createElement('a'),
                            input = doc.createElement('input'),
                            move=doc.createElement('div'),
                            up=doc.createElement('div'),
                    middle=doc.createElement('div'),
                    
                    down=doc.createElement('div');
                    up.className='tb_arrow tb_up_row';
                    middle.className='tb_drag_opt tb_no_sort tf_h';
                    down.className='tb_arrow tb_down_row';
                    move.className='tb_move_opt';
                    up.title=ThemifyConstructor.label.up;
                    down.title=ThemifyConstructor.label.down;
                    move.append(up,middle,down);
                    input.type = 'text';
                    input.className = 'tb_multi_option';
                    input.value = opt[i];
                    a.className = 'tb_contact_value_remove tb_ui_icon_link tf_close';
                    a.href = '#';
                    li.append(move,input,a);
                    fr.appendChild(li);
                }
                return fr;
            },
            _default(data, type) {
                if (type === 'text' || type === 'textarea' || type === 'tel' || type === 'number' || type === 'email' ) {
                    const inputType = type === 'textarea' ? type : 'input';
                    return this.getText(data, type, inputType);
                }
                const ul = doc.createElement('ul'),
                        add = doc.createElement('a'),
                        d = doc.createDocumentFragment(),
                        opt = data.value || [''];
                ul.appendChild(this.getOptions(opt));
                add.href = '#';
                add.className = 'tb_add_field_option tb_ui_icon_link';
                add.textContent = tb_contact_l10n.add_option;
                this.setType(add, type);
                d.append(ul,add);
                return d;
            }
        },
        addField(data) {
            const newItem = Object.keys(data).length === 0,
                selected = data.type ? data.type : 'text',
                tr = doc.createElement('tr'),
                td = doc.createElement('td'),
                closeTd = doc.createElement( 'td' ),
                name = doc.createElement('input'),
                //type
                colspan = doc.createElement('td'),
                selectWrap = doc.createElement('div'),
                fieldType = doc.createElement('select'),
                f = doc.createDocumentFragment(),
                control = doc.createElement('div'),
                newField = doc.createElement('div'),
                reqLabel = doc.createElement('label'),
                reqInput = doc.createElement('input'),
                remove = doc.createElement('a'),
                templateTag = doc.createElement( 'span' ),
                types = tb_contact_l10n.types,
                uniq = 'tb_' + api.Helper.generateUniqueID();

            control.className = 'control-input tf_rel';
            newField.className = 'tb_new_field';
            selectWrap.className = 'selectwrapper tf_rel tf_inline_b tf_vmiddle';
            fieldType.className = 'tb_new_field_type tb_lb_option';
            tr.className = 'tb_contact_new_row';

            // Column 1, Label
            name.type = 'text';
            name.className = 'tb_new_field_textbox';
            name.value = data.label === und ? (true === newItem ? tb_contact_l10n.field_name : '') : data.label.replace(/&quot;/g,'"');
            name.dataset.id = data.id === und ? '' : data.id;
            name.dataset.order = data.order === und ? '' : data.order;
            reqInput.type = 'checkbox';
            reqInput.className = 'tb_new_field_required';
            reqInput.value = 'required';
            if (selected === 'static') {
                reqLabel.style.display = 'none';
            }
            if (data.required === true) {
                reqInput.checked = true;
            }
            colspan.setAttribute('colspan', '2');
            td.appendChild(name);
            tr.appendChild(td);

			// Column 2, Field settings
            for (let i in types) {
                let option = doc.createElement('option');
                option.name = uniq;
                if (i === selected) {
                    option.selected = 'selected';
                }
                option.value = i;
                option.textContent = types[i];
                f.appendChild(option);
            }
            fieldType.appendChild(f);
            selectWrap.appendChild(fieldType);
            control.appendChild(this.render.call(data, selected));
            reqLabel.append(reqInput,doc.createTextNode(tb_contact_l10n.req));
            newField.append(control,reqLabel);
			newField.append( this.render.icon( data.icon ) );
            colspan.append(selectWrap,newField);

            closeTd.appendChild(remove);
            remove.className = 'tb_contact_field_remove tf_close tb_ui_icon_link';
            remove.href = '#';
            tr.append(colspan,closeTd);

            templateTag.appendChild( doc.createTextNode( '%' + name.value + '%' ) );
            api.LightBox.el.querySelector( '.tb_contact_custom_tags' ).appendChild( templateTag );
            name.tfOn( 'change', function() {
                templateTag.textContent = '%' + this.value + '%';
            },{passive:true} );

            return tr;
        },
        switchField(el) {
            const type = el.value,
                    control = el.closest('td').tfClass('control-input')[0],
                    req = control.closest('.tb_new_field').tfClass('tb_new_field_required')[0].parentNode;
            while (control.firstChild) {
                control.lastChild.remove();
            }
            control.appendChild(this.render.call({}, type));
            req.style.display = type === 'static' ? 'none' : '';
            if(type==='radio' || type==='select' || type==='checkbox'){
                this.sortableFieldItems();
            }
        },
        changeObject(isinline) {
            const items = this.$table[0].tfTag('tbody')[0].tfTag('tr'),
                    object = {fields: []}, /* list of custom fields */
            order = {};
            for (let i = 0, len = items.length; i < len; ++i) {//exclude new field button
                if (items[i].classList.contains('tb_contact_new_row')) {
                    let type = items[i].tfClass('tb_new_field_type')[0].options[items[i].tfClass('tb_new_field_type')[0].selectedIndex].value,
                            label = items[i].tfClass('tb_new_field_textbox')[0].value.trim(),
                            req = type !== 'static' && items[i].tfClass('tb_new_field_required')[0].checked === true,
							icon = items[i].tfClass('themify_field_icon')[0].value.trim(),
                            value;
                    switch (type) {
                        case 'text':
                        case 'email':
                        case 'number':
                        case 'textarea':
                        case 'static':
                        case 'tel':
                            value = items[i].tfClass('tb_new_field_value')[0].value.trim();
                            break;
                        case 'radio':
                        case 'select':
                        case 'checkbox':
                            value = [];
                            let multi = items[i].tfClass('control-input')[0].tfTag('input');
                            for (let j = 0, len2 = multi.length; j < len2; ++j) {
                                let v = multi[j].value.trim();
                                if (v !== '' || 'select'===type) {
                                    value.push(v);
                                }
                            }
                            break;
                    }
                    if ((value !== '' && value !== und) || label !== '') {
                        let field = {
                            type: type,
                            order: i,
							icon : icon
                        };
                        if (req) {
                            field.required = req;
                        }
                        if (label !== '') {
                            field.label = label.replace(/"/g,'&quot;');
                        }else{
                            // Plan B for sorting solution
                            field.id = 'ex'+i;
                        }
                        if (value !== und && value !== '' && value.length > 0) {
                            if('static'===type){
                                value=value.replace(/"/g,'\\\\"');
                            }else if('text'===type || 'textarea'===type){
                                value=value.replace(/"/g,'&quot;');
                            }
                            if('static'===type || 'textarea'===type){
                                value=value.replace(/\n/g,'\\\\n');
                            }
                            field.value = value;
                        }
                        object.fields.push(field);
                    }
                }
                else if (!items[i].classList.contains('tb_no_sort')) {
                    let id = items[i].tfClass('tb_lb_option')[0].id;
                    order[id] = i;
                }
            }
            const el = api.LightBox.el.querySelector('#field_extra'),
			orderVal = JSON.stringify(order);
            el.value = JSON.stringify(object);
            api.LightBox.el.querySelector('#field_order').value = orderVal;
            this.app.settings.field_order = orderVal;
            if(!isinline){
                    Themify.triggerEvent(el, 'change');
            }
        }
    };
    const init=()=>{

        ThemifyConstructor.contact_fields = {
            render(data, self) {
                window.top.Themify.fonts('ti-split-v');
                let tr=doc.createElement('tr');
                const table = doc.createElement('table'),
                        thead = doc.createElement('thead'),
                        tbody = doc.createElement('tbody'),
                        tfoot = doc.createElement('tfoot'),
                        f = doc.createDocumentFragment(),
                        head = data.options.head,
                        body = data.options.body,
                        foot = data.options.foot,
                        render = {
                            text(id, placeholder, desc) {
                                const args = {
                                    id: 'field_' + id,
                                    placeholder: placeholder,
                                    type: 'text'
                                };
                                if (desc) {
                                    args.help = desc;
                                }
                                return self.create([args]);
                            },
							icon( id ) {
								const el = self.create( [
									{
										type : 'icon',
										id : id,
										after : ThemifyConstructor.label.icon
									}
								] ),
								after = doc.createElement( 'span' );
								after.className = 'tb_input_after';
								after.textContent = ThemifyConstructor.label.icon;
								el.querySelector( '.tb_field' ).append( after );
								return el;
							},
                            checkbox(id) {
                                const args = {
                                    id: 'field_' + id,
                                    new_line: true,
                                    type: 'checkbox',
                                    options: [{value: '', name: 'yes'}]
                                };
                                if('sendcopy_active'===id){
                                    args.binding = {
                                        checked:{show:'field_sendcopy_subject'},
                                        not_checked:{hide:'field_sendcopy_subject'}
                                    };
                                }
                                else if ( 'optin_active' === id ) {
                                    args.binding = {
                                        checked : { show:'optin'},
                                        not_checked : { hide:'optin'}
                                    };
                                }
                                return self.create([args]);
                            }
                        };
                //head
                for (let i in head) {
                    let th = doc.createElement('th');
                    if(i==='l'){
                        th.colSpan=2;
                    }
                    th.textContent = head[i];
                    tr.appendChild(th);
                }
                thead.appendChild(tr);
                //body
                for (let i in body) {
                    tr = doc.createElement('tr');
                    for (let k in head) {
                        let td = doc.createElement('td'),
                                el = null;
                        if (k === 'f') {
                            el = doc.createElement('span');
                            el.textContent = body[i];
							td.appendChild(el);
                        }else if (k === 'l') {
                            td.colSpan='2';
                            let d;
                            d = render.text(i + '_label',body[i]);
                            d.appendChild(render.text(i + '_placeholder',tb_contact_l10n.pl));
                            d.appendChild( render.icon( i + '_icon' ) );
                            if ( i === 'recipients' ) {
								/* Recipient Choice */
								el = doc.createDocumentFragment();
								el.appendChild( render.text( i + '_label',body[i] ) );
								el.appendChild( render.icon( i + '_icon' ) );
								const display = self.create( [
									{
										id : 'sr_display',
										type : 'select',
										label : tb_contact_l10n.display,
										options : { 'radio' : tb_contact_l10n.radio, 'select' : tb_contact_l10n.select },
										control : false
									},
									{
										id : 'sr',
										type : 'builder',
										options : [
											{ id : 'label', type : 'text', label : tb_contact_l10n.label, wrap_class : 'tb_disable_dc' },
											{ id : 'email', type : 'text', label : tb_contact_l10n.types.email, wrap_class : 'tb_disable_dc', control : false }
										]
									}
								] );
								el.appendChild( display );
							} else if (i !== 'message') {
                                var tmp = doc.createDocumentFragment(),
                                        checkbox = render.checkbox(i + '_require');
                                tmp.appendChild(d);
                                checkbox.querySelector('.tb_lb_option').appendChild(doc.createTextNode(tb_contact_l10n.req));
                                tmp.appendChild(checkbox);
                                el = tmp;
                            }else{
                                el = d;
                            }
                        }
                        else if (k === 'sh') {
							if ( i === 'recipients' ) {
								el = self.help( tb_contact_l10n.sr_info );
							} else {
								el = render.checkbox(i + '_active');
							}
                        }
                        if (el !== null) {
                            td.appendChild(el);
                        }
                        tr.appendChild(td);
                    }
                    f.appendChild(tr);
                }

                tr = doc.createElement('tr');
                const td = doc.createElement('td'),
                        a = doc.createElement('a'),
                        plus = doc.createElement('span');
                a.className = 'tb_new_field_action';
                a.href = '#';
                plus.className = 'tf_plus_icon tf_rel';
                a.append(plus,doc.createTextNode(data.new_row));
                td.setAttribute('colspan', '4');
                td.appendChild(a);
                tr.className = 'tb_no_sort';
                tr.appendChild(td);
                f.appendChild(tr);
                tbody.appendChild(f);
                //footer
                for (let i in foot) {
                    if (i !== 'align') {
                        tr = doc.createElement('tr');
                        for (let k in head) {
                            if(k==='sh' && i==='send'){
                                continue;
                            }
                            let td = doc.createElement('td'),
                                    el = null;
                            if (k === 'f') {
                                td.textContent = foot[i];
                            }
                            else if (k === 'l') {
                                td.colSpan=2;
                                let text = render.text(i + '_label', foot[i]);
                                if (i === 'send') {
                                    td.colSpan=3;
                                    var tmp = doc.createDocumentFragment(),
                                            select = self.select.render({
                                                id: foot.align.id,
                                                options: foot.align.options
                                            }, self);
                                    tmp.append(text,select,doc.createTextNode(foot.align.label));
									const icon = self.create( [ {
											id : 'send_icon',
											after : ThemifyConstructor.label.icon,
											type : 'icon',
											wrap_class : 'tb_disable_dc'
										} ] ),
										after = doc.createElement( 'span' );
									after.className = 'tb_input_after';
									after.textContent = ThemifyConstructor.label.icon;
									icon.querySelector( '.tb_field' ).append( after );
									tmp.append( icon );
                                    el = tmp;
                                }
                                else if ( i === 'optin' ) {
                                    el = doc.createDocumentFragment();
                                    let optin_provider = ThemifyConstructor.create( [
                                            {
                                                    type : 'optin_provider',
                                                    id : 'optin'
                                            }
                                    ] );
                                    el.append( text,optin_provider );
                                                            }
                                else {
                                    el = text;
                                }


                                tmp.appendChild(checkbox);
                            }
                            else if (k === 'sh' && i !== 'send') {
                                el = render.checkbox(i + '_active');
                                if(i==='captcha' && tb_contact_l10n.captcha!==''){
                                    el.querySelector('input').tfOn('change',function(e){
                                        let p = this.closest('td').previousElementSibling;
                                        if(this.checked===true){
                                            const message = doc.createElement('div');
                                            message.className='tb_captcha_message tb_field_error_msg';
                                            message.innerHTML = tb_contact_l10n.captcha;
                                            p.appendChild(message);
                                        }
                                        else{
                                            let ch = p.tfClass('tb_captcha_message')[0];
                                            if( ch !== und){
                                                ch.parentNode.removeChild(ch);
                                            }
                                        }
                                    },{passive:true});
                                }
                            }
                            if (el !== null) {
                                td.appendChild(el);
                            }
                            if(k === 'l' && i === 'sendcopy'){
                                td.appendChild(self.create([{
                                    id: 'field_' + i + '_subject',
                                    after: tb_contact_l10n.sendcopy_sub,
                                    type: 'text',
                                    class: 'small'
                                }]));
                            }
                            tr.appendChild(td);

                        }
                        f.appendChild(tr);
                    }
                }
                tfoot.appendChild(f);

                table.className = 'contact_fields';
                table.append(thead,tbody,tfoot);
                Themify.on('tb_editing_contact_setting', lb=> {
                    const template = lb.querySelector( '#template' );
                    if ( template.value === '' ) {
                        template.value= tb_contact_l10n.default_template;
                    }
                    instance = new contactFormBuilder(table, self);
                    Themify.on('themify_builder_lightbox_close', ()=> {
                        instance=null;
                    },true);

                },true);

                return table;
            }
        };

            if(api.mode==='visual'){
                
                    class contact extends api.BaseInLineEdit{
                        constructor(){
                            super();
                        }
                        save(model,activeEl,name,val,data,isSaving){
                            const el=activeEl,
                                    extra=el.closest('.builder-contact-field-extra');
                            if(extra!==null){
                                let index=-1;
                                for(let allExtra=model.el.tfClass('builder-contact-field-extra'),i=allExtra.length-1;i>-1;--i){
                                    if(allExtra[i]===extra){
                                        index=i;
                                        break;
                                    }
                                }
                                if(index!==-1){
                                    const control=activeEl.closest('.control-input');
                                    let arrIndex=-1;
                                    if(control!==null){
                                        for(let childs=control.children,i=childs.length-1;i>-1;--i){
                                            if(childs[i].contains(activeEl)){
                                                arrIndex=i;
                                                break;
                                            }
                                        }
                                    }
                                    if(instance){
                                        let item=api.LightBox.el.tfClass('tb_new_field_textbox')[index];
                                        if(item!==und){
                                            if(arrIndex!==-1){
                                                item=item.closest('tr').querySelectorAll('.control-input li')[arrIndex].tfClass('tb_multi_option')[0];
                                            }
                                            item.value=val;
                                            instance.changeObject(true);
                                        }
                                    }
                                    else{
                                        const settings=model.get('mod_settings'),
                                            fieldExtra=typeof settings.field_extra==='string'? JSON.parse(settings.field_extra):settings.field_extra;
                                        if(fieldExtra.fields && fieldExtra.fields[index]!==und){
                                            const item=fieldExtra.fields[index];
                                            if(control!==null){
                                                if(!item.value || arrIndex===-1 || item.value[arrIndex]===und){
                                                    return false;
                                                }
                                                item.value[arrIndex]=val;
                                            }
                                            else{
                                                item.label=val;
                                            }
                                            settings.fieldExtra=fieldExtra;
                                            model.set('mod_settings',settings);
                                        }
                                    }
                                }
                                return false;
                            }
                            return val;
                        }
                    };
                    new contact();
            }

            // validator for Recipient field
            api.Forms.registerValidator( 'tb_contact_recipient', item=>{
                    if ( !api.LightBox.el.querySelector( '#send_to_admins .tb_checkbox:checked' ) ) {
                        return item.value.trim() === '' ?false:api.Forms.getValidator('email')(item);
                    }
                    return true;
            } );
    };
    api.jsModuleLoaded().then(init);
    
    Themify.on('themify_builder_ready',()=>{
        Themify.requestIdleCallback(()=>{
            let lb=window.top.document.tfId('themify-builder-lightbox-css');
            if(lb){
               lb= lb.nextElementSibling;
            }
            window.top.Themify.loadCss(tb_contact_l10n.admin_css,null, tb_contact_l10n.v,lb);
        },-1,1500);
    },true,api.is_builder_ready);

})(jQuery,tb_app,Themify,document,undefined);
