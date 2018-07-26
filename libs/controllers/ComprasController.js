import { filter, findIndex, forEach } from 'lodash'
import events from './tools'

module.exports.controllers = function($scope, $rootScope, Restangular, $uibModal, comprasList, toastr) {
	$scope.comprasList = comprasList;
	$scope.permisos = filter($rootScope.recursos, { url: 'compras' })[0];

  $scope.openModalDateBeginAndEnd = () => {
    let modal = $uibModal.open({
      templateUrl: 'public/templates/modals/rango-fechas.html',
      controller: ($scope, $uibModalInstance, toastr) => {
        $scope.cerrar = () => $uibModalInstance.close({ result: false })
        // Configuracion para el calendario
        $scope.dateend = new Date();
        $scope.datebegin = new Date();
        $scope.format = 'dd-MMMM-yyyy';
        $scope.popup1 = { opened: false };
        $scope.popup2 = { opened: false };
        $scope.altInputFormats = ['M!/d!/yyyy'];

        $scope.open1 = () => { $scope.popup1.opened = true;  };
        $scope.open2 = () => { $scope.popup2.opened = true;  };

        /* Evento para el submit */
        $scope.onValidateRango = () => {
          if($scope.datebegin >= $scope.dateend ){
            toastr.error('Lo sentimos pero la fecha de inicio no debe ser mayor o igual a la fecha final', 'Error!')
          }else{
            $uibModalInstance.close({ 
              result: true, 
              url: 'reporte/solicitud/compras',
              begin: $scope.datebegin,
              end  : $scope.dateend
            })
          }
        }
      }
    })

    modal.result.then( response => {
      if(response.result){
        let { begin, end, url} = response
        let endDate   = `${ end.getUTCFullYear() }-${ end.getUTCMonth() + 1 }-${ end.getUTCDate() }`
        let beginDate = `${ begin.getUTCFullYear() }-${ begin.getUTCMonth() + 1 }-${ begin.getUTCDate() }`
        window.open(`${ url }?b=${beginDate}&f=${endDate}`, '_blank')
      }
    })
  }

	$scope.options = {
    aoColumns: [
      { mData: 'No' },
      { mData: 'fecha' },
      { mData: 'proveedor' },
      { mData: 'total' },
      { mData: 'estado' },
      { mData: 'operaciones', bSortable: false }
    ]
  };

	$scope.open = () => {
		var modalInstance = $uibModal.open({
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: 'public/templates/modals/proveedores.html',
			resolve: {
				proveedores: ['Restangular', function(Restangular) {
					return Restangular.all('proveedores').getList();
				}]
			},
			controller: ($scope, $uibModalInstance, $state, proveedores) => {
				$scope.proveedores = proveedores;
				$scope.cerrar = () => { $uibModalInstance.close({}); };

				$scope.onCreateCompra = () => {
					if($scope.proveedor){
						$scope.cerrar();
						$state.go('app.comprasnueva', {
							proveedor_id: $scope.proveedor
						}, {location: 'replace'})
					}
				}
			}
		});
	}
}

module.exports.compraNueva = function($scope, $rootScope, $state, productosItems, proveedor, Restangular, toastr) {
	$scope.isCollapsed = true;
	$scope.listProducts = [];
	$scope.listSelect = [];
	$scope.subTotal = 0;

	$scope.permisos = filter($rootScope.recursos, { url: 'compras' })[0];

	function resetForm() {
		$scope.frm = {};
		$scope.listSelect = [];
		$scope.isCollapsed = true;
	}

	$scope.$watchCollection("listProducts", (isNew, oldValue) => {
		$scope.subTotal = subTotalCompra();
	})

	function subTotalCompra () {
		let total = 0;
		forEach($scope.listProducts, (item) => {
			total += parseInt(item.compra.nameCantidad) * parseFloat(item.compra.precio)
		})
		return total;
	}

	$scope.onAdd = (isValid) => {
		if(isValid){
			let objectData = $scope.frm;
			let index = findIndex(productosItems.data, { 'material_id': objectData.material })
			let $element = productosItems.data.splice(index, 1)[0];
			$element.compra = objectData;
			$scope.listProducts.push($element);
			resetForm();
		}
	}

	$scope.onDeleteItem = ($index) => {
		let element = $scope.listProducts.splice($index, 1)[0];
		productosItems.data.push(element);
		resetForm();
	}

	$scope.onChangeFilter = () => {
		let type = $scope.frm["type"];
		let {data} = productosItems;
		let filters = filter(data, { 'catalogo_id' : type })
		$scope.listSelect = filters;
	}

	$scope.generarCompra = () => {
		Restangular.all('compras').post({
			list: $scope.listProducts,
			proveedor : proveedor
		}).then((response) => {
			toastr.success('Exito!', 'Compra generada con exito');
			$state.go("app.compras");
		})
	}
}

module.exports.detalles = function($scope, $rootScope, $state, $uibModal, compra, items, Restangular, toastr) {
	$scope.compra = compra;
	$scope.itemsCompra = items;

	$scope.permisos = filter($rootScope.recursos, { url: 'compras' })[0];

	$scope.addCodeFactura = (compra) => {
		var modalInstance = $uibModal.open({
			templateUrl: 'public/templates/modals/codigo.factura.html',
			resolve: {
				compraItem: ['Restangular', function(Restangular) {
					return Restangular.all('compras').customGET(compra.id);
				}]
			},
			controller: ($scope, $uibModalInstance, compraItem, Restangular, toastr) => {
				$scope.compra = compraItem;
				let compraItemCopy = angular.copy(compraItem);
				$scope.cerrar = () => { $uibModalInstance.close({}); }

				$scope.close = function () {
					$scope.compra.codigo_factura = compraItemCopy.codigo_factura;
					$scope.editing = false;
				}

				$scope.onUpdateCode = () => {
					Restangular.all('compras').customPOST({
						compra: $scope.compra
					}, compraItem.id + '/codigo').then(function(response) {
						toastr.success(response.message, 'Exito!');
						$uibModalInstance.close({ compra: response.item });
					}, function(error) {
						toastr.error(response.message, 'Problema!');
					});
				}
			}
		});
	};

	function onSendCompraStatus($objects, $isDate ) {
		Restangular.all('compras').customPOST({
			objects: $objects,
			isDate: $isDate
		}, 'status').then((response) => {
			$scope.compra.total_compra = response.compra.total_compra;
			$objects.estado = 0;
			toastr.success('Exito!', 'Hemos cambiado con exito el estado');
		});
	}

	$scope.onClose = (item) => {
		let modalVerificacion = $uibModal.open({
			templateUrl: 'public/templates/modals/verificar.cambio.compras.html',
			controller: ($scope, $uibModalInstance) => {
				$scope.ok = (event) => { $uibModalInstance.close(true); };
				$scope.cerrar = () => { $uibModalInstance.dismiss('cancel'); }
			}
		});

		modalVerificacion.result.then(function() {
			Restangular.all("compras/" + item.id ).customPOST({}, "close").then(() => {
				item.estado = 0;
				toastr.success('La compra ya fue cerrada, ya no podra hacer modificaciones a ella', 'Exito!');
			});
		}, function(){
			toastr.error('Has renuciando al cambio', 'Exito!');
		});
	};

	$scope.onChangeStatus = (item, $index) => {
		var modalVerificacion = $uibModal.open({
			templateUrl: 'public/templates/modals/verificar.cambio.items.html',
			resolve: {
				item: item
			},
			controller: ($scope, $uibModalInstance, item) => {
				$scope.item = item;
				$scope.ok = (event) => { $uibModalInstance.close(true); };
				$scope.cerrar = () => { $uibModalInstance.dismiss('cancel'); }
			}
		});

		modalVerificacion.result.then(function() {
			var fechaVencimiento = new Date();
			if( item.is_fecha_vencimiento === 1 ) {
				var modalInstance = $uibModal.open({
					templateUrl: 'public/templates/modals/asignar.fecha.vencimiento.html',
					controller: ($scope, $uibModalInstance) => {
						$scope.onCreate = (event) => {
							$uibModalInstance.close($scope.fechaVencimiento);
						};

						$scope.cerrar = () => {
							$uibModalInstance.dismiss('cancel');
						}

						$scope.altInputFormats = ['M!/d!/yyyy'];
						$scope.dateOptions = {
							formatYear: 'yy',
							maxDate: new Date(2020, 5, 22),
							minDate: new Date(),
							startingDay: 1
						};

						$scope.popup1 = { opened: false };
						$scope.format = 'dd-MMMM-yyyy';
						$scope.open1 = function() { $scope.popup1.opened = true; };
						$scope.today = function() { $scope.fechaVencimiento = new Date(); };
						$scope.today();
					}
				});
				modalInstance.result.then((value) => {
					onSendCompraStatus( item, new Date(value));
				});
			}else{
				onSendCompraStatus( item, new Date() );
			}
		}, function() {
			toastr.error('Has renuciando al cambio', 'Exito!');
		});
	};
};
