window.onload =function() {
var iframe = document.createElement('iframe'); 
var div = document.createElement('div');
iframe.id='hw_widget-frame';
iframe.style.display = 'none';
iframe.setAttribute('class','helpwise_chat_widget_iframe');
iframe.setAttribute('data-user-id',helpwiseSettings.user_id);
iframe.name = "helpwise-main-iframe";
iframe.src = `display.php?user_id=${helpwiseSettings.user_id}&mailbox_id=${helpwiseSettings.mailbox_id}&firstname=${helpwiseSettings.firstname}&lastname=${helpwiseSettings.lastname}&email=${helpwiseSettings.email}`;
iframe.style.width = "25vw";
iframe.style.height = "80vh";
//iframe.scrolling = "no";
iframe.style.position ='fixed';
iframe.style.bottom = "100px";
iframe.style.right = "40px";
//iframe.frameBorder="0" ;
document.body.appendChild(iframe);
var element = document.getElementById('expand-icon');

div.id = 'helpwise-widget-launcher';
div.setAttribute('class','helpwise_chat_widget_launcher');

div.onclick = function(){
	if(iframe.style.display=='none'){
document.getElementById('expand-icon').style.display = 'none';
document.getElementById('contract-icon').style.display = 'block';
iframe.style.display='block';
}
else{
	document.getElementById('expand-icon').style.display = 'block';
	document.getElementById('contract-icon').style.display = 'none';
	iframe.style.display='none';
	}
}

div.innerHTML=`
<svg xmlns="http://www.w3.org/2000/svg" id="expand-icon" width="64" height="64" focusable="false" aria-hidden="true" viewBox="0 0 28 32" fill="none" stroke="Blue" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-circle" style="
    position: absolute;
    bottom: 20px;
    right: 50px;
">
<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
</svg> 

<svg xmlns="http://www.w3.org/2000/svg" display="none" id="contract-icon" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="Blue" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle" style="
    position: absolute;
    bottom: 20px;
    right: 50px; 
    ">
    <circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
`;
document.body.appendChild(div);
};