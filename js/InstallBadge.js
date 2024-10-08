/*	
installBadge.js v. 1.1
Combines code from AIR SDK Install Badge Example and
Flash Player Detection Kit Express Install Example
REQUIRES: AC_RunActiveContent.js (Can Be Obtained from Adobe AIR SDK)
*/

// -----------------------------------------------------------------------------
// Globals
// Major version of Flash required
var requiredMajorVersion = 10;
// Minor version of Flash required
var requiredMinorVersion = 0;
// Minor version of Flash required
var requiredRevision = 0;
// AIR Version Required
var airVersion = "1.5";


// -----------------------------------------------------------------------------



// Optional Settings
if( flashContentWidth == undefined ) var flashContentWidth = 217;
if( flashContentHeight == undefined ) var flashContentHeight = 180;
if( badgeDirectory == undefined ) var badgeDirectory = "";
if( expressInstallDirectory == undefined ) var expressInstallDirectory = "";
if( buttonColor == undefined ) var buttonColor = "E00000";
if( messageColor == undefined ) var messageColor = "000000";
if( airApplicationArguments == undefined ) var airApplicationArguments = "";

// Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
var hasProductInstall = DetectFlashVer(6, 0, 65);

// Version check based upon the values defined in globals
var hasReqestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);


// Check to see if a player with Flash Product Install is available and the version does not meet the requirements for playback
if ( hasProductInstall && !hasReqestedVersion ) {
	// MMdoctitle is the stored document.title value used by the installation process to close the window that started the process
	// This is necessary in order to close browser windows that are still utilizing the older version of the player after installation has completed
	// DO NOT MODIFY THE FOLLOWING FOUR LINES
	// Location visited after installation is complete if installation is required
	var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";
	var MMredirectURL = window.location;
	document.title = document.title.slice(0, 47) + " - Flash Player Installation";
	var MMdoctitle = document.title;

	AC_FL_RunContent(
		'codebase','http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab',
		'width', flashContentWidth,
		'height', flashContentHeight,
		'id', 'detection',
		'align', 'middle',
		'src', badgeDirectory + 'playerProductInstall',
		'quality', 'high',
		'bgcolor', '#3A6EA5',
		'name', 'detectionExample',
		'allowScriptAccess','always',
		'type', 'application/x-shockwave-flash',
		'pluginspage', 'http://www.adobe.com/go/getflashplayer',
		'flashvars', 'MMredirectURL='+MMredirectURL+'&MMplayerType='+MMPlayerType+'&MMdoctitle='+MMdoctitle+'',
		'movie', badgeDirectory + 'playerProductInstall'
	);
	} 
	else if (hasReqestedVersion) 
	{
	// if we've detected an acceptable version
	// embed the Flash Content SWF when all tests are passed
	AC_FL_RunContent(
		'codebase','http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab',
		'width', flashContentWidth,
		'height', flashContentHeight,
		'id','badge',
		'align','middle',
		'src', badgeDirectory + 'badge',
		'quality','high',
		'bgcolor','#FFFFFF',
		'name','badge',
		'allowscriptaccess','all',
		'type', 'application/x-shockwave-flash',
		'pluginspage','http://www.macromedia.com/go/getflashplayer',
		'flashvars','appname='+escape(airApplicationName)+'&appurl='+airApplicationURL+'&airversion='+airVersion+'&imageurl='+airApplicationImage+'&buttoncolor='+buttonColor+'&messagecolor='+messageColor+'&arguments='+airApplicationArguments,
		'movie', badgeDirectory + 'badge'
	);
} else {  // flash is too old or we can't detect the plugin
	var platform = 'unknown';
	var airLink = '';
	if (typeof(window.navigator.platform) != undefined)
	{
		platform = window.navigator.platform.toLowerCase();
		if (platform.indexOf('win') != -1)
			platform = 'win';
		else if (platform.indexOf('mac') != -1)
			platform = 'mac';
	}
	if (platform == 'win') {
		airLink = 'http://download.macromedia.com/pub/labs/air/air_b3_win_121207.exe';
	}
	else if (platform == 'mac') {
		airLink = 'http://download.macromedia.com/pub/labs/air/air_b3_mac_121207.dmg';
	}
	else {
		airLink = 'http://www.adobe.com/go/getair/';
	}
	var alternateContent = '<table id="messageTable"><tr><td>'
	+ 'This application requires the following be installed:<ol>'
	+ '<li><a href="'+airLink+'">Adobe&#174; AIR&#8482; Runtime</a></li>'
	+ '<li><a href="'+ airApplicationURL +'">'+ airApplicationName +'</a></li>'
	+ '</ol>Please click on each link in the order above to complete the installation process.</td></tr></table></div>';
	document.write(alternateContent);  // insert non-flash content
}