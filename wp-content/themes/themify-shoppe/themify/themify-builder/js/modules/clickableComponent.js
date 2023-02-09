(doc=>{
    doc.body.tfOn('click',e=>{
        const item=e.target && !e.target.closest( 'a' )?e.target.closest('[data-tb_link]'):null;
        if(item){
            e.preventDefault();
            const link = doc.createElement('a');
            link.href = item.dataset.tb_link;
            link.style.display = 'none';
            doc.body.appendChild( link ); /* add to body to ensure proper click events are triggered */
            link.click();
            link.remove();
        }
    });

})(document);
