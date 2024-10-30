jQuery( document ).ready( function( $ ) {
   function redirectWithParams (url, parameter, value) {
      var cleanUrl = url
       .replace(new RegExp('[?&]' + parameter + '=[^&#]*(#.*)?$'), '$1')
       .replace(new RegExp('([?&])' + parameter + '=[^&]*&'), '$1');
      
      window.location.href =  cleanUrl+'&'+parameter+'='+value;
   }

   var requestTable = $('#cf7-rm-response-list');
   var dataTable = false;
   if(requestTable.length) {
      // Custom filtering function which will search data in column four between two values
      $.fn.dataTable.ext.search.push(
         function( settings, data, dataIndex ) {
            var min = new Date( $('#min').val() );
            var max = new Date( $('#max').val() + ' 23:59:59' );
            var date = new Date( data[5] );

            if (
                  ( min.toString() === 'Invalid Date' && max.toString() === 'Invalid Date' ) ||
                  ( min.toString() === 'Invalid Date' && date <= max ) ||
                  ( min <= date   && max.toString() === 'Invalid Date' ) ||
                  ( min <= date   && date <= max )
            ) {
                  return true;
            }
            return false;
         }
      );
      dataTable = requestTable.DataTable({
         columnDefs: [
            { 
               orderable: false, 
               targets: 0,
            }
         ],
         pageLength: 25,
         lengthMenu: [25, 50, 100, 250, 500],
         buttons: [
            {
               extend: 'csv',
               filename: 'cf7-rm.csv',
               exportOptions: {
                  columns: [1,2,3,4,5,6],
                  rows: function ( idx, data, node ){
                     return $(node).find('input[type="checkbox"]:checked').length > 0;
                  },
               }
           }
        ]
      });
   
      // Refilter the table
      $('#min, #max').on('change', function () {
         dataTable.draw();
      });

   }

   $('#cf7-rm-select-form').change(function(event) {
      var formId = $(this).val();
      redirectWithParams(window.location.href, 'formId', formId);
   });

   $('.status-navigation a').on('click', function(event) {
      event.preventDefault();
      var filter = $(this).data('filter');
      redirectWithParams(window.location.href, 'filter', filter);
   });
   
   $('#cf7-rm-toggle-all').change(function(event) {
      $('#cf7-rm-response-list tbody input:checkbox').each(function () { 
         this.checked = !this.checked; 
      });
   });


   

   function checkStatus (form) {
      var hasStatus = [];
      var checked = form.find('input[name="ids[]"]:checked');
      if(checked.length > 0){
         var records = checked.closest('tr');
         records.each(function(index, el) {
            if($(el).hasClass('approved') || $(el).hasClass('rejected') ){
               hasStatus.push(el)
            }
         });
         if(hasStatus.length > 0){
            var text = 'You have already replied to '+hasStatus.length+' of selected requests';
            var action = function() {
               form.submit();
            };
            printModal(text, action);
         } else {
            form.submit();
         }
      } else {
         var text = 'Select at least one record';
         printModal(text, null);
      }
   }

   $('#approve-button').on('click', function(event) {
      event.preventDefault();
      var form = $(this).closest('form');
      checkStatus(form);
      // form.submit();
   });

   $('#reject-button').on('click', function(event) {
      event.preventDefault();
      $('#mode').val('reject');
      var form = $(this).closest('form');
      checkStatus(form);
   });

   $('#remove-button').on('click', function(event) {
      event.preventDefault();
      $('#mode').val('remove');
      var form = $(this).closest('form');
      form.submit();
   });

   if(false !== dataTable){
      $('#csv-button').on('click', function(event) {
         dataTable.button(0).trigger();
      });
   }

   $('#clean-approval-attachment').on('click', function(event) {
      event.preventDefault();
      $(this).closest('.file-attachment').hide();
      $('input[name="approval_attachment"]').removeClass('hidden').val('');
      $('input[name="approval_attachment_removed"]').val('true');
   });

   $('input[name="approval_attachment"]').on('change', function(event) {
      event.preventDefault();
      var filename = $(this).val().replace(/C:\\fakepath\\/i, '');
      var fileAttachment  =  $(this).closest('.attachment-section').find('.file-attachment');
      fileAttachment.find('strong').text(filename);
      fileAttachment.show();
      $(this).addClass('hidden');
      $('input[name="approval_attachment_removed"]').val('false');
   });

   $('#clean-rejection-attachment').on('click', function(event) {
      event.preventDefault();
      $(this).closest('.file-attachment').hide();
      $('input[name="rejection_attachment"]').removeClass('hidden').val('');
      $('input[name="rejection_attachment_removed"]').val('true');
   });

   $('input[name="rejection_attachment"]').on('change', function(event) {
      event.preventDefault();
      var filename = $(this).val().replace(/C:\\fakepath\\/i, '');
      var fileAttachment  =  $(this).closest('.attachment-section').find('.file-attachment');
      fileAttachment.find('strong').text(filename);
      fileAttachment.show();
      $(this).addClass('hidden');
      $('input[name="rejection_attachment_removed"]').val('false');
   });

   if($('.cf7-rm-popup').length > 0){      
      setTimeout(function(){
         $('.cf7-rm-popup').fadeOut('500');
      }, 3000);
   }

   function printModal(text, action){
      // console.log(text, action);
      var modalHtml = $('#cf7-rm-modal');
      if(action){
         var modal = 
         '<div id="cf7-rm-modal">'+
            '<p>'+ text +'</p>' +
            '<a id="cf7-rm-proceedTo">Send again</a>'+
            '<a id="cf7-rm-close-modal">Undo</a>'
         '</div>'
      }
      else{
         var modal = 
         '<div class="display" id="cf7-rm-modal">'+
            '<p>'+ text +'</p>' +
            '<a id="cf7-rm-close-modal">OK</a>'
         '</div>'
      }
      $('.wrap').append(modal);
      if(action){
         $('#cf7-rm-proceedTo').bind('click', action);
      }
   }

   $(document).on('click', '#cf7-rm-close-modal', function(event) {
      var modalHtml = $('#cf7-rm-modal');
      event.preventDefault();
      modalHtml.fadeOut('400', function(){
         modalHtml.remove();
      });
   });
});