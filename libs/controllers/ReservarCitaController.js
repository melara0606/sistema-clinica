import { filter, groupBy, map, remove, includes } from 'lodash'

module.exports = function($scope, $uibModal, object, Restangular, toastr) {
  $scope.arrayListOf = object.data
  console.log("message");
  $scope.openModal = () => {
    Restangular.all('user').customGET().then(json => {
      var entidad = json.entidad_id
      var isViewFalseAndTrue = false;

      let modal = $uibModal.open({
        lg: 'sm',
        templateUrl : 'public/templates/modals/reserva.cita.html',
        controller: ($scope, $uibModalInstance) => {
          $scope.isShowEntidad = !(entidad == '1b6ef36f-b05b-4c35-910d-661f86fde307')
          $scope.cerrar = () => { $uibModalInstance.close({ }) }
          $scope.crearSolicitud  = () => {
            $uibModalInstance.close({ 
              response: $scope.type_solicitud == 1
            })
          }
        }
      })

      modal.result.then(res => {
        if(res.response){
          isViewFalseAndTrue = true
        }

        let modalOpen = $uibModal.open({
          size: 'noventa',
          resolve: {
            'horarios'    : ['Restangular', Restangular =>  Restangular.all('horarios/all/active').customGET() ],
            'categorias'  : ['Restangular', function (Restangular) {
              if(isViewFalseAndTrue) {
                return Restangular.all('categoria_examenes').customGET(`${entidad}/entidad`)
              }else{
                return Restangular.all('categoria_examenes').customGET()
              }
            }],
            isViewFalseAndTrue: isViewFalseAndTrue
          },
          templateUrl: "public/templates/modals/agregar.citas.paciente.html",
          controller: ($scope, $uibModalInstance, Restangular, horarios, categorias, toastr, isViewFalseAndTrue) => {
            $scope.dineroEntidad = true;
            $scope.itemsListExamen = [];
            $scope.horarios = horarios.data
            $scope.categorias = categorias.data
            $scope.isViewFalseAndTrue = isViewFalseAndTrue

            $scope.itemExamens = { show: false, item: [], ids: [], pagar: 0.00 }
            $scope.cerrar = () => $uibModalInstance.close({ result: false })

            if($scope.isViewFalseAndTrue){
              console.log($scope.categorias)
              $scope.categorias.forEach( i =>{
                i.value = $scope.itemExamens.ids.includes(i.id)
                $scope.itemsListExamen = $scope.categorias
              })
              $scope.dineroEntidad = categorias.response
            }
            
            $scope.onCreateCode = () => {
              if($scope.fecha < new Date()){
                toastr.error('Lo sentimos pero no puedes seleccionar fecha menor a la actual');
                return;
              }

              Restangular.all('citas/add').customPOST({
                fecha: $scope.fecha,
                horario: $scope.horario,
                itemsListExamen: $scope.itemExamens,
                tipoCita: $scope.isViewFalseAndTrue ? 2 : 1
              }, '').then((json) => {
                $uibModalInstance.close({
                  result: true,
                  data: json.data
                })
              }, (error) => {
                toastr.error(error.data.message, 'Error')
              })
            }

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
        modalOpen.result.then( rs => {
          if(rs.result){
            $scope.arrayListOf.push(rs.data)
            toastr.success('Hemos agregado con exito su reserva', 'Exito')
          }
        })
      })

      /**/
    })

    // verificar si pertenece a una entidad con dinero    
    

    /**/

  }

  // Para cancelar la cita
  $scope.cancelar = function(object) {
   let response = $uibModal.open({
      templateUrl: "public/templates/modals/confirmar.cierre.cita.html",
      controller: function($scope, $uibModalInstance) {
        $scope.confirmation = (response = false) => $uibModalInstance.close({ response })
      }
    })

   response.result.then( response => {
    if(response.response) {
      Restangular.all('citas').customPOST({
        item: object.id
      }, 'cancelar').then((json) => {
        toastr.success(json.message, 'Exito')
        object.estado = 2
      })
    }
   })
  }
}

module.exports.citas = function($scope, $uibModal, Restangular, toastr, $state) {
  $scope.dt = new Date();
  $scope.popup1 = { opened: false };
  $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
  $scope.format = $scope.formats[0];
  $scope.altInputFormats = ['M!/d!/yyyy'];

  $scope.object = []
  $scope.dateOptions = {
    formatYear: 'yy',
    maxDate: new Date(2020, 5, 22),
    minDate: new Date(),
    startingDay: 1
  };

   $scope.open1 = function() {
    $scope.popup1.opened = true;
  };

  $scope.$watch('dt', function(newValue) {
    if(newValue){
      let value = new Date(newValue);
      Restangular.all('citas').customPOST({ 'fecha': value }, 'all/administration').then(json => {
        $scope.object = json.data
      })
    }
  });

  $scope.onActiveSelect = (item) => {
    let listExamen = new Array();
    Restangular.all('citas/all/administration/').customPOST({
      cita_id: item.id
    }, 'items').then( json => {
      if(item.tipo_cita == 1)
        json.data.forEach(item => listExamen.push({ "examen": { "id" : item.examen_id, precio: item.precio } }))
      else
        json.data.forEach(item => listExamen.push({ "id_examen" : item.examen_id, precio: item.precio }))

      Restangular.all('solicitud/add').customPOST({
        descuento: 0,
        type: (item.tipo_cita == 1) ? 2 : 3,
        total: item.pagar,
        entidad: { id: item.entidad_id },
        paciente: item.paciente_id,
        listExamen: listExamen
      }, '').then((json) => {
        toastr.success(json.message, 'Exito!');
        Restangular.all('citas/all/').customPOST({
          id: item.id
        }, 'changeStatus').then( data => {
          $state.go('app.catalogosolicitud.item', {
            id: json.data.id_solicitud
          }, { reload: true })
        })
      })
    })
  }

  $scope.viewDetalle = function(item) {
    $uibModal.open({
      size: 'lg',
      templateUrl: 'public/templates/modals/mostrar.examenes.citas.paciente.html',
      resolve: {
        cita: ["Restangular", () => Restangular.all(`citas/${item.id}/examenes`).customGET()]
      },
      controller: ($scope, $uibModalInstance, cita) => {
        $scope.object = cita.arrayOfObject;
        $scope.cerrar = () => $uibModalInstance.close({ result: false })
      }
    })
  }
}

module.exports.bitacora  = function  ($scope, object) {
  $scope.object = object.data;
  $scope.createDate = (value) => {
    return new Date(value)
  };

  $scope.options = {
    aoColumns: [
      { 'mData': 'No', bSortable: false },
      { 'mData': 'Usuario' },
      { 'mData': 'Recurso' },
      { 'mData': 'Operacion', bSortable: false },
      { 'mData': 'IP', bSortable: false },
      { 'mData': 'Fecha' }
    ]
  }
}