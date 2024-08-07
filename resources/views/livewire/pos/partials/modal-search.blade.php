    <div class="modal fade modal-fullscreen" id="modalSearchProduct" tabindex="-1">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">

                    <div class="input-group">
                        <input type="text" wire:model="modalsearch" id="modal-search-input" placeholder="Puedes buscar por nombre del producto, código ó categoría..." class="form-control">
                        <div class="input-group-prepend">
                            <span class="input-group-text input-gp">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>

                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mt-1">
                                <thead class="text-white" style="background: #3B3F5C">
                                    <tr>
                                        <th width="10%"></th>
                                        <th class="table-th text-left text-white">DESCRIPCIÓN</th>
                                        <th class="table-th text-center text-white">CÓDIGO</th>
                                        <th width="13%" class="table-th text-center text-white">PRECIO</th>
                                        <th class="table-th text-center text-white">CATEGORÍA</th>
                                        <th class="table-th text-center text-white">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">CERRAR VENTANA</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal.modal-fullscreen .modal-dialog {
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
            max-width: none;
        }

        .modal.modal-fullscreen .modal-content {
            height: auto;
            height: 100vh;
            border-radius: 0;
            border: none;
        }

        .modal.modal-fullscreen .modal-body {
            overflow-y: auto;
        }
    </style>