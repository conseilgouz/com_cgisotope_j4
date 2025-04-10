/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*
*/
document.addEventListener('DOMContentLoaded', function() {

    list = document.querySelector('#jform_request_id');
    if (list.selectedIndex < 0) return;
    // init filters lists
    loadfilters(list);
})
function loadfilters(element) {
    document.body.appendChild(document.createElement('joomla-core-loader'));
    if (element.selectedIndex < 0) {
        return;
    }
    moddef = document.querySelectorAll('input[name="jform[request][moddef]"]');
    val = "";
    for (let i = 0; i < moddef.length; i++) {
        if (moddef[i].checked) val = moddef[i].value;
    }
    if (!val) {
        listbox = document.getElementById('jform_request_default_cat');
        listbox.innerHTML = ""; // empty listbox
        listbox = document.getElementById('jform_request_default_tag');
        listbox.innerHTML = ""; // empty listbox
        return;
    }
    // empty unused listbox
    if (val == 'cat') {
        listbox = document.getElementById('jform_request_default_tag');
        listbox.innerHTML = ""; // empty listbox
    } else {
        listbox = document.getElementById('jform_request_default_cat');
        listbox.innerHTML = ""; // empty listbox
    }
	var ajax = new XMLHttpRequest();
    url = '?option=com_cgisotope&task=display&type='+val+'&page='+element.value+'&format=json';
	Joomla.request({
		method : 'POST',
		url : url,
		onSuccess: function(data, xhr) {
            var result = JSON.parse(data);
            listbox = document.getElementById('jform_request_default_'+val);
            selectedValue = "";
            if (listbox.selectedOptions[0]) {
                selectedValue = listbox.selectedOptions[0].value;
            }
            listbox.innerHTML = ""; // empty listbox
            for (i = 0; i < result.length; i++ ) {
                sel = false;
                if (result[i].id == selectedValue) sel = true;
                if (val == "tag" && result[i].title == "ROOT") continue; // ignore ROOT tag
                let newOption = new Option(result[i].title,result[i].id,sel,sel);
                listbox.add(newOption,undefined);
            }
        }
    })
}
function changefilters(element) {
    val = element.value;
    list = document.querySelector('#jform_request_id');
    loadfilters(list);
}
