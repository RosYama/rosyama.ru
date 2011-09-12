<?
/*
You can place here your functions and event handlers

AddEventHandler("module", "EventName", "FunctionName");
function FunctionName(params)
{
	//code
}
*/

// подключение модуля greensight_utils
if(!IsModuleInstalled('greensight_utils'))
{
	RegisterModule('greensight_utils');
}
CModule::IncludeModule('greensight_utils');

// подключение модуля st1234holes
if(!IsModuleInstalled('st1234holes'))
{
	RegisterModule('st1234holes');
}
CModule::IncludeModule('st1234holes');

?>