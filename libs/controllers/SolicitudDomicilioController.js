import { filter, groupBy, map } from 'lodash'

module.exports = function($scope, $rootScope, $uibModal, object, Restangular, toastr) {
  $scope.arrayListOf = object.data
}

module.exports.item = function($scope, $rootScope, $uibModal, object, Restangular, toastr, NgMap, $state) {
  $scope.vm = this;
  
  $scope.object = object.data;
  $scope.googleMapsUrl="https://maps.googleapis.com/maps/api/js?key=AIzaSyAhvC3rIiMvEM4JUPAl4fG1xNPRKoRnoTg&libraries=geometry"

  NgMap.getMap().then((map) => {
    $scope.vm = map
    let position = { 
      "lat": parseFloat($scope.object.lat),
      "lng": parseFloat($scope.object.lng)
    };
    $scope.vm.setCenter(position)    
    $scope.vm.positions = [position]
  });

  $scope.convertirSolicitud = () => {
    let listExamen = new Array();

    $scope.object.examenesList.forEach(item => {
      listExamen.push({ "examen": { "id" : item.examen_id, precio: item.precio } })
    })

    Restangular.all('solicitud/add').customPOST({
      type: 2,
      total: $scope.object.pagar,
      paciente: $scope.object.paciente_id,
      listExamen: listExamen
    }, '').then((json) => {
      toastr.success(json.message, 'Exito!');
      Restangular.all('solicitudes/convertirToNormal').customPOST({
        id: $scope.object.id
      }, '').then(rs => {
        $state.go('app.catalogosolicitud.item', {
          id: json.data.id_solicitud
        }, { reload: true })
      })
    })
  }
}
