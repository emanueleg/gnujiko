<?php

function scheduledtask_commercialdocs_overdueInvoices($sessid, $shellid)
{
 $out = "";
 $outArr = array();

 /* GET CONFIG */
 $ret = GShell("aboutconfig get-config -app gcommercialdocs -sec alerts",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $config = $ret['outarr']['config'];
 $recp = $config['overdueinvoices']['recp'];
 $sender = $config['overdueinvoices']['sender'];
 $subject = $config['overdueinvoices']['subject'];
 $ap = $config['overdueinvoices']['emailcontentap'];
 $id = $config['overdueinvoices']['emailcontentid'];

 if($ap && $id)
 {
  $ret = GShell("dynarc item-info -ap '".$ap."' -id '".$id."' || parserize -p overdueinvoices *.desc",$sessid,$shellid);
  if($ret['error']) return $ret;
  $ret = GShell("sendmail -to `".$recp."`".($sender ? " -from `".$sender."`" : "")." -subject `".($subject ? $subject : "Fatture di vendita scadute")."` -message `".$ret['message']."`",$sessid,$shellid);
  return $ret;
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function scheduledtask_commercialdocs_paymentReminders($sessid, $shellid)
{
 $out = "";
 $outArr = array();


 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//
function scheduledtask_commercialdocs_overduePurchaseInvoices($sessid, $shellid)
{
 $out = "";
 $outArr = array();

 /* GET CONFIG */
 $ret = GShell("aboutconfig get-config -app gcommercialdocs -sec alerts",$sessid,$shellid);
 if($ret['error'])
  return $ret;

 $config = $ret['outarr']['config'];
 $recp = $config['overduepurchaseinvoices']['recp'];
 $sender = $config['overduepurchaseinvoices']['sender'];
 $subject = $config['overduepurchaseinvoices']['subject'];
 $ap = $config['overduepurchaseinvoices']['emailcontentap'];
 $id = $config['overduepurchaseinvoices']['emailcontentid'];

 if($ap && $id)
 {
  $ret = GShell("dynarc item-info -ap '".$ap."' -id '".$id."' || parserize -p overduepurchaseinvoices *.desc",$sessid,$shellid);
  if($ret['error']) return $ret;
  $ret = GShell("sendmail -to `".$recp."`".($sender ? " -from `".$sender."`" : "")." -subject `".($subject ? $subject : "Fatture di acquisto scadute")."` -message `".$ret['message']."`",$sessid,$shellid);
  return $ret;
 }

 return array("message"=>$out, "outarr"=>$outArr);
}
//-------------------------------------------------------------------------------------------------------------------//

