document.addEventListener('DOMContentLoaded', function() {
console.log('cargó script pos')
		//listening events
		window.livewire.on('scan-ok', Msg => {			
			noty(Msg)
		})
		window.livewire.on('scan-notfound', Msg => {			
			noty(Msg, 2)
		})
		window.livewire.on('no-stock', Msg => {			
			noty(Msg, 2)
		})
		window.livewire.on('sale-ok', Msg => {			
			noty(Msg)
			$(':focus').blur() //quitar focus en efectivo después de guardar venta para seguir escaneando
		})
		window.livewire.on('sale-error', Msg => {			
			noty(Msg, 2)
		})
		window.livewire.on('print-ticket', saleId => {			
			window.open("print://" + saleId,  '_blank')
		})

	})