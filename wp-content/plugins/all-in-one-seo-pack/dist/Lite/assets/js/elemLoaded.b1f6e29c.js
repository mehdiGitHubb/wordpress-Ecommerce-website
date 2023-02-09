const e=(o,n)=>{const t=document.createElement("style");t.innerHTML=`@keyframes ${n} {
			from { opacity: 0.99; }
			to { opacity: 1; }
		}
		${o} {
			animation-duration: 0.001s;
			animation-name: ${n};
		}`,document.head.appendChild(t)};export{e};
