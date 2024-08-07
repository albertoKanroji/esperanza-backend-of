<script src="{{ asset('assets/js/libs/jquery-3.1.1.min.js') }}"></script>
<script src="{{ asset('bootstrap/js/popper.min.js') }}"></script>
<script src="{{ asset('bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('plugins/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        App.init();
    });
</script>
<script src="{{ asset('assets/js/custom.js') }}"></script>
<script src="{{ asset('plugins/sweetalerts/sweetalert2.min.js')}}"></script>
<script src="{{ asset('plugins/notification/snackbar/snackbar.min.js')}}"></script>
<script src="{{ asset('plugins/nicescroll/nicescroll.js')}}"></script>
<script src="{{ asset('plugins/currency/currency.js')}}"></script>

<script>
    function noty(msg, option = 1)    
    {
        Snackbar.show({
            text: msg.toUpperCase(),
            actionText: 'CERRAR',
            actionTextColor: '#fff',
            backgroundColor: option == 1 ? '#3b3f5c' : '#e7515a',
            pos: 'top-right'
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        window.livewire.on('global-msg', msg => {
            noty(msg)
        });
    })

    document.onkeydown = function(e) {
        // F2
    if (e.keyCode == '113') { 
        var el1 = document.getElementById('lang')
        var el2 = document.getElementById('body')
        var el3 = document.getElementById('container')
        
        
        if(el1.classList.contains('sidebar-noneoverflow')) {

            el1.classList.remove("sidebar-noneoverflow")
            el2.classList.remove("sidebar-noneoverflow")
            el3.classList.remove("sidebar-closed","sbar-open")
                    
        } else {           

            el1.classList.add("sidebar-noneoverflow")
            el2.classList.add("sidebar-noneoverflow")
            el3.classList.add("sidebar-closed","sbar-open")
        }        
           

    }
    
}
</script>



<script src="{{ asset('plugins/flatpickr/flatpickr.js')}}"></script>


@livewireScripts