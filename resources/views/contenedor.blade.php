<div class="container">
	
	<div class="row">
		<div class="col">
			<livewire:component1>
		</div>
		<div class="col">
			<livewire:component2>
		</div>
	</div>
</div>

  <script>
        function mandar(){
            console.log('se envio')
            window.livewire.emitTo('component2','evento1', $('.valor').val()); 
        }
        document.addEventListener('DOMContentLoaded', function(){

        })
    </script>