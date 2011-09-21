//===========::FORM VALIDATIONs::===============

function chkValidity(form, location){
	var reqFields = jQuery(form).find(".mendatory");
	var err=false;
	
	//check for empty fields:
	for(i=0; i < reqFields.length; i++){ // ERR = NULL or ERR_MSG or DEFAULT_SELECT
		if( reqFields[i].value=='' || (reqFields[i].value==0 && reqFields[i].tagName.toUpperCase()=='SELECT') ){//SELECT TAG but has initial value to '0':
			err=true;
			if( jQuery(reqFields[i]).attr('type') == 'hidden' ){
				err = false; continue; //do nothing;
			}
			else if( jQuery(reqFields[i]).attr('type') == 'file' )
				alert("Please choose media file(s).");
			else if( reqFields[i].tagName.toUpperCase() == 'SELECT' ){//SELECT TAG but no initial value:
				alert('Please complete selecting [ '+jQuery(form).find("label[for='"+reqFields[i].name+"']").text()+' ]');
			}
			else{
				var alertMsg = (location=='sidebar')? 'Please complete all fields' : 'Please complete the required fields marked with (*)';
				alert(alertMsg);
			}
			break;
		}
	}
	return (err)? false : true;
}