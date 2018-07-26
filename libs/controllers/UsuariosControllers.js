import { filter, findIndex, forEach } from 'lodash'
//import $ from 'jquery'

module.exports.show = function($scope) {
	$scope.menus = [
		{ url: 'users', nombre: 'Usuarios'},
		{ url: 'recursos', nombre: 'Recursos'},
		{ url: 'perfiles', nombre: 'Perfiles'}
	]
}

module.exports.users = function($scope, $uibModal, Restangular, toastr, objectList, sucursales, perfiles ) {
	$scope.lists = objectList;
	$scope.sucursales = sucursales;
	$scope.perfiles = perfiles;
	$scope.listEmployeer = [];

	$scope.editing = false;
	$scope.userNew = {};

	$scope.onCreateUser = (isValid) => {
		if(isValid){
			Restangular.all('users').customPOST({ employeer: $scope.userNew }, 'createUser').then((response) => {
				$scope.userNew = {};
				$scope.editing = false;
				$scope.lists.push(response.data);
				toastr.success('Usuario creado con exito!', 'Exito')
			}, (error) => {
				toastr.error(error.data.message, 'Error')
			});
		}
	}

	$scope.changeEmployeers = () => {
		let { sucursal } = $scope.userNew;
		if(sucursal){
			Restangular.all('users').customPOST({
				sucursal: sucursal
			}, 'QueryEmployeer').then((response) => {
				let { data } = response;

				if(data.length > 0)
					$scope.listEmployeer = response.data;
				else {
					$scope.listEmployeer = [];
					toastr.info('Por el momento todos los empleados tiene usuario asignados en esta sucursal', 'Mensaje');
				}
			});
		}
	};

	$scope.createUser = (event) => {};

	$scope.onChangeStatus = (item) => {
		Restangular.all('users').customPOST({
			user : item
		}, 'changeStatus').then((res) => {
			toastr.info(res.response, "Message");
			item.estado = !item.estado
		});
	}

	$scope.openPerfil = (item) => {
		var modalInstance = $uibModal.open({
			templateUrl: 'public/templates/modals/user.key.perfil.html',
			controller: ($scope, $uibModalInstance, perfiles, perfil) => {
				$scope.perfiles = perfiles;
				$scope.perfil = perfil.idPerfil;
				$scope.cerrar = () => { $uibModalInstance.dismiss(); };
				$scope.onChangePerfil= () => {
          Restangular.all('users/perfiles').customPOST({
            perfil: perfil,
            change: $scope.perfil
          }, 'changePerfil').then((response) => {
						toastr.info(response.response, "Message");
						$uibModalInstance.close({
							response: response.data
						});
          }, function(response) {
						toastr.error(response.response, "Message");
          });
				}
			},
			resolve: {
				perfiles: () => {
					return Restangular.all('users').customGET('perfiles');
				},
				perfil: () => {
					return item;
				}
			}
		});

		modalInstance.result.then((resolve) => {
			let { response } = resolve

			item.idPerfil = response.id;
			item.nombre = response.nombre;
		})
	};

	$scope.open = (item) => {
		var modalInstance = $uibModal.open({
			templateUrl: 'public/templates/modals/user.key.change.html',
			controller: function($scope, $uibModalInstance) {
        $scope.cerrar = () => { $uibModalInstance.dismiss(); };
				$scope.onChangePassword = () => {
          Restangular.all('users').customPOST({
            passwordTxt: $scope.txtPassword,
            idUser: item.id_user
          }, 'changePassword').then((response) => {
            let message = `${response.response}: ${  item.name_emp } ${ item.lastname_emp }`
            toastr.info(message, "Message");
            $uibModalInstance.close({});
          }, function(response) {
            let message = `${response.response}`
            toastr.error(message, "Tenemos un problema");
            $uibModalInstance.dismiss();
          });
				}
			}
		});
	}
}

module.exports.recursos = function($scope, $uibModal, Restangular, toastr, objectList) {
	$scope.recursos = objectList;
	$scope.item = {};
	$scope.isEditing = false;

		$scope.icons = [
		'icon-user', 'icon-user-female', 'icon-users',
		'icon-user-follow', 'icon-user-following', 'icon-user-unfollow',
		'icon-trophy', 'icon-speedometer', 'icon-social-youtube', 'icon-symbol-male',
		'icon-screen-desktop', 'icon-disc', 'icon-fire', 'icon-chemistry', 'icon-basket',
		'icon-drop', 'icon-bar-chart', 'icon-wrench', 'icon-settings', 'icon-calculator',
		'icon-pencil', 'icon-layers', 'icon-notebook', 'icon-speech', 'icon-bubbles',
		'icon-list', 'icon-home', 'fa fa-building', 'fa fa-flask', 'fa fa-male',
		'fa fa-dollar', 'fa fa-ambulance', 'fa fa-stethoscope', 'fa fa-file-text', 
		'fa fa-medkit', 'icon-pointer'
	];

	$scope.openIcons = () => {
		var modalInstance = $uibModal.open({
			templateUrl: 'public/templates/modals/user.icons.html',
			size: 'lg',
			controller: function($scope, $uibModalInstance, icons) {
				$scope.icons = icons;
				$scope.changeIcons = (iconsItem) => {
					$uibModalInstance.close({
						icons: iconsItem
					});
				}
			},
			resolve: {
				icons: function() {
					return $scope.icons;
				}
			}
		});

		modalInstance.result.then((resp) => {
			$scope.item.icons = resp.icons
		})
	}

	$scope.onEditSelect = (item) => {
		$scope.item = item;
		$scope.isEditing = true;
	}

	$scope.onChangeRecurso = (item) => {
		Restangular.all("users/recursos").post($scope.item).then((resp) => {
			toastr.info(resp.response, "Message");
			$scope.isEditing = false;
			$scope.item = resp.data;
		});
	}

	/*
	* Funcion para poder activar 
	*/
	$scope.onActiveSelect = (recurso) => {
		Restangular.all('users/changeStatusRecurso').customPOST({
			id: recurso.id
		}, '').then(json => {
			recurso = Object.assign(recurso, json.object)
			toastr.success(json.message, 'Exito!')
		});
	}
}

module.exports.perfiles = function($scope, objectList, Restangular) {
	$scope.perfiles = objectList;
	$scope.isEditing = false;

	$scope.isShow = () => { $scope.isEditing = true; }

	$scope.onCreatePerfil = function () {
		Restangular.all('users/perfiles').post({ nombre: $scope.perfilNombre }).then((res) => {
			$scope.perfiles.push(res.data);
			$scope.perfilNombre = null;
			$scope.isEditing = false;
		})
	}
}

module.exports.perfil = function($scope, Restangular, objectData, toastr, $uibModal, $state) {
	let objectDataCopy = angular.copy(objectData);
	let { usuarios, recursos } = objectData;

	$scope.editing = false;
	$scope.editingRecurso = false;
	$scope.perfil = objectData;
	$scope.usuarios = usuarios;
	$scope.recursos = recursos;
	$scope.listRecursos = [];
	$scope.item = {};

	let onResetForm = () => {
		$scope.item = {
			recurso: null, consultar: true, perfil: objectData.id,
			editar: false, eliminar: false, agregar: false
		};
	}

	onResetForm();
	$scope.onChangeStatus = (item) => {
		Restangular.all('users').customPOST({
			user : item
		}, 'changeStatus').then((res) => {
			toastr.info(res.response, "Message");
			item.estado = !item.estado
		});
	}

	$scope.onDeleteRecurso = (item, $index) => {
		let modalVerificar = $uibModal.open({
			templateUrl: "public/templates/modals/verificar.eliminar.recurso.html",
			controller: ($scope, $uibModalInstance) => {
				$scope.ok = () => {  $uibModalInstance.close(true); };
				$scope.cerrar = () => { $uibModalInstance.close(false); }
			}
		});

		modalVerificar.result.then((rs) => {
			if(rs){
				Restangular.all(`users/perfiles/${item.id}`).customPOST({}, "delete")
					.then((res) => {
						toastr.success(res.message, "Exito!");
						$scope.recursos.splice($index, 1);
						toastr.info("Espere un momento mientas actualiza el sistema", "Message");
						setTimeout(() => {
							window.location.reload();
						}, 5000);
					}, (rs) => {
						toastr.error(rs.message, "Error!");
					})
			}
		});
	}

	$scope.onEditPerfil = () => {
		Restangular.all("users/perfiles/update").post({
			perfil: {
				nombre: $scope.perfil.nombre,
				id: $scope.perfil.id
			}
		}).then((resp) => {
			toastr.info('Hemos actualizado con exito el usuario', 'Exito!')
			$scope.perfil = resp.data;
			$scope.editing = false;
		}, (error) => {
			toastr.error(error.message, 'Error')
		});
	}

	$scope.onDoubleClick = (options) => {
		$scope.editing = options;
		if(!options){
			$scope.perfil.nombre = objectDataCopy.nombre;
		}
	}

	$scope.onCreateRecurso = (isValid) => {
		if(isValid){
			Restangular.all("users/perfiles/").customPOST({
				item: $scope.item
			}, "recursos").then((response) => {
				$scope.recursos.push(response.data[0])
				toastr.info(response.message, "Message");
				$scope.onCloseEditingRecurso();

				toastr.info("Espere un momento mientas actualiza el sistema", "Message");
				setTimeout(() => {
					window.location.reload();
				}, 5000);
			})
		}
	}

	$scope.EditRecurso = (item, $index) => {
		let modal = $uibModal.open({
			templateUrl: 'public/templates/modals/recursos.html',
			controller: ($scope, Restangular, $uibModalInstance, recurso) => {
				$scope.recurso = recurso;

				$scope.onUpdateRecurso = (event) => {
					Restangular.all(`users/perfiles/${recurso.id}`).customPOST({
						item: $scope.recurso
					}, 'recursos').then((response) => {
						toastr.info(response.message, "Mensaje");
						$uibModalInstance.close(response.data);
					})
				}

				$scope.cerrar = () => {
					$uibModalInstance.dismiss('cancel');
				}
			},
			resolve: {
				recurso: () => {
					item.agregar = item.agregar ? true : false;
					item.editar = item.editar ? true : false;
					item.eliminar = item.eliminar ? true : false;
					item.consultar = item.consultar ? true : false;
					return item;
				}
			}
		})

		modal.result.then((result) => {
			toastr.info("Espere un momento mientas actualiza el sistema", "Message");
			setTimeout(() => {
				window.location.reload();
			}, 5000);
			$scope.recursos[$index] = result;
		})
	}

	$scope.onCloseEditingRecurso = () => {
		$scope.editingRecurso = false;
		onResetForm()
	}

	$scope.onAddRecurso = () => {
		Restangular.all("users/perfiles/").customGET('recursosperfiles', {
			perfil: objectData.id
		}).then((resp) => {
			$scope.listRecursos = resp;
			$scope.editingRecurso = true
		})
	}
}
