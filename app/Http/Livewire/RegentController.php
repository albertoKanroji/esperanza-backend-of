<?php

namespace App\Http\Livewire;
use Illuminate\Support\Facades\DB;

use Livewire\WithPagination;
use Livewire\Component;



class RegentController extends Component
{
	use WithPagination;
	protected $paginationTheme = 'bootstrap';

	//public properties

	public $selectedCity = null, $selectedProvincesa = null,$selectedProfession = null;
	public $citiess = [], $provincesa = [];
	public  $name,$lastname,$secondlastname,$birthday;            //campos de la tabla tipos
	public  $persona_id,$ci,$profession=[],$mat_psd,$reg_aooc,$reg_uds,$fec_ta,$reg_ta,$fec_tpn,$fec_re,$reg_sedes,$city,$province;
	public  $selected_id, $search;   //para búsquedas y fila seleccionada
    public  $action = 1;             //manejo de ventanas
    private $pagination = 4;         //paginación de tabla


    //primer método que se ejecuta al inicializar el componente
    public function mount()
    {

    	$this->lastname ='FAX';
    	$this->mat_psd ='4128426789';
    	$this->ci ='WA530';

    }


    //método que se ejecuta después de mount al inciar el componente
    public function render()
    { 	
    	$this->calcularAOC();

    	return view('livewire.regents.component')
    	->extends('layouts.theme.app')
    	->section('content');


    }

    public function calcularAOC()
    {
    	$app = substr($this->lastname, 0, 2); // primeros 2 caracteres apellido
    	$mat = substr($this->mat_psd, - 3); // ultimos 3 caracteres matricula
    	$car = substr($this->ci, 0, 5); // primeros 5 caracteres del carnet

    	$this->reg_aooc = "$app$mat$car";

    }




}

