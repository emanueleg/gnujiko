var ifaceh = document.getElementById("ifaceh-"+SHELL_ID);
if(ifaceh)
{
 ifaceh.OnUpdate = function(msg, outArr, msgType, msgRef){
	 switch(msgType)
	 {
	  case 'ESTIMATION' : {
		 document.getElementById("ifacemsglist-"+SHELL_ID+"-title").innerHTML = msg;
		} break;

	  default : {
		 var container = document.getElementById("ifacemsglist-"+SHELL_ID);
		 var div = document.createElement('DIV');
		 div.innerHTML = msg;
		 container.appendChild(div);
		 container.scrollTop = container.scrollHeight;
		} break;
	 }
	}
}

