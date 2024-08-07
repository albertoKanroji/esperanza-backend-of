<div>

	<div class="row layout-top-spacing">

		<div class="col-sm-12 col-md-8">
			<!-- DETALLES -->
			@include('livewire.pos.partials.detail')
		</div>

		<div class="col-sm-12 col-md-4">
			<!-- TOTAL -->
			@include('livewire.pos.partials.total')

			<!-- DENOMINATIONS -->
			@include('livewire.pos.partials.coins')

		</div>

	</div>
	<livewire:modal-search />
</div>

<script src="{{ asset('js/keypress.js') }}"></script>
<script src="{{ asset('js/onscan.js') }}"></script>
<script>
	try {

		onScan.attachTo(document, {
			suffixKeyCodes: [13],
			onScan: function(barcode) {
				console.log(barcode)
				window.livewire.emit('scan-code', barcode)
			},
			onScanError: function(e) {
				//console.log(e)
			}
		})

		console.log('Scanner ready!')


	} catch (e) {
		console.log('Error de lectura: ', e)
	}

</script>


@include('livewire.pos.scripts.shortcuts')
@include('livewire.pos.scripts.events')
@include('livewire.pos.scripts.general')

<script>

	document.addEventListener('DOMContentLoaded', function() {
        window.livewire.on('global-msg', msg => {
           console.log(Msg)
			$('.tblscroll').niceScroll({
				cursoscolor: "#515365",
				cursorwidth: "30px",
				background: "rgba(20,20,20,0.3)",
				cursorborder: "0px",
				cursorborderradius: 3

			})
        });
    })

</script>