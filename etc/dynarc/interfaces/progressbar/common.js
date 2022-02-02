var ifaceh = document.getElementById("ifaceh-"+SHELL_ID);
if(ifaceh)
{
 ifaceh.bar = new ProgressBar();
 ifaceh.steps = 100;
 ifaceh.step = 0;

 ifaceh.OnUpdate = function(msg, outArr, msgType, msgRef){
	 switch(msgType)
	 {
	  case 'ESTIMATION' : {
		 this.steps = parseFloat(outArr['steps']);
		 this.step = 0;
		 document.getElementById("progressbar-"+SHELL_ID+"-title").innerHTML = msg;
		} break;
	  case 'PROGRESS' : {
		 this.step++;
		 document.getElementById("progressbar-"+SHELL_ID+"-message").innerHTML = msg;
		 this.bar.setValue((100/this.steps) * this.step);
		 document.getElementById("progressbar-"+SHELL_ID+"-percentage").innerHTML = this.bar.value+"%";
		} break;
	 }
	}
}

