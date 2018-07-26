import { filter } from 'lodash'

module.exports = function($scope, $rootScope, objectData, toastr, Restangular, $uibModal) {
    $scope.list = objectData.data;
    $scope.permisos = filter($rootScope.recursos, { url: 'promociones' })[0];

    $scope.open = (isEdit, promocion = {}, $index) => {
        if($scope.permisos.agregar || $scope.permisos.editar){
            var modalInstance = $uibModal.open({
                templateUrl: 'public/templates/modals/agregar.promocion.html',
                resolve: {
                    isEdit: isEdit,
                    promocion: promocion
                },
                controller: ($scope, $uibModalInstance, isEdit, Restangular, toastr, promocion) => {
                    $scope.isEdit = promocion.id ? true: false;
                    $scope.promocion = promocion;
                    $scope.cerrar = () => { $uibModalInstance.close({}); }

                    $scope.onUpdateCode = () => {
                        if(!$scope.isEdit){
                            Restangular.all('promocion').customPOST({
                                data: $scope.promocion
                            }, '').then(function(response) {
                                toastr.success(response.message, 'Exito!');
                                $uibModalInstance.close({ promocion: response, isEdit: $scope.isEdit  });
                            });
                        }else {
                            console.log($scope.promocion)
                            Restangular.all('promocion/' + $scope.promocion.id ).customPOST({
                                data: $scope.promocion
                            }, 'update').then(function(response) {
                                toastr.success(response.message, 'Exito!');
                                $uibModalInstance.close({ categoria: response, isEdit: $scope.isEdit });
                            }, function(error) {
                                toastr.error(error.data.message, 'Problema!');
                            });
                        }
                    }
                }
            });

            modalInstance.result.then((response) => {
                let { promocion, isEdit } = response;
                if(isEdit){
                    $scope.list[$index] = promocion.data;
                }else{
                    $scope.list.unshift(promocion.data);
                }
            });
        }
	}
}

module.exports.Item = function($scope, $rootScope, objectData, toastr, Restangular, $uibModal) {
    $scope.list = objectData.data;
    $scope.listExamenes = objectData.data.examenes;
    $scope.permisos = filter($rootScope.recursos, { url: 'promociones' })[0];

    $scope.onAddExamen = () => {
        var modalInstance = $uibModal.open({
            templateUrl: 'public/templates/modals/agregar.examen.promocion.html',
            resolve: {
                ctr: $scope
            },
            controller:function($scope, ctr, $uibModalInstance){
                $scope.list = [];
                $scope.server = false;
                $scope.onClose = () => { $uibModalInstance.close('cancel'); }
                $scope.onSearchExamen = () => {
                    if($scope.searchExamen){
                        Restangular.all("promocion/search").customGET('', {
                            q: $scope.searchExamen
                        }).then((response) => {
                            $scope.list = response.data;
                            $scope.server = true;
                        });
                    }
                }

                $scope.onSelectExamen = (item) => {
                     Restangular.all("promocion/add/examen").customPOST( {
                        promocion_id: ctr.list.id,
                        examen_id   : item.id
                    }, '').then(function(response) {
                        toastr.success(response.message, 'Exito!');
                        ctr.listExamenes.unshift(response.data);
                        $scope.list = [];
                        $scope.searchExamen = '';
                        $scope.server = false;
                    }, function(error) {
                        toastr.error(error.data.message, 'Problema!');
                    });
                }
            }
		});
    }

    $scope.onUpdateStatus = () => {
        Restangular.all('promocion/estado').customPOST({
            id: $scope.list.id
        }, '').then(function(response) {
            $scope.list.estado = !$scope.list.estado;
            toastr.success(response.message, 'Exito!');
        });
    }

    $scope.deleteItem = (item, $index) => {
        if(item){
            Restangular.all('promocion/examen/delete').customPOST({
                id: item.id
            }, '').then(function(response) {
                toastr.success(response.message, 'Exito!');
                $scope.listExamenes.splice($index, 1);
            });
        }
    }
}
