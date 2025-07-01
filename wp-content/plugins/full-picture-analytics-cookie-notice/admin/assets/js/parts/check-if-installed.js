(()=>{
    
    // Show notification if the tool is installed

    let required = FP.findAll('tr.fupi_required'),
        installed_info = FP.findID('fupi_installed_info'),
        fupi_not_installed_info = FP.findID('fupi_not_installed_info');

    if ( installed_info ) {
        if ( required.length == 0 ) {
            installed_info.classList.remove('fupi_hidden');
        } else {
            
            let some_required_are_empty = required.some( tr => {
                return ! FP.findFirst('input', tr).value;
            } );

            if ( ! some_required_are_empty ) {
                installed_info.classList.remove('fupi_hidden');
            } else {
                if ( fupi_not_installed_info ) fupi_not_installed_info.classList.remove('fupi_hidden');
            }
        }
    }
    
})();