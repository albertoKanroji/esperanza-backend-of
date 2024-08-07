<div class="mt-5">

    <div wire:ignore >        
           
            <select class="form-control" id="select2-dropdown">
                <option value="">Seleccionar Producto</option>
                @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        
    </div>

</div>



<script>
  document.addEventListener('DOMContentLoaded', function(){
    $('#select2-dropdown').select2() // inicializar 
    // capturamos values when change event
    $('#select2-dropdown').on('change', function (e) {
        var pId = $('#select2-dropdown').select2("val") // get product id
        var pName = $('#select2-dropdown option:selected').text() // get product name
        @this.set('productSelectedId', pId) // set product id selected
        @this.set('productSelectedName', pName) // set product name selected
    });
});

</script>

