<div class="widget-content-area ">
    <div class="widget-one">
        <form>
            


            <div class="row">
                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                    <label>Nombre</label>
                    <input type="text" wire:model.lazy="name" class="form-control" placeholder="Nombre">
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                    <label >Apellido Paterno  </label>
                    <input type="text" wire:model="lastname" class="form-control" placeholder="paterno">
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                    <label >Apellido materno  </label>
                    <input type="text" wire:model.lazy="secondlastname"  class="form-control"  placeholder="Materno">
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                    <label >Fecha Nacimiento  </label>
                    <div class="input-group ">
                                            <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>                                                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                                                            </svg></span>
                                            </div>

                                           <input   type="date" class="flatpickr form-control " placeholder="Fecha Nacimiento" wire:model.lazy="birthday" >
                    </div>
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                    <label > Carnet Identidad</label>
                    <input wire:model="ci" type="text" class="form-control"  placeholder="Carnet">
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                <label >Profesion</label>

                     <select wire:model="selectedProfession" class="form-control">
                        <option value="">---Profesion----</option>                       
                            <option value="profesion1">profesion1</option>                        
                            <option value="profesion2">profesion2</option>                        
                     </select>
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                    <label >Matricula  </label>
                    <input wire:model="mat_psd" type="text" class="form-control"  placeholder="Descripcion" onkeyup="
                                var start = this.selectionStart;
                                var end = this.selectionEnd;
                                this.value = this.value.toUpperCase();
                                this.setSelectionRange(start, end);
                                ">
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                    <label >Nro. Registro AOOC  </label>
                    <input wire:model.lazy="reg_aooc" type="text" class="form-control text-danger"  placeholder="Descripcion">
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                    <label >Registro Uds </label>
                    <input wire:model.lazy="reg_uds" type="text" class="form-control"  placeholder="Descripcion">
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                    <label >Fecha de Reg. Titulo Academico </label>
                    <div class="input-group ">
                                            <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>                                                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                                                            </svg></span>
                                            </div>

                                            <input  type="date" class="flatpickr form-control" placeholder="" wire:model.lazy="fec_ta">
                                        </div>
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                    <label >Nro Registro titulo Academico  </label>
                    <input wire:model.lazy="reg_ta" type="text" class="form-control"  placeholder="Descripcion">
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                            <label >Fecha Titulo Provicion Nacional  </label>
                                		<div class="input-group ">
                                            <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>                                                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                                                            </svg></span>
                                            </div>

                                            <input  type="date" class="flatpickr form-control" placeholder="Nombre del tipo" wire:model.lazy="fec_tpn">
                                        </div>
                </div>
                <div class="form-group col-lg-4 col-md-4 col-sm-12">
                         <label >Fecha titulo Re   </label>
                                		<div class="input-group ">
                                            <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>                                                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                                                            </svg></span>
                                            </div>

                                            <input  type="date" class="flatpickr form-control" placeholder="Nombre del tipo" wire:model.lazy="fec_re">
                                        </div>
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
 					<label >Registro Sedes</label>
                     <div class="input-group ">
                                            <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>                                                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                                                            </svg></span>
                                            </div>

                                            <input  type="date" class="flatpickr form-control" placeholder="...." wire:model.lazy="reg_sedes">
                                        </div>

 				</div>

                <div class="form-group col-lg-4 col-md-4 col-sm-12">
 					<label >Ciudad</label>
                     <select wire:model="selectedCity" class="form-control">
                        <option value="">---Ciudad----</option>                      
                            <option value="1">Barcelona</option>
                            <option value="2">Madrid</option>
                       
                     </select>
        		</div>
               
                <div class="form-group col-lg-4 col-md-4 col-sm-12">
 					<label >Province</label>
                     <select wire:model="selectedProvincesa" class="form-control">
                        <option value="">---municipios----</option>                            
                                <option value="Provincia1">Provincia1</option>                            
                                <option value="Provincia2">Provincia2</option>                            
                     </select>

 				</div>
               

              </div>

                <div class="row ">
                    <div class="col-lg-5 mt-2  text-left">
                        <button type="button" wire:click="doAction(1)" class="btn btn-dark mr-1">
                            <i class="mbri-left"></i> Regresar
                        </button>
                        <button type="button"
                        wire:click="StoreOrUpdate() "
                        class="btn btn-primary ml-2">
                        <i class="mbri-success"></i> Guardar
                        </button>
                    </div>
                </div>
    </form>
</div>
</div>


