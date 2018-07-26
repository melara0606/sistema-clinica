module.exports = function($scope, perfil, $uibModal, Restangular) {
  $scope.perfil = perfil.data;
  $scope.dataOption = {};
  $scope.include = false;
  $scope.solicitud = null;
  $scope.debe = 0.00;

  $scope.cerrar = () => {
    $scope.include = false
  }
  
  $scope.openClickDetalle = (solicitud) => {
    $scope.solicitudItem = solicitud;
    Restangular.all('solicitud').customPOST({
      id: solicitud.id
    }, 'perfil').then((json) => {
      $scope.include = true
      $scope.dataOption = json.data;
      if(!$scope.solicitudItem.Abono){
        $scope.solicitudItem.Abono = 0.0;
      }
      $scope.solicitudItem.debe = $scope.solicitudItem.monto - $scope.solicitudItem.Abono;
    });
  }
};
