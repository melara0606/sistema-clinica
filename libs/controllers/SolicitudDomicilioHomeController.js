module.exports = function($scope, Restangular, toastr, arrayOfList) {
  $scope.arrayOfList = arrayOfList.data;

  $scope.cancelar = (item) => {
    Restangular.all('solicitud/adomicilio').customPOST({},`${item.id}/cancelar`).then(json => {
      toastr.success(json.message, 'Exito')
      item.estado = 2
    })
  }
}