module.exports = function($scope, $uibModal, noticias, $sce, Restangular, toastr) {
  $scope.noticias = noticias.data;

  $scope.generadorHTML = (html) => {
    return $sce.trustAsHtml(html)
  }

  $scope.darBajaNoticia = (elemento) => {
    Restangular.all('noticias').customPOST({
      noticia: elemento.id
    }, 'status').then( json => {
      elemento.publisher = !elemento.publisher
      toastr.info(json.message, 'Exito')
    })
  }

  $scope.onpenModal = (isNew = false, elemento, $index = 0) => {
    let object = { html: '', titulo: '', id: 0};
    if(!isNew){
      object.html = elemento.body,
      object.titulo = elemento.titulo
      object.id     = elemento.id
    }

    let result = $uibModal.open({
      size: 'lg',
      resolve: {
        isNuevo: isNew,
        object:  object
      },
      templateUrl: 'public/templates/modals/noticias.html',
      controller: ($scope, $uibModalInstance, isNuevo, Restangular, toastr, object) => {
        $scope.isNuevo = isNuevo;
        $scope.titulo = object.titulo
        $scope.html   = object.html

        $scope.close = () => {
          $uibModalInstance.dismiss('cancel');
        };

        $scope.guardarDocumento = () => {
          let url = isNuevo ? 'noticias' : 'noticias/update'
          Restangular.all(url).customPOST({
            titulo : $scope.titulo,
            html   : $scope.html,
            id     : object.id
          }).then( json => {
            $uibModalInstance.close({ data: json.data, method: isNuevo });
            toastr.info(json.message, 'Exito')
          })
        }
      }
    });

    result.result.then( json => {
      if(json.method){
        $scope.noticias.push( json.data )
      }else{
        $scope.noticias[$index] = json.data
      }
    })
  }
}