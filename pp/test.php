<?php
 //**************************************************************************
 //**************************************************************************
 // Project          :  MAYAVA FINANCE
 // Module           :  Customer class
 // Domain           :  http://wwa.mayavafinance.com
 // Programmer       :  Karthik B
 // Inprogress       :  \\offlinev5\Mayavafinance
 // Backup           :  \\dataserver\EIT\Projects\In Progress\
 // Last Modified    :  29-Oct-2011
 //**************************************************************************
 // Description      :  This is the Customer class  for Mayavafinance.
//**************************************************************************
class DailyReport {
     /*
         * Define the required fields and return text
         */
    public static function ListviewCount() 
    {
    /*
     * Get the function parameters , if exist . And make the search condition
     */
    $args = func_get_args();

    $app = $GLOBALS['app'];
   
        
        $s_branch = $args[0];
        $s_sdate = $args[1];
        $s_edate = $args[2];
		$s_sub_branch = $args[3];

       
        $search_condition .= ($s_branch != "")? "AND ( CF.branch_id  = '".$s_branch."' )  ":"";
		$search_condition .= ($s_sub_branch != "")? "AND ( P.sub_branch_id  = '".$s_sub_branch."' )  ":"";

        if($s_sdate != "" && $s_edate != ""){  
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') BETWEEN '".date("Y-m-d",strtotime($s_sdate))."' AND '".date("Y-m-d",strtotime($s_edate))."')";
        }elseif($s_sdate != "" && $s_edate == ""){  
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') >= '".date("Y-m-d",strtotime($s_sdate))."')";
        }elseif($s_sdate == "" && $s_edate != ""){ 
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') <= '".date("Y-m-d",strtotime($s_edate))."')";
        }else{
            //$search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') = '".date("Y-m-d")."')";
        }


         if(!is_superadmin($_SESSION['session_usergroupname'])){
            $search_condition .= ($_SESSION['session_user_branch_id'] != "")?" AND  CF.branch_id = '".$_SESSION['session_user_branch_id']."'":"";
         }
    /*
     * Return the Customers list
     */
        $query = "SELECT 
        
        COUNT(DISTINCT CONCAT(B.id,'-',DATE_FORMAT(CF.date_transaction,'%Y-%m-%d')))
        FROM ".CASH_FLOW_TRANSACTIONS." as CF 
        LEFT JOIN ". BRANCH." AS B ON B.id = CF.branch_id
        WHERE 1  ".$search_condition;
        $data = $app->getrow($query);

        return $count = (int)$data[0];


   }

    public static function Listview() 
    {
        /*
         * Get the function parameters , if exist . And make the search condition
         */
        //Modified by karthik
        $args = func_get_args();

        $app = $GLOBALS['app'];
        $paging = $GLOBALS['paging'];

        $s_branch = $args[0];
        $s_sdate = $args[1];
        $s_edate = $args[2];
		$s_sub_branch = $args[3];

       
        $search_condition .= ($s_branch != "")? "AND ( CF.branch_id  = '".$s_branch."' )  ":"";
//		$search_condition .= ($s_sub_branch != "")? "AND ( P.sub_branch_id  = '".$s_sub_branch."' )  ":"";

        if($s_sdate != "" && $s_edate != ""){  
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') BETWEEN '".date("Y-m-d",strtotime($s_sdate))."' AND '".date("Y-m-d",strtotime($s_edate))."')";
        }elseif($s_sdate != "" && $s_edate == ""){  
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') >= '".date("Y-m-d",strtotime($s_sdate))."')";
        }elseif($s_sdate == "" && $s_edate != ""){ 
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') <= '".date("Y-m-d",strtotime($s_edate))."')";
        }else{
            //$search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') = '".date("Y-m-d")."')";
        }


         if(!is_superadmin($_SESSION['session_usergroupname'])){
            $search_condition .= ($_SESSION['session_user_branch_id'] != "")?" AND  CF.branch_id = '".$_SESSION['session_user_branch_id']."'":"";
         }
         /*
          * Return the Customers list count
          */
        $count = self::ListviewCount($s_branch , $s_sdate,$s_edate);

        $count = self::ListviewCount($s_branch , $s_sdate,$s_edate);

        if ($count > 0) 
           {
                /*
                * Return the Customers list
                */
                Paging::set_max( $count );
                Paging::calc();// calculation for paging

                $query = "SELECT 
                CONCAT(B.id,'-',DATE_FORMAT(CF.date_transaction,'%Y-%m-%d')) AS id,
                B.id AS branch_id,
                DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') AS report_date,
                DATE_FORMAT(CF.date_transaction,'%d-%b-%Y') AS trans_date,
                SUM(P.loan_amount) AS total_pledged,
                COUNT(DISTINCT C.id) AS no_of_customer,
                COUNT(DISTINCT P.id) AS no_of_issued,
		CONCAT(
                    SB.name,' - (',SB.sub_branch_code,')') AS sub_details,
                B.name AS branch_name              
                FROM ".CASH_FLOW_TRANSACTIONS." as CF 
                LEFT JOIN ". BRANCH." AS B ON B.id = CF.branch_id
                LEFT JOIN ". PLEDGE." AS P ON CF.id = P.cash_flow_id
                LEFT JOIN ".CUSTOMERS." as C ON C.id = P.customer_id 
	        LEFT JOIN ".SUB_BRANCH." AS SB ON SB.id = P.sub_branch_id
                WHERE 1  ".$search_condition." 
                GROUP BY DATE_FORMAT(CF.date_transaction,'%Y-%m-%d'), B.id
                ORDER BY CF.date_transaction DESC ". Paging::get_sql_limit();
//                $query = "SELECT 
//                CONCAT(B.id,'-',DATE_FORMAT(CF.date_transaction,'%Y-%m-%d')) AS id,
//                B.id AS branch_id,
//                DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') AS report_date,
//                DATE_FORMAT(CF.date_transaction,'%d-%b-%Y') AS trans_date,
//                SUM(P.loan_amount) AS total_pledged,
//                COUNT(DISTINCT C.id) AS no_of_customer,
//                COUNT(DISTINCT P.id) AS no_of_issued,
//		CONCAT(
//                    SB.name,' - (',SB.sub_branch_code,')') AS sub_details,
//                B.name AS branch_name              
//                FROM ".CASH_FLOW_TRANSACTIONS." as CF 
//                LEFT JOIN ". BRANCH." AS B ON B.id = CF.branch_id
//                LEFT JOIN ". PLEDGE." AS P ON CF.id = P.cash_flow_id
//                LEFT JOIN ".CUSTOMERS." as C ON C.id = P.customer_id 
//	        LEFT JOIN ".SUB_BRANCH." AS SB ON SB.id = P.sub_branch_id
//                WHERE 1  ".$search_condition." 
//                GROUP BY DATE_FORMAT(CF.date_transaction,'%Y-%m-%d'), B.id
//                ORDER BY CF.date_transaction DESC ". Paging::get_sql_limit();
                
                //echo "<pre>"; echo $query."<hr>";die;
                return $app->getrows($query);

           }
    }   

    public static function subListviewCount() 
    {
        /*
         * Get the function parameters , if exist . And make the search condition
         */
        $args = func_get_args();

        $app = $GLOBALS['app'];
        $s_branch = $args[0];
        $s_sdate = $args[1];
        $s_edate = $args[2];

       
        $search_condition .= ($s_branch != "")? "AND ( CF.branch_id  = '".$s_branch."' )  ":"";

        if($s_sdate != "" && $s_edate != ""){  
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') BETWEEN '".date("Y-m-d",strtotime($s_sdate))."' AND '".date("Y-m-d",strtotime($s_edate))."')";
        }elseif($s_sdate != "" && $s_edate == ""){  
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') >= '".date("Y-m-d",strtotime($s_sdate))."')";
        }elseif($s_sdate == "" && $s_edate != ""){ 
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') <= '".date("Y-m-d",strtotime($s_edate))."')";
        }else{
            //$search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') = '".date("Y-m-d")."')";
        }


         if(!is_superadmin($_SESSION['session_usergroupname'])){
            $search_condition .= ($_SESSION['session_user_branch_id'] != "")?" AND  CF.branch_id = '".$_SESSION['session_user_branch_id']."'":"";
         }
        /*
         * Return the Customers list
         */
        $query = "SELECT 
                
                CF.id
                
                FROM ".CASH_FLOW_TRANSACTIONS." as CF 
                LEFT JOIN ". BRANCH." AS B ON B.id = CF.branch_id
                LEFT JOIN ". PLEDGE." AS P ON CF.id = P.cash_flow_id
                LEFT JOIN ".CUSTOMERS." as C ON C.id = P.customer_id 
                LEFT JOIN ".REDEMPTION." as PR ON CF.id = PR.cash_flow_id
                LEFT JOIN ".PRODUCT_AUCTION." as PAI ON CF.id = PAI.cash_in_id
                
                WHERE 1  ".$search_condition." 
                GROUP BY CF.cash_purpose";
        //echo $query;
        $data = $app->getrows($query);
        return $count = count($data);

     }


    public static function subListview() 
    {
            /*
             * Get the function parameters , if exist . And make the search condition
             */
            $args = func_get_args();

            $app = $GLOBALS['app'];
            $paging = $GLOBALS['paging'];  
            
        $s_branch = $args[0];
        $s_sdate = $args[1];
        $s_edate = $args[2];

        $search_condition .= ($s_branch != "")? "AND ( branch_id  = '".$s_branch."' )  ":"";

        if($s_sdate != "" && $s_edate != ""){  
            $search_condition .= " AND  ( report_date BETWEEN '".date("Y-m-d",strtotime($s_sdate))."' AND '".date("Y-m-d",strtotime($s_edate))."')";
        }elseif($s_sdate != "" && $s_edate == ""){  
            $search_condition .= " AND  ( report_date >= '".date("Y-m-d",strtotime($s_sdate))."')";
        }elseif($s_sdate == "" && $s_edate != ""){ 
            $search_condition .= " AND  ( report_date <= '".date("Y-m-d",strtotime($s_edate))."')";
        }else{
            //$search_condition .= " AND  ( report_date = '".date("Y-m-d")."')";
        }


        if(!is_superadmin($_SESSION['session_usergroupname'])){
        $search_condition .= ($_SESSION['session_user_branch_id'] != "")?" AND  branch_id = '".$_SESSION['session_user_branch_id']."'":"";
        }             
       
        $having_query = "
        HAVING 1  
        ".$search_condition." ";
        
        $where_query = "";
        
        $where_query .= ($s_branch != "")? "AND ( CF.branch_id  = '".$s_branch."' )  ":"";

        if($s_sdate != "" && $s_edate != ""){  
            $where_query .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') BETWEEN '".date("Y-m-d",strtotime($s_sdate))."' AND '".date("Y-m-d",strtotime($s_edate))."')";
        }elseif($s_sdate != "" && $s_edate == ""){  
            $where_query .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') >= '".date("Y-m-d",strtotime($s_sdate))."')";
        }elseif($s_sdate == "" && $s_edate != ""){ 
            $where_query .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') <= '".date("Y-m-d",strtotime($s_edate))."')";
        }else{
            //$search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') = '".date("Y-m-d")."')";
        }
        
        if(!is_superadmin($_SESSION['session_usergroupname'])){
            $where_query .= ($_SESSION['session_user_branch_id'] != "")?" AND  CF.branch_id = '".$_SESSION['session_user_branch_id']."'":"";
        } 
        
        $order_by = " ORDER BY branch_id,report_date,order_id,CF.id";

 
        //$aquery = self::getQuery($s_branch, $s_sdate, $s_edate);    
        //$query = $aquery['s'];
        
        
       $query = "SELECT 
         CF.id AS id,
         DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') AS report_date,
         DATE_FORMAT(CF.date_transaction,'%d-%b-%Y') AS trans_date,
         CF.id AS cash_flow_id,
         CF.parent_id AS cash_parent_id,
         1 AS order_id,
         B.id as branch_id,
         B.name as branch_name,
         CF.cash_purpose AS particulars,
         CF.opening_balance AS opening_balance,
         CF.closing_balance AS closing_balance,
         IF(CF.transaction_type = 'cashin', CF.amount, 0) AS cash_in,
         IF(CF.transaction_type = 'cashout', CF.amount, 0) AS cash_out,PA.profit_status,PA.amount_to_pay
         FROM ".CASH_FLOW_TRANSACTIONS." as CF
         LEFT JOIN ". BRANCH." AS B ON B.id = CF.branch_id
		 LEFT JOIN ". PRODUCT_AUCTION." AS PA ON PA.cash_in_id = CF.id WHERE module_type != 11 ".$having_query." ".$order_by; 
        
        
        if(is_array($query)){
        
            $result = array();
            foreach($query as $qry){
                $res = $app->getrows($qry);
                foreach($res as $r){
                    $result[] = $r;
                }
            }
        
            return $result; 
        
        }else{
 
         /*
              * Return the Customers list count
              */
            //$count = self::subListviewCount($s_branch, $s_sdate, $s_edate);
            $preresult = $app->getrows($query); 
            $result = self::tallyData($preresult);
           
            $count = count($result);
            
            if ($count > 0) 
            {
             /*
              * Return the Customers list
              */

                return $result;
            }
        }    
    }




    public static function Listexport() 
            {
                /*
                 * Get the function parameters , if exist . And make the search condition
                 */
                $args = func_get_args();
                $app = $GLOBALS['app'];
                $paging = $GLOBALS['paging'];  
                
        $s_branch = $args[0];
        $s_sdate = $args[1];
        $s_edate = $args[2];
		$s_sub_branch = $args[3];

       
       /* $search_condition .= ($s_branch != "")? "AND ( CF.branch_id  = '".$s_branch."' )  ":"";

        if($s_sdate != "" && $s_edate != ""){  
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') BETWEEN '".date("Y-m-d",strtotime($s_sdate))."' AND '".date("Y-m-d",strtotime($s_edate))."')";
        }elseif($s_sdate != "" && $s_edate == ""){  
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') >= '".date("Y-m-d",strtotime($s_sdate))."')";
        }elseif($s_sdate == "" && $s_edate != ""){ 
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') <= '".date("Y-m-d",strtotime($s_edate))."')";
        }else{
            //$search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') = '".date("Y-m-d")."')";
        }


         if(!is_superadmin($_SESSION['session_usergroupname'])){
            $search_condition .= ($_SESSION['session_user_branch_id'] != "")?" AND  CF.branch_id = '".$_SESSION['session_user_branch_id']."'":"";
         }*/
         
        $search_condition .= ($s_branch != "")? "AND ( CF.branch_id  = '".$s_branch."' )  ":"";
		$search_condition .= ($s_sub_branch != "")? "AND ( P.sub_branch_id  = '".$s_sub_branch."' )  ":"";

        if($s_sdate != "" && $s_edate != ""){  
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') BETWEEN '".date("Y-m-d",strtotime($s_sdate))."' AND '".date("Y-m-d",strtotime($s_edate))."')";
        }elseif($s_sdate != "" && $s_edate == ""){  
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') >= '".date("Y-m-d",strtotime($s_sdate))."')";
        }elseif($s_sdate == "" && $s_edate != ""){ 
            $search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') <= '".date("Y-m-d",strtotime($s_edate))."')";
        }else{
            //$search_condition .= " AND  ( DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') = '".date("Y-m-d")."')";
        }


        if(!is_superadmin($_SESSION['session_usergroupname'])){
        $search_condition .= ($_SESSION['session_user_branch_id'] != "")?" AND  CF.branch_id = '".$_SESSION['session_user_branch_id']."'":"";
        }             
       
        $having_query = "
        HAVING 1  
        ".$search_condition." ";
        
		//$order_by = "  ORDER BY CF.id DESC ";
            
                 /*
                  * Return the Customers list count
                  */
               //$count = self::subListviewCount($s_branch, $s_sdate, $s_edate);
               
                //$aquery = self::getQuery($s_branch, $s_sdate, $s_edate);   
                //$query = $aquery['s'];
                
                 $query = "SELECT 
                CONCAT(B.id,'-',DATE_FORMAT(CF.date_transaction,'%Y-%m-%d')) AS id,
                B.id AS branch_id,
                DATE_FORMAT(CF.date_transaction,'%Y-%m-%d') AS report_date,
                DATE_FORMAT(CF.date_transaction,'%d-%b-%Y') AS trans_date,
                SUM(P.loan_amount) AS total_pledged,
                COUNT(DISTINCT C.id) AS no_of_customer,
                COUNT(DISTINCT P.id) AS no_of_issued,
                B.name AS branch_name              
                FROM ".CASH_FLOW_TRANSACTIONS." as CF 
                LEFT JOIN ". BRANCH." AS B ON B.id = CF.branch_id
                LEFT JOIN ". PLEDGE." AS P ON CF.id = P.cash_flow_id
                LEFT JOIN ".CUSTOMERS." as C ON C.id = P.customer_id 
					LEFT JOIN ".SUB_BRANCH." AS SB ON SB.id = P.sub_branch_id
                WHERE 1  ".$search_condition." 
                GROUP BY DATE_FORMAT(CF.date_transaction,'%Y-%m-%d'), B.id
                ORDER BY CF.date_transaction ASC"; 
             
                if(is_array($query)){
                
                    $result = array();
                    foreach($query as $qry){
                        $res = $app->getrows($qry);
                        foreach($res as $r){
                            $result[] = $r;
                        }
                    }

                        return $result; 
                }else{
                
                //$result = $app->getrows($query);
                
                $preresult = $app->getexport($query);
				foreach($preresult as $val) {
					$search_branch_id = $val['branch_id'];
					$search_sdate = $val['trans_date'];
					$search_edate = $val['trans_date'];
				
					$result[] = self::subListview($search_branch_id,$search_sdate,$search_edate); 

				}
                //$result = self::exportData($preresult, false);
                
                
                $count = count($result);

                if ($count > 0) {
					$rtn_result = array();
					foreach($result as $array)

					{
						foreach($array as $val)
						{
							array_push($rtn_result, $val);
						}    
					}
					return array_reverse($rtn_result);		
					}

                }
            }
            
    private static function tallyData($reports, $sublist = true){
       global $app;

	   $report_count = count($reports);
          
       if($report_count > 0){

          $tally = array();
              
          $pledge_amount = 0;
          $pledge_loanids = '';
          $inipledge_loanids = '';
          $redeem_amount = 0;
          $redeem_count = 0;
          $auction_amount = 0;
          $auction_amount_profit = 0;
          $auction_amount_lose = 0;
          $interest_amount = 0;
          $prebranch_id = 0;
          $pretrans_date = $rows[0]['trans_date'];
          $total_cash_in = 0;
          $total_cash_out = 0;
          
          $other_total =  array();
          
          
          $dpi = 0;
          $di = 1;
		
          foreach($reports as $key => $rows)
          {
			
                $opening_amount = floatval($rows['opening_balance']);
                $closing_amount = floatval($rows['closing_balance']);

//		$total_cash_in += (int)$rows['cash_in'];
//                $total_cash_out += (int)$rows['cash_out'];
                $cash_purpose_open =  CASHFLOW_PURPOSE_OPENING;
                $cash_purpose_close =  CASHFLOW_PURPOSE_CLOSING;
                $cash_purpose_total =  CASHFLOW_PURPOSE_TOTAL;
                
                $cash_in = floatval($rows['cash_in']);
                $cash_out = floatval($rows['cash_out']);
                $id = $rows['report_date'];
                
                $branch_id = $rows['branch_id'];
                $trans_date = $rows['trans_date'];
                
                if($di == 1){
                
                    $order_id = '01';
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_open]['id'] = $id;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_open]['report_date'] = $rows['report_date'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_open]['trans_date'] = $rows['trans_date'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_open]['order_id'] = $order_id;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_open]['branch_id'] = $rows['branch_id'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_open]['branch_name'] = $rows['branch_name'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_open]['particulars'] = $cash_purpose_open;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_open]['cash_in'] = $opening_amount;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_open]['cash_out'] = 0;

					if($rows['particulars'] == "Auction") {
						$open_to_cal_cls = $opening_amount;
					}
                
                }
                
			  
                //if((($key+1) == $report_count && $sublist) || ($pretrans_date != $trans_date && !$sublist)){ //

				
                if(($key+1) == $report_count){ 

					if($rows['particulars'] == "Auction") {
						$closing_amount = $open_to_cal_cls+$total_cash_in;
					}

//                    $order_id = '09';
//                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['id'] = $id;
//                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['report_date'] = $rows['report_date'];
//                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['trans_date'] = $rows['trans_date'];
//                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['order_id'] = $order_id;
//                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['branch_id'] = $rows['branch_id'];
//                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['branch_name'] = $rows['branch_name'];
//                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['particulars'] = $cash_purpose_total;
//                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['cash_in'] = $total_cash_in;
//                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['cash_out'] = $total_cash_out;
                    
                    $order_id = '12';
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_close]['id'] = $id;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_close]['report_date'] = $rows['report_date'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_close]['trans_date'] = $rows['trans_date'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_close]['order_id'] = $order_id;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_close]['branch_id'] = $rows['branch_id'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_close]['branch_name'] = $rows['branch_name'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_close]['particulars'] = $cash_purpose_close;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_close]['cash_in'] = 0;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_close]['cash_out'] = $closing_amount;
                    
                
                      $pledge_amount = 0;
                      $pledge_loanids = '';
                      $redeem_amount = 0;
                      $redeem_count = 0;
                      $auction_amount = 0;
                      $auction_amount_profit = 0;
                      $auction_amount_lose = 0;
                      //$interest_amount = 0;
                      $prebranch_id = 0;
                      $pretrans_date = 0;
                      //$total_cash_in = 0;
//                      $total_cash_out = 0;

                      $dpi = 0;
                      $di = 0;
                
                }
                
                
                $loan_id = 0;
                $pquery = "SELECT id, loan_id FROM ".PLEDGE." WHERE cash_flow_id = ".$rows['cash_flow_id'];
                $pcid = $app->getrow($pquery);
                if((int)$pcid[0] > 0){
                  $cash_purpose = CASHFLOW_PURPOSE_PLEDGED;
                  $loan_id = (int)$pcid[0];
                  $loan_code = (int)$pcid[1];
                  $order_id = '02';
                  $dpi++;
                }else{
                
                    $rquery = "SELECT loan_id FROM ".REDEMPTION." WHERE cash_flow_id =  ".$rows['cash_flow_id'];
                    $rcid = $app->getrow($rquery);
                    if((int)$rcid[0] > 0){
                      $cash_purpose = CASHFLOW_PURPOSE_REDEEMED;
                      $loan_id = (int)$rcid[0];  
					  //echo "Test";
					  //echo "<br/>";
					 //echo "loan id :".$loan_id;
					 //echo "<br/>";
                     $order_id = '03';
					 if($loan_id > 0)
                {
                    $pquery1 = "SELECT loan_amount, loan_id FROM ".PLEDGE." WHERE id = ".$loan_id;
					$pr1 = $app->getrow($pquery1);   
                    $loan_amount1 += (int)$pr1[0];					
                    $loan_code1 = $pr1[1];					
                }
                    }else{

                        $apquery = "SELECT loan_id,amount_difference,profit_status,auction_amount FROM ".PRODUCT_AUCTION." WHERE cash_in_id =  ".$rows['cash_flow_id']." OR cash_in_id =  ".$rows['cash_flow_id'];
                        $apcid = $app->getrow($apquery);
						
                        if((int)$apcid[0] > 0){
                          
                          //$cash_purpose2 = CASHFLOW_PURPOSE_AUCTION_PROFIT;
                          $loan_id = (int)$apcid[0];  
                          $auction_diff_amt = $apcid[1];
                          $auc_amount += (int)$apcid[3];
                          if($auc_status == 1){  
                              $order_id = '05';  
                              $cash_purpose = CASHFLOW_PURPOSE_AUCTION;
                          }else{
                              $order_id = '07';
                              $cash_purpose = CASHFLOW_PURPOSE_AUCTION;
                          }
                          
                        }else{

                            /*$alquery = "SELECT loan_id, amount_difference, profit_status FROM ".PRODUCT_AUCTION." WHERE cash_out_id = ".$rows['cash_flow_id'];
                            $alcid = $app->getrow($alquery);
                            if((int)$alcid[0] > 0){
                              $cash_purpose = CASHFLOW_PURPOSE_AUCTION_LOSS;
                              $loan_id = (int)$alcid[0];
                              $auction_diff_amt = $apcid[1];
                              $auc_status = $apcid[3];
                              $order_id = 6;  
                            }else{*/

                                $piquery = "SELECT loan_id, interest_amount FROM ".PRODUCT_INTERESTS." WHERE cash_flow_id = ".$rows['cash_flow_id'];
                                $picid = $app->getrow($piquery);
                                if((int)$picid[0] > 0){
                                  $cash_purpose = PAY_INTEREST;
                                  $loan_id = (int)$picid[0];  
                                  $int_amt = (int)$picid[1];
                                  $order_id = '04'; 
                                }else{
                                  $cash_purpose = $rows['particulars'];
                                  $loan_id = 0;  
                                  $order_id = '08';
                                  
                                }
                            //}
                        }
                    }
                }
                
                if($loan_id > 0)
                {
                    $pquery = "SELECT loan_amount, loan_id FROM ".PLEDGE." WHERE id = ".$loan_id;
					$pr = $app->getrow($pquery);   
                    $loan_amount += (int)$pr[0];					
                    $loan_code = $pr[1];					
                }
				$different = '';
				
                switch($cash_purpose){

                    case CASHFLOW_PURPOSE_PLEDGED:
                        
                        $pledge_amount = $loan_amount;
                        if($dpi == 1)
                            $inipledge_loanids = $loan_code;
                            
                        $pledge_loanids = $inipledge_loanids.'-'.$loan_code;

                        $rows['particulars'] = $cash_purpose." (".$pledge_loanids.")";
                        $rows['cash_in'] = 0;
                        $rows['order_id'] = $order_id;
                        $rows['cash_out']= $pledge_amount;
                        
                    break;

                    case CASHFLOW_PURPOSE_REDEEMED:
					$redeem_amount = $loan_amount1;
					$redeem_count ++;
					
                        $rows['particulars'] = $cash_purpose." (".$redeem_count.")";
                        $rows['cash_in'] = $redeem_amount;
                        $rows['order_id'] = $order_id;
                        $rows['cash_out']= 0;
                    break;
					
                    case CASHFLOW_PURPOSE_AUCTION:
						

						$different = $cash_purpose; 
                        $auction_amount = $loan_amount;
						$auc_amnt = $auc_amount;
						//$auc_lamount = $rows['cash_in'] + $auc_lamount;
						//$auc_lamount   += $rows['cash_in'];
                        $auction_count ++;
						if($cash_purpose == 'Auction') 
							$cash_purpose = 'Auction Sale :- Principle';
                        $rows['particulars'] = $cash_purpose." (".$auction_count.")";
                        $rows['cash_in'] = $auction_amount;
                        $rows['order_id'] = $order_id;
                        $rows['cash_out']= 0;

						//exit;



                        /*if($auc_status == 1){
                            

                            $rows1['particulars'] = $cash_purpose2;
                            $rows1['cash_in'] = 0;
                            $rows1['order_id'] = 5;
                            $rows1['cash_out']= $auction_amount_lose;
                            if(isset($tally[$id.'_'.$cash_purpose2])){
                                $tally[$id.'_'.$cash_purpose2]['particulars'] = $rows1['particulars'];
                                $tally[$id.'_'.$cash_purpose2]['cash_in'] = $rows1['cash_in'];
                                $tally[$id.'_'.$cash_purpose2]['cash_out'] = $rows1['cash_out'];
                            }else{
                                $tally[$id.'_'.$cash_purpose2]['id'] = $id;
                                $tally[$id.'_'.$cash_purpose2]['report_date'] = $rows['report_date'];
                                $tally[$id.'_'.$cash_purpose2]['trans_date'] = $rows['trans_date'];
                                $tally[$id.'_'.$cash_purpose2]['order_id'] = $rows['order_id'];
                                $tally[$id.'_'.$cash_purpose2]['branch_id'] = $rows['branch_id'];
                                $tally[$id.'_'.$cash_purpose2]['branch_name'] = $rows['branch_name'];
                                $tally[$id.'_'.$cash_purpose2]['particulars'] = $rows1['particulars'];
                                $tally[$id.'_'.$cash_purpose2]['cash_in'] = $rows1['cash_in'];
                                $tally[$id.'_'.$cash_purpose2]['cash_out'] = $rows1['cash_out'];
                            }
                        }*/


                    break;

                    case CASHFLOW_PURPOSE_AUCTION_PROFIT:

                        $auction_amount_profit += $auction_diff_amt;
						$auc_amount += $auc_amount;
                        $order_id = '06';
                        $rows['particulars'] = $cash_purpose;
                        $rows['cash_in'] = 0;
                        $rows['order_id'] = $order_id;
                        $rows['cash_out']= $auction_amount_lose;

                    break;
                    
                    case CASHFLOW_PURPOSE_AUCTION_LOSS:

                        $auction_amount_lose += $auction_diff_amt;
                        $rows['particulars'] = $cash_purpose;
                        $rows['cash_in'] = 0;
                        $rows['order_id'] = $order_id;
                        $rows['cash_out']= $auction_amount_lose;

                    break;

                    case PAY_INTEREST:
						 
                        $interest_amount += $int_amt;
                        $rows['particulars'] = $cash_purpose;
                        $rows['cash_in'] = $interest_amount;
                        $rows['order_id'] = $order_id;
                        $rows['cash_out']= 0;

                    break;

                    default:
                        
                        $cash_in = (int)$other_total_cash_in[$id.'_'.$order_id.'_'.$cash_purpose] + $rows['cash_in']; 
                        $cash_out = (int)$other_total_cash_out[$id.'_'.$order_id.'_'.$cash_purpose] + $rows['cash_out']; 
                        
                        $other_total_cash_in[$id.'_'.$order_id.'_'.$cash_purpose] = $cash_in;
                        $other_total_cash_out[$id.'_'.$order_id.'_'.$cash_purpose] = $cash_out;
                        
                        $rows['particulars'] = $cash_purpose;
                        $rows['cash_in'] = $cash_in;
                        $rows['order_id'] = $order_id;
                        $rows['cash_out']= $cash_out;

                    break;
                }
				
				if($different == 'Auction') {
						
						
						$different = 'Auction Sale :- Margin';		
						$amount_to_pay +=  $rows['amount_to_pay'];
						
						/*echo 'auc_amnt (1) = '.$auc_amnt.'<br>';
						echo 'amount_to_pay (2) = '.$amount_to_pay.'<br>';
						echo 'auction_amount (3) = '.$auction_amount.'<br>';*/
						
						
						/*if($auc_amnt >= $amount_to_pay) {				
							$cash_in = (int)$auc_amnt - (int)$amount_to_pay;
							$cash_out = 0;
						} else {	
							//Shan
							//$cash_out = (int)$auc_amnt - (int)$auction_amount;
							$cash_out = abs((int)$auc_amnt - (int)$amount_to_pay);
							$cash_in = 0;
						}*/
                        
						//CashIn only displayed for margin

						$cash_out = 0;

						//echo $auc_amnt ." - ". $auction_amount;
						if($auction_amount >= $auc_amnt)
							$cash_in = $auction_amount - $auc_amnt;
						else
							$cash_in = $auc_amnt - $auction_amount;

						/*echo 'cash_in (1 - 2) = '.$cash_in.'<br>';
						echo 'cash_out (1 - 3) = '.$cash_out.'<br>';
						echo '---------------------------------------<br>';*/
						
						$tally[$id.'_'.$order_id.'_'.$different]['id'] = $id;
						$tally[$id.'_'.$order_id.'_'.$different]['report_date'] = $rows['report_date'];
						$tally[$id.'_'.$order_id.'_'.$different]['trans_date'] = $rows['trans_date'];
						$tally[$id.'_'.$order_id.'_'.$different]['order_id'] = $rows['order_id'];
						$tally[$id.'_'.$order_id.'_'.$different]['branch_id'] = $rows['branch_id'];
						$tally[$id.'_'.$order_id.'_'.$different]['branch_name'] = $rows['branch_name'];
						$tally[$id.'_'.$order_id.'_'.$different]['particulars'] = 'Margin ('.$auction_count.')';
						$tally[$id.'_'.$order_id.'_'.$different]['cash_in'] = $cash_in;
						$tally[$id.'_'.$order_id.'_'.$different]['cash_out'] = $cash_out;		
						$different = '';
						//Shan starts
						//$rows['cash_in'] = $auc_amnt - $cash_in;s
						/*if($cash_in != 0) {
							$rows['cash_in'] = $auc_amnt - $cash_in;
						} else {
							$rows['cash_in'] = $auc_amnt + $cash_out;
							$total_cash_in += $cash_out;
						}*/
						$rows['cash_in'] = $auction_amount;
						if($cash_out != 0) {
							$total_cash_out += $cash_out;
						}
						//Shan ends
				}

				
                if(isset($tally[$id.'_'.$order_id.'_'.$cash_purpose])){
					
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['particulars'] = $rows['particulars'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['cash_in'] = $rows['cash_in'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['cash_out'] = $rows['cash_out'];
                }else{

					//diffrent amount calculation when auction process
					
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['id'] = $id;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['report_date'] = $rows['report_date'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['trans_date'] = $rows['trans_date'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['order_id'] = $rows['order_id'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['branch_id'] = $rows['branch_id'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['branch_name'] = $rows['branch_name'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['particulars'] = $rows['particulars']; //."-".$rows['cash_flow_id'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['cash_in'] = $rows['cash_in'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose.'-'.$key]['cash_out'] = $rows['cash_out'];
                }  
              $total_cash_in += (int)$rows['cash_in'];
              $total_cash_out += (int)$rows['cash_out'];
                  
         if(($key+1) == $report_count){ 

//					if($rows['particulars'] == "Auction") {
//						$closing_amount = $open_to_cal_cls+$total_cash_in;
//					}

                    $order_id = '09';
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['id'] = $id;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['report_date'] = $rows['report_date'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['trans_date'] = $rows['trans_date'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['order_id'] = $order_id;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['branch_id'] = $rows['branch_id'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['branch_name'] = $rows['branch_name'];
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['particulars'] = $cash_purpose_total;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['cash_in'] = $total_cash_in;
                    $tally[$id.'_'.$order_id.'_'.$cash_purpose_total]['cash_out'] = $total_cash_out;
                    
                    $total_cash_in = 0;
                    $total_cash_out = 0;
                    
         }
                $prebranch_id = $rows['branch_id'];
                $pretrans_date = $rows['trans_date'];

                
                $di++;
              }
          }
//		  echo '<pre>';
//		  print_r($tally);
//		  echo '</pre>';
		  ksort($tally);
          return $tally;
    }

}
