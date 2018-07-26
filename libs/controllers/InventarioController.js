import { filter, findIndex, forEach } from 'lodash'

module.exports.controllers = function($scope, $rootScope, inventario) {
	$scope.inventario = inventario.items;
	$scope.sucursal 	= inventario.sucursal;

	$scope.options = {
		aoColumns: [
			{ 'mData': 'No', bSortable: false },
			{ 'mData': 'NameMaterial' },
			{ 'mData': 'existencias' },
			{ 'mData': 'tipoCatalogo', bSortable: false },
			{ 'mData': 'options', bSortable: false }
		]
	}
}


module.exports.reajustar = function($scope, $uibModal, object, Restangular, toastr) {
	$scope.editing = false
	$scope.data = object.data;
	$scope.createDate = (validDate) => new Date(validDate) 

	/*
	* Eventos
	*/
  $scope.openFormNuevo = () => {
  	$scope.editing = true;
  	$scope.object = {
  		cantidad:0.01, motivo: ''
  	};
  }

  $scope.onCreateReajuste = () => {
  	Restangular.all('inventario/post/').customPOST({
  		object: $scope.object,
  		inventario: object.data.id
  	}, 'item').then( json => {
  		$scope.data.items.unshift(json.data)
  		$scope.data = Object.assign($scope.data, {
  			existencia: json.existencia.existencia
  		}),
  		toastr.success(json.message, 'Exito!')
  	}, (error) => {
  		toastr.error(error.data.message, 'Error!')
  	})
  	$scope.editing = false;
  }
}