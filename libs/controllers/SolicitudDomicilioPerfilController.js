import { filter, groupBy, map, remove, includes } from 'lodash'

module.exports = function($scope, NgMap, sucursales, examenes, Restangular, toastr, $state, $uibModal,) {
  $scope.googleMapsUrl="https://maps.googleapis.com/maps/api/js?key=AIzaSyAhvC3rIiMvEM4JUPAl4fG1xNPRKoRnoTg&libraries=geometry"

  $scope.vm = this;
  $scope.paths = [];
  $scope.position = {}
  $scope.isValido = false
  $scope.isLoading = false
  $scope.sucursales = sucursales;
  $scope.examenes = examenes.object;

  $scope.listExamen = { show: false, item: [], ids: [], pagar: 0.00 };
  var objectPosition = {};

  if(navigator.geolocation){
    navigator.geolocation.getCurrentPosition(position => {
      objectPosition = Object.assign(objectPosition, {
        "lat": position.coords.latitude,
        "lng": position.coords.longitude  
      })
      $scope.position = Object.assign($scope.position, objectPosition)
    })
  }

  NgMap.getMap().then(map => {
    $scope.vm = map;
    $scope.isLoading = true
    $scope.vm.setCenter(objectPosition)
  })

  function ValidarPuntosGeograficos(){
    let arrayPolygon  = $scope.paths;
    let markePosition = $scope.position;

    arrayPolygon = arrayPolygon.map(i => ({
      "lat": i[0], 
      "lng": i[1]
    }))

    const polygon = new google.maps.Polygon({paths: arrayPolygon});
    const LatLng  = new google.maps.LatLng(markePosition.lat, markePosition.lng)
    const result  = google.maps.geometry.poly.containsLocation(LatLng, polygon )

    if(result){
      $scope.isValido = true
      toastr.success('Podemos ayudarte ahora selecciona el examen para poder ir a tu hogar por la muestra', 'Exito');
    }else{
      $scope.isValido = false
      toastr.error('Lo sentimos pero su ubicacion no esta dentro del area seleccionado', 'Lo sentimos');
    }    
  }

  $scope.$watch('sucursalSelect', function(newValue, oldValue) {
    if(newValue){
      Restangular.all(`sucursales/${newValue}/puntosGeograficos`).customGET().then( json => {
        let paths = json.data.map( i => [i.lat, i.lng])
        $scope.paths = paths        
        $scope.vm.setCenter(json.promedio)
        ValidarPuntosGeograficos()
      });
    }else{
      $scope.isValido = false
    }
  })

  $scope.onAddExamen = () => {
    if($scope.listExamen.show){
      Restangular.all('addSolicitud').customPOST({
        sucursal: $scope.sucursalSelect,
        listExamen: $scope.listExamen.item,
        pagar: $scope.listExamen.pagar,
        ubicacion: $scope.position
      }, 'adomicilio').then((json) => {
        toastr.success(json.message, 'Exito!');
        $scope.isValido = false
        $scope.vm = null;
        $state.go('app.perfil.domiciliohome', {})
      } , 
      (error)=> toastr.error(error.data.message, 'Error!'))
    }else{
      toastr.error('Lo sentimos pero debes seleccionar por lo menos un examen', 'Error!')
    }
  }

  $scope.ubicacionPuntoMarket = (event) => {
    puntsLocationGeometric(event, $scope, ValidarPuntosGeograficos);
  }

  $scope.openClickExamenes = () => {
    let modal = $uibModal.open({
      size: 'noventa',
      resolve: {
        'categorias'  : ['Restangular', Restangular =>  Restangular.all('categoria_examenes').customGET() ],
        'listExamen'  : $scope.listExamen
      },
      templateUrl: "public/templates/modals/agregar.examenes.solicitud.html",
      controller: ($scope, $uibModalInstance, Restangular, categorias, listExamen ) => {
        $scope.itemsListExamen = [];
        $scope.categorias = categorias.data

        $scope.itemExamens = listExamen
        $scope.cerrar = () => $uibModalInstance.close({ result: false })
        $scope.agregar = () => $uibModalInstance.close({ result: true, itemExamens: $scope.itemExamens })

        $scope.changeCategoria = () => {
          Restangular.all(`categoria_examenes/${$scope.categoriaId}/`).customGET('examenes').then( json => {
            let data = json.data.examenes
            data.forEach(i => {
              i.value = $scope.itemExamens.ids.includes(i.id)
            })
            $scope.itemsListExamen = data
          })
        }

        function mostrarFormulario() {
          $scope.itemExamens.show = $scope.itemExamens.item.length > 0
        }

        function removeElement(element) {
          remove($scope.itemExamens.item, item => Object.is(element.id, item.id) )
          remove($scope.itemExamens.ids,  item => Object.is(element.id, item) )
          $scope.itemExamens.pagar -= element.precio
          
          $scope.itemsListExamen.forEach(item => {
            if(item.id === element.id){
              item.value = false
            }
          })
        }

        $scope.itemDelete = (element, $index) => {
          removeElement(element)
          mostrarFormulario()
        }

        $scope.onValueData = (element) => {
          if(element.value){
            $scope.itemExamens.item.push(element)
            $scope.itemExamens.ids.push(element.id)
            $scope.itemExamens.pagar += element.precio
          }else{
            removeElement(element)
          }
          mostrarFormulario()
        }
      }
    })

    modal.result.then( rs => {
      if(rs.result){
        $scope.listExamen = rs.itemExamens
      }else{
        toastr.error('No se te olvide que debes agregar por lo menos un examen.', 'Error')
      }
    })
  }
}

function puntsLocationGeometric(event, $scope, callback) {
  let latLng = event.latLng;
  if (latLng) {
    $scope.position = Object.assign($scope.position, {
      'lat': latLng.lat(),
      'lng': latLng.lng()
    });

    if($scope.sucursalSelect){
      callback();      
    }
  }
}
