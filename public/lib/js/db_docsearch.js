function formatDate(timestamp, fmt) {

        date = new Date(timestamp*1000);

        function pad(value) {
            return (value.toString().length < 2) ? '0' + value : value;
        }

        return fmt.replace(/%([a-zA-Z])/g, function (_, fmtCode) {
            switch (fmtCode) {
                case 'd':
            return pad(date.getUTCDate());
                case 'M':
            return pad(date.getUTCMonth() + 1);
                case 'Y':
            return pad(date.getUTCFullYear());
                case 'H':
            return pad(date.getUTCHours());
                case 'm':
            return pad(date.getUTCMinutes());
                case 's':
            return pad(date.getUTCSeconds());
                default:
            throw new Error('Unsupported format code: ' + fmtCode);
            }
        });
}
    
function reload() {
  $("form").submit();
}
   
function initSearchSlider() {
  var high = parseInt($("#min").val());
  var low = parseInt($("#max").val());
  var from = parseInt($("#from").val());
  var to = parseInt($("#to").val());
  
  
  $( "#slider-range" ).slider({
	range: true,
	min: high,
	max: low,
	values: [from, to],
	slide: function( event, ui ) {
	  $( "#daterange" ).val( formatDate(ui.values[ 0 ], '%d.%M.%Y') + 
							" - " + formatDate(ui.values[ 1 ], '%d.%M.%Y'));
	  $("#from").val(ui.values[ 0 ]);
	  $("#to").val(ui.values[ 1 ]);
	},
	change: reload
  }); 
  
  $( "#daterange" ).val( formatDate($( "#slider-range" ).slider( "values", 0 ), '%d.%M.%Y') +
						" - " + formatDate($( "#slider-range" ).slider( "values", 1 ), '%d.%M.%Y'));
  $("#from").val($( "#slider-range" ).slider( "values", 0 ));
  $("#to").val($( "#slider-range" ).slider( "values", 1 ));       
}
   
  
$(document).ready(function() {
	$("#searchForm").change(reload);
	initSearchSlider(); 
});
