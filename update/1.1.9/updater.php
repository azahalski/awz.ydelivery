<?
$moduleId = "awz.ydelivery";
if(IsModuleInstalled($moduleId)) {
	$updater->CopyFiles("install/js", "js/".$moduleId);
}
?>