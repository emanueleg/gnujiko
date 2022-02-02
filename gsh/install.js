/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-01-2012
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Command Line Installer
 #VERSION: 2.0beta
 #CHANGELOG:
 #DEPENDS:
 #TODO:
 
*/

function shell_install(args, sessid, sh)
{
 sh.ftpServer = "";
 sh.ftpUser = "";
 sh.ftpPasswd = "";
 sh.ftpBasePath = "";

 for(var c=0; c < args.length; c++)
 switch(args[c])
 {
  case '--ftp-server' : { sh.ftpServer = args[c+1]; c++;} break;
  case '--ftp-user' : { sh.ftpUser = args[c+1]; c++;} break;
  case '--ftp-passwd' : { sh.ftpPasswd = args[c+1]; c++;} break;
  case '--ftp-basepath' : { sh.ftpBasePath = args[c+1]; c++;} break;
 }

 
 sh.print("Welcome to Gnujiko 10.1 installer.");
 sh.input("Please specify the database host. (usually <i>localhost</i>)",function(db_host){
	 sh.input("Specify the database name. (ie: gnujiko10)",function(db_name){
		 sh.input("Specify database user",function(db_user){
			 sh.input("Type password for database user",function(db_passwd){
				 shell_install_ftp_settings(sh,sessid,db_host,db_name,db_user,db_passwd);
				 
				});
			});
		});
	});
}

function shell_install_ftp_settings(sh,sessid,db_host,db_name,db_user,db_passwd)
{
 sh.print("If you are installing Gnujiko on a remote server (eg: Aruba, EticoWeb, and many other providers), you must provide credentials for FTP access as often for security reasons there may be restrictions on files and folders. If you are installing Gnujiko on a local machine, however, you can leave blank the parameters below.");
 sh.input("Please specify the FTP Server",function(ftp_server){
	 sh.ftpServer = ftp_server;
	 sh.input("Please specify the FTP User Name",function(ftp_user){
		 sh.ftpUser = ftp_user;
		 sh.input("Please specify the FTP Password",function(ftp_passwd){
			 sh.ftpPasswd = ftp_passwd;
			 sh.input("Please specify the FTP Path",function(ftp_path){
				 sh.ftpBasePath = ftp_path;
				 shell_install_dbcheck(sh,sessid,db_host,db_name,db_user,db_passwd);
				});
			});
		});
	});
}

function shell_install_dbcheck(sh,sessid,db_host, db_name, db_user, db_passwd)
{
 var hHR = new GHTTPRequest();
 hHR.OnHttpRequest = function(reqObj){
	 if(!reqObj.responseXML)
	  return sh.error("Checking database failed!. Unknown error: The system does not respond to the request sent.");
	 var response = reqObj.responseXML.documentElement;
	 var request = response.getElementsByTagName('request')[0];
	 if(request.getAttribute('result') == "false")
	  return sh.error(request.getAttribute('msg'));
	 if(request.getAttribute('exists') == "true")
	 {
	  sh.input("Database "+db_name+" already exists. Do you want replace it? (y/N)",function(yN){
		 if(yN.toLowerCase() == "y")
		  shell_install_dbcreate(sh,sessid,db_host,db_name,db_user,db_passwd);
		 else
		  shell_install_abort(sh,sessid);
		});
	 }
	 else
	  shell_install_dbcreate(sh,sessid,db_host,db_name,db_user,db_passwd);
	}
 hHR.send(BASE_PATH+"./installation/install_httprequest.php?request=checkdb&sessid="+sessid+"&host="+db_host+"&name="+db_name+"&user="+db_user+"&passwd="+db_passwd);
}

function shell_install_dbcreate(sh,sessid,db_host,db_name,db_user,db_passwd)
{
 sh.print("Creating tables...");
 var hHR = new GHTTPRequest();
 hHR.OnHttpRequest = function(reqObj){
	 if(!reqObj.responseXML)
	  return sh.error("Database install failed!. Unknown error: The system does not respond to the request sent.");
	 var response = reqObj.responseXML.documentElement;
	 var request = response.getElementsByTagName('request')[0];
	 if(request.getAttribute('result') == "false")
	  return sh.error(request.getAttribute('msg'));
	 sh.print('done!');
	 shell_install_createrootuser(sh,sessid,db_host,db_name,db_user,db_passwd);
	}
 hHR.send(BASE_PATH+"./installation/install_httprequest.php?request=dbinstall&sessid="+sessid+"&host="+db_host+"&name="+db_name+"&user="+db_user+"&passwd="+db_passwd+"&ftpserver="+sh.ftpServer+"&ftpuser="+sh.ftpUser+"&ftppasswd="+sh.ftpPasswd+"&ftppath="+sh.ftpBasePath);
}

function shell_install_createrootuser(sh,sessid,db_host,db_name,db_user,db_passwd)
{
 sh.print("Now the system try to create root user (the Super Administrator)");
 sh.input("Please insert a password for root",function(rootPwd){
	 sh.input("Retype root password",function(rootPwdR){
		 if(rootPwd != rootPwdR)
		 {
		  sh.print("Error: Passwords do not match! retry...");
		  return shell_install_createrootuser(sh,sessid,db_host,db_name,db_user,db_passwd);
		 }
		 var hHR = new GHTTPRequest();
		 hHR.OnHttpRequest = function(reqObj){
	 	 	 var response = reqObj.responseXML.documentElement;
	 	 	 var request = response.getElementsByTagName('request')[0];
	 	 	 if(request.getAttribute('result') == "false")
	  	 	  return sh.error(request.getAttribute('msg'));
	 	 	 sh.print('done! Root user has been created.');
	 	 	 shell_install_createfirstuser(sh,sessid,rootPwd);
		 	}
		 hHR.send(BASE_PATH+"./installation/install_httprequest.php?request=createroot&sessid="+sessid+"&host="+db_host+"&name="+db_name+"&user="+db_user+"&passwd="+db_passwd+"&rootpwd="+rootPwd);
		},'PASSWORD');
	},'PASSWORD');
}

function shell_install_createfirstuser(sh,sessid,rootPwd)
{
 sh.print("OK, now we need to create the first user.");
 sh.input("Please insert the first user name",function(userName){
	 sh.input("Type a password for user "+userName,function(userPasswd){
		 sh.input("Please retype password for "+userName,function(userPasswdR){
			 if(userPasswd != userPasswdR)
		 	 {
		  	  sh.print("Error: Passwords do not match! retry...");
		  	  return shell_install_createfirstuser(sh,sessid,rootPwd);
		 	 }
			 /* Login to root */
			 var sh2 = new GShell();
			 sh2.OnNewSession = function(){
				 sh.SessionID = this.SessionID;
				 sh.sendCommand("useradd -name `"+userName+"` -password `"+userPasswd+"` --enable-shell -privileges 'mkdir_enable=1,edit_account_info=1,run_sudo_commands=1'");
				 // Qui si deve lanciare il comando per installare i pacchetti //
				 sh.sendCommand("apm update");
				 // sh.sendCommand("gpkg install gnujiko-desktop-base apm-gui system-config-gui");
				 sh.output("<h4 style='color:#f31903'>REMOVE DIRECTORY INSTALLATION / AND PRESS CONTINUE</h4><input type='button' onclick='document.location.href=\"../accounts/Logout.php\"' value='Continue'/>");
				}
			 sh2.login("root",rootPwd);
			 sh2.OnError = function(e,s){sh.error(s);}
			 sh2.OnOutput = function(o,a){sh.finish(o);}
			},'PASSWORD');
		},'PASSWORD');
	});
}

function shell_install_abort(sh,sessid)
{
 sh.finish("Install aborted by user.");
}

