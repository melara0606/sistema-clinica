import { filter, map } from 'lodash'

module.exports = function($scope, $rootScope, sucursales, Restangular, NgMap, toastr, $uibModal) {
  $scope.items = sucursales
  $scope.item  = sucursales[0]
  $scope.item.selected = true

  $scope.permisos = filter($rootScope.recursos, { url: 'sucursales' })[0];

  $scope.createItem = () => {
    let item = { status: 1, isNew: true, lat: 13.642307, lng:-88.784270 }
    $scope.items.push(item)
    $scope.selectItem(item)
    $scope.item.editing = true
  }

  $scope.selectItem = (item) =>{
    angular.forEach($scope.items, (i, index) => {
      i.selected  = false
      i.editing   = false
    })
    $scope.item = item
    $scope.item.selected = true

    if(!$scope.isNew){
      let object = {
        "lat": parseFloat($scope.item.lat),
        "lng": parseFloat($scope.item.lng)
      }
      $scope.vm.positions = [object]
      $scope.vm.setCenter(object)
    }
  }

  $scope.editItem = (item) => {
    if(item && item.selected){
      item.editing = true
      item.isNew = false
    }
  }

  $scope.deleteItem = (item) =>{
    if(item.id && !item.editing){
      item.post('delete', item).then((res) => {
        item.status = !item.status
      })
    }else if(item.isNew){
      $scope.items.pop()
      $scope.item = $scope.items[0]
      $scope.item.selected = true
    }else{
      item.editing = false
    }
  }

  $scope.doneEditing = (item) => {
    if(item.isNew){
      Restangular.all('sucursales').post(item).then((element) => {
        $scope.items.pop()
        $scope.items.push(element)
        $scope.selectItem(element)
      });
    }else{
      Restangular.all('sucursal').customPOST({
        item
      }, '').then((it) => {
        item.editing = false
      })
    }
  }

  // configuracion del mapas
  $scope.googleMapsUrl="https://maps.googleapis.com/maps/api/js?key=AIzaSyAhvC3rIiMvEM4JUPAl4fG1xNPRKoRnoTg"
  $scope.vm = this;

  NgMap.getMap().then((map) => {
    $scope.vm = map
    let object = {
      "lat": parseFloat($scope.item.lat),
      "lng": parseFloat($scope.item.lng)
    }
    $scope.vm.setCenter(object)
    $scope.vm.positions = [object]
  })

  $scope.addMarker = (event) => {
    var ll = event.latLng;
    if(ll && !$scope.item.isNew){
      Restangular.all('modificaciones/sucursal').customPOST({
        id: $scope.item.id,
        lat:ll.lat(),
        lng: ll.lng()
      }, 'geolocation').then((response) => {
        $scope.item = Object.assign($scope.item, {
          lat: response.data.lat,
          lng: response.data.lng
        })
        $scope.vm.positions[0] = {lat:ll.lat(), lng: ll.lng()}
        toastr.success(response.message, 'Exito!')
      }, (error) => {
        toastr.success(error.data.message, 'Error!')
      })
    }
  }

  /* Puntos geograficos */
  $scope.openGoogleMaps = (item) => {
    $uibModal.open({
      size: 'lg',
      controller: function($scope, item, NgMap, $timeout, toastr, objectPuntos) {
        if(objectPuntos.data.length > 0){
          $scope.paths = map(objectPuntos.data, (item) => [item.lat, item.lng])
          $scope.isClose = true
        }else{
          $scope.paths = [];
          $scope.isClose = false;
        }



        $scope.googleMapsUrl="https://maps.googleapis.com/maps/api/js?key=AIzaSyAhvC3rIiMvEM4JUPAl4fG1xNPRKoRnoTg"
        $scope.positionCenter = { "lat": parseFloat(item.lat) , "lng": parseFloat(item.lng)  }
        
        NgMap.getMap('mapModals').then( map => {
          $scope.map = map;
          $timeout(() => { google.maps.event.trigger($scope.map, 'resize') }, 1000)
          $scope.map.setCenter($scope.positionCenter)
        })

        $scope.addMarkerAndPath = (event) => {
          let ll = event.latLng
          if(!$scope.isClose){
            $scope.paths.push([ll.lat(), ll.lng()])
          }
        }

        $scope.SaveMarkers = () => {
          if($scope.paths.length == 0){
            toastr.error('Debes marcas el area a seleccionar', 'Error!')
            return;
          }

          Restangular.all('sucursales').customPOST({
            id: item.id, 
            paths: $scope.paths
          }, 'puntosGeograficos').then((response) => {
            $scope.isClose = true;
            toastr.success(response.message, 'Exito!')
          }, (error) => {
            toastr.error(error.data.message, 'Problemas!')
          })
        }

        $scope.deletePaths = () => {
          if($scope.paths.length == 0){
            toastr.error('No hay datos que modificar', 'Error!')
            return;
          }
          Restangular.all('sucursales').customPOST({
            id: item.id
          }, 'puntosGeograficosDelete').then((response) => {
            $scope.paths = [];
            $scope.isClose = false
            toastr.success(response.message, 'Exito!')
          }, (error) => {
            toastr.success(error.data.message, 'Problemas!')
          })          
        }
      },
      resolve: {
        item: item,
        objectPuntos: ["Restangular", function(Restangular) {
          return Restangular.all('sucursales').customPOST({
            'id': item.id
          }, 'puntos');
        }]
      },
      windowTopClass : 'modal-googleMaps',
      templateUrl : "public/templates/modals/maps.google.sucursal.html"
    })
  }
}
