import { filter, groupBy, map, forEach, find } from 'lodash'
import { generador } from '../filters/filters'

module.exports = function($scope, $rootScope, $uibModal, object, Restangular, toastr) {
		$scope.filter = false;
		$scope.fechaBusquedad = null;
		$rootScope.solicitudes = object.data;

    Date.prototype.yyyymmdd = function() {
      var mm = this.getMonth() + 1;
      var dd = this.getDate();

      return [this.getFullYear(),
              (mm>9 ? '' : '0') + mm,
              (dd>9 ? '' : '0') + dd
            ].join('-');
    };

    $scope.onViewReporte = ($this) => {
      if($scope.datebegind > $scope.dateEnd){
        toastr.error('Lo sentimos pero la fecha de inicio no puede ser mayor a la de final', 'Error');
        return;
      }

      window.open(`reporte/solicitudes/atentidas?typeSolicitud=${$scope.typeSolicitud}&begind=${$scope.datebegind.yyyymmdd()}&end=${$scope.dateEnd.yyyymmdd()}`, '_blank');
      $scope.filters.informe = false;
      $scope.typeSolicitud = null
      $scope.datebegind = null;
      $scope.dateEnd = null;
    }

		$scope.filters = {
			type: false,
      status: false,
      informe: false,
			criterio: false
		}

		$scope.onChageStatus = () => {
			if($scope.queryOptionStatus){
				Restangular.all('queryOptionStatus').customPOST({
					code: $scope.queryOptionStatus
				}, '').then((json) =>
				{
					$rootScope.solicitudes = json.data;					
				})
			}
		}

		$scope.onSearchFilter = (type = 'tipo') => {
			let filters = { type: false, criterio: false, status: false, informe: false };
			switch (type) {
				case "tipo"     : filters['type'] = true; break;
				case "status"   : filters['status'] = true; break;
        case "criterio" : filters['criterio'] = true; break;
				case "informe"  : filters['informe'] = true; break;
			}
			$scope.filters = filters;
		}

		$scope.onFilterSolicitud = () => {
				let fechaBusquedad = $scope.frm.fechaBusquedad;
				Restangular.all('solicitud').customPOST({
						q: $scope.q,
						queryOption: $scope.queryOption,
						fechaBusquedad: fechaBusquedad ? fechaBusquedad.$viewValue : null
				}, 'search')
				.then((response) => {
						$rootScope.solicitudes = response.data;
						$scope.cancelarSearch();
						toastr.success("La busqueda ha finalizado", "Exito!");
				}, (error) => {
						toastr.error(error.message, "Error");
				})
		}

		$scope.cancelarSearch = () => {
				$scope.filter = false;
				$scope.q = null;
				$scope.fechaBusquedad = null;
		}

		$scope.onChangeStatus = () => {
				if($scope.orderBy){
						let data = filter(object.data, {
								'tipo_solicitud': parseInt($scope.orderBy)
						})
						$rootScope.solicitudes = data;
				}else{
						$rootScope.solicitudes = object.data;
				}
		}
}

module.exports.item = function($scope, $rootScope, $uibModal, object, Restangular, toastr) {
		$scope.campos = [];
		$scope.isOpen = false;
		$scope.object = object.data;
		$scope.gruposSeleccion = [];
		$scope.examenes = groupBy($scope.object.examenes, 'nombre_categoria');
		
		$scope.changeStatusSolicitud = (object) => {
			Restangular.all('solicitud').customPOST({
				id : object.id
			}, 'changeStatusSolicitud').then((response) => {
				toastr.success(response.message, 'Exito!')
				$scope.object.estado = response.response

				$rootScope.solicitudes = map($rootScope.solicitudes, function(ob) {
					if(ob.id == object.id){
						ob.estado = response.response; return ob;
					}
					return ob;
				});

			}, (error) =>{
				toastr.error(error.data.message, 'Error!')
			})
		}

		$scope.factuarSolicitud = (object) => {
			let modal = $uibModal.open({
				size: 'lg',
				templateUrl : "public/templates/modals/facturar.solicitud.item.html",
				resolve: {
					solicitud: ["Restangular", function() {
						return Restangular.all('solicitud/facturar').customPOST({
							id : object.id
						}, 'code')
					}]
				},
				controller	: function($scope, $uibModalInstance, solicitud, Restangular) {
					$scope.solicitud = solicitud.data;

					if(!$scope.solicitud.abono){
						$scope.solicitud.abono = 0;
					}

          // Verificar lo subtotal
          $scope.subTotal = ($scope.solicitud.monto - $scope.solicitud.abono)
					$scope.onPagarSolicitud = () => {
						Restangular.all('solicitud').customPOST({
							id			    : $scope.solicitud.id,
							abono 	    : $scope.solicitud.abono,
							monto       : $scope.solicitud.monto,
							descuento   : $scope.solicitud.montoDescuento,
							isDescuento : $scope.solicitud.descuento,
						}, 'facturar').then((response) => {
							toastr.success('Proceso realizo con exito!', 'Exito!');
							$uibModalInstance.close({ complete: true });
						});
					}

					$scope.cerrar = () => { $uibModalInstance.close({  }); }
          $scope.$watch('solicitud.montoPorcentajeDescuento', (value) => {
            if(value){
              let subTotal = ($scope.solicitud.monto - $scope.solicitud.abono)
              let descuento = (subTotal * (value/100))
              
              $scope.subTotal = subTotal - descuento
              $scope.solicitud.montoDescuento = descuento
            }
          })
				}
			});

			modal.result.then((response) => {
				if(response.complete){
					object.estado = 4;
					$rootScope.solicitudes = map($rootScope.solicitudes, function(ob) {
					  if(ob.id == object.id){
					    ob.estado = 4; return ob;
					  }
					  return ob;
					});
				}
			})
		}

		$scope.onObservaciones = (item) => {
			let modal = $uibModal.open({
				templateUrl: "public/templates/modals/observaciones.examen.html",
				resolve : {
					item: item,
					observacion: ['Restangular', function() {
							return Restangular.all('solicitud/observaciones/item').customPOST({ item: item }, '');
					}]
				},
				controller: ($scope, $uibModalInstance, toastr, item, observacion, $rootScope) => {
					$scope.item = item;
					$scope.observacion = null;
					$scope.cerrar = function() { $uibModalInstance.close({}) }

					if(observacion.data){
						$scope.observacion = observacion.data;
					}

					$scope.onEnviar = () => {
						Restangular.all('solicitud/observaciones').customPOST({
							item: item,
							observacion : $scope.observacion
						}, '').then((response) => {
							item.estado = 2;
							toastr.success(response.message, 'Exito!');
							$uibModalInstance.close({})
							$rootScope.solicitudes = map($rootScope.solicitudes, function(ob) {
								if(ob.id == item.solicitud_id){
									ob.estado = 2;
									return ob;
								}
								return ob;
							});
						})
					}
				}
			});
		}

		$scope.onChangeStatus = (item) => {
				Restangular.all('changeStatusExamen').customPOST({
						item: item
				}, '').then(function(response){
						if(response.isError){
								let modal = $uibModal.open({
										templateUrl: "public/templates/modals/confirmar.cierre.examen.html",
										controller: ($scope, $uibModalInstance, toastr) => {
												$scope.cerrar = function() { $uibModalInstance.close({}) }
												$scope.ok = () => {
														Restangular.all('changeStatusExamenItem').customPOST({ id: item.id }, '').then((response) => {
																$uibModalInstance.close({})
																toastr.success(response.message, 'Exito!');
																item.estado = 3;

																$rootScope.solicitudes = map($rootScope.solicitudes, function(ob) {
																	if(ob.id == item.solicitud_id){
																		ob.estado = 3;
																		return ob;
																	}
																	return ob;
																});
														})
												}
										}
								});
						}else{
								toastr.success(response.message, 'Exito!');
								item.estado = 3;
						}
				})
		}

		$scope.createCampos = (item) => {
		  let modal = $uibModal.open({
			  templateUrl: "public/templates/ng-templates/campos-templates.html",
			  resolve: {
					  campos: ["Restangular", function(Restangular) {
							  return Restangular.all('solicitud').customPOST({
									  item: item
							  }, 'campos');
					  }],
					  estado : item.estado
			  },
			  size: 'lg',
			  controller: function($scope, campos, $uibModalInstance, toastr, estado) {
				  let { results } = campos.data;
				  $scope.examen = campos.data.examen
				  $scope.campos = campos.data.campos
				  $scope.estado = estado;

				  results = groupBy(results, "catalogo_campo_id")
				  $scope.gruposSeleccion = groupBy(campos.data.grupoSeleccion, 'grupo_seleccion')

				  $scope.campos = map($scope.campos, (item, index) => {
					  let $item = results[item.catalogo_campo_id]

					  if( $item ){
							  item.result = $item[0]
							  item.id_respuesta = item.result.id;

							  if(item.result.seleccion_valor && item.id_tipo_catalogo != 3){
									  item.value_seleccion = item.result.seleccion_valor
							  }

							  if(item.result.resultado && item.id_tipo_catalogo != 3){
									  item.value_campos = parseFloat(item.result.resultado)
							  }

							  if(item.id_tipo_catalogo == 3){
									  item.digitable_value = item.result.resultado
							  }
						  }
						  return item;
				  });
				  
				  $scope.onSaveResponse = () => {
            let campos = $scope.campos.map( item => {
              if(item.id_tipo_catalogo === 2)
                item.criterio_rango = generador(item.value_campos, item.rango_valor)
              return item
            })
					  Restangular.all('solicitud/add/response').customPOST({
							  campos : $scope.campos,
							  solicitudItem : item.id
					  }, '').then(function(response) {
							  toastr.success(response.message, 'Exito!');
							  $uibModalInstance.close({})
					  })
				  }

				  $scope.cerrar = function() {
						  $uibModalInstance.close({})
				  }
			  }
		  })
		}
		
		/* Multiples seleccion */
		$scope.createMultiplesCampos = (item) => {
		  let modal = $uibModal.open({
			  templateUrl: "public/templates/ng-templates/campos-template-multiples.html",
			  resolve: {
					  campos: ["Restangular", function(Restangular) {
							return Restangular.all('examen_multiples').customGET();
						}],
						resultados: ["Restangular", function(Restangular) {
							return Restangular.all('examen_multiples').customPOST({
								id: item.id
							},'response')
						}],
					  item : item
			  },
			  size: 'lg',
			  controller: function($scope, campos, $uibModalInstance, toastr, item, resultados) {
					let array = [];
					$scope.itemParent = item;
					forEach(campos.arrayOfList.grupo_seleccion, (element, key) => {
						let id = '';
						array[key] = [];
						forEach(element, (i, k) => {
							let arrayElement = [];
							forEach(i, (it) => {
								arrayElement.push(it.nombre_seleccion)
							})
							array[key].push({
								nombre: i[0].nombre_grupo,
								items: arrayElement
							})
						})
					});

					forEach(campos.arrayOfList.campos, it => {
						let $element = find(resultados.arrayOfList, (element) => { return element.catalogo_campo_id == it.id })
						if($element){
							it.resultado = JSON.parse($element.resultado)
							it.id_resultado = $element.id
						}
					})

					$scope.campos = groupBy(campos.arrayOfList.campos, 'categoria_seleccion')
					$scope.categoria = groupBy(campos.arrayOfList.categoria_seleccion, 'categoria_seleccion')
					$scope.categoriaValue = array;

					$scope.onSave = () => {
						Restangular.all('examen_multiples').customPOST({
							campos:  $scope.campos,
							solicitudId: item.id
						}, 'add').then(json => {
							toastr.success(json.message, 'Exito!');
							$uibModalInstance.close({})							
						})
					}

				  $scope.cerrar = function() {
						$uibModalInstance.close({})
				  }
			  }
		  })
	  }

    // Programacion para los examenes de cortesia
    $scope.onItemExamenCortesia = (item) => {
      Restangular.all('solicitud/examenes').customPOST({
        id: item.id,
        estado: item.is_cortesias
      }, 'cortesia').then(json => {
        if(item.is_cortesias == 1){
          item.is_cortesias = 0;
          $scope.object.monto = parseFloat($scope.object.monto) + parseFloat(item.precio)
        }else{
          item.is_cortesias = 1;
          $scope.object.monto = parseFloat($scope.object.monto) - parseFloat(item.precio)
        }
        toastr.success(json.message, 'Exito!');
      }, (error) => {
        toastr.success(json.error.message, 'Error!');
      })
    }
}
