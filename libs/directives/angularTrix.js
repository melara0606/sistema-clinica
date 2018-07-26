module.exports = ($timeout) => {
  return {
    restrict: 'A',
    require: 'ngModel',
    scope: {
      trixInitialize: '&',
      trixChange: '&',
      trixSelectionChange: '&',
      trixFocus: '&',
      trixBlur: '&',
      trixFileAccept: '&',
      trixAttachmentAdd: '&',
      trixAttachmentRemove: '&'
    },
    link: function(scope, element, attrs, ngModel) {
      let el = document.querySelector("trix-editor")

      ngModel.$render = function() {
        if(el.editor){
          el.editor.loadHTML(ngModel.$modelValue);
        }
      }

      el.addEventListener('trix-change', function() {
          ngModel.$setViewValue(el.innerHTML);
      }, false)
    }
  }
}