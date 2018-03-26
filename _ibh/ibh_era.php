<?php
	
	
	// TEMP
	// Have used "__ACTION" to remove updates to encounters and whatnot
	// to keep fingers out of regular data;
	
	
	$invoice_total = 0;
	$pay_total = 0;
	
	$debug = true;
	
	$undistributed = 0;
	
	
// for Integrated A/R.
// This retrieves existing ar_activity and billing 
function ar_get_invoice_summary($patient_id, $encounter_id, $with_detail = false) {
  
  $codes = array();
  $keysuff1 = 1000;
  $keysuff2 = 5000;

  // Get charges from services.
  $res = sqlStatement("SELECT " .
    "date, code_type, code, modifier, code_text, fee " .
    "FROM billing WHERE " .
    "pid = ? AND encounter = ? AND " .
    "activity = 1 AND fee != 0.00 ORDER BY id", array($patient_id,$encounter_id) );

  while ($row = sqlFetchArray($res)) {
    $amount = sprintf('%01.2f', $row['fee']);

	$code = $row['code'];
	if (! $code) $code = "Unknown";
	if ($row['modifier']) $code .= ':' . $row['modifier'];
	
	$codes[$code]['chg'] += $amount;
	$codes[$code]['bal'] += $amount;

    // Pass the code type, code and code_text fields
    // Although not all used yet, useful information
    // to improve the statement reporting etc.
    $codes[$code]['code_type'] = $row['code_type'];
    $codes[$code]['code_value'] = $row['code'];
    $codes[$code]['modifier'] = $row['modifier'];
    $codes[$code]['code_text'] = $row['code_text'];

    // Add the details if they want 'em.
    if ($with_detail) {
      if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
      $tmp = array();
      $tmp['chg'] = $amount;
      $tmpkey = "          " . $keysuff1++;
      $codes[$code]['dtl'][$tmpkey] = $tmp;
    }
  }
 /*
  // Get charges from product sales.
  $query = "SELECT s.drug_id, s.sale_date, s.fee, s.quantity " .
    "FROM drug_sales AS s " .
    "WHERE " .
    "s.pid = ? AND s.encounter = ? AND s.fee != 0 " .
    "ORDER BY s.sale_id";
  $res = sqlStatement($query, array($patient_id,$encounter_id) );
  
  while ($row = sqlFetchArray($res)) {
    $amount = sprintf('%01.2f', $row['fee']);
    $code = 'PROD:' . $row['drug_id'];
    $codes[$code]['chg'] += $amount;
    $codes[$code]['bal'] += $amount;
    // Add the details if they want 'em.
    if ($with_detail) {
      if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
      $tmp = array();
      $tmp['chg'] = $amount;
      $tmpkey = "          " . $keysuff1++;
      $codes[$code]['dtl'][$tmpkey] = $tmp;
    }
  }
  */

  // Get payments and adjustments. (includes copays)
  $res = sqlStatement("SELECT " .
    "a.code_type, a.code, a.modifier, a.memo, a.payer_type, a.adj_amount, a.pay_amount, a.reason_code, " .
    "a.post_time, a.session_id, a.sequence_no, a.account_code, " .
    "s.payer_id, s.reference, s.check_date, s.deposit_date " .
    ",i.name " .
    "FROM ar_activity AS a " .
    "LEFT OUTER JOIN ar_session AS s ON s.session_id = a.session_id " .
    "LEFT OUTER JOIN insurance_companies AS i ON i.id = s.payer_id " .
    "WHERE a.pid = ? AND a.encounter = ? " .
    "ORDER BY s.check_date, a.sequence_no", array($patient_id,$encounter_id) );
    
  while ($row = sqlFetchArray($res)) {
    $code = $row['code'];
    if (! $code) $code = "Unknown";
    if ($row['modifier']) $code .= ':' . $row['modifier'];
    $ins_id = 0 + $row['payer_id'];
    
    $codes[$code]['bal'] -= $row['pay_amount'];
    $codes[$code]['bal'] -= $row['adj_amount'];
    $codes[$code]['chg'] -= $row['adj_amount'];
    $codes[$code]['adj'] += $row['adj_amount'];
    
    if ($ins_id) $codes[$code]['ins'] = $ins_id;
    // Add the details if they want 'em.
    if ($with_detail) {
      if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
      $tmp = array();
      $paydate = empty($row['deposit_date']) ? substr($row['post_time'], 0, 10) : $row['deposit_date'];
      if ($row['pay_amount'] != 0) $tmp['pmt'] = $row['pay_amount'];
      if ( isset($row['reason_code'] ) ) {
      	$tmp['msp'] = $row['reason_code'];
      }
      if ($row['adj_amount'] != 0 || $row['pay_amount'] == 0) {
        $tmp['chg'] = 0 - $row['adj_amount'];
        // $tmp['rsn'] = (empty($row['memo']) || empty($row['session_id'])) ? 'Unknown adjustment' : $row['memo'];
        $tmp['rsn'] = empty($row['memo']) ? 'Unknown adjustment' : $row['memo'];
        $tmpkey = $paydate . $keysuff1++;
      }
      else {
        $tmpkey = $paydate . $keysuff2++;
      }
      if ($row['account_code'] == "PCP") {
        //copay
        $tmp['src'] = 'Pt Paid';
      }
      else {
        $tmp['src'] = empty($row['session_id']) ? $row['memo'] : $row['reference'];
      }
      $tmp['insurance_company'] = substr($row['name'], 0, 10);
      if ($ins_id) $tmp['ins'] = $ins_id;
      $tmp['plv'] = $row['payer_type'];
      $tmp['arseq'] = $row['sequence_no'];
      $codes[$code]['dtl'][$tmpkey] = $tmp;
    }
  }
  return $codes;
}


function ibh_get_era_undistributed($session_id) {
	
		$sq = sqlStatement("SELECT * FROM ar_session WHERE session_id='$session_id'");
		$session_info = sqlFetchArray($sq);
		$check_total = $session_info['pay_total'];
		
		$sql = "SELECT * FROM ar_activity WHERE session_id='$session_id' ORDER BY encounter, sequence_no";

	    $distributed = 0;
		
	    $arq = sqlStatement($sql);
	    
	    while($a = sqlFetchArray($arq)){ 
		    $pay = $a['pay_amount'] == "0.00"? 0: $a['pay_amount'];
		    $distributed += $pay;  
		}
		
		$undistributed = number_format($check_total - $distributed, 2);
		
		return '{"undistributed":"' . $undistributed . '", "check_total":"' . $check_total . '"}';
		
}



function ibh_ar_insert($values) {
		
	$question_marks = getQuestionMarks($values);
	$insert = getInsertString($values);
	
	$query = "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_user, session_id, memo, pay_amount, adj_amount, modified_time) VALUES (" . getQuestionMarks($values) . ")";
	
	// return $query;
	
	$stmt = sqlStatementx($query, $values);
	
	if ($stmt) {
		return true;
	} else {
		return false;
	}
	
}



function ibh_get_era_error_code($era) {
	
	global $era_data;
	global $era_stack;
	global $chunk_id;
	global $warning_html;
	
	
	$era_data = $era;
	
	$era_data['id']	= $chunk_id;
	
	
	$ibh_heads_up = array();
		
	$era_data_codes = ibh_get_era_svc_codes($era['svc']);
			
	if (count($era_data_codes) > 0) {
		$warning_html .= "<div class='warning-section chunk'><a href='#chunk_" . $chunk_id . "'>" . $era['patient_fname'] . " " . $era['patient_lname'];
		foreach ($era_data_codes as $w_codes) {
			$warning_html .= "<br>" . $w_codes['era_code'] . ": " . $w_codes['message'] . "";
			
			$ibh_heads_up[] = $w_codes['era_code'];
		}
		$warning_html .= "</a></div>";
		
	} else {
		// $warning_html .= "<div class='non-warning-section chunk'><a href='#chunk_" . $chunk_id . "'>" . $era['patient_fname'] . " " . $era['patient_lname'] . "</div>";
		
	} 
	
	$chunk_id++;
	
	if (count($ibh_heads_up) > 0) $era_data['ibh_heads_up'] = implode(",", $ibh_heads_up);
	
	$era_stack[] = $era_data;
}	



###
###
### FROM billing.inc
###
###
function updateClaim($newversion, $patient_id, $encounter_id, $payer_id=-1, $payer_type=-1,
  $status=-1, $bill_process=-1, $process_file='', $target='', $partner_id=-1,$crossover=0) {
	
  global $debug;
  
  if (!$newversion) {
    $sql = "SELECT * FROM claims WHERE patient_id = '$patient_id' AND " .
      "encounter_id = '$encounter_id' AND status > 0 AND status < 4 ";
    if ($payer_id >= 0) $sql .= "AND payer_id = '$payer_id' ";
    $sql .= "ORDER BY version DESC LIMIT 1";
    $row = sqlQuery($sql);
    if (!$row) return 0;
    
    // if defaults indicate empty params
    if ($payer_id     < 0) $payer_id     = $row['payer_id'];
    if ($status       < 0) $status       = $row['status'];
    if ($bill_process < 0) $bill_process = $row['bill_process'];
    if ($partner_id   < 0) $partner_id   = $row['x12_partner_id'];
    if (!$process_file   ) $process_file = $row['process_file'];
    if (!$target         ) $target       = $row['target'];
  }

  $claimset = "";
  $billset = "";
  if (empty($payer_id) || $payer_id < 0) $payer_id = 0;

  if ($status==7) {//$status==7 is the claim denial case.
    $claimset .= ", status = '$status'";
  }
  elseif ($status >= 0) {
    $claimset .= ", status = '$status'";
    if ($status > 1) {
      $billset .= ", billed = 1";
      if ($status == 2) $billset  .= ", bill_date = NOW()";
    } else {
      $billset .= ", billed = 0";
    }
  }
  if ($status==7) {//$status==7 is the claim denial case.
    $billset  .= ", bill_process = '$status'";
  }
  elseif ($bill_process >= 0) {
    $claimset .= ", bill_process = '$bill_process'";
    $billset  .= ", bill_process = '$bill_process'";
  }
  if ($status==7) {//$status==7 is the claim denial case.
    $claimset  .= ", process_file = '$process_file'";//Denial reason code is stored here
  }
  elseif ($process_file) {
    $claimset .= ", process_file = '$process_file', process_time = NOW()";
    $billset  .= ", process_file = '$process_file', process_date = NOW()";
  }
  if ($target) {
    $claimset .= ", target = '$target'";
    $billset  .= ", target = '$target'";
  }
  if ($payer_id >= 0) {
    $claimset .= ", payer_id = '$payer_id', payer_type = '$payer_type'";
    $billset  .= ", payer_id = '$payer_id'";
  }
  if ($partner_id >= 0) {
    $claimset .= ", x12_partner_id = '$partner_id'";
    $billset  .= ", x12_partner_id = '$partner_id'";
  }


  if ($billset) {
    $billset = substr($billset, 2);
    $bill_query = "UPDATE billing SET $billset WHERE encounter = '$encounter_id' AND pid='$patient_id' AND activity = 1";
    if (!$debug) {
	    sqlStatement($bill_query);
    } else {
	    echo $bill_query . "<br>";
    }
    
  }

  // If a new claim version is requested, insert its row.
  //
  if ($newversion) {
    
    if($crossover<>1) {
	    
    $sql = "INSERT INTO claims SET " .
      "patient_id = '$patient_id', " .
      "encounter_id = '$encounter_id', " .
      "bill_time = NOW() $claimset";
    
    } else { //Claim automatic forward case.
     
     $sql = "INSERT INTO claims SET " .
      "patient_id = '$patient_id', " .
      "encounter_id = '$encounter_id', " .
      "bill_time = NOW(), status=$status";
     
     }
    
     if (!$debug) sqlStatement($sql);
     
  } else if ($claimset) {

  // Otherwise update the existing claim row.
  //
  
    $claimset = substr($claimset, 2);
    if (!$debug) {
	    sqlStatement("UPDATE claims SET $claimset WHERE " .
      "patient_id = '$patient_id' AND encounter_id = '$encounter_id' AND " .
      // "payer_id = '" . $row['payer_id'] . "' AND " .
      "version = '" . $row['version'] . "'");
    }
    
  }

  // Whenever a claim is marked billed, update A/R accordingly.
  //
  if ($status == 2) {
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) {
      if ($payer_type > 0) {
	      if (!$debug) {
          sqlStatement("UPDATE form_encounter SET " .
          "last_level_billed = '$payer_type' WHERE " .
          "pid = '$patient_id' AND encounter = '$encounter_id'");
          }
      }
    }
    else {
      $ws = new WSClaim($patient_id, $encounter_id);
    }
  }

  return 1;
}



	
###
### FROM sl_eob.inc.php
###
###
###
  
  function slInvoiceNumber(&$out) {
    
    $invnumber = $out['our_claim_id'];
    $atmp = preg_split('/[ -]/', $invnumber);
    $acount = count($atmp);

    $pid = 321;
    $encounter = 123;
    
   
    if ($acount == 2) {
	    
      $pid = $atmp[0];
      $encounter = $atmp[1];
      
      //echo "ACOUNT_2: " . $invnumber;
       
    } else if ($acount == 3) {
      
      $pid = $atmp[0];
      $brow = sqlQuery("SELECT encounter FROM billing WHERE " .
        "pid = '$pid' AND encounter = '" . $atmp[1] . "' AND activity = 1");
        
      $encounter = $brow['encounter'];
      
      
      //echo "ACOUNT_3: " . $invnumber;
      
      
    } else if ($acount == 1) {
	    
	   // NON-PARSIBLE ACCOUNT NUMBER
	   
      /*$pres = sqlStatement("SELECT pid FROM patient_data WHERE " .
        "lname LIKE '" . addslashes($out['patient_lname']) . "' AND " .
        "fname LIKE '" . addslashes($out['patient_fname']) . "' " .
        "ORDER BY pid DESC");
      while ($prow = sqlFetchArray($pres)) {
        if (strpos($invnumber, $prow['pid']) === 0) {
          $pid = $prow['pid'];
          $encounter = substr($invnumber, strlen($pid));
          break;
        }
      }
      */
      $pid = $invnumber;
      $encounter = "";
      
    }

    if ($pid && $encounter) $invnumber = "$pid.$encounter";
    return array($pid, $encounter, $invnumber);
  }



  // This gets a posting session ID.  If the payer ID is not 0 and a matching
  // session already exists, then its ID is returned.  Otherwise a new session
  // is created.
  // IF THERE'S A PAYER ID _AND RECORDED SESSION, IT RETRIEVES SESSION ID
  // OTHERWISE: CREATES SESSION WITH payer_id=0 (patient payer)
  
  function arGetSession($payer_id, $reference, $check_date, $deposit_date='', $pay_total=0) {
    if (empty($deposit_date)) $deposit_date = $check_date;
    
    if ($payer_id) {
      $row = sqlQuery("SELECT session_id FROM ar_session WHERE " .
        "payer_id = '$payer_id' AND reference = '$reference' AND " .
        "check_date = '$check_date' AND deposit_date = '$deposit_date' " .
        "ORDER BY session_id DESC LIMIT 1");
      if (!empty($row['session_id'])) return $row['session_id'];
    }
    
    $query = "INSERT INTO ar_session ( " .
      "payer_id, user_id, reference, check_date, deposit_date, pay_total " .
      ") VALUES ( " .
      "'$payer_id', " .
      "'" . $_SESSION['authUserID'] . "', " .
      "'$reference', " .
      "'$check_date', " .
      "'$deposit_date', " .
      "'$pay_total' " .
      ")";
    
    if (!$debug) {
	    return sqlInsert($query);
    } else {
	    echo $query;
    }
    
  }
  
  
  
  //writing the check details to Session Table on ERA proxcessing
function arPostSession($payer_id,$check_number,$check_date,$pay_total,$post_to_date,$deposit_date) {
	
	global $debug;
	
      $query = "INSERT INTO ar_session( " .
      "payer_id, user_id, closed, reference, check_date, pay_total, post_to_date, deposit_date, patient_id, payment_type, adjustment_code, payment_method) VALUES ( " .
      "'$payer_id'," .
      $_SESSION['authUserID']."," .
      "0," .
      "'ePay - $check_number'," .
      "'$check_date', " .
      "$pay_total, " .
      "'$post_to_date','$deposit_date', " .
      "0,'insurance','insurance_payment','electronic'" .
        ")";
    
    
    
    if ($debug) {
      echo $query . "<br>\n";
    } else {
     $sessionId=sqlInsert($query);
    return $sessionId;
    }
    
    
  }
  
  
  
  // Post a payment, new style.
  // // requires that session_id has been created with insertion of session
  function arPostPayment($patient_id, $encounter_id, $session_id, $amount, $code, $payer_type, $memo, $time='', $codetype='') {
	
	global $debug;
    
    $codeonly = $code;
    $modifier = '';
    $tmp = strpos($code, ':');
    if ($tmp) {
      $codeonly = substr($code, 0, $tmp);
      $modifier = substr($code, $tmp+1);
    }
    if (empty($time)) $time = date('Y-m-d H:i:s');
    $query = "INSERT INTO ar_activity ( " .
      "pid, encounter, code_type, code, modifier, payer_type, post_time, post_user, " .
      "session_id, memo, pay_amount " .
      ") VALUES ( " .
      "'$patient_id', " .
      "'$encounter_id', " .
      "'$codetype', " .
      "'$codeonly', " .
      "'$modifier', " .
      "'$payer_type', " .
      "'$time', " .
      "'" . $_SESSION['authUserID'] . "', " .
      "'$session_id', " .
      "'$memo', " .
      "'$amount' " .
      ")";
     
    if (!$debug) {
	    sqlStatement($query);
	} else {
		// echo $query;
	}
  
    return;
  }
  



  // Post an adjustment, new style.
  //
  function arPostAdjustment($patient_id, $encounter_id, $session_id, $amount, $code, $payer_type, $reason, $time='', $codetype='') {
	  
	global $debug;
	
    $codeonly = $code;
    $modifier = '';
    $tmp = strpos($code, ':');
    if ($tmp) {
      $codeonly = substr($code, 0, $tmp);
      $modifier = substr($code, $tmp+1);
    }
    if (empty($time)) $time = date('Y-m-d H:i:s');
    
    $query = "INSERT INTO ar_activity ( " .
      "pid, encounter, code_type, code, modifier, payer_type, post_user, post_time, " .
      "session_id, memo, adj_amount " .
      ") VALUES ( " .
      "'$patient_id', " .
      "'$encounter_id', " .
      "'$codetype', " .
      "'$codeonly', " .
      "'$modifier', " .
      "'$payer_type', " .
      "'" . $_SESSION['authUserID'] . "', " .
      "'$time', " .
      "'$session_id', " .
      "'$reason', " .
      "'$amount' " .
      ")";
      
    if (!$debug) sqlStatement($query);
      
    return;
    
  }


  function arGetPayerID($patient_id, $date_of_service, $payer_type) {
    if ($payer_type < 1 || $payer_type > 3) return 0;
    $tmp = array(1 => 'primary', 2 => 'secondary', 3 => 'tertiary');
    $value = $tmp[$payer_type];
    $query = "SELECT provider FROM insurance_data WHERE " .
      "pid = ? AND type = ? AND date <= ? " .
      "ORDER BY date DESC LIMIT 1";
    $nprow = sqlQuery($query, array($patient_id,$value,$date_of_service) );
    if (empty($nprow)) return 0;
    return $nprow['provider'];
  }
  
  

  // 
  function arSetupSecondary($patient_id, $encounter_id, $debug,$crossover=0) {
    
    if ($crossover==1) {
    	//if claim forwarded setting a new status 
		$status=6;
    
    } else {
    
    	$status=1;
    
    }
    // Determine the next insurance level to be billed.
    $ferow = sqlQuery("SELECT date, last_level_billed " .
      "FROM form_encounter WHERE " .
      "pid = '$patient_id' AND encounter = '$encounter_id'");
    $date_of_service = substr($ferow['date'], 0, 10);
    $new_payer_type = 0 + $ferow['last_level_billed'];
    if ($new_payer_type < 3 && !empty($ferow['last_level_billed']) || $new_payer_type == 0)
      ++$new_payer_type;

    $new_payer_id = arGetPayerID($patient_id, $date_of_service, $new_payer_type);

    if ($new_payer_id) {
        // Queue up the claim.
        updateClaim(true, $patient_id, $encounter_id, $new_payer_id, $new_payer_type,$status, 5, '', 'hcfa','',$crossover);
      
    }
    else {
        // Just reopen the claim.
        updateClaim(true, $patient_id, $encounter_id, -1, -1, $status, 0, '','','',$crossover);
    }

    return xl("Encounter ") . $encounter . xl(" is ready for re-billing.");
  }
  
  
  
  
########
###
### FROM library/parse_era.inc.php
###
###
###
// This is a sub-routine of parse_era
function parse_era_2100(&$out, $cb) {
	
	// only runs for 2100 or 2110, which seem to be entire X12
    if ($out['loopid'] == '2110' || $out['loopid'] == '2100') {

        // Production date is posted with adjustments, so make sure it exists.
        if (!$out['production_date']) $out['production_date'] = $out['check_date'];

        // Force the sum of service payments to equal the claim payment
        // amount, and the sum of service adjustments to equal the CLP's
        // (charged amount - paid amount - patient responsibility amount).
        // This may result from claim-level adjustments, and in this case the
        // first SVC item that we stored was a 'Claim' type.  It also may result
        // from poorly reported payment reversals, in which case we may need to
        // create the 'Claim' service type here.
        //
        $paytotal = $out['amount_approved'];
        $adjtotal = $out['amount_charged'] - $out['amount_approved'] - $out['amount_patient'];
        foreach ($out['svc'] as $svc) {
            $paytotal -= $svc['paid'];
            foreach ($svc['adj'] as $adj) {
                if ($adj['group_code'] != 'PR') $adjtotal -= $adj['amount'];
            }
        }
        $paytotal = round($paytotal, 2);
        $adjtotal = round($adjtotal, 2);
        if ($paytotal != 0 || $adjtotal != 0) {
            if ($out['svc'][0]['code'] != 'Claim') {
                array_unshift($out['svc'], array());
                $out['svc'][0]['code'] = 'Claim';
                $out['svc'][0]['mod']  = '';
                $out['svc'][0]['chg']  = '0';
                $out['svc'][0]['paid'] = '0';
                $out['svc'][0]['adj']  = array();
                $out['warnings'] .= "Procedure 'Claim' is inserted artificially to " .
                    "force claim balancing.\n";
            }
            $out['svc'][0]['paid'] += $paytotal;
            if ($adjtotal) {
                $j = count($out['svc'][0]['adj']);
                $out['svc'][0]['adj'][$j] = array();
                $out['svc'][0]['adj'][$j]['group_code']  = 'CR'; // presuming a correction or reversal
                $out['svc'][0]['adj'][$j]['reason_code'] = 'Balancing';
                $out['svc'][0]['adj'][$j]['amount'] = $adjtotal;
            }
            // if ($out['svc'][0]['code'] != 'Claim') {
            //   $out['warnings'] .= "First service item payment amount " .
            //   "adjusted by $paytotal due to payment imbalance. " .
            //   "This should not happen!\n";
            // }
        }
        $cb($out);
    }
}


// $filename is uploaded file
// $cb = callback used in nested functions, as in parse_era_2100
// returns $alertsmsg for file uploading
// formerly function parse_era
function parse_era($filename, $cb) {
  	
  	$delimiter1 = '~';
  	$delimiter2 = '|';
  	$delimiter3 = '^';

    $infh = fopen($filename, 'r');
    if (! $infh) return "ERA input file open failed";

    $out = array();
    $out['loopid'] = '';
    $out['st_segment_count'] = 0;
    $buffer = '';
    $segid = '';

    while (true) {
	    
    	if (strlen($buffer) < 2048 && ! feof($infh)) $buffer .= fread($infh, 2048);
    
        $tpos = strpos($buffer, $delimiter1);
        if ($tpos === false) break; // stops while(true) loop
        
        // inline is now next section; we're looping
        $inline = substr($buffer, 0, $tpos); 
       
        $buffer = substr($buffer, $tpos + 1); 

	    // If this is the ISA segment then figure out what the delimiters are.
	    if ($segid === '' && substr($inline, 0, 3) === 'ISA') {
	      $delimiter2 = substr($inline, 3, 1); // asterisk (4th char)
	      $delimiter3 = substr($inline, -1); // tilde (last char)
	    }

        $seg = explode($delimiter2, $inline);
        $segid = $seg[0];

        if ($segid == 'ISA') {
            if ($out['loopid']) return 'Unexpected ISA segment';
            $out['isa_sender_id']      = trim($seg[6]);
            $out['isa_receiver_id']    = trim($seg[8]);
            $out['isa_control_number'] = trim($seg[13]);
            
        } else if ($segid == 'GS') {
            if ($out['loopid']) return 'Unexpected GS segment';
            $out['gs_date'] = trim($seg[4]);
            $out['gs_time'] = trim($seg[5]);
            $out['gs_control_number'] = trim($seg[6]);
        } else if ($segid == 'ST') {
	        
            parse_era_2100($out, $cb);
            
            $out['loopid'] = '';
            $out['st_control_number'] = trim($seg[2]);
            $out['st_segment_count'] = 0;
        } else if ($segid == 'BPR') {
            if ($out['loopid']) return 'Unexpected BPR segment';
            $out['check_amount'] = trim($seg[2]);
            $out['check_date'] = trim($seg[16]); // yyyymmdd
            // TBD: BPR04 is a payment method code.
        } else if ($segid == 'TRN') {
            if ($out['loopid']) return 'Unexpected TRN segment';
            $out['check_number'] = trim($seg[2]);
            $out['payer_tax_id'] = substr($seg[3], 1); // 9 digits
            $out['payer_id'] = trim($seg[4]);
            // Note: TRN04 further qualifies the paying entity within the
            // organization identified by TRN03.
        } else if ($segid == 'REF' && $seg[1] == 'EV') {
            if ($out['loopid']) return 'Unexpected REF|EV segment';
        } else if ($segid == 'CUR' && ! $out['loopid']) {
            if ($seg[3] && $seg[3] != 1.0) {
                return("We cannot handle foreign currencies!");
            }
        } else if ($segid == 'REF' && ! $out['loopid']) {
            // ignore
        } else if ($segid == 'DTM' && $seg[1] == '405') {
            if ($out['loopid']) return 'Unexpected DTM|405 segment';
            $out['production_date'] = trim($seg[2]); // yyyymmdd
        
        // Loop 1000A is Payer Information.
        } else if ($segid == 'N1' && $seg[1] == 'PR') {
            if ($out['loopid']) return 'Unexpected N1|PR segment';
            $out['loopid'] = '1000A';
            $out['payer_name'] = trim($seg[2]);
            
        } else if ($segid == 'N3' && $out['loopid'] == '1000A') {
            $out['payer_street'] = trim($seg[1]);
            // TBD: N302 may exist as an additional address line.
            
        } else if ($segid == 'N4' && $out['loopid'] == '1000A') {
            $out['payer_city']  = trim($seg[1]);
            $out['payer_state'] = trim($seg[2]);
            $out['payer_zip']   = trim($seg[3]);
            
        } else if ($segid == 'REF' && $out['loopid'] == '1000A') {
            // Other types of REFs may be given to identify the payer, but we
            // ignore them.
        } else if ($segid == 'PER' && $out['loopid'] == '1000A') {
            // TBD: Report payer contact information as a note.
        
        // Loop 1000B is Payee Identification.
        } else if ($segid == 'N1' && $seg[1] == 'PE') {
            if ($out['loopid'] != '1000A') return 'Unexpected N1|PE segment';
            $out['loopid'] = '1000B';
            $out['payee_name']   = trim($seg[2]);
            $out['payee_tax_id'] = trim($seg[4]);
        } else if ($segid == 'N3' && $out['loopid'] == '1000B') {
            $out['payee_street'] = trim($seg[1]);
        } else if ($segid == 'N4' && $out['loopid'] == '1000B') {
            $out['payee_city']  = trim($seg[1]);
            $out['payee_state'] = trim($seg[2]);
            $out['payee_zip']   = trim($seg[3]);
        } else if ($segid == 'REF' && $out['loopid'] == '1000B') {
            // Used to report additional ID numbers.  Ignored.
        
        //
        // Loop 2000 provides for logical grouping of claim payment information.
        // LX is required if any CLPs are present, but so far we do not care
        // about loop 2000 content.
        //
        } else if ($segid == 'LX') {
            if (! $out['loopid']) return 'Unexpected LX segment';
            
            parse_era_2100($out, $cb);
            
            
            $out['loopid'] = '2000';
        } else if ($segid == 'TS2' && $out['loopid'] == '2000') {
            // ignore
        } else if ($segid == 'TS3' && $out['loopid'] == '2000') {
            // ignore
        
        // Loop 2100 is Claim Payment Information. The good stuff begins here.
        } else if ($segid == 'CLP') {
            if (! $out['loopid']) return 'Unexpected CLP segment';
  
            parse_era_2100($out, $cb);
            
            
            $out['loopid'] = '2100';
            $out['warnings'] = '';
            // Clear some stuff to start the new claim:
            $out['subscriber_lname']     = '';
            $out['subscriber_fname']     = '';
            $out['subscriber_mname']     = '';
            $out['subscriber_member_id'] = '';
            $out['crossover']=0;
            $out['svc'] = array();
            //
            // This is the poorly-named "Patient Account Number".  For 837p
            // it comes from CLM01 which we populated as pid-diagid-procid,
            // where diagid and procid are id values from the billing table.
            // For HCFA 1500 claims it comes from field 26 which we
            // populated with our familiar pid-encounter billing key.
            //
            // The 835 spec calls this the "provider-assigned claim control
            // number" and notes that it is specifically intended for
            // identifying the claim in the provider's database.
            $out['our_claim_id']      = trim($seg[1]);
            //
            $out['claim_status_code'] = trim($seg[2]);
            $out['amount_charged']    = trim($seg[3]);
            $out['amount_approved']   = trim($seg[4]);
            $out['amount_patient']    = trim($seg[5]); // pt responsibility, copay + deductible
            $out['payer_claim_id']    = trim($seg[7]); // payer's claim number
        
        } else if ($segid == 'CAS' && $out['loopid'] == '2100') {
            // This is a claim-level adjustment and should be unusual.
            // Handle it by creating a dummy zero-charge service item and
            // then populating the adjustments into it.  See also code in
            // parse_era_2100() which will later plug in a payment reversal
            // amount that offsets these adjustments.
            $i = 0; // if present, the dummy service item will be first.
            if (!$out['svc'][$i]) {
                $out['svc'][$i] = array();
                $out['svc'][$i]['code'] = 'Claim';
                $out['svc'][$i]['mod']  = '';
                $out['svc'][$i]['chg']  = '0';
                $out['svc'][$i]['paid'] = '0';
                $out['svc'][$i]['adj']  = array();
            }
            for ($k = 2; $k < 20; $k += 3) {
                if (!$seg[$k]) break;
                $j = count($out['svc'][$i]['adj']);
                $out['svc'][$i]['adj'][$j] = array();
                $out['svc'][$i]['adj'][$j]['group_code']  = $seg[1];
                $out['svc'][$i]['adj'][$j]['reason_code'] = $seg[$k];
                $out['svc'][$i]['adj'][$j]['amount']      = $seg[$k+1];
            }
        } else if ($segid == 'NM1' && $seg[1] == 'QC' && $out['loopid'] == '2100') {
	        // QC = Patient
            $out['patient_lname']     = trim($seg[3]);
            $out['patient_fname']     = trim($seg[4]);
            $out['patient_mname']     = trim($seg[5]);
            $out['patient_member_id'] = trim($seg[9]);
        }
        // IL = Insured or Subscriber
        else if ($segid == 'NM1' && $seg[1] == 'IL' && $out['loopid'] == '2100') {
            $out['subscriber_lname']     = trim($seg[3]);
            $out['subscriber_fname']     = trim($seg[4]);
            $out['subscriber_mname']     = trim($seg[5]);
            $out['subscriber_member_id'] = trim($seg[9]);
        }
        // 82 = Rendering Provider
        else if ($segid == 'NM1' && $seg[1] == '82' && $out['loopid'] == '2100') {
            $out['provider_lname']     = trim($seg[3]);
            $out['provider_fname']     = trim($seg[4]);
            $out['provider_mname']     = trim($seg[5]);
            $out['provider_member_id'] = trim($seg[9]);
        }
        else if ($segid == 'NM1' && $seg[1] == 'TT' && $out['loopid'] == '2100') {
            $out['crossover']     = 1;//Claim automatic forward case.
            
        }
        // 74 = Corrected Insured
        // TT = Crossover Carrier (Transfer To another payer)
        // PR = Corrected Payer
        else if ($segid == 'NM1' && $out['loopid'] == '2100') {
            // $out['warnings'] .= "NM1 segment at claim level ignored.\n";
        }
        else if ($segid == 'MOA' && $out['loopid'] == '2100') {
            $out['warnings'] .= "MOA segment at claim level ignored.\n";
        }
        // REF segments may provide various identifying numbers, where REF02
        // indicates the type of number.
        else if ($segid == 'REF' && $seg[1] == '1W' && $out['loopid'] == '2100') {
            $out['claim_comment'] = trim($seg[2]);
        }
        else if ($segid == 'REF' && $out['loopid'] == '2100') {
            // ignore
        }
        else if ($segid == 'DTM' && $seg[1] == '050' && $out['loopid'] == '2100') {
            $out['claim_date'] = trim($seg[2]); // yyyymmdd
        }
        // 036 = expiration date of coverage
        // 050 = date claim received by payer
        // 232 = claim statement period start
        // 233 = claim statement period end
        else if ($segid == 'DTM' && $out['loopid'] == '2100') {
            // ignore?
        }
        else if ($segid == 'PER' && $out['loopid'] == '2100') {
        
            $out['payer_insurance']  = trim($seg[2]);
            $out['warnings'] .= 'Claim contact information: ' .
                $seg[4] . "\n";
        }
        // For AMT01 see the Amount Qualifier Codes on pages 135-135 of the
        // Implementation Guide.  AMT is only good for comments and is not
        // part of claim balancing.
        else if ($segid == 'AMT' && $out['loopid'] == '2100') {
            $out['warnings'] .= "AMT segment at claim level ignored.\n";
        }
        // For QTY01 see the Quantity Qualifier Codes on pages 137-138 of the
        // Implementation Guide.  QTY is only good for comments and is not
        // part of claim balancing.
        else if ($segid == 'QTY' && $out['loopid'] == '2100') {
            $out['warnings'] .= "QTY segment at claim level ignored.\n";
        }
        //
        // Loop 2110 is Service Payment Information.
        //
        else if ($segid == 'SVC') {
            if (! $out['loopid']) return 'Unexpected SVC segment';
            $out['loopid'] = '2110';
            if (isset($seg[6])) {
                // SVC06 if present is our original procedure code that they are changing.
                // We will not put their crap in our invoice, but rather log a note and
                // treat it as adjustments to our originally submitted coding.
                $svc = explode($delimiter3, $seg[6]);
                $tmp = explode($delimiter3, $seg[1]);
                $out['warnings'] .= "Payer is restating our procedure " . $svc[1] .
                    " as " . $tmp[1] . ".\n";
            } else {
                $svc = explode($delimiter3, $seg[1]);
            }
            if ($svc[0] != 'HC') return 'SVC segment has unexpected qualifier';
            // TBD: Other qualifiers are possible; see IG pages 140-141.
            $i = count($out['svc']);
            $out['svc'][$i] = array();
            
        
		      // It seems some payers append the modifier with no separator!
		      if (strlen($svc[1]) == 7 && empty($svc[2])) {
		        $out['svc'][$i]['code'] = substr($svc[1], 0, 5);
		        $out['svc'][$i]['mod']  = substr($svc[1], 5);
		      } else {
		        $out['svc'][$i]['code'] =  $svc[1];
		        $out['svc'][$i]['mod']  =  isset($svc[2]) ? $svc[2] . ':' : '';
		        $out['svc'][$i]['mod']  .= isset($svc[3]) ? $svc[3] . ':' : '';
		        $out['svc'][$i]['mod']  .= isset($svc[4]) ? $svc[4] . ':' : '';
		        $out['svc'][$i]['mod']  .= isset($svc[5]) ? $svc[5] . ':' : '';
		        $out['svc'][$i]['mod'] = preg_replace('/:$/','',$out['svc'][$i]['mod']);
		      }
            $out['svc'][$i]['chg']  = $seg[2];
            $out['svc'][$i]['paid'] = $seg[3];
            $out['svc'][$i]['adj']  = array();
            // Note: SVC05, if present, indicates the paid units of service.
            // It defaults to 1.
        }
        // DTM01 identifies the type of service date:
        // 472 = a single date of service
        // 150 = service period start
        // 151 = service period end
        else if ($segid == 'DTM' && $out['loopid'] == '2110') {
            $out['dos'] = trim($seg[2]); // yyyymmdd
        } else if ($segid == 'CAS' && $out['loopid'] == '2110') {
            $i = count($out['svc']) - 1;
            for ($k = 2; $k < 20; $k += 3) {
                if (!isset($seg[$k])) break;
                
		        if ($seg[1] == 'CO' && $seg[$k+1] < 0) {
			        // IBH_DEV
			        // This looks like a possible source of the negative issue...
		          $out['warnings'] .= "Negative Contractual Obligation adjustment seems wrong. Inverting, but should be checked!\n";
		          $seg[$k+1] = 0 - $seg[$k+1];
		        }
				$j = count($out['svc'][$i]['adj']);
				$out['svc'][$i]['adj'][$j] = array();
				$out['svc'][$i]['adj'][$j]['group_code']  = $seg[1];
				$out['svc'][$i]['adj'][$j]['reason_code'] = $seg[$k];
				$out['svc'][$i]['adj'][$j]['amount']      = $seg[$k+1];
				// Note: $seg[$k+2] is "quantity".  A value here indicates a change to
				// the number of units of service.  We're ignoring that for now.
            }
        } else if ($segid == 'REF' && $out['loopid'] == '2110') {
            // ignore
        } else if ($segid == 'AMT' && $seg[1] == 'B6' && $out['loopid'] == '2110') {
            $i = count($out['svc']) - 1;
            $out['svc'][$i]['allowed'] = $seg[2]; // report this amount as a note
        } else if ($segid == 'AMT' && $out['loopid'] == '2110') {
            $out['warnings'] .= "$inline at service level ignored.\n";
        } else if ($segid == 'LQ' && $seg[1] == 'HE' && $out['loopid'] == '2110') {
            $i = count($out['svc']) - 1;
            $out['svc'][$i]['remark'] = $seg[2];
        } else if ($segid == 'QTY' && $out['loopid'] == '2110') {
            $out['warnings'] .= "QTY segment at service level ignored.\n";
        } else if ($segid == 'PLB') {
            // Provider-level adjustments are a General Ledger thing and should not
            // alter the A/R for the claim, so we just report them as notes.
            for ($k = 3; $k < 15; $k += 2) {
                if (!$seg[$k]) break;
                $out['warnings'] .= 'PROVIDER LEVEL ADJUSTMENT (not claim-specific): $' .
                    sprintf('%.2f', $seg[$k+1]) . " with reason code " . $seg[$k] . "\n";
                // Note: For PLB adjustment reason codes see IG pages 165-170.
            }
        } else if ($segid == 'SE') {
	        
            parse_era_2100($out, $cb);
            
            $out['loopid'] = '';
            if ($out['st_control_number'] != trim($seg[2])) {
                return 'Ending transaction set control number mismatch';
            }
            if (($out['st_segment_count'] + 1) != trim($seg[1])) {
                return 'Ending transaction set segment count mismatch';
            }
        } else if ($segid == 'GE') {
            if ($out['loopid']) return 'Unexpected GE segment';
            if ($out['gs_control_number'] != trim($seg[2])) {
                return 'Ending functional group control number mismatch';
            }
        } else if ($segid == 'IEA') {
            if ($out['loopid']) return 'Unexpected IEA segment';
            if ($out['isa_control_number'] != trim($seg[2])) {
                return 'Ending interchange control number mismatch';
            }
        } else {
            return "Unknown or unexpected segment ID $segid";
        }

        ++$out['st_segment_count'];
    }

    if ($segid != 'IEA') return 'Premature end of ERA file';
    
}







//for getting the check details and provider details
function parse_era_for_check($filename) {
  $delimiter1 = '~';
  $delimiter2 = '|';
  $delimiter3 = '^';

    $infh = fopen($filename, 'r');
    if (! $infh) return "ERA input file open failed";

    $out = array();
    $out['loopid'] = '';
    $out['st_segment_count'] = 0;
    $buffer = '';
    $segid = '';
    $check_count=0;
    while (true) {
    
    if (strlen($buffer) < 2048 && ! feof($infh)) $buffer .= fread($infh, 2048);
        $tpos = strpos($buffer, $delimiter1);
        if ($tpos === false) break;
        $inline = substr($buffer, 0, $tpos);
        $buffer = substr($buffer, $tpos + 1);

    // If this is the ISA segment then figure out what the delimiters are.
    if ($segid === '' && substr($inline, 0, 3) === 'ISA') {
      $delimiter2 = substr($inline, 3, 1);
      $delimiter3 = substr($inline, -1);
    }

        $seg = explode($delimiter2, $inline);
        $segid = $seg[0];

        if ($segid == 'ISA') {
            
        }
        else if ($segid == 'BPR') {
        ++$check_count;
            //if ($out['loopid']) return 'Unexpected BPR segment';
            $out['check_amount'.$check_count] = trim($seg[2]);
            $out['check_date'.$check_count] = trim($seg[16]); // yyyymmdd
            // TBD: BPR04 is a payment method code.
        
        }
        else if ($segid == 'N1' && $seg[1] == 'PE') {
            //if ($out['loopid'] != '1000A') return 'Unexpected N1|PE segment';
            $out['loopid'] = '1000B';
            $out['payee_name'.$check_count]   = trim($seg[2]);
            $out['payee_tax_id'.$check_count] = trim($seg[4]);
            
        }
        else if ($segid == 'TRN') {
            //if ($out['loopid']) return 'Unexpected TRN segment';
            $out['check_number'.$check_count] = trim($seg[2]);
            $out['payer_tax_id'.$check_count] = substr($seg[3], 1); // 9 digits
            $out['payer_id'.$check_count] = trim($seg[4]);
            // Note: TRN04 further qualifies the paying entity within the
            // organization identified by TRN03.
        }
            
        
        
        
        ++$out['st_segment_count'];
    }
    $out['check_count']=$check_count;
    era_callback_check($out);

    if ($segid != 'IEA') return 'Premature end of ERA file';
    return '';
    }



#####
#####
##### From sl_eob_process
#####

    function parse_date($date) {
        $date = substr(trim($date), 0, 10);
        if (preg_match('/^(\d\d\d\d)\D*(\d\d)\D*(\d\d)$/', $date, $matches)) {
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }
        return '';
    }

    function writeTableRow($name, $description, $class="normal") {
	   	   
	   	// $class = str_replace(" " , "-", strtolower($name));
	   	
        $dline =
            " <tr class='era_row $class'>\n" .
             "<td>$name</td><td colspan='6'>$description</td>\n" .
            " </tr>\n";
            
        return $dline;
    }


	
function oeFormatMoney($amount, $symbol=false) {
  $s = number_format($amount,
    $GLOBALS['currency_decimals'],
    $GLOBALS['currency_dec_point'],
    $GLOBALS['currency_thousands_sep']);
  // If the currency symbol exists and is requested, prepend it.
  if ($symbol && !empty($GLOBALS['gbl_currency_symbol']))
    $s = $GLOBALS['gbl_currency_symbol'] . " $s";
  return $s;
}

function oeFormatShortDate($date='today') {
  if ($date === 'today') $date = date('Y-m-d');
  if (strlen($date) == 10) {
    // assume input is yyyy-mm-dd
    if ($GLOBALS['date_display_format'] == 1)      // mm/dd/yyyy
      $date = substr($date, 5, 2) . '/' . substr($date, 8, 2) . '/' . substr($date, 0, 4);
    else if ($GLOBALS['date_display_format'] == 2) // dd/mm/yyyy
      $date = substr($date, 8, 2) . '/' . substr($date, 5, 2) . '/' . substr($date, 0, 4);
  }
  return $date;
}




    function writeDetailLine($bgcolor, $class, $ptname, $invnumber, $code, $date, $description, $amount, $balance) {
        
        global $last_ptname, $last_invnumber, $last_code;
        
        //if ($ptname == $last_ptname) $ptname = '&nbsp;';
        //    else $last_ptname = $ptname;
            
        if ($invnumber == $last_invnumber) $invnumber = '&nbsp;';
            else $last_invnumber = $invnumber;
            
        //if ($code == $last_code) $code = '&nbsp;';
        //   else $last_code = $code;
            
        if ($amount ) $amount  = sprintf("%.2f", $amount );
        if ($balance) $balance = sprintf("%.2f", $balance);
        
        $dline =
            " <tr class='$class'>\n" .
            "  <td>$ptname</td>\n" .
            "  <td>$invnumber</td>\n" .
            "  <td>$code</td>\n" .
            "  <td>" . oeFormatShortDate($date) . "</td>\n" .
            "  <td>$description</td>\n" .
            "  <td align='right'>" . oeFormatMoney($amount) . "</td>\n" .
            "  <td align='right'>&nbsp;</td>\n" .
            " </tr>\n";
            
        return $dline;
    }

    // This writes detail lines that were already in SQL-Ledger for a given
    // charge item.
    //
    function write_billing_ar_record(&$prev, $ptname, $invnumber, $dos, $code, $bgcolor) {
        
        global $invoice_total;
        // $prev['total'] = 0.00; // to accumulate total charges
        ksort($prev['dtl']);
        $is_billing = false;
        
        $pid = explode(".", $invnumber)[0];
        $enc = explode(".", $invnumber)[1];
         
        foreach ($prev['dtl'] as $dkey => $ddata) {
            $ddate = substr($dkey, 0, 10);
            $description = $ddata['src'] . $ddata['rsn'];
            if ($ddate == '          ') { // this is the service item
                $ddate = $dos;
                $description = 'Billed';
                $is_billing = true;
            }
            $amount = sprintf("%.2f", $ddata['chg'] - $ddata['pmt']);
            // $invoice_total = sprintf("%.2f", $invoice_total + $amount);
            $class = $is_billing ? "olddetail": "errdetail";
            
            return writeDetailLine($bgcolor, $class, "<a target='_blank' href='patient_account.php?pid=" . $pid . "#ENC_" . $enc . "'>" . $ptname . "</a>", $invnumber, $code, $ddate, $description, $amount, $invoice_total);
                
        }
        
    }





    // This is called back by parse_era() once per claim.
    //
    function era_callback_check(&$out) {
	    
    	global $InsertionId;//last inserted ID of 
        global $StringToEcho,$debug;
        
        if($_GET['original']=='original')
        {
        $StringToEcho="<br/><br/><br/><br/><br/><br/>";
        $StringToEcho.="<table border='1' cellpadding='0' cellspacing='0'  width='750'>";
        $StringToEcho.="<tr bgcolor='#cccccc'><td width='50'></td><td class='dehead' width='150' align='center'>".htmlspecialchars( xl('Check Number'), ENT_QUOTES)."</td><td class='dehead' width='400'  align='center'>".htmlspecialchars( xl('Payee Name'), ENT_QUOTES)."</td><td class='dehead'  width='150' align='center'>".htmlspecialchars( xl('Check Amount'), ENT_QUOTES)."</td></tr>";
        $WarningFlag=false;
        for ($check_count=1;$check_count<=$out['check_count'];$check_count++)
         { 
            if($check_count%2==1) {
                $bgcolor='#ddddff';
             } else {
                $bgcolor='#ffdddd';
             }
             $rs=sqlQ("select reference from ar_session where reference='".$out['check_number'.$check_count]."'");
             if(sqlNumRows($rs)>0)
             {
                $bgcolor='#ff0000';
                $WarningFlag=true;
             }
            $StringToEcho.="<tr bgcolor='$bgcolor'>";
            $StringToEcho.="<td><input type='checkbox'  name='chk".$out['check_number'.$check_count]."' value='".$out['check_number'.$check_count]."'/></td>";
            $StringToEcho.="<td>".htmlspecialchars($out['check_number'.$check_count])."</td>";
            $StringToEcho.="<td>".htmlspecialchars($out['payee_name'.$check_count])."</td>";
            $StringToEcho.="<td align='right'>".htmlspecialchars(number_format($out['check_amount'.$check_count],2))."</td>";
            $StringToEcho.="</tr>";
        }
        $StringToEcho.="<tr bgcolor='#cccccc'><td colspan='4' align='center'><input type='submit'  name='CheckSubmit' value='Submit'/></td></tr>";
        if($WarningFlag==true)
            $StringToEcho.="<tr bgcolor='#ff0000'><td colspan='4' align='center'>".htmlspecialchars( xl('Warning, Check Number already exist in the database'), ENT_QUOTES)."</td></tr>";
         $StringToEcho.="</table>";
        }
        else
        {
        for ($check_count=1;$check_count<=$out['check_count'];$check_count++)
         { 
        $chk_num=$out['check_number'.$check_count];
        $chk_num=str_replace(' ','_',$chk_num);
        
        
        
        // if there's a check number...
        if(isset($_REQUEST['chk'.$chk_num]))
        {
        $check_date=$out['check_date'.$check_count]?$out['check_date'.$check_count]:$_REQUEST['paydate'];
        $post_to_date=$_REQUEST['post_to_date']!=''?$_REQUEST['post_to_date']:date('Y-m-d');
        $deposit_date=$_REQUEST['deposit_date']!=''?$_REQUEST['deposit_date']:date('Y-m-d');
        
        
        // STUFF HAPPENING HERE
        // uses arPostSession()
        // params
        /*
	        $_REQUEST['InsId']                     $payer_id
	        $out['check_number' . $check_count]	   $check_number
	        $out['check_date' . $check_count]      $check_date
	        $out['check_amount' . $check_count]    $pay_total
	        $post_to_date                          $post_to_date
	        $deposit_date                          $deposit_date
	        $debug                                 $debug
	    */
	    
	    
        $InsertionId[$out['check_number'.$check_count]] = arPostSession($_REQUEST['InsId'],$out['check_number'.$check_count],$out['check_date'.$check_count],$out['check_amount'.$check_count],$post_to_date,$deposit_date);
        
        
        }
        }
        }
    }
    
  
    function check_ar_by_encounter($enc) {
	    
		$query = sqlStatement("SELECT * FROM ar_activity WHERE encounter=?", array($enc));
		$mssg = "";
		
		while ($ret = sqlFetchArray($query)) {
			$mssg .= $ret['code'] . $ret['modifier'] . ":S#" . $ret['session_id'] . ": Paid: $" . $ret['pay_amount'] . " Adj: $" . $ret['adj_amount'] . "<br>";  
		}
	
		
	    return $mssg;
    }

    
    function era_callback($out) {
	    
	    $table = "";
	    $html = "";
	    $billing_html = "";
	    $ar_html = "";
	    $session_callout = false;
	    
	    
        global $encount, $debug, $claim_status_codes, $adjustment_reasons, $remark_codes;
        global $invoice_total, $last_code, $paydate, $InsertionId, $check_total, $undistributed;
        
        global $era_stack, $chunk_id;
        
        $check_123 = true; // isset($_REQUEST['chk' . $chk_123])
        
		$out['id']	= $chunk_id++;
		
		
		$missing_post = false;
		
		$html .= writeTableRow('Payer', htmlspecialchars($out['check_number'], ENT_QUOTES));
		 
        // Some heading information.
        $chk_123 = $out['check_number'];
        $chk_123 = str_replace(' ', '_', $chk_123);
        
        if($check_123 == true){
	        
	        
	     
	     /*   
        if ($encount == 0) {
            $html .= writeTableRow("Payer" , htmlspecialchars($out['payer_name'], ENT_QUOTES));
            if ($debug) {
                $html .= writeTableRow('Debug', "NO ENCOUNTER");
            }
        }
		*/
		
        $last_code = "";
        // $invoice_total = 0.00;
        $bgcolor = "";
                
        list($pid, $encounter, $invnumber) = slInvoiceNumber($out);
		
		
		$html .= writeTableRow('Invoice', $invnumber);
		
		
        // Get details, if we have them, for the invoice.
        $inverror = true;
        $codes = array();
        
        if ($pid && $encounter) {
        
        // Get encounter + patient name data into $ferow.
        $ferow = sqlQuery("SELECT e.*, p.fname, p.mname, p.lname " .
          "FROM form_encounter AS e, patient_data AS p WHERE " .
          "e.pid = '$pid' AND e.encounter = '$encounter' AND ".
          "p.pid = e.pid");
          
	        if (empty($ferow)) {
	         
	          $invnumber = $out['our_claim_id'];
	          
	          $html .= writeTableRow('Debug', "NO ENCOUNTER #" . $encounter . " FOR pid: " . $pid . " claim id: " . $invnumber);
	          $inverror = true;
	          
	        } else {
	          $inverror = false;
	          
	          $codes = ar_get_invoice_summary($pid, $encounter, true);
	          $svcdate = substr($ferow['date'], 0, 10);
	        }
	        
	        // $html .= "<tr><td colspan=6>CODZ:<br>" . print_r($codes, true) . "</td></tr>";
	        
	        
        } else {
	        $inverror = true;
	        $html .= writeTableRow( 'Error', "Missing pid or encounter #");
        }

        // Show the claim status.
        $csc = $out['claim_status_code'];
        $inslabel = 'Ins1';
        if ($csc == '1' || $csc == '19') $inslabel = 'Ins1';
        if ($csc == '2' || $csc == '20') $inslabel = 'Ins2';
        if ($csc == '3' || $csc == '21') $inslabel = 'Ins3';
        $primary = ($inslabel == 'Ins1');
        
        $html .= writeTableRow( 'Claim Status', $csc . ":" . $claim_status_codes[$csc] . $inslabel);

        

		// Denial case, code is stored in the claims table for display 
		// in the billing manager screen with reason explained.
        if ($csc == '4') {
	        $html .= writeTableRow( 'Error', "inverror csc=4");
            $inverror = true;
            
            if (!$debug) {
                if ($pid && $encounter) {
                    $code_value = '';
                    foreach ($out['svc'] as $svc) {
                           foreach ($svc['adj'] as $adj) {//Per code and modifier the reason will be showed in the billing manager.
                                 $code_value .= $svc['code'].'_'.$svc['mod'].'_'.$adj['group_code'].'_'.$adj['reason_code'].',';
                            }
                    }
                    $code_value = substr($code_value,0,-1);
                    //We store the reason code to display it with description in the billing manager screen.
                    //process_file is used as for the denial case file name will not be there, and extra field(to store reason) can be avoided.
                    
                    updateClaim(true, $pid, $encounter, $_REQUEST['InsId'], substr($inslabel,3),7,0,$code_value);
                    
                }
            }
            
            $html .= writeTableRow( 'Error', "Not posting adjustments for denied claims, please follow up manually!");
        
        } else if ($csc == '22') {
            $inverror = true;
            $html .= writeTableRow( 'Error', "Payment reversals are not automated, please enter manually!");
        }

        if ($out['warnings']) {
            $html .= writeTableRow( 'Warnings', nl2br(rtrim($out['warnings'])));
        }

        // Simplify some claim attributes for cleaner code.
        $service_date = parse_date($out['dos']);
		$check_date      = $paydate ? $paydate : parse_date($out['check_date']);
		$production_date = $paydate ? $paydate : parse_date($out['production_date']);

		$insurance_id = arGetPayerID($pid, $service_date, substr($inslabel, 3));
		
		if (empty($ferow['lname'])) {
        	$patient_name = $out['patient_fname'] . ' ' . $out['patient_lname'];
      	} else {
        	$patient_name = $ferow['fname'] . ' ' . $ferow['lname'];
      	}

        $error = $inverror;
        
		$extra_html = "";

        // This loops once for each service item in this claim.
        foreach ($out['svc'] as $svc) {

	      // Treat a modifier in the remit data as part of the procedure key.
	      // This key will then make its way into SQL-Ledger.
	      $codekey = $svc['code'];
	      if ($svc['mod']) $codekey .= ':' . $svc['mod'];
	      $html .= writeTableRow( 'codekey', $codekey, "svc");
	      $prev = $codes[$codekey];
	      $codetype = '';
		  	
		  	// This is the main billing look up
          	if ($prev) {
	          	// This reports detail lines already on file for this service item.
                $codetype = $codes[$codekey]['code_type']; //store code type
                
                $billing_html .= write_billing_ar_record($prev, $patient_name, $invnumber, $service_date, $codekey, $bgcolor);
                
                $prevchg = sprintf("%.2f", $prev['chg'] + $prev['adj']);
       
                
                if ($prevchg != abs($svc['chg'])) {
                    $html .= writeTableRow( 'ERROR', "MAY NOT BE POSTED", "error");
                    $error = true;
                    
                    $ar_records = check_ar_by_encounter($encounter);
                    if ($ar_records) {
	                    $html .= writeTableRow('AR RECORDS', check_ar_by_encounter($encounter), "ar-records");
                    }
                    
                    
                    //$html .= writeTableRow( 'prevchg', $prevchg, "svc");
					//$html .= writeTableRow( 'svc chg', $svc['chg'], "svc"); 
                }
                
                //$html .= writeTableRow( 'code key', $codekey, "svc");
                //$html .= writeTableRow( 'service charge', $codekey . ":" . $svc['chg'], "svc"); 
                //$html .= writeTableRow( 'billing charges', $codekey . ":" . $prev['chg'], "existing");
                //$html .= writeTableRow( 'billing adj', $codekey . ":" . $prev['adj'], "existing");
                //$html .= writeTableRow( 'existing bal', floor($prev['bal']), "existing");
                
               
                unset($codes[$codekey]);
                
            } else {
                
                // THE INITIAL CHARGE
                $ar_html .= writeDetailLine($bgcolor, $class, $patient_name, $invnumber,
                    $codekey, $production_date, $description,
                    $svc['chg'], ($error ? '' : $invoice_total));
                    

            }

            $class = $error ? 'errdetail' : 'newdetail';

            // Report Allowed Amount.
            if ($svc['allowed']) {
                // A problem here is that some payers will include an adjustment
                // reflecting the allowed amount, others not.  So here we need to
                // check if the adjustment exists, and if not then create it.  We
                // assume that any nonzero CO (Contractual Obligation) or PI
        // (Payer Initiated) adjustment is good enough.
                $contract_adj = sprintf("%.2f", $svc['chg'] - $svc['allowed']);
                foreach ($svc['adj'] as $adj) {
                    if (($adj['group_code'] == 'CO' || $adj['group_code'] == 'PI') && $adj['amount'] != 0)
                        $contract_adj = 0;
                }
                if ($contract_adj > 0) {
                    $svc['adj'][] = array('group_code' => 'CO', 'reason_code' => 'A2',
                        'amount' => $contract_adj);
                }
                // $html .= writeTableRow( 'Allowed Amount', sprintf("%.2f", $svc['allowed']));
            }

            // Report miscellaneous remarks.
            if ($svc['remark']) {
                $rmk = $svc['remark'];
                $html .= writeTableRow( 'Remark Codes', $rmk . ":" . $remark_codes[$rmk]);
            }
		

            // Post and report the payment for this service item from the ERA.
            // By the way a 'Claim' level payment is probably going to be negative,
            // i.e. a payment reversal.
            if ($svc['paid']) {
	            
	            // $html .= writeTableRow( 'SVC Section', print_r($svc, true));
	            
	            $check_total += $svc['paid'];
	            
	            
	            
                if (!$error) {
	                
	                // __ACTION
            		arPostPayment($pid, $encounter,$InsertionId[$out['check_number']], $svc['paid'], $codekey, substr($inslabel,3), $out['check_number'], '',$codetype);
					
					$html .= writeTableRow( 'POSTED PAYMENT', $svc['paid']);
					 
                    $invoice_total -= $svc['paid'];
                    
                    
                } else {
	                
	                $debug_str = $debug ? "(debugging) " : "(live) ";
	                
	                $html .= writeTableRow( 'ERROR', "May not have posted $" . $svc['paid']);	                
	                $missing_post = true;
	                
	                $undistributed += $svc['paid'];
	                // $html .= writeTableRow( 'undistributed', $undistributed);
                }
                
                
                
                // $html .= writeTableRow( 'check total', $check_total);
				


                $description = "$inslabel " . $out['check_number'] . ' payment';
                if ($svc['paid'] < 0) $description .= ' reversal';
                
                // PATIENT NAME LINE
                $ar_html .= writeDetailLine($bgcolor, $class, $patient_name, $invnumber,
                    $codekey, $check_date, $description,
                    0 - $svc['paid'], ($error ? "": $invoice_total));
                    
                    
            }

            // Post and report adjustments from this ERA.  Posted adjustment reasons
            // must be 25 characters or less in order to fit on patient statements.
            foreach ($svc['adj'] as $adj) {
                $description = $adj['reason_code'] . ': ' . $adjustment_reasons[$adj['reason_code']];
               
               if ($adj['group_code'] == 'PR' || !$primary) {
                    // Group code PR is Patient Responsibility.  Enter these as zero
                    // adjustments to retain the note without crediting the claim.
                    if ($primary) {
         
                        $reason = "$inslabel ptresp: "; // Reasons should be 25 chars or less.
                        if ($adj['reason_code'] == '1') $reason = "$inslabel dedbl: ";
                        else if ($adj['reason_code'] == '2') $reason = "$inslabel coins: ";
                        else if ($adj['reason_code'] == '3') $reason = "$inslabel copay: ";
                    } else {
                   		// Non-primary insurance adjustments are garbage, either repeating
				   		// the primary or are not adjustments at all.  Report them as notes
	                    // but do not post any amounts. 
                        $reason = "$inslabel note " . $adj['reason_code'] . ': ';
                    }
                    
                    $reason .= sprintf("%.2f", $adj['amount']);
                    
                    if (!$error) {
	           
						// _ACTION      
						arPostAdjustment($pid, $encounter, $InsertionId[$out['check_number']], 0, $codekey, substr($inslabel,3), $reason, '', $codetype);
						
						$html .= writeTableRow( $class, "PATIENT RESPONSIBILITY " . $description . ' ' . sprintf("%.2f", $adj['amount']));
				   
                    }
                    
                    
                    
                } else {
	                
                    if (!$error) {
	                    // Other group codes for primary insurance are real adjustments.
	                    
						// __ACTION 
						arPostAdjustment($pid, $encounter, $InsertionId[$out['check_number']], $adj['amount'], $codekey, substr($inslabel,3), " Adjust code " . $adj['reason_code'], '', $codetype);
                
                        // $invoice_total -= $adj['amount'];
						// $html .= writeTableRow("post adjustment", $adj['amount']);
                    
						$ar_html .= writeDetailLine($bgcolor, "adjustment", $patient_name, $invnumber,
                        	$codekey, $production_date, $description,
							0 - $adj['amount'], ($error ? '' : $invoice_total));
                    
                    }   
                        
                }
            }

        } // End of service item

        // Report any existing service items not mentioned in the ERA, and
        // determine if any of them are still missing an insurance response
        // (if so, then insurance is not yet done with the claim).
        $insurance_done = true;
        
        foreach ($codes as $code => $prev) {
			// this the lesser of the two billing_ar_record calls
			$billing_html .= write_billing_ar_record($prev, $patient_name, $invnumber, $service_date, $code, $bgcolor);
            $got_response = false;
            foreach ($prev['dtl'] as $ddata) {
                if ($ddata['pmt'] || $ddata['rsn']) $got_response = true;
            }
            if (!$got_response) $insurance_done = false;
        }
        
        

        // Cleanup: If all is well, mark Ins<x> done and check for secondary billing.
        if (!$error && !$debug && $insurance_done) {
            $level_done = 0 + substr($inslabel, 3);
			
            if($out['crossover'] == 1) {
              
              
               $html .= writeTableRow('Crossover', "Payer: " . htmlspecialchars($out['payer_name'], ENT_QUOTES));
               
              // __ACTION
              if (!$debug) {
	             sqlStatement("UPDATE form_encounter SET last_level_closed = $level_done,last_level_billed=".$level_done." WHERE pid = '$pid' AND encounter = '$encounter'"); 
              }
              
              
              $html .= writeTableRow("Crossover", 'Claim processed by insurance ' . $level_done . ', automatically forwarded to Insurance '.($level_done+1) .' for processing. ');
            
            } else {
	            
	            $html .= writeTableRow('Non-Crossover Payer', htmlspecialchars($out['payer_name'], ENT_QUOTES));
	            
	          // __ACTION
	          if (!$debug) {
              	sqlStatement("UPDATE form_encounter SET last_level_closed = $level_done WHERE pid = '$pid' AND encounter = '$encounter'");
              }
              
            }
            
            // Check for secondary insurance.
            if ($primary && arGetPayerID($pid, $service_date, 2)) {
	            
              arSetupSecondary($pid, $encounter, $debug,$out['crossover']);
              
              if($out['crossover']<>1) {
                $html .= writeTableRow("", 'This claim is now re-queued for secondary paper billing');
              }
            }
            
        }
        }
        
        $posted_status = $missing_post ? "non-posted": "posted";
        
        echo "<div class='claim-section $posted_status'><table class='info-table'>";
        echo $billing_html;
        echo $ar_html;
        echo $html;
        echo "</table>";
        echo "<div data-check='" . $chk_123 . "' data-invoice='" . $invnumber . "' class='post-btn-div'><button class='post-btn'>post to account</button></div>";
        echo "</div>";
        
        $era_stack[] = $out;
    }	
  
  
  ?>