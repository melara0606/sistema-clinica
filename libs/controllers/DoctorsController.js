import { filter } from 'lodash'

module.exports = function($scope, $rootScope, doctors, Restangular, NgMap, toastr) {
  $scope.items = doctors
  $scope.item  = doctors[0]
  $scope.item.selected = true

  $scope.permisos = filter($rootScope.recursos, { url: 'doctors' })[0];

  $scope.createItem = () => {
    let item = { status: 1, isNew: true, lat: 13.642307, lng:-88.784270  }
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
      /* Modificando las ruta del punto */
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
      Restangular.all('doctors').post(item).then((element) => {
        $scope.items.pop()
        $scope.items.push(element)
        $scope.selectItem(element)
      });
    }else{
      Restangular.all('doctor').customPOST({
        item,
      }, '').then((it) => {
        item.editing = false
      })
    }
  }

  // configuracion del mapas
  $scope.googleMapsUrl="https://maps.googleapis.com/maps/api/js?key=AIzaSyAhvC3rIiMvEM4JUPAl4fG1xNPRKoRnoTg&libraries=geometry"
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
      Restangular.all('modificaciones/doctores').customPOST({
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

  $scope.onKeypress = (keyEvent) => {
    if(keyEvent.which === 13){
      $scope.query = "";
      Restangular.all('doctors').customGET()
        .then(json => {
          generador($scope, json)
        })
    }
  }

  $scope.$watch('query', function (value) {
    if(value && value.length >= 3){
      Restangular.all('doctors/searchQuery?q='+ value).customGET()
      .then(json => {
        if(json.data.length > 0){
          generador($scope, json.data)
        }else{
          $scope.items = [];
        }
      })
    }
  })

   function generador($scope, data){
    $scope.items = data;
    $scope.item  = $scope.items[0]
    $scope.item.selected = true
  }
}
