<div class="row sales layout-top-spacing">
	
	<div class="col-sm-12">
		<div class="widget widget-chart-one">
			<div class="widget-heading">
				<h4 class="card-title">
					<b>{{$componentName}} | {{$pageTitle}}</b>
				</h4>
				<ul class="tabs tab-pills">		
					@can('Category_Create')	
					<li>
						<a href="javascript:void(0)" class="tabmenu bg-dark" data-toggle="modal" data-target="#theModal" 
						>Agregar</a>
					</li>	
					@endcan
				</ul>
			</div>
			@can('Category_Search')	
			@include('common.searchbox')
			@endcan
			
			<div class="widget-content">		
				

				<div class="table-responsive">
					<table class="table table-bordered table striped mt-1">
						<thead class="text-white" style="background: #3B3F5C">
							<tr>
								<th class="table-th text-white">DESCRIPCIÓN</th>
								<th class="table-th text-white text-center">IMAGEN</th>
								<th class="table-th text-white text-center">ACTIONS</th>
							</tr>
						</thead>
						<tbody>
							@foreach($categories as $category)
							<tr>
								<td><h6>{{$category->name}}</h6></td>
								<td class="text-center">
									<span>
										<img src="{{ asset('storage/categories/' . $category->imagen) }}" alt="imagen de ejemplo" height="70" width="80" class="rounded">
									</span>
								</td>

								<td class="text-center">
									@can('Category_Update')	
									<a href="javascript:void(0)" 
									wire:click="Edit({{$category->id}})"
									class="btn btn-dark mtmobile" title="Edit">
									<i class="fas fa-edit"></i>
								</a>
								@endcan
								

								@if($category->products->count() < 1 )								@can('Category_Destroy')	
								<a href="javascript:void(0)"
								onclick="Confirm('{{$category->id}}')" 
								class="btn btn-dark" title="Delete">
								<i class="fas fa-trash"></i>
							</a>
							@endcan
							@endif

							

						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
			{{$categories->links()}}
		</div>

	</div>


</div>


</div>

@include('livewire.category.form')
</div>


<script>
	document.addEventListener('DOMContentLoaded', function(){

		window.livewire.on('show-modal', msg =>{
			$('#theModal').modal('show')
		});
		window.livewire.on('category-added', msg =>{
			$('#theModal').modal('hide')
		});
		window.livewire.on('category-updated', msg =>{
			$('#theModal').modal('hide')
		});


	});



	function Confirm(id)
	{	

		swal({
			title: 'CONFIRMAR',
			text: '¿CONFIRMAS ELIMINAR EL REGISTRO?',
			type: 'warning',
			showCancelButton: true,
			cancelButtonText: 'Cerrar',
			cancelButtonColor: '#fff',
			confirmButtonColor: '#3B3F5C',
			confirmButtonText: 'Aceptar'
		}).then(function(result) {
			if(result.value){
				window.livewire.emit('deleteRow', id)
				swal.close()
			}

		})
	}


</script>