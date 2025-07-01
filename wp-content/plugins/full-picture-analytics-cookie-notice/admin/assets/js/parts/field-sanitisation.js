;(()=>{

	// format: KEY
	// document.addEventListener('focusout', e=>{
	// 	if ( e.target.dataset.dataformat && e.target.dataset.dataformat == 'key' ){
	// 		console.log('focusout !');
	// 	}
	// })

	document.addEventListener('keyup', e=>{
		if ( e.target.dataset.dataformat && e.target.dataset.dataformat == 'key' ){
			let reg = /^\d/, // digit at the begining
				reg2 = /[^\w]/gi, // not a special char or underscore
				txt = e.target.value;

			txt = txt.replace(reg,'');
			txt = txt.replace(reg2,'');

			e.target.value = txt;
		}
	})
})();
