/*import $ from 'jquery'
global.jQuery = $
*/
let events = {
    toastrs: (toastr, header='', text='', type='success') => {    
        switch (type) {
            case 'success': toastr.success(header, text); break;
        }
    }
}

module.exports = events;